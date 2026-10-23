<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BillRow from '@/Components/BillRow.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    dd_payments: Array,
    shopping_groups: Array,
    shops: Array,
    payment_methods: Array,
    shopping_types: Array,
});

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
    shopping_type_id: '',
});

const inputClass =
    'block w-full rounded-xl border-border shadow-sm focus:border-accent focus:ring-accent';

function handleEdit(edited) {
    const editForm = useForm({
        name: edited.name,
        payment_date: edited.payment_date,
        end_date: edited.end_date || null,
        frequency: edited.frequency,
        price: edited.price,
        is_active: !!edited.is_active,
        shopping_group_id: edited.shopping_group_id,
        shop_id: edited.shop_id,
        payment_method_id: edited.payment_method_id,
        shopping_type_id: edited.shopping_type_id,
    });

    editForm.put(route('dd_payments.update', edited.id));
}

function handleDelete(payment) {
    if (confirm(`Delete bill “${payment.name}”?`)) {
        router.delete(route('dd_payments.destroy', payment.id));
    }
}
</script>

<template>
    <Head title="Bills" />

    <AuthenticatedLayout>
        <PageHeader
            title="Bills"
            description="Recurring direct debits feed the overview forecast for days still left in the month. Edit a row to change amount, schedule, or pause it."
        />

        <Card>
            <h2 class="text-lg font-semibold text-ink">Add bill</h2>
            <form
                class="mt-4 grid gap-4 sm:grid-cols-2"
                @submit.prevent="form.post(route('dd_payments.store'), { onSuccess: () => form.reset() })"
            >
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Name</label>
                    <input v-model="form.name" type="text" :class="inputClass" required />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Group</label>
                    <select v-model="form.shopping_group_id" :class="inputClass" required>
                        <option value="" disabled>Select group</option>
                        <option v-for="group in shopping_groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </select>
                    <InputError :message="form.errors.shopping_group_id" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Next payment date</label>
                    <input v-model="form.payment_date" type="date" :class="inputClass" required />
                    <InputError :message="form.errors.payment_date" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">End date</label>
                    <input v-model="form.end_date" type="date" :class="inputClass" />
                    <InputError :message="form.errors.end_date" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Frequency</label>
                    <select v-model="form.frequency" :class="inputClass" required>
                        <option value="" disabled>Select frequency</option>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="fortnightly">Fortnightly</option>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="half-yearly">Half-yearly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                    <InputError :message="form.errors.frequency" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Amount</label>
                    <input v-model="form.price" type="number" step="0.01" min="0" :class="inputClass" required />
                    <InputError :message="form.errors.price" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Shop</label>
                    <select v-model="form.shop_id" :class="inputClass" required>
                        <option value="" disabled>Select shop</option>
                        <option v-for="shop in shops" :key="shop.id" :value="shop.id">{{ shop.name }}</option>
                    </select>
                    <InputError :message="form.errors.shop_id" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Payment method</label>
                    <select v-model="form.payment_method_id" :class="inputClass" required>
                        <option value="" disabled>Select payment</option>
                        <option v-for="method in payment_methods" :key="method.id" :value="method.id">{{ method.name }}</option>
                    </select>
                    <InputError :message="form.errors.payment_method_id" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Category</label>
                    <select v-model="form.shopping_type_id" :class="inputClass" required>
                        <option value="" disabled>Select category</option>
                        <option v-for="type in shopping_types" :key="type.id" :value="type.id">{{ type.description }}</option>
                    </select>
                    <InputError :message="form.errors.shopping_type_id" class="mt-1" />
                </div>
                <div class="flex items-center gap-2">
                    <input id="is_active" v-model="form.is_active" type="checkbox" class="rounded border-border text-accent focus:ring-accent" />
                    <label for="is_active" class="text-sm text-ink">Active</label>
                </div>
                <div>
                    <PrimaryButton :disabled="form.processing">Add bill</PrimaryButton>
                </div>
            </form>
        </Card>

        <Card class="mt-6" :padding="false">
            <div class="border-b border-border p-5">
                <h2 class="text-lg font-semibold text-ink">Scheduled bills</h2>
                <p class="mt-1 text-sm text-ink-muted">Click Edit to change schedule, amount, category, or pause a bill.</p>
            </div>
            <div v-if="dd_payments?.length" class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-border bg-surface/70 text-ink-muted">
                        <tr>
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Next date</th>
                            <th class="px-4 py-3 font-medium">Frequency</th>
                            <th class="px-4 py-3 font-medium">Amount</th>
                            <th class="px-4 py-3 font-medium">Active</th>
                            <th class="px-4 py-3 font-medium">Group</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Shop</th>
                            <th class="px-4 py-3 font-medium">Payment</th>
                            <th class="px-4 py-3 font-medium">End date</th>
                            <th class="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <BillRow
                            v-for="payment in dd_payments"
                            :key="payment.id"
                            :payment="payment"
                            :shopping_groups="shopping_groups"
                            :shops="shops"
                            :payment_methods="payment_methods"
                            :shopping_types="shopping_types"
                            @submit-edit="handleEdit"
                            @delete="handleDelete"
                        />
                    </tbody>
                </table>
            </div>
            <EmptyState
                v-else
                class="m-6"
                title="No bills yet"
                description="Add rent, utilities, or subscriptions to project them on the overview."
            />
        </Card>
    </AuthenticatedLayout>
</template>
