<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm, Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';

const form = useForm({
    name: '',
    payment_date: '',
    end_date: '',
    frequency: '',
    price: '',
    is_active: true,
    shopping_group_id: '',
    shop_id: '',
    payment_method_id: '',
    shopping_type_id: ''
});

const props = defineProps(['dd_payments', 'shopping_groups', 'shops', 'payment_methods', 'shopping_types']);

</script>

<template>
    <Head title="DD Payments" />

    <AuthenticatedLayout>
        <div class="max-w-6xl mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('dd_payments.store'), { onSuccess: () => form.reset() })">
                <div class="flex flex-col space-y-4">
                    <div class="flex space-x-4">
                        <input
                            type="text"
                            v-model="form.name"
                            placeholder="Payment Name"
                            class="block w-1/2 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        />

                        <select
                            v-model="form.shopping_group_id"
                            class="block w-1/2 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        >
                            <option value="" disabled>Select Shopping Group</option>
                            <option v-for="group in shopping_groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                        </select>
                    </div>

                    <div class="flex space-x-4">
                        <input
                            type="date"
                            v-model="form.payment_date"
                            placeholder="Start Date"
                            class="block w-1/2 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        />

                        <input
                            type="date"
                            v-model="form.end_date"
                            placeholder="End Date"
                            class="block w-1/2 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        />
                    </div>

                    <div class="flex space-x-4">
                        <select
                            v-model="form.frequency"
                            class="block w-1/2 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        >
                            <option value="" disabled>Select Frequency</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="fortnightly">Fortnightly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="half-yearly">Half-Yearly</option>
                            <option value="yearly">Yearly</option>
                        </select>

                        <input
                            type="float"
                            v-model="form.price"
                            placeholder="Price"
                            class="block w-1/2 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        />
                    </div>

                    <div class="flex space-x-4">
                        <select
                            v-model="form.shop_id"
                            class="block w-1/3 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        >
                            <option value="" disabled>Select Shop</option>
                            <option v-for="shop in shops" :key="shop.id" :value="shop.id">{{ shop.name }}</option>
                        </select>

                        <select
                            v-model="form.payment_method_id"
                            class="block w-1/3 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        >
                            <option value="" disabled>Select Payment Method</option>
                            <option v-for="paymentMethod in payment_methods" :key="paymentMethod.id" :value="paymentMethod.id">{{ paymentMethod.name }}</option>
                        </select>

                        <select
                            v-model="form.shopping_type_id"
                            class="block w-1/3 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                        >
                            <option value="" disabled>Select Shopping Type</option>
                            <option v-for="shoppingType in shopping_types" :key="shoppingType.id" :value="shoppingType.id">{{ shoppingType.description }}</option>
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input
                            type="checkbox"
                            v-model="form.is_active"
                            class="mr-2"
                        />
                        <label for="is_active">Is Active</label>
                    </div>

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
                                            <th scope="col" class="px-6 py-4">Payment Name</th>
                                            <th scope="col" class="px-6 py-4">Payment Date</th>
                                            <th scope="col" class="px-6 py-4">End Date</th>
                                            <th scope="col" class="px-6 py-4">Frequency</th>
                                            <th scope="col" class="px-6 py-4">Price</th>
                                            <th scope="col" class="px-6 py-4">Is Active</th>
                                            <th scope="col" class="px-6 py-4">Shopping Group</th>
                                            <th scope="col" class="px-6 py-4">Shop</th>
                                            <th scope="col" class="px-6 py-4">Payment Method</th>
                                            <th scope="col" class="px-6 py-4">Shopping Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="dd_payment in dd_payments"
                                            :key="dd_payment.id"
                                            class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
                                            <td class="whitespace-nowrap px-6 py-4">{{ dd_payment.name }}</td>
                                            <td class="whitespace-nowrap px-6 py-4">{{ dayjs(dd_payment.payment_date).format('DD-MM-YYYY') }}</td>
                                            <td class="whitespace-nowrap px-6 py-4">{{ dayjs(dd_payment.end_date).format('DD-MM-YYYY') }}</td>
                                            <td class="whitespace-nowrap px-6 py-4">{{ dd_payment.frequency }}</td>
                                            <td class="whitespace-nowrap px-6 py-4">{{ dd_payment.price }}</td>
                                            <td class="whitespace-nowrap px-6 py-4">{{ dd_payment.is_active ? 'Yes' : 'No' }}</td>
                                            <td class="whitespace-nowrap px-6 py-4">{{ dd_payment.shopping_group.name }}</td>
                                            <td class="whitespace-nowrap px-6 py-4">{{ dd_payment.shop.name }}</td>
                                            <td class="whitespace-nowrap px-6 py-4">{{ dd_payment.payment_method.name }}</td>
                                            <td class="whitespace-nowrap px-6 py-4">{{ dd_payment.shopping_type.description }}</td>
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