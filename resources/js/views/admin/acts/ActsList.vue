<template>
    <div class="py-8 px-3 md:px-12 w-full h-full">

        <div>
            <h1 class="text-gray-900 font-semibold text-2xl">Список актов приема-передачи</h1>
        </div>
        <!--    <div-->
        <!--        class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg px-6 py-4 bg-white mt-5 flex gap-4 justify-between items-center">-->
        <!--      Тут будет фильтр-->
        <!--    </div>-->
        <div class="flex flex-col mt-5">
            <div class="mt-5">
                <div v-if="acts && acts.length" class="shadow overflow-x-auto border-b border-gray-200 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Контрагент
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Дата
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Сумма
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Количество
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Вес
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Заказы
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                            </th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="act in acts" :key="act.id">
                            <td class="px-6 py-2 whitespace-nowrap">
                                <span class="text-gray-400">#</span>{{ act.id }}
                            </td>
                            <td class="px-4 py-2">
                                <div class="text-xs text-gray-500">
                                    {{ act.client && act.client.company ? act.client.company.title : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-2">
                                <div class="text-xs text-gray-500">
                                    {{ parseDate(act.created_at, 'dd.MM.yy') }}
                                </div>
                            </td>
                            <td class="px-6 py-2">
                                <div class="text-xs text-gray-500" v-if="act.totalPrice">
                                    {{ act.totalPrice }}₽
                                </div>
                            </td>
                            <td class="px-6 py-2">
                                <div class="text-xs text-gray-500" v-if="act.totalQuantity">
                                    {{ act.totalQuantity }} шт.
                                </div>
                            </td>
                            <td class="px-6 py-2">
                                <div class="text-xs text-gray-500" v-if="act.totalWeight">
                                    {{ act.totalWeight }} кг.
                                </div>
                            </td>
                            <td class="px-6 py-2">
                                <div class="text-xs text-gray-500">
                                    {{ act.orders_count }}
                                </div>
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex gap-2 justify-end">
                                    <svg class="animate-spin h-4 w-4 text-indigo-600"
                                         v-if="loading && loadingItem && loadingItem === act.id"
                                         xmlns="http://www.w3.org/2000/svg"
                                         fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <button @click="handlePrint(act.id)"
                                            v-else
                                            class="text-indigo-600 hover:text-indigo-900">
                                        <DownloadIcon class="w-4 h-4"/>
                                    </button>
                                    <button @click="showModalDeleteForm(act)"
                                            class="text-red-600 hover:text-red-900">
                                        <TrashIcon class="w-4 h-4"/>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                        <tr>
                            <th colspan="9" scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <Pagination v-if="acts && acts.length && actsMeta" :meta="actsMeta"
                                            @pageChange="fetchActs"/>
                            </th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="text-3xl my-16 font-semibold text-gray-300 text-center" v-else>
                    Актов приема-передачи пока нет
                </div>
            </div>
        </div>

        <!--    <OrderForm :key="$route.params.id"/>-->

        <DeleteConfirmation :open="showDeleteConfirmation" @submit="handleDelete" @close="handleCloseDeleteForm">
            <template v-slot:title>Удаление заказа!</template>
            <template v-slot:description>Вы действительно хотите безвозвратно удалить заказ? Заказ также перестанет
                отображаться и у клиента.
            </template>
        </DeleteConfirmation>

    </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";
import {DateTime} from "luxon";
import {CheckIcon, DownloadIcon, PencilAltIcon, TrashIcon} from "@heroicons/vue/outline";
import DeleteConfirmation from "../../../components/orders/DeleteConfirmation";
import ApiService from "../../../services/ApiService";
import {getError} from "../../../utils/helpers";
import CommonSelect from "../../../components/common/CommonSelect";
import {Form} from "vee-validate";
import CommonButton from "../../../components/common/CommonButton";
import Pagination from "../../../components/common/Pagination";

export default {
    name: "ActsList",

    components: {
        PencilAltIcon,
        TrashIcon,
        DeleteConfirmation,
        CommonSelect,
        Form,
        CheckIcon,
        CommonButton,
        DownloadIcon,
        Pagination
    },

    computed: {
        ...mapGetters({
            acts: "act/acts",
            actsMeta: "act/actsMeta"
        })
    },

    data() {
        return {
            loading: false,
            loadingItem: null,
            showDeleteConfirmation: false,
            deletingItem: null,
        }
    },

    methods: {
        ...mapActions({
            fetchActs: "act/fetchActs",
            downloadAct: "act/downloadAct"
        }),
        parseDate(date) {
            return DateTime.fromISO(date).toFormat('dd.MM.yy HH:mm')
        },
        showModalDeleteForm(client) {
            this.deletingItem = client
            this.showDeleteConfirmation = true
        },
        handleCloseDeleteForm() {
            this.deletingItem = null
            this.showDeleteConfirmation = false
        },
        handleDelete() {
            ApiService.removeAct(this.deletingItem.id).then(async () => {
                this.$notify({
                    type: 'warning',
                    title: 'Данные актов обновлены'
                })
                this.handleCloseDeleteForm()
                await this.fetchActs()
            })
                .catch((error) => {
                    this.$notify({
                        type: 'error',
                        title: 'Возникла ошибка!',
                    })
                    const errors = getError(error)
                    console.log('errors', errors)
                });
        },
        async handlePrint(act_id) {
            this.loading = true
            this.loadingItem = act_id
            this.downloadAct(act_id).then((res) => {
                let fileURL = window.URL.createObjectURL(new Blob([res.data]));
                let fileLink = document.createElement('a');

                fileLink.href = fileURL;
                fileLink.setAttribute('download', res.headers['content-disposition'].split('filename=')[1].split('"')[1]);
                document.body.appendChild(fileLink);

                fileLink.click();
            }).finally(() => {
                this.loading = false
                this.loadingItem = null
            })
        }
    },

    async mounted() {
        await this.fetchActs()
    }
}
</script>
