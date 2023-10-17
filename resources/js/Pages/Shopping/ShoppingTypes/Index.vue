<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, Head } from '@inertiajs/vue3';
 
const form = useForm({
    description: ''
});


defineProps(['shopping_types']);
</script>
 
<template>
    <Head title="Shopping Types" />
 
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('shopping_types.store'), { onSuccess: () => form.reset() })">
                <div class="flex justify-between">

                    <input
                        type="text"
                        v-model="form.description"
                        placeholder="Shopping Type Name"
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
                        <th scope="col" class="px-6 py-4">Shopping Types</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="shopping_type in shopping_types"
                            :key="shopping_type.id"
                            :shopping_type="shopping_type"
                            class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
                            <td class="whitespace-nowrap px-6 py-4">{{ shopping_type.description }}</td>
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