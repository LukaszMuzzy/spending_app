<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PageHeader from '@/Components/PageHeader.vue';
import StatCard from '@/Components/StatCard.vue';
import { formatMoney, categoryColor } from '@/utils/money';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Bar, Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
    ArcElement,
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const props = defineProps({
    month_label: String,
    spent_so_far: Number,
    budget_total: Number,
    expected_income: Number,
    projected_spend: Number,
    remaining_bills_total: Number,
    variable_estimate: Number,
    money_left: Number,
    budget_remaining: Number,
    pace_tone: String,
    category_breakdown: Array,
    monthly_history: Array,
    top_shops: Array,
    upcoming_bills: Array,
    bank_balance: Number,
    bank_institution: String,
    bank_last_synced_at: String,
});

const barChartData = computed(() => ({
    labels: (props.monthly_history || []).map((row) => row.label),
    datasets: [
        {
            label: 'Spend',
            backgroundColor: '#0f766e',
            borderRadius: 8,
            data: (props.monthly_history || []).map((row) => row.total),
        },
    ],
}));

const categoryChartData = computed(() => ({
    labels: (props.category_breakdown || []).map((row) => row.label),
    datasets: [
        {
            data: (props.category_breakdown || []).map((row) => row.spent),
            backgroundColor: (props.category_breakdown || []).map((row) => categoryColor(row.shopping_type_id)),
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
    },
};
</script>

<template>
    <Head title="Overview" />

    <AuthenticatedLayout>
        <PageHeader
            :title="`Overview · ${month_label}`"
            description="Spent so far, what bills are left, and where the month is likely to finish."
        >
            <template #actions>
                <Link
                    :href="route('receipts.index')"
                    class="rounded-xl bg-accent px-4 py-2.5 text-sm font-semibold text-white"
                >
                    Add spending
                </Link>
                <Link
                    :href="route('plan.index')"
                    class="rounded-xl border border-border bg-card px-4 py-2.5 text-sm font-semibold text-ink"
                >
                    Edit plan
                </Link>
            </template>
        </PageHeader>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatCard label="Spent so far" :value="spent_so_far" :tone="pace_tone" />
            <StatCard label="Budget total" :value="budget_total" hint="Sum of category budgets" />
            <StatCard label="Expected income" :value="expected_income" hint="Active income due this month" />
            <StatCard
                label="Projected spend"
                :value="projected_spend"
                :tone="budget_total > 0 && projected_spend > budget_total ? 'warn' : 'good'"
                hint="Receipts + remaining bills + recent pace"
            />
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Month outlook</h2>
                        <p class="mt-1 text-sm text-ink-muted">
                            Remaining bills {{ formatMoney(remaining_bills_total) }} · variable estimate
                            {{ formatMoney(variable_estimate) }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-ink-muted">Money left after projected spend</p>
                        <p
                            class="text-xl font-semibold"
                            :class="money_left >= 0 ? 'text-good' : 'text-warn'"
                        >
                            {{ formatMoney(money_left) }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 h-64">
                    <Bar v-if="monthly_history?.length" :data="barChartData" :options="chartOptions" />
                    <EmptyState
                        v-else
                        title="No monthly history yet"
                        description="Add a few receipts and this chart will fill in."
                    />
                </div>
            </Card>

            <Card>
                <h2 class="text-lg font-semibold text-ink">This month by category</h2>
                <div class="mt-4 h-56">
                    <Doughnut
                        v-if="category_breakdown?.length"
                        :data="categoryChartData"
                        :options="{ responsive: true, maintainAspectRatio: false }"
                    />
                    <EmptyState
                        v-else
                        title="No spending yet"
                        description="Spending by category will appear here."
                    />
                </div>
                <ul class="mt-4 space-y-2">
                    <li
                        v-for="row in category_breakdown"
                        :key="row.shopping_type_id"
                        class="flex items-center justify-between text-sm"
                    >
                        <span class="flex items-center gap-2 text-ink">
                            <span
                                class="h-2.5 w-2.5 rounded-full"
                                :style="{ backgroundColor: categoryColor(row.shopping_type_id) }"
                            />
                            {{ row.label }}
                        </span>
                        <span class="text-ink-muted">
                            {{ formatMoney(row.spent) }}
                            <span v-if="row.budget"> / {{ formatMoney(row.budget) }}</span>
                        </span>
                    </li>
                </ul>
            </Card>
        </div>

        <div class="mt-4 grid gap-4 lg:grid-cols-2">
            <Card>
                <h2 class="text-lg font-semibold text-ink">Upcoming bills</h2>
                <p class="mt-1 text-sm text-ink-muted">Next 90 days from your direct debit schedule.</p>

                <div v-if="upcoming_bills?.length" class="mt-4 divide-y divide-border">
                    <div
                        v-for="bill in upcoming_bills"
                        :key="`${bill.id}-${bill.date}`"
                        class="flex items-center justify-between gap-3 py-3 text-sm"
                    >
                        <div>
                            <p class="font-medium text-ink">{{ bill.name }}</p>
                            <p class="text-ink-muted">{{ bill.date }} · {{ bill.shopping_type || 'Bill' }}</p>
                        </div>
                        <p class="font-semibold text-ink">{{ formatMoney(bill.amount) }}</p>
                    </div>
                </div>
                <EmptyState
                    v-else
                    class="mt-4"
                    title="No upcoming bills"
                    description="Add active direct debits under Bills to project them here."
                />
            </Card>

            <Card>
                <h2 class="text-lg font-semibold text-ink">Top shops this month</h2>
                <div v-if="top_shops?.length" class="mt-4 space-y-3">
                    <div v-for="shop in top_shops" :key="shop.label" class="flex items-center justify-between text-sm">
                        <span class="text-ink">{{ shop.label }}</span>
                        <span class="font-medium text-ink">{{ formatMoney(shop.total) }}</span>
                    </div>
                </div>
                <EmptyState
                    v-else
                    class="mt-4"
                    title="No shops yet"
                    description="Shop totals appear once receipts are logged."
                />

                <div v-if="bank_balance !== null && bank_balance !== undefined" class="mt-6 rounded-xl bg-accent-soft p-4">
                    <p class="text-sm font-medium text-accent">{{ bank_institution || 'Connected bank' }}</p>
                    <p class="mt-1 text-2xl font-semibold text-ink">{{ formatMoney(bank_balance) }}</p>
                    <p class="mt-1 text-xs text-ink-muted">
                        Balance is shown for context only. Forecasts use receipts and bills.
                        <span v-if="bank_last_synced_at"> Last synced {{ bank_last_synced_at }}.</span>
                    </p>
                </div>
            </Card>
        </div>
    </AuthenticatedLayout>
</template>
