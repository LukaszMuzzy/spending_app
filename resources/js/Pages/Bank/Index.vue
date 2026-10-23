<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { formatMoney } from '@/utils/money';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    configured: Boolean,
    connection: Object,
    shopping_groups: Array,
    payment_methods: Array,
    shops: Array,
    shopping_types: Array,
    inbox_count: Number,
});

const page = usePage();
const bankError = computed(() => page.props.errors?.bank);

const inputClass =
    'block w-full rounded-xl border-border shadow-sm focus:border-accent focus:ring-accent';

const syncForm = useForm({
    shopping_group_id: '',
    shop_id: '',
    payment_method_id: '',
    shopping_type_id: '',
});

function connect() {
    router.post(route('bank.connect'));
}

function disconnect() {
    if (confirm('Disconnect this bank and remove the stored session?')) {
        router.delete(route('bank.disconnect'));
    }
}

function sync() {
    syncForm.post(route('bank.sync'));
}
</script>

<template>
    <Head title="Bank connection" />

    <AuthenticatedLayout>
        <PageHeader
            title="Bank connection"
            description="Optional read-only sync through Enable Banking. The rest of the app works without this. CSV import is available under Setup."
        />

        <Card>
            <div v-if="!configured" class="space-y-3">
                <h2 class="text-lg font-semibold text-ink">Enable Banking is not configured</h2>
                <p class="text-sm text-ink-muted">
                    Add <code class="rounded bg-surface px-1">ENABLEBANKING_APP_ID</code> and
                    <code class="rounded bg-surface px-1">ENABLEBANKING_PRIVATE_KEY</code> to your
                    <code class="rounded bg-surface px-1">.env</code>, then restart the app. Use sandbox keys to try Connect,
                    or restricted production after you whitelist your own accounts in the Enable Banking control panel.
                </p>
                <p class="text-sm text-ink-muted">
                    Until then, export a CSV from your bank and use
                    <Link :href="route('imports.index')" class="font-medium text-accent underline">CSV import</Link>.
                </p>
            </div>

            <div v-else class="space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">Read-only account information</h2>
                        <p class="mt-1 text-sm text-ink-muted">
                            Consent happens on your bank’s screen. This app requests balances and transactions only, never payments.
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <PrimaryButton v-if="connection?.status !== 'connected'" @click="connect">
                            Connect bank
                        </PrimaryButton>
                        <SecondaryButton v-else @click="disconnect">Disconnect</SecondaryButton>
                    </div>
                </div>

                <InputError :message="bankError" />

                <div v-if="connection" class="rounded-xl bg-surface p-4 text-sm">
                    <p class="font-medium text-ink">{{ connection.institution_name || 'Bank' }}</p>
                    <p class="mt-1 text-ink-muted">
                        Status: {{ connection.status }}
                        <span v-if="connection.balance !== null && connection.balance !== undefined">
                            · balance {{ formatMoney(connection.balance) }}
                        </span>
                    </p>
                    <p v-if="connection.last_synced_at" class="mt-1 text-ink-muted">
                        Last synced {{ connection.last_synced_at }}
                    </p>
                    <p v-if="connection.expires_at" class="mt-1 text-ink-muted">
                        Consent expires {{ connection.expires_at }}
                    </p>
                </div>

                <form
                    v-if="connection?.status === 'connected'"
                    class="grid gap-3 sm:grid-cols-2"
                    @submit.prevent="sync"
                >
                    <p class="sm:col-span-2 text-sm text-ink-muted">
                        New bank transactions land in the spending inbox as Uncategorised so you can set the category before they affect budgets.
                        <Link v-if="inbox_count" :href="route('receipts.index', { needs_review: 1 })" class="font-medium text-accent underline">
                            {{ inbox_count }} waiting
                        </Link>
                    </p>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Default group</label>
                        <select v-model="syncForm.shopping_group_id" :class="inputClass" required>
                            <option disabled value="">Select group</option>
                            <option v-for="group in shopping_groups" :key="group.id" :value="group.id">{{ group.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Default shop</label>
                        <select v-model="syncForm.shop_id" :class="inputClass" required>
                            <option disabled value="">Select shop</option>
                            <option v-for="shop in shops" :key="shop.id" :value="shop.id">{{ shop.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Default payment</label>
                        <select v-model="syncForm.payment_method_id" :class="inputClass" required>
                            <option disabled value="">Select payment</option>
                            <option v-for="method in payment_methods" :key="method.id" :value="method.id">{{ method.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-ink">Fallback category</label>
                        <select v-model="syncForm.shopping_type_id" :class="inputClass" required>
                            <option disabled value="">Select category</option>
                            <option v-for="type in shopping_types" :key="type.id" :value="type.id">{{ type.description }}</option>
                        </select>
                        <p class="mt-1 text-xs text-ink-muted">Used only if Uncategorised cannot be created. Synced rows still prefer Uncategorised.</p>
                    </div>
                    <div>
                        <PrimaryButton :disabled="syncForm.processing">Sync transactions</PrimaryButton>
                    </div>
                </form>
            </div>
        </Card>
    </AuthenticatedLayout>
</template>
