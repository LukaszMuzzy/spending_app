<script setup>
import { formatMoney } from '@/utils/money';
import { ref, watch } from 'vue';

const props = defineProps({
    payment: Object,
    shopping_groups: Array,
    shops: Array,
    payment_methods: Array,
    shopping_types: Array,
});

const emit = defineEmits(['submitEdit', 'delete']);

const isEditing = ref(false);
const edited = ref(copyPayment(props.payment));

const inputClass =
    'block w-full min-w-[7rem] rounded-lg border-border text-sm shadow-sm focus:border-accent focus:ring-accent';

watch(
    () => props.payment,
    (value) => {
        if (!isEditing.value) {
            edited.value = copyPayment(value);
        }
    }
);

function copyPayment(payment) {
    return {
        id: payment.id,
        name: payment.name,
        payment_date: (payment.payment_date || '').toString().slice(0, 10),
        end_date: payment.end_date ? payment.end_date.toString().slice(0, 10) : '',
        frequency: payment.frequency || '',
        price: payment.price,
        is_active: !!payment.is_active,
        shopping_group_id: payment.shopping_group_id,
        shop_id: payment.shop_id,
        payment_method_id: payment.payment_method_id,
        shopping_type_id: payment.shopping_type_id,
    };
}

function startEdit() {
    isEditing.value = true;
    edited.value = copyPayment(props.payment);
}

function cancelEdit() {
    isEditing.value = false;
    edited.value = copyPayment(props.payment);
}

function submitEdit() {
    emit('submitEdit', { ...edited.value });
    isEditing.value = false;
}

function deleteBill() {
    emit('delete', props.payment);
}
</script>

<template>
    <tr class="border-b border-border transition hover:bg-surface/60">
        <td class="px-4 py-3">
            <template v-if="!isEditing">
                <span class="font-medium text-ink">{{ payment.name }}</span>
            </template>
            <input v-else v-model="edited.name" type="text" :class="inputClass" />
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ payment.payment_date }}</template>
            <input v-else v-model="edited.payment_date" type="date" :class="inputClass" />
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">
                <span class="capitalize">{{ payment.frequency }}</span>
            </template>
            <select v-else v-model="edited.frequency" :class="inputClass">
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="fortnightly">Fortnightly</option>
                <option value="monthly">Monthly</option>
                <option value="quarterly">Quarterly</option>
                <option value="half-yearly">Half-yearly</option>
                <option value="yearly">Yearly</option>
            </select>
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ formatMoney(payment.price) }}</template>
            <input v-else v-model="edited.price" type="number" step="0.01" min="0" :class="inputClass" />
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ payment.is_active ? 'Yes' : 'No' }}</template>
            <label v-else class="inline-flex items-center gap-2 text-sm text-ink">
                <input v-model="edited.is_active" type="checkbox" class="rounded border-border text-accent focus:ring-accent" />
                Active
            </label>
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ payment.shopping_group?.name }}</template>
            <select v-else v-model="edited.shopping_group_id" :class="inputClass">
                <option v-for="group in shopping_groups" :key="group.id" :value="group.id">{{ group.name }}</option>
            </select>
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ payment.shopping_type?.description }}</template>
            <select v-else v-model="edited.shopping_type_id" :class="inputClass">
                <option v-for="type in shopping_types" :key="type.id" :value="type.id">{{ type.description }}</option>
            </select>
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ payment.shop?.name }}</template>
            <select v-else v-model="edited.shop_id" :class="inputClass">
                <option v-for="shop in shops" :key="shop.id" :value="shop.id">{{ shop.name }}</option>
            </select>
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ payment.payment_method?.name }}</template>
            <select v-else v-model="edited.payment_method_id" :class="inputClass">
                <option v-for="method in payment_methods" :key="method.id" :value="method.id">{{ method.name }}</option>
            </select>
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <template v-if="!isEditing">{{ payment.end_date || '—' }}</template>
            <input v-else v-model="edited.end_date" type="date" :class="inputClass" />
        </td>
        <td class="whitespace-nowrap px-4 py-3">
            <div class="flex gap-2">
                <button
                    v-if="!isEditing"
                    type="button"
                    class="rounded-lg bg-accent px-3 py-1.5 text-xs font-semibold text-white"
                    @click="startEdit"
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
                    @click="deleteBill"
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
