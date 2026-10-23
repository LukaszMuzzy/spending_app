<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatMoney } from '@/utils/money';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    shopping_groups: Array,
    payment_methods: Array,
    shops: Array,
    shopping_types: Array,
    preview: Array,
    mapping: Object,
    defaults: Object,
});

const inputClass =
    'block w-full rounded-xl border-border shadow-sm focus:border-accent focus:ring-accent';

const form = useForm({
    file: null,
    date_column: 'Date',
    amount_column: 'Amount',
    description_column: 'Description',
    shopping_group_id: '',
    shop_id: '',
    payment_method_id: '',
    shopping_type_id: '',
    has_header: true,
});

function onFileChange(event) {
    form.file = event.target.files[0];
}

function submitPreview() {
    form.post(route('imports.preview'), {
        forceFormData: true,
    });
}

function confirmImport() {
    router.post(route('imports.store'));
}
</script>

<template>
    <Head title="CSV import" />

    <AuthenticatedLayout>
        <PageHeader
            title="CSV import"
            description="Upload a bank export, map the columns, preview the first rows, then import. Duplicates with the same group, date, amount, and description are skipped."
        />

        <Card>
            <form class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" @submit.prevent="submitPreview">
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="mb-1 block text-sm font-medium text-ink">CSV file</label>
                    <input type="file" accept=".csv,text/csv" class="block w-full text-sm" @change="onFileChange" />
                    <InputError :message="form.errors.file" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Date column</label>
                    <input v-model="form.date_column" type="text" :class="inputClass" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Amount column</label>
                    <input v-model="form.amount_column" type="text" :class="inputClass" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Description column</label>
                    <input v-model="form.description_column" type="text" :class="inputClass" required />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Default group</label>
                    <select v-model="form.shopping_group_id" :class="inputClass" required>
                        <option disabled value="">Select group</option>
                        <option v-for="group in shopping_groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Default shop</label>
                    <select v-model="form.shop_id" :class="inputClass" required>
                        <option disabled value="">Select shop</option>
                        <option v-for="shop in shops" :key="shop.id" :value="shop.id">{{ shop.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Default payment</label>
                    <select v-model="form.payment_method_id" :class="inputClass" required>
                        <option disabled value="">Select payment</option>
                        <option v-for="method in payment_methods" :key="method.id" :value="method.id">{{ method.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Default category</label>
                    <select v-model="form.shopping_type_id" :class="inputClass" required>
                        <option disabled value="">Select category</option>
                        <option v-for="type in shopping_types" :key="type.id" :value="type.id">{{ type.description }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input id="has_header" v-model="form.has_header" type="checkbox" class="rounded border-border text-accent focus:ring-accent" />
                    <label for="has_header" class="text-sm text-ink">First row is a header</label>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <PrimaryButton :disabled="form.processing || !form.file">Preview import</PrimaryButton>
                </div>
            </form>
        </Card>

        <Card class="mt-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-ink">Preview</h2>
                    <p class="text-sm text-ink-muted">First rows after mapping. Confirm to save them as receipts.</p>
                </div>
                <PrimaryButton v-if="preview?.length" @click="confirmImport">Confirm import</PrimaryButton>
            </div>

            <div v-if="preview?.length" class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-border text-ink-muted">
                        <tr>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Amount</th>
                            <th class="px-3 py-2">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, index) in preview" :key="index" class="border-b border-border">
                            <td class="px-3 py-2">{{ row.date }}</td>
                            <td class="px-3 py-2">{{ formatMoney(row.amount) }}</td>
                            <td class="px-3 py-2">{{ row.description }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <EmptyState
                v-else
                class="mt-4"
                title="No preview yet"
                description="Upload a CSV and map the date, amount, and description columns."
            />
        </Card>
    </AuthenticatedLayout>
</template>
