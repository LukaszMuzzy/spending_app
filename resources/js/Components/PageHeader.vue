<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-ink">{{ title }}</h1>
        <p v-if="description" class="mt-1 text-sm text-ink-muted">{{ description }}</p>
        <div v-if="$slots.actions" class="mt-4 flex flex-wrap gap-2">
            <slot name="actions" />
        </div>
    </div>
</template>
