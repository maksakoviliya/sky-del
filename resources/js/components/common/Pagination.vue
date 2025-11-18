<template>
    <div class="flex-1 flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Всего: {{ meta.total }}</span>
            </p>
        </div>
        <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <router-link
                    :to="{ name: $route.name, params: $route.params, query: {...$route.query, page: meta.current_page - 1} }"
                    :class="{'pointer-events-none opacity-50': meta.current_page === 1}"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                >
                    <span class="sr-only">Previous</span>
                    <ChevronLeftIcon class="h-5 w-5" aria-hidden="true"/>
                </router-link>

                <!-- Первая страница -->
                <router-link
                    v-if="showFirstPage"
                    :to="{ name: $route.name, params: $route.params, query: {...$route.query, page: 1} }"
                    class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                    :class="{'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': 1 === meta.current_page}"
                >
                    1
                </router-link>

                <!-- Многоточие после первой страницы -->
                <span
                    v-if="showLeftDots"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                >
                    ...
                </span>

                <!-- Основной диапазон страниц -->
                <router-link
                    v-for="page in pageRange"
                    :key="page"
                    :to="{ name: $route.name, params: $route.params, query: {...$route.query, page} }"
                    class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                    :class="{'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': page === meta.current_page}"
                >
                    {{ page }}
                </router-link>

                <!-- Многоточие перед последней страницей -->
                <span
                    v-if="showRightDots"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                >
                    ...
                </span>

                <!-- Последняя страница -->
                <router-link
                    v-if="showLastPage"
                    :to="{ name: $route.name, params: $route.params, query: {...$route.query, page: meta.last_page} }"
                    class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                    :class="{'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': meta.last_page === meta.current_page}"
                >
                    {{ meta.last_page }}
                </router-link>

                <router-link
                    :to="{ name: $route.name, params: $route.params, query: {...$route.query, page: meta.current_page + 1} }"
                    :class="{'pointer-events-none opacity-50': meta.current_page === meta.last_page}"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                >
                    <span class="sr-only">Next</span>
                    <ChevronRightIcon class="h-5 w-5" aria-hidden="true"/>
                </router-link>
            </nav>
        </div>
    </div>
</template>

<script>
import {ChevronLeftIcon, ChevronRightIcon} from "@heroicons/vue/outline";

export default {
    name: "Pagination",

    props: {
        meta: {
            required: true
        },
    },

    computed: {
        // Показывать ли первую страницу отдельно
        showFirstPage() {
            return this.pageRange[0] > 1;
        },

        // Показывать ли последнюю страницу отдельно
        showLastPage() {
            return this.pageRange[this.pageRange.length - 1] < this.meta.last_page;
        },

        // Показывать ли многоточие слева
        showLeftDots() {
            return this.pageRange[0] > 2;
        },

        // Показывать ли многоточие справа
        showRightDots() {
            return this.pageRange[this.pageRange.length - 1] < this.meta.last_page - 1;
        },

        // Диапазон отображаемых страниц
        pageRange() {
            const current = this.meta.current_page;
            const last = this.meta.last_page;
            const delta = 2; // Количество страниц слева и справа от текущей
            const range = [];

            for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
                range.push(i);
            }

            // Если диапазон пустой (мало страниц), показываем все страницы кроме первой и последней
            if (range.length === 0 && last > 1) {
                for (let i = 2; i <= last - 1; i++) {
                    range.push(i);
                }
            }

            return range;
        }
    },

    watch: {
        '$route.query.page': {
            handler: function () {
                this.$emit('pageChange')
            },
            deep: true,
        }
    },

    components: {
        ChevronLeftIcon,
        ChevronRightIcon,
    },
}
</script>