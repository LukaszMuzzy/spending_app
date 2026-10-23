<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ name: '' });
defineProps({ shopping_groups: Array });

const inputClass =
    'block w-full rounded-xl border-border shadow-sm focus:border-accent focus:ring-accent';
</script>

<template>
    <Head title="Groups" />

    <AuthenticatedLayout>
        <PageHeader title="Groups" description="Household or shared spending groups you belong to." />

        <Card>
            <form class="flex flex-wrap items-end gap-3" @submit.prevent="form.post(route('shopping_groups.store'), { onSuccess: () => form.reset() })">
                <div class="min-w-[16rem] flex-1">
                    <label class="mb-1 block text-sm font-medium text-ink">Group name</label>
                    <input v-model="form.name" type="text" :class="inputClass" required />
                    <InputError :message="form.errors.name" class="mt-1" />
                </div>
                <PrimaryButton :disabled="form.processing">Add group</PrimaryButton>
            </form>
        </Card>

        <Card class="mt-6" :padding="false">
            <ul v-if="shopping_groups?.length" class="divide-y divide-border">
                <li v-for="group in shopping_groups" :key="group.id" class="px-5 py-3 text-sm text-ink">{{ group.name }}</li>
            </ul>
            <EmptyState v-else class="m-6" title="No groups yet" description="Create a group so receipts can be shared across people." />
        </Card>
    </AuthenticatedLayout>
</template>
