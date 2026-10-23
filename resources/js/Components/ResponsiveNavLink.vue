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
        ? 'block w-full border-l-4 border-accent bg-accent-soft py-2 pl-3 pr-4 text-base font-medium text-accent'
        : 'block w-full border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-ink-muted transition hover:border-border hover:bg-surface hover:text-ink'
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
