<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps(['totalByGroup', 'total']);

const totalByGroup = ref(props.totalByGroup);
const total = ref(props.total)

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">You're logged in!</div>
                </div>
            </div>
        </div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">Your spend this month £{{ total }}, here is breakdonw by Shopping Type:</div>
                    <div v-if="props.totalByGroup.length === 0" class="px-6 text-gray-900">Nothing to display</div>
                    <div v-for="shoppingType in props.totalByGroup" :key="shopping_type_id" class="px-6 text-gray-900">
                        {{ shoppingType.shopping_type.description }} - £ {{ shoppingType.total_price }}
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
