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
    <div class="rounded-2xl border border-dashed border-border bg-surface/60 px-6 py-10 text-center">
        <h3 class="text-base font-medium text-ink">{{ title }}</h3>
        <p v-if="description" class="mx-auto mt-2 max-w-md text-sm text-ink-muted">{{ description }}</p>
        <div v-if="$slots.default" class="mt-4 flex justify-center">
            <slot />
        </div>
    </div>
</template>
