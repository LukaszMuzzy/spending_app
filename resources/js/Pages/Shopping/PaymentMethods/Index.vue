<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ name: '' });
defineProps({ payment_methods: Array });

const inputClass =
    'block w-full rounded-xl border-border shadow-sm focus:border-accent focus:ring-accent';
</script>

<template>
    <Head title="Payment methods" />

    <AuthenticatedLayout>
        <PageHeader title="Payment methods" description="Cards and accounts linked to your user for receipt entry." />

        <Card>
            <form class="flex flex-wrap items-end gap-3" @submit.prevent="form.post(route('payment_methods.store'), { onSuccess: () => form.reset() })">
                <div class="min-w-[16rem] flex-1">
                    <label class="mb-1 block text-sm font-medium text-ink">Payment method</label>
                    <input v-model="form.name" type="text" :class="inputClass" required />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>
                <PrimaryButton :disabled="form.processing">Add method</PrimaryButton>
            </form>
        </Card>

        <Card class="mt-6" :padding="false">
            <ul v-if="payment_methods?.length" class="divide-y divide-border">
                <li v-for="method in payment_methods" :key="method.id" class="px-5 py-3 text-sm text-ink">{{ method.name }}</li>
            </ul>
            <EmptyState v-else class="m-6" title="No payment methods yet" description="Add a debit card or account name to use on receipts." />
        </Card>
    </AuthenticatedLayout>
</template>
