<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Receipt from '@/Components/Receipt.vue';
import { useForm, Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import {ref} from 'vue';
 
const props = defineProps(['receipts', 'shopping_types', 'payment_methods', 'shops', 'shopping_groups']);

let currentReceipts = ref(props.receipts);

const form = useForm({
    shopping_type_id: '',
    payment_method_id: '',
    shop_id: '',
    price: null,
    note: '',
    date: dayjs().format('YYYY-MM-DD'),
    shopping_group_id: ''
});


function handleEditReceipt(editedReciept) {
    console.log('Edit Parent')
    console.log(editedReciept);
    const originalReceipt = currentReceipts.value.find( receipt => receipt.id === editedReciept.id)
    const url = route('receipts.update', {receipt: 100})

    const editedReceiptForm = useForm({
        shopping_type_id: editedReciept.shopping_type_id,
        payment_method_id: editedReciept.payment_method_id,
        shop_id: editedReciept.shop_id,
        price: editedReciept.price,
        note: editedReciept.note,
        date: editedReciept.date,
        shopping_group_id: editedReciept.shopping_group_id
    });


    let isEdited = async () => {

        editedReceiptForm.put(url, {
            onSuccess: () => editedReceiptForm.reset()
        })
        // console.log(response);
    }
    isEdited();
}




</script>
 
<template>
    <Head title="Receipts" />
 
    <AuthenticatedLayout>
        <div class="mx-auto p-4 sm:p-6 lg:p-8">
            <form @submit.prevent="form.post(route('receipts.store'), { onSuccess: () => form.reset() })">
                <div class="flex flex-wrap justify-between items-center">
                    
                    <div>

                        <label class="block mx-2 px-3" for="date" name="date">Date:</label>
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
                    </div>
                    <div>
                        <label class="block mx-2 px-3" for="shopping_type_id" name="shopping_type_id">Shopping Type:</label>
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
                        <option disabled value="">Shoping Type</option>
                        <option v-for="shopping_type in shopping_types" :value="shopping_type.id">{{ shopping_type.description }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mx-2 px-3" for="price" name="price">Price:</label>

                        <input
                            type="number"
                            id="price"
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
                                    h-fit
                                    mx-2"
                        />
                    </div>

                    <div>
                        <label class="block mx-2 px-3 w-fit" for="payment_method_id" name="payment_method_id">Payment:</label>

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
                        <option disabled value="">Payment method</option>
                        <option v-for="payment_method in payment_methods" :value="payment_method.id">{{ payment_method.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mx-2 px-3 w-fit" for="shop_id" name="shop_id">Shop:</label>

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
                        <option disabled value="">Shop</option>
                        <option v-for="shop in shops" :value="shop.id">{{ shop.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mx-2 px-3 w-fit" for="shopping_group_id" name="shopping_group_id">Group:</label>

                        <select  v-model="form.shopping_group_id" id="shopping_group_id"
                            class=" block
                                    border-gray-300
                                    focus:border-indigo-300
                                    focus:ring
                                    focus:ring-indigo-200
                                    focus:ring-opacity-50
                                    rounded-md
                                    shadow-sm
                                    mx-2">
                        <option disabled value="">Shoping Group</option>
                        <option v-for="shopping_group in shopping_groups" :value="shopping_group.id">{{ shopping_group.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block mx-2 px-3 w-fit" for="note" name="note">Notes:</label>

                        <input
                            type="text"
                            id="note"
                            v-model="form.note"
                            placeholder="Notes"
                            class=" block
                                    border-gray-300Update Google Calendar API
                                    
                                    
                                    focus:border-indigo-300
                                    focus:ring
                                    focus:ring-indigo-200
                                    focus:ring-opacity-50
                                    rounded-md
                                    shadow-sm
                                    h-fit
                                    mx-2"
                        />

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
                        <th scope="col" class="px-6 py-4">Date</th>
                        <th scope="col" class="px-6 py-4">Shopping Type</th>
                        <th scope="col" class="px-6 py-4">Price</th>
                        <th scope="col" class="px-6 py-4">Payment Method</th>
                        <th scope="col" class="px-6 py-4">Shop</th>
                        <th scope="col" class="px-6 py-4">Shopping Group</th>
                        <th scope="col" class="px-6 py-4">Note</th>
                        <th scope="col" class="px-6 py-4">Added By</th>
                        <th scope="col" class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <Receipt
                            v-for="receipt in receipts"
                            :key="receipt.id"
                            :receipt="receipt"
                            @submitEditReceipt="handleEditReceipt"
                            
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