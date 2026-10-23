<script setup>
import { formatMoney } from '@/utils/money';

defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [Number, String],
        required: true,
    },
    hint: {
        type: String,
        default: '',
    },
    tone: {
        type: String,
        default: 'default',
    },
    money: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <div
        class="rounded-2xl border border-border bg-card p-5 shadow-sm"
        :class="{
            'ring-1 ring-good/30': tone === 'good',
            'ring-1 ring-warn/30': tone === 'warn',
        }"
    >
        <p class="text-sm font-medium text-ink-muted">{{ label }}</p>
        <p
            class="mt-2 text-2xl font-semibold tracking-tight"
            :class="{
                'text-good': tone === 'good',
                'text-warn': tone === 'warn',
                'text-ink': tone === 'default',
            }"
        >
            {{ money ? formatMoney(value) : value }}
        </p>
        <p v-if="hint" class="mt-1 text-xs text-ink-muted">{{ hint }}</p>
    </div>
</template>
