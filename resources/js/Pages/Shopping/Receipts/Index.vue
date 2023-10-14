<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Receipt from '@/Components/Receipt.vue';
import { useForm, Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
 
const form = useForm({
    shopping_type_id: '',
    payment_method_id: 1,
    shop_id: 1,
    price: 0,
    note: '',
    date: dayjs().format('YYYY-MM-DD')
});


defineProps(['receipts', 'shopping_types', 'payment_methods', 'shops']);


</script>
 
<template>
    <Head title="Receipts" />
 
    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('receipts.store'), { onSuccess: () => form.reset() })">
                <div class="flex justify-between">

                    <input
                        type="date"
                        v-model="form.date"
                        id="date"
                        class=" block
                                min-w-fit
                                border-gray-300
                                focus:border-indigo-300
                                focus:ring
                                focus:ring-indigo-200
                                focus:ring-opacity-50
                                rounded-md
                                shadow-sm
                                mx-2"
                    />
                    <div>
                        <select  v-model="form.shopping_type_id" id="shopping_type_id"
                            class=" block
                                    border-gray-300
                                    focus:border-indigo-300
                                    focus:ring
                                    focus:ring-indigo-200
                                    focus:ring-opacity-50
                                    rounded-md
                                    shadow-sm
                                    mx-2">
                        <option disabled value="">Select your shopping</option>
                        <option v-for="shopping_type in shopping_types" :value="shopping_type.id">{{ shopping_type.description }}</option>
                        </select>
                    </div>

                    



                    <input
                        type="number"
                        v-model="form.price"
                        step=".01"
                        placeholder="£0.00"
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

                    <div>
                        <select  v-model="form.payment_method_id" id="payment_method_id"
                            class=" block
                                    border-gray-300
                                    focus:border-indigo-300
                                    focus:ring
                                    focus:ring-indigo-200
                                    focus:ring-opacity-50
                                    rounded-md
                                    shadow-sm
                                    mx-2">
                        <option v-for="payment_method in payment_methods" :value="payment_method.id">{{ payment_method.name }}</option>
                        </select>
                    </div>

                    <div>
                        <select  v-model="form.shop_id" id="shop_id"
                            class=" block
                                    border-gray-300
                                    focus:border-indigo-300
                                    focus:ring
                                    focus:ring-indigo-200
                                    focus:ring-opacity-50
                                    rounded-md
                                    shadow-sm
                                    mx-2">
                        <option v-for="shop in shops" :value="shop.id">{{ shop.name }}</option>
                        </select>
                    </div>

                    <input
                        type="text"
                        v-model="form.note"
                        placeholder="Notes"
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
                        <th scope="col" class="px-6 py-4">Date</th>
                        <th scope="col" class="px-6 py-4">Shopping Type</th>
                        <th scope="col" class="px-6 py-4">Price</th>
                        <th scope="col" class="px-6 py-4">Payment Method</th>
                        <th scope="col" class="px-6 py-4">Shop</th>
                        <th scope="col" class="px-6 py-4">Note</th>
                        <th scope="col" class="px-6 py-4">Added By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <Receipt
                            v-for="receipt in receipts"
                            :key="receipt.id"
                            :receipt="receipt"
                        />

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