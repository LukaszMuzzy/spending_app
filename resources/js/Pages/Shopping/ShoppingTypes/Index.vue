<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Card from '@/Components/Card.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputError from '@/Components/InputError.vue';
import PageHeader from '@/Components/PageHeader.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({ description: '' });
defineProps({ shopping_types: Array });

const inputClass =
    'block w-full rounded-xl border-border shadow-sm focus:border-accent focus:ring-accent';
</script>

<template>
    <Head title="Categories" />

    <AuthenticatedLayout>
        <PageHeader title="Categories" description="Shopping types such as groceries, fuel, or entertainment." />

        <Card>
            <form class="flex flex-wrap items-end gap-3" @submit.prevent="form.post(route('shopping_types.store'), { onSuccess: () => form.reset() })">
                <div class="min-w-[16rem] flex-1">
                    <label class="mb-1 block text-sm font-medium text-ink">Category name</label>
                    <input v-model="form.description" type="text" :class="inputClass" required />
                    <InputError :message="form.errors.description" class="mt-1" />
                </div>
                <PrimaryButton :disabled="form.processing">Add category</PrimaryButton>
            </form>
        </Card>

        <Card class="mt-6" :padding="false">
            <ul v-if="shopping_types?.length" class="divide-y divide-border">
                <li v-for="type in shopping_types" :key="type.id" class="px-5 py-3 text-sm text-ink">{{ type.description }}</li>
            </ul>
            <EmptyState v-else class="m-6" title="No categories yet" description="Add categories before logging spending." />
        </Card>
    </AuthenticatedLayout>
</template>
