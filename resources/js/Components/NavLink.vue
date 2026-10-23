<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center rounded-lg bg-accent-soft px-3 py-2 text-sm font-medium text-accent'
        : 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-ink-muted transition hover:bg-surface hover:text-ink'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
