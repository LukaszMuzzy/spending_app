<script setup>
defineProps(['receipt']);

</script>
 
<template>
    <tr class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
        <td class="whitespace-nowrap px-6 py-4 font-medium">{{ receipt.date }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ receipt.shopping_type.description }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ receipt.price }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ receipt.payment_method.name }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ receipt.shop.name }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ receipt.note }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ receipt.user.name }}</td>
    </tr>
</template>