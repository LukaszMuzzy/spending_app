<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, Head } from '@inertiajs/vue3';
 
const form = useForm({
    name: ''
});


defineProps(['shops']);


</script>
 
<template>
    <Head title="Shops" />
 
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('shops.store'), { onSuccess: () => form.reset() })">
                <div class="flex justify-between">

                    <input
                        type="text"
                        v-model="form.name"
                        placeholder="Shop Name"
                        class=" block
                                border-gray-300
                                focus:border-indigo-300
                                focus:ring
                                focus:ring-indigo-200
                                focus:ring-opacity-50
                                rounded-md
                                shadow-sm
                                mx-2"
                    />

                    <InputError :message="form.errors.message" class="mt-2" />
                    
                </div>
                <div class="flex justify-end">
                    <PrimaryButton class="mt-4">Add</PrimaryButton>                    
                </div>
            </form>

            <div class="mt-6 bg-white shadow-sm rounded-lg divide-y">
                <div class="flex flex-col">
        <div class="overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 sm:px-6 lg:px-8">
                <div class="overflow-hidden">
                    <table class="min-w-full text-left text-sm font-light">
                    <thead class="border-b font-medium dark:border-neutral-500">
                        <tr>
                        <th scope="col" class="px-6 py-4">Shop</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="shop in shops"
                            :key="shop.id"
                            :shop="shop"
                            class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
                            <td class="whitespace-nowrap px-6 py-4">{{ shop.name }}</td>
                        </tr>

                    </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
 
            </div>
        </div>
    </AuthenticatedLayout>
</template>