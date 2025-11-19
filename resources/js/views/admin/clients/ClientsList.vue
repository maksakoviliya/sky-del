<template>
    <div class="py-8 px-3 md:px-12 w-full h-full">
        <div>
            <h1 class="text-gray-900 font-semibold text-2xl">Список клиентов</h1>
        </div>
        <div
            class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg px-6 py-4 bg-white mt-5 flex gap-4 justify-between items-center">
            <CommonButton class="whitespace-nowrap ml-auto" color="success" component="router-link"
                          :to="{name: $route.name, params: {id: 'create'}, query: $route.query}">
                <template v-slot:icon>
                    <PlusIcon class="-ml-1 mr-2 h-4 w-4"></PlusIcon>
                </template>
                Новый клиент
            </CommonButton>
        </div>
        <div class="flex flex-col mt-5">
            <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="py-2 align-bottom inline-block min-w-full sm:px-6 lg:px-8">
                    <div v-if="clients.length" class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    ID
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Компания
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Имя
                                </th>
                                <!--                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">-->
                                <!--                  Контакты-->
                                <!--                </th>-->
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Заказы
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Тариф
                                </th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Edit</span>
                                </th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="client in clients" :key="client.id">
                                <td class="px-6 py-2 whitespace-nowrap">
                                    {{ client.id }}
                                </td>
                                <!--                <td class="px-6 py-2">-->
                                <!--                  <div class="text-xs text-gray-500">{{ client.name }}</div>-->
                                <!--                </td>-->
                                <td class="px-6 py-2">
                                    <div class="text-xs text-gray-500">{{
                                            client.company ? client.company.title : ''
                                        }}
                                    </div>
                                </td>
                                <td class="px-6 py-2">
                                    <div class="text-xs text-gray-500">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ client.name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ client.phone }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-2">
                                    <div class="text-xs text-gray-500">{{ client.orders_count }}</div>
                                </td>
                                <td class="px-6 py-2">
                                    <div class="text-xs text-gray-500">
                                        <Popper>
                                            <button>{{
                                                    client.tarif ? `${client.tarif.foot_today}₽ | ${client.tarif.foot}₽ | ${client.tarif.car_today}₽ | ${client.tarif.car}₽` : ''
                                                }}
                                            </button>

                                            <template #content v-if="client.tarif">
                                                <dl class="bg-white rounded-lg shadow-lg p-4">
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <dt>Пешком сеогдня:</dt>
                                                        <dd>{{ client.tarif.foot_today }}₽</dd>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <dt>Пешком:</dt>
                                                        <dd>{{ client.tarif.foot }}₽</dd>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <dt>На авто сегодня:</dt>
                                                        <dd>{{ client.tarif.car_today }}₽</dd>
                                                    </div>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <dt>На авто:</dt>
                                                        <dd>{{ client.tarif.car }}₽</dd>
                                                    </div>
                                                </dl>
                                            </template>
                                        </Popper>
                                    </div>
                                </td>
                                <td class="px-6 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex gap-2">
                                        <router-link class="text-green-600 hover:text-green-900"
                                                     :to="{name: 'export', params: { id: client.id }}"
                                        >
                                            <DocumentDownloadIcon class="w-4 h-4"/>
                                        </router-link>
                                        <router-link
                                            :to="{name: $route.name, params: { id: client.id }, query: $route.query}"
                                            class="text-indigo-600 hover:text-indigo-900">
                                            <PencilAltIcon class="w-4 h-4"/>
                                        </router-link>
                                        <button @click="showModalDeleteForm(client)"
                                                class="text-red-600 hover:text-red-900">
                                            <TrashIcon class="w-4 h-4"/>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                            <!--              <tfoot class="bg-gray-50">-->
                            <!--              <tr>-->
                            <!--                <th colspan="9" scope="col"-->
                            <!--                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">-->
                            <!--                  Тут будет пагинация-->
                            <!--                </th>-->
                            <!--              </tr>-->
                            <!--              </tfoot>-->
                        </table>
                    </div>
                    <div class="text-3xl my-16 font-semibold text-gray-300 text-center" v-else>
                        Клиентов пока нет
                    </div>
                </div>
            </div>
        </div>

        <ClientForm :key="$route.params.id"/>

        <DeleteConfirmation :open="showDeleteConfirmation" @submit="handleDelete" @close="handleCloseDeleteForm">
            <template v-slot:title>Удаление пользователя!</template>
            <template v-slot:description>Вы действительно хотите безвозвратно удалить пользователя?</template>
        </DeleteConfirmation>

    </div>
</template>

<script>
import CommonButton from "../../../components/common/CommonButton";
import {mapActions, mapGetters} from "vuex";
import {PencilAltIcon, PlusIcon, TrashIcon, DocumentDownloadIcon} from "@heroicons/vue/outline";
import ClientForm from "./ClientForm";
import Popper from "vue3-popper";
import DeleteConfirmation from "../../../components/orders/DeleteConfirmation";
import ApiService from "../../../services/ApiService";
import {getError} from "../../../utils/helpers";

export default {
    name: "ClientsList",

    components: {
        CommonButton,
        PlusIcon,
        PencilAltIcon,
        TrashIcon,
        DocumentDownloadIcon,
        ClientForm,
        Popper,
        DeleteConfirmation
    },

    data() {
        return {
            showDeleteConfirmation: false,
            deletingItem: null
        }
    },

    computed: {
        ...mapGetters({
            clients: "client/clients"
        })
    },

    methods: {
        ...mapActions({
            fetchClients: "client/fetchClients"
        }),
        showModalDeleteForm(client) {
            this.deletingItem = client
            this.showDeleteConfirmation = true
        },
        handleCloseDeleteForm() {
            this.deletingItem = null
            this.showDeleteConfirmation = false
        },
        handleDelete() {
            ApiService.removeClient(this.deletingItem.id).then(async () => {
                this.$notify({
                    type: 'warning',
                    title: 'Данные клиентов обновлены'
                })
                await this.fetchClients()
                this.handleCloseDeleteForm()
            })
                .catch((error) => {
                    const errors = getError(error)
                    actions.setErrors(errors);
                    this.$notify({
                        type: 'error',
                        title: 'Возникла ошибка!',
                    })
                });
        },
    },

    async mounted() {
        await this.fetchClients()
    }
}
</script>