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
import dayjs from 'dayjs';

const props = defineProps({
    budgets: Array,
    incomes: Array,
    shopping_groups: Array,
    shopping_types: Array,
    frequencies: Array,
});

const budgetForm = useForm({
    shopping_group_id: '',
    shopping_type_id: '',
    amount: '',
});

const incomeForm = useForm({
    name: '',
    amount: '',
    frequency: 'monthly',
    next_date: dayjs().format('YYYY-MM-DD'),
    is_active: true,
    shopping_group_id: '',
});

const inputClass =
    'block w-full rounded-xl border-border shadow-sm focus:border-accent focus:ring-accent';

function deleteBudget(id) {
    if (confirm('Remove this budget?')) {
        router.delete(route('plan.budgets.destroy', id));
    }
}

function deleteIncome(id) {
    if (confirm('Remove this income?')) {
        router.delete(route('plan.incomes.destroy', id));
    }
}
</script>

<template>
    <Head title="Plan" />

    <AuthenticatedLayout>
        <PageHeader
            title="Plan"
            description="Set monthly category budgets and expected income. The overview uses these for pace and money left."
        />

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <h2 class="text-lg font-semibold text-ink">Category budgets</h2>
                <p class="mt-1 text-sm text-ink-muted">One monthly amount per group and category.</p>

                <form
                    class="mt-4 grid gap-3 sm:grid-cols-2"
                    @submit.prevent="budgetForm.post(route('plan.budgets.store'), { onSuccess: () => budgetForm.reset('amount') })"
                >
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Group</label>
                        <select v-model="budgetForm.shopping_group_id" class="input" :class="inputClass" required>
                            <option disabled value="">Select group</option>
                            <option v-for="group in shopping_groups" :key="group.id" :value="group.id">
                                {{ group.name }}
                            </option>
                        </select>
                        <InputError :message="budgetForm.errors.shopping_group_id" class="mt-1" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Category</label>
                        <select v-model="budgetForm.shopping_type_id" :class="inputClass" required>
                            <option disabled value="">Select category</option>
                            <option v-for="type in shopping_types" :key="type.id" :value="type.id">
                                {{ type.description }}
                            </option>
                        </select>
                        <InputError :message="budgetForm.errors.shopping_type_id" class="mt-1" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Monthly amount</label>
                        <input v-model="budgetForm.amount" type="number" step="0.01" min="0" :class="inputClass" required />
                        <InputError :message="budgetForm.errors.amount" class="mt-1" />
                    </div>
                    <div class="flex items-end">
                        <PrimaryButton :disabled="budgetForm.processing">Save budget</PrimaryButton>
                    </div>
                </form>

                <div v-if="budgets?.length" class="mt-6 divide-y divide-border">
                    <div
                        v-for="budget in budgets"
                        :key="budget.id"
                        class="flex flex-wrap items-center justify-between gap-3 py-3"
                    >
                        <div>
                            <p class="font-medium text-ink">
                                {{ budget.shopping_type?.description }}
                                <span class="text-ink-muted">· {{ budget.shopping_group?.name }}</span>
                            </p>
                            <p class="text-sm text-ink-muted">
                                Spent {{ formatMoney(budget.spent) }} of {{ formatMoney(budget.amount) }}
                            </p>
                            <div class="mt-2 h-2 w-48 overflow-hidden rounded-full bg-surface">
                                <div
                                    class="h-full rounded-full"
                                    :class="budget.spent > budget.amount ? 'bg-warn' : 'bg-accent'"
                                    :style="{ width: `${Math.min(100, (budget.spent / Math.max(budget.amount, 0.01)) * 100)}%` }"
                                />
                            </div>
                        </div>
                        <SecondaryButton @click="deleteBudget(budget.id)">Remove</SecondaryButton>
                    </div>
                </div>
                <EmptyState
                    v-else
                    class="mt-6"
                    title="No budgets yet"
                    description="Add a category budget to track pace on the overview."
                />
            </Card>

            <Card>
                <h2 class="text-lg font-semibold text-ink">Expected income</h2>
                <p class="mt-1 text-sm text-ink-muted">Used for money left. Does not create receipts.</p>

                <form
                    class="mt-4 grid gap-3 sm:grid-cols-2"
                    @submit.prevent="incomeForm.post(route('plan.incomes.store'), { onSuccess: () => incomeForm.reset('name', 'amount') })"
                >
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-ink">Name</label>
                        <input v-model="incomeForm.name" type="text" :class="inputClass" required />
                        <InputError :message="incomeForm.errors.name" class="mt-1" />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Amount</label>
                        <input v-model="incomeForm.amount" type="number" step="0.01" min="0" :class="inputClass" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Frequency</label>
                        <select v-model="incomeForm.frequency" :class="inputClass">
                            <option v-for="freq in frequencies" :key="freq" :value="freq">{{ freq }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Next date</label>
                        <input v-model="incomeForm.next_date" type="date" :class="inputClass" required />
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Group</label>
                        <select v-model="incomeForm.shopping_group_id" :class="inputClass" required>
                            <option disabled value="">Select group</option>
                            <option v-for="group in shopping_groups" :key="group.id" :value="group.id">
                                {{ group.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 sm:col-span-2">
                        <input id="income_active" v-model="incomeForm.is_active" type="checkbox" class="rounded border-border text-accent focus:ring-accent" />
                        <label for="income_active" class="text-sm text-ink">Active</label>
                    </div>
                    <div>
                        <PrimaryButton :disabled="incomeForm.processing">Add income</PrimaryButton>
                    </div>
                </form>

                <div v-if="incomes?.length" class="mt-6 divide-y divide-border">
                    <div
                        v-for="income in incomes"
                        :key="income.id"
                        class="flex flex-wrap items-center justify-between gap-3 py-3"
                    >
                        <div>
                            <p class="font-medium text-ink">{{ income.name }}</p>
                            <p class="text-sm text-ink-muted">
                                {{ formatMoney(income.amount) }} · {{ income.frequency }} · next {{ income.next_date }}
                                <span v-if="!income.is_active"> · paused</span>
                            </p>
                        </div>
                        <SecondaryButton @click="deleteIncome(income.id)">Remove</SecondaryButton>
                    </div>
                </div>
                <EmptyState
                    v-else
                    class="mt-6"
                    title="No income set"
                    description="Add salary or other expected income to see money left on the overview."
                />
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
