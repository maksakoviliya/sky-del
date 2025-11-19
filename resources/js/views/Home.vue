<!-- This example requires Tailwind CSS v2.0+ -->
<template>
    <div>
        <Navbar/>
        <OrdersList class="max-w-6xl mx-auto px-3 md:px-10 mt-10"/>
    </div>
</template>

<script>
import {Popover} from '@headlessui/vue'
import OrdersList from "../components/orders/OrdersList";
import Navbar from "../components/common/Navbar";

export default {
    components: {
        Popover,
        OrdersList,
        Navbar
    }
}
</script>