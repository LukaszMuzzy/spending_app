<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ReceiptRow from '@/Components/Receipt.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { ref, watch } from 'vue';

const props = defineProps({
    receipts: Object,
    filters: Object,
    shopping_types: Array,
    payment_methods: Array,
    shops: Array,
    shopping_groups: Array,
    inbox_count: Number,
});

const inputClass =
    'block w-full rounded-xl border-border shadow-sm focus:border-accent focus:ring-accent';

const form = useForm({
    shopping_type_id: '',
    payment_method_id: '',
    shop_id: '',
    price: null,
    note: '',
    date: dayjs().format('YYYY-MM-DD'),
    shopping_group_id: '',
});

const localFilters = ref({ ...props.filters });

watch(
    () => props.filters,
    (value) => {
        localFilters.value = { ...value };
    }
);

function applyFilters() {
    router.get(
        route('receipts.index'),
        {
            search: localFilters.value.search || undefined,
            month: localFilters.value.month || undefined,
            shopping_type_id: localFilters.value.shopping_type_id || undefined,
            shopping_group_id: localFilters.value.shopping_group_id || undefined,
            needs_review: localFilters.value.needs_review ? 1 : undefined,
        },
        { preserveState: true, replace: true }
    );
}

function handleEditReceipt(editedReceipt) {
    const editedReceiptForm = useForm({
        shopping_type_id: editedReceipt.shopping_type_id,
        payment_method_id: editedReceipt.payment_method_id,
        shop_id: editedReceipt.shop_id,
        price: editedReceipt.price,
        note: editedReceipt.note,
        date: dayjs(editedReceipt.date).format('YYYY-MM-DD'),
        shopping_group_id: editedReceipt.shopping_group_id,
    });

    editedReceiptForm.put(route('receipts.update', editedReceipt.id));
}

function handleDeleteReceipt(receipt) {
    if (confirm('Delete this receipt?')) {
        router.delete(route('receipts.destroy', receipt.id));
    }
}
</script>

<template>
    <Head title="Spending" />

    <AuthenticatedLayout>
        <PageHeader
            title="Spending"
            description="Add receipts, filter by month or category, and review bank imports waiting for a category."
        >
            <template #actions>
                <Link
                    v-if="inbox_count"
                    :href="route('receipts.index', { needs_review: 1 })"
                    class="rounded-xl border border-warn bg-warn-soft px-4 py-2.5 text-sm font-semibold text-warn"
                >
                    {{ inbox_count }} need review
                </Link>
                <Link
                    :href="route('imports.index')"
                    class="rounded-xl border border-border bg-card px-4 py-2.5 text-sm font-semibold text-ink"
                >
                    Import CSV
                </Link>
            </template>
        </PageHeader>

        <Card>
            <h2 class="text-lg font-semibold text-ink">Add receipt</h2>
            <form
                class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3"
                @submit.prevent="form.post(route('receipts.store'), { onSuccess: () => form.reset('price', 'note') })"
            >
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Date</label>
                    <input v-model="form.date" type="date" :class="inputClass" required />
                    <InputError :message="form.errors.date" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Category</label>
                    <select v-model="form.shopping_type_id" :class="inputClass" required>
                        <option disabled value="">Select category</option>
                        <option v-for="type in shopping_types" :key="type.id" :value="type.id">
                            {{ type.description }}
                        </option>
                    </select>
                    <InputError :message="form.errors.shopping_type_id" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Amount</label>
                    <input v-model="form.price" type="number" step="0.01" min="0" placeholder="0.00" :class="inputClass" required />
                    <InputError :message="form.errors.price" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Payment method</label>
                    <select v-model="form.payment_method_id" :class="inputClass" required>
                        <option disabled value="">Select payment</option>
                        <option v-for="method in payment_methods" :key="method.id" :value="method.id">
                            {{ method.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.payment_method_id" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Shop</label>
                    <select v-model="form.shop_id" :class="inputClass" required>
                        <option disabled value="">Select shop</option>
                        <option v-for="shop in shops" :key="shop.id" :value="shop.id">{{ shop.name }}</option>
                    </select>
                    <InputError :message="form.errors.shop_id" class="mt-1" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink">Group</label>
                    <select v-model="form.shopping_group_id" :class="inputClass" required>
                        <option disabled value="">Select group</option>
                        <option v-for="group in shopping_groups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.shopping_group_id" class="mt-1" />
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label class="mb-1 block text-sm font-medium text-ink">Notes</label>
                    <input v-model="form.note" type="text" placeholder="Optional note" :class="inputClass" />
                    <InputError :message="form.errors.note" class="mt-1" />
                </div>
                <div>
                    <PrimaryButton :disabled="form.processing">Add receipt</PrimaryButton>
                </div>
            </form>
        </Card>

        <Card class="mt-6" :padding="false">
            <div class="border-b border-border p-5 sm:p-6">
                <h2 class="text-lg font-semibold text-ink">Receipts</h2>
                <form class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5" @submit.prevent="applyFilters">
                    <input v-model="localFilters.search" type="search" placeholder="Search notes or shops" :class="inputClass" />
                    <input v-model="localFilters.month" type="month" :class="inputClass" />
                    <select v-model="localFilters.shopping_type_id" :class="inputClass">
                        <option value="">All categories</option>
                        <option v-for="type in shopping_types" :key="type.id" :value="type.id">
                            {{ type.description }}
                        </option>
                    </select>
                    <select v-model="localFilters.shopping_group_id" :class="inputClass">
                        <option value="">All groups</option>
                        <option v-for="group in shopping_groups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-2 text-sm text-ink">
                            <input v-model="localFilters.needs_review" type="checkbox" class="rounded border-border text-accent focus:ring-accent" />
                            Needs review
                        </label>
                        <SecondaryButton type="submit">Filter</SecondaryButton>
                    </div>
                </form>
            </div>

            <div v-if="receipts.data?.length" class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-border bg-surface/70 text-ink-muted">
                        <tr>
                            <th class="px-4 py-3 font-medium">Date</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Amount</th>
                            <th class="px-4 py-3 font-medium">Payment</th>
                            <th class="px-4 py-3 font-medium">Shop</th>
                            <th class="px-4 py-3 font-medium">Group</th>
                            <th class="px-4 py-3 font-medium">Note</th>
                            <th class="px-4 py-3 font-medium">Source</th>
                            <th class="px-4 py-3 font-medium">By</th>
                            <th class="px-4 py-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <ReceiptRow
                            v-for="receipt in receipts.data"
                            :key="receipt.id"
                            :receipt="receipt"
                            :shopping_types="shopping_types"
                            :payment_methods="payment_methods"
                            :shops="shops"
                            :shopping_groups="shopping_groups"
                            @submit-edit-receipt="handleEditReceipt"
                            @delete-receipt="handleDeleteReceipt"
                        />
                    </tbody>
                </table>
            </div>
            <EmptyState
                v-else
                class="m-6"
                title="No receipts match"
                description="Try another month, or add your first receipt above."
            />

            <div v-if="receipts.links?.length > 3" class="flex flex-wrap gap-2 border-t border-border p-4">
                <Link
                    v-for="link in receipts.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    class="rounded-lg px-3 py-1.5 text-sm"
                    :class="link.active ? 'bg-accent text-white' : 'bg-surface text-ink-muted'"
                    v-html="link.label"
                    preserve-scroll
                />
            </div>
        </Card>
    </AuthenticatedLayout>
</template>
