<script>
import {Dialog, DialogOverlay, TransitionChild, TransitionRoot} from "@headlessui/vue";
import CommonButton from "../common/CommonButton";
import {mapActions, mapGetters, mapMutations} from "vuex";
import {DocumentDownloadIcon} from '@heroicons/vue/outline'
import store from "../../store";
import log from "tailwindcss/lib/util/log";

export default {
    name: "OrdersSelectedActions",

    components: {
        TransitionChild,
        TransitionRoot,
        CommonButton,
        Dialog,
        DialogOverlay,
        DocumentDownloadIcon
    },

    props: {
        open: {
            required: true
        },
        actions: {
            type: Array,
            default: () => {
                return ['act']
            }
        }
    },

    computed: {
        ...mapGetters({
            selectedOrders: "order/selectedOrders"
        })
    },

    methods: {
        ...mapMutations({
            setSelectedOrders: "order/SET_SELECTED_ORDERS"
        }),
        ...mapActions({
            createAct: "act/createAct",
            fetchOrders: "order/fetchOrders",
            ordersPay: "order/ordersPay",
        }),
        handleGenerateAct() {
            this.createAct({
                acts: this.selectedOrders.map(item => item.id)
            }).then(() => {
                this.$notify({
                    type: 'success',
                    text: 'Акт приема-передачи учпешно создан'
                })
                this.setSelectedOrders([])
                this.fetchOrders()
            })
        },
        handlePay() {
            this.ordersPay({
                orders: this.selectedOrders.map(item => item.id)
            }).then(() => {
                this.$notify({
                    type: 'success',
                    text: 'Выбранные заказы оплачены'
                })
                this.setSelectedOrders([])
                this.fetchOrders()
            })
        },
        async handleExport() {
            try {
                const response = await store.dispatch('order/exportOrders', this.selectedOrders)
                console.log('Res', response)    
                if (!response) {
                    throw Error("Unable to export orders");
                }
                const blob = new Blob([response.data])

                const disposition = response.headers['content-disposition']
                const fileNameMatch = disposition && disposition.match(/filename="?([^"]+)"?/)
                const fileName = fileNameMatch ? fileNameMatch[1] : `orders_export.xlsx`

                const url = URL.createObjectURL(blob)
                const link = document.createElement('a')
                link.href = url
                link.download = fileName

                document.body.appendChild(link)
                link.click()
                link.remove()
            } catch (e) {
                this.$notify({
                    type: 'error',
                    text: e.message
                })
                console.error('Error: ', e)
            }
        }
    }
}
</script>

<template>
    <TransitionRoot as="template" :show="open" enter="ease-out duration-300"
                    enter-from="opacity-0 -translate-y-4 sm:scale-95"
                    enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                    leave-from="opacity-100 translate-y-0 sm:scale-100"
                    leave-to="opacity-0 -translate-y-4 sm:scale-95">
        <div class="fixed z-10 inset-x-0 top-16 mt-1">
            <div
                class="flex mx-auto align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full px-4 py-4 items-center justify-between">
                <div class="text-gray-700 text-sm">
                    Выбрано заказов: <span class="text-gray-900 font-semibold">{{ selectedOrders.length }}</span>
                </div>

                <div class="flex gap-2 items-center">
                    <CommonButton type="button" color="success" @click="handleExport">
                        <DocumentDownloadIcon class="w-4 h-4" />
                        Export
                    </CommonButton>
                    <CommonButton type="button" color="primary" @click="handleGenerateAct"
                                  v-if="actions.includes('act')">
                        Сформировать АПП
                    </CommonButton>
                    <CommonButton type="button" color="primary" @click="handlePay" v-if="actions.includes('pay')">
                        Оплатить
                    </CommonButton>
                </div>
            </div>
        </div>
    </TransitionRoot>
</template>