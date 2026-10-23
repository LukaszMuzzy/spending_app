<script setup>
import { formatMoney } from '@/utils/money';
import { ref, watch } from 'vue';

const props = defineProps({
    receipt: Object,
    shopping_types: Array,
    payment_methods: Array,
    shops: Array,
    shopping_groups: Array,
});

const emit = defineEmits(['submitEditReceipt', 'deleteReceipt']);

const isEditing = ref(false);
const editedReceipt = ref({ ...props.receipt });

const inputClass =
    'block w-full rounded-lg border-border text-sm shadow-sm focus:border-accent focus:ring-accent';

watch(
    () => props.receipt,
    (value) => {
        editedReceipt.value = { ...value };
    }
);

function editReceipt() {
    isEditing.value = true;
    editedReceipt.value = { ...props.receipt };
}

function cancelEdit() {
    isEditing.value = false;
    editedReceipt.value = { ...props.receipt };
}

function submitEdit() {
    emit('submitEditReceipt', editedReceipt.value);
    isEditing.value = false;
}

function deleteReceipt() {
    emit('deleteReceipt', props.receipt);
}

function sourceLabel(source) {
    return source || 'manual';
}
</script>

<template>
    <tr class="border-b border-border transition hover:bg-surface/60" :class="{ 'bg-warn-soft/40': receipt.needs_review }">
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ receipt.date }}</template>
            <input v-else v-model="editedReceipt.date" type="date" :class="inputClass" />
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ receipt.shopping_type?.description }}</template>
            <select v-else v-model="editedReceipt.shopping_type_id" :class="inputClass">
                <option v-for="type in shopping_types" :key="type.id" :value="type.id">{{ type.description }}</option>
            </select>
        </td>
        <td class="whitespace-nowrap px-4 py-3 font-medium">
            <template v-if="!isEditing">{{ formatMoney(receipt.price) }}</template>
            <input v-else v-model="editedReceipt.price" type="number" step="0.01" :class="inputClass" />
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ receipt.payment_method?.name }}</template>
            <select v-else v-model="editedReceipt.payment_method_id" :class="inputClass">
                <option v-for="method in payment_methods" :key="method.id" :value="method.id">{{ method.name }}</option>
            </select>
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ receipt.shop?.name }}</template>
            <select v-else v-model="editedReceipt.shop_id" :class="inputClass">
                <option v-for="shop in shops" :key="shop.id" :value="shop.id">{{ shop.name }}</option>
            </select>
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ receipt.shopping_group?.name }}</template>
            <select v-else v-model="editedReceipt.shopping_group_id" :class="inputClass">
                <option v-for="group in shopping_groups" :key="group.id" :value="group.id">{{ group.name }}</option>
            </select>
        </td>
        <td class="px-4 py-3">
            <template v-if="!isEditing">{{ receipt.note }}</template>
            <input v-else v-model="editedReceipt.note" type="text" :class="inputClass" />
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <span class="rounded-full bg-surface px-2 py-1 text-xs font-medium capitalize text-ink-muted">
                {{ sourceLabel(receipt.source) }}
            </span>
            <span
                v-if="receipt.needs_review"
                class="ml-1 rounded-full bg-warn-soft px-2 py-1 text-xs font-medium text-warn"
            >
                review
            </span>
        </td>
        <td class="whitespace-nowrap px-4 py-3 text-ink-muted">{{ receipt.user?.name }}</td>
        <td class="whitespace-nowrap px-4 py-3">
            <div class="flex gap-2">
                <button
                    v-if="!isEditing"
                    type="button"
                    class="rounded-lg bg-accent px-3 py-1.5 text-xs font-semibold text-white"
                    @click="editReceipt"
                >
                    Edit
                </button>
                <button
                    v-else
                    type="button"
                    class="rounded-lg bg-good px-3 py-1.5 text-xs font-semibold text-white"
                    @click="submitEdit"
                >
                    Save
                </button>
                <button
                    v-if="!isEditing"
                    type="button"
                    class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white"
                    @click="deleteReceipt"
                >
                    Delete
                </button>
                <button
                    v-else
                    type="button"
                    class="rounded-lg border border-border px-3 py-1.5 text-xs font-semibold text-ink"
                    @click="cancelEdit"
                >
                    Cancel
                </button>
            </div>
        </td>
    </tr>
</template>
