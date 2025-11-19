<script setup>
import CommonDatepicker from "../../../components/common/CommonDatepicker.vue";
import {Form} from "vee-validate";
import * as yup from "yup";
import {DateTime} from "luxon";
import CommonButton from "../../../components/common/CommonButton.vue";
import {useRoute} from "vue-router";
import { useStore } from 'vuex'
import {computed} from "vue";

const schema = yup.object({
    start: yup.string().required(),
    finish: yup.string().required(),
    client_id: yup.string().required()
});

const route = useRoute();

const data = {
    start: DateTime.now().minus({months: 1}),
    finish: DateTime.now(),
    client_id: route.params.id
}

const store = useStore()
const loading = computed(() => store.state.load)
const onSubmit = async (values) => {
    const response = await store.dispatch('client/exportOrders', values)
    const blob = new Blob([response.data])

    const disposition = response.headers['content-disposition']
    const fileNameMatch = disposition && disposition.match(/filename="?([^"]+)"?/)
    const fileName = fileNameMatch ? fileNameMatch[1] : `orders_${values.start}_${values.finish}.xlsx`

    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = fileName

    document.body.appendChild(link)
    link.click()
    link.remove()
}
</script>

<template>
    <div class="py-8 px-3 md:px-12 w-full h-full relative">

        <div>
            <h1 class="text-gray-900 font-semibold text-2xl">Экспорт заказов</h1>
        </div>

        <Form
            @submit="onSubmit"
            :validation-schema="schema"
            v-slot="{ values, setFieldValue }"
            :initial-values="data"
            class="flex items-end mt-10 gap-8">

            <div class="absolute inset-0 bg-white opacity-40 z-20 flex flex-col items-center justify-center" v-if="loading">
                <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
            <CommonDatepicker name="start" key="start" label="Начальная дата" :min-date="DateTime.now().minus({years: 5}).toJSDate()"
                             v-model="values.start"/>
            <CommonDatepicker name="finish" key="finish" label="Конечная дата" :min-date="DateTime.now().minus({years: 5}).toJSDate()"
                              v-model="values.finish"/>
            <CommonButton class="whitespace-nowrap" color="success" type="submit">
                Экспортировать
            </CommonButton>
        </Form>
    </div>
</template>