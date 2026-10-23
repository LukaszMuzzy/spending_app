<script setup>
defineProps({
    padding: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
        :class="padding ? 'p-5 sm:p-6' : ''"
    >
        <slot />
    </div>
</template>
