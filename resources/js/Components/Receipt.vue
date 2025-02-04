<script setup>
import {ref} from 'vue'

let props = defineProps(['receipt']);
const emit = defineEmits(['submitEditReceipt']);

console.log(props.receipt)

let isEditing = ref(false);
let editedReceipt = ref({ ...props.receipt }); // Create a copy for editing

const editReceipt = () => {
  isEditing.value = !isEditing.value; // Toggle editing state

  // Reset editedReceipt to original data on cancel (optional)
  if (!isEditing) {
    editedReceipt.value = { ...props.receipt };
  }
};

const submitEdit = () => {
  // Handle editing logic (e.g., send updated data to parent)
  emit('submitEditReceipt', editedReceipt.value);

  // Reset editing state and potentially emit event to parent
  isEditing.value = false;
};

const cancelEdit = () => {
    isEditing.value = false
}


</script>

<template>
    <tr class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
      <td
        v-if="!isEditing"
        class="whitespace-nowrap px-6 py-4 font-medium"
      >
        {{ props.receipt.date }}
      </td>
      <td
        v-else
        class="whitespace-nowrap px-6 py-4"
      >
        <input v-model="editedReceipt.date" type="text" class="w-full" />
      </td>
      <td
        v-if="!isEditing"
        class="whitespace-nowrap px-6 py-4"
      >
        {{ props.receipt.shopping_type.description }}
      </td>
      <td
        v-else
        class="whitespace-nowrap px-6 py-4"
      >
        <input v-model="editedReceipt.shopping_type.description" type="text" class="w-full" />
      </td>
      <td
        v-if="!isEditing"
        class="whitespace-nowrap px-6 py-4 font-medium"
      >
        {{ props.receipt.price }}
      </td>
      <td
        v-else
        class="whitespace-nowrap px-6 py-4"
      >
        <input v-model="editedReceipt.price" type="text" class="w-full" />
      </td>
      <td
        v-if="!isEditing"
        class="whitespace-nowrap px-6 py-4"
      >
        {{ props.receipt.payment_method.name }}
      </td>
      <td
        v-else
        class="whitespace-nowrap px-6 py-4"
      >
        <input v-model="editedReceipt.payment_method.name" type="text" class="w-full" />
      </td>

      <td
        v-if="!isEditing"
        class="whitespace-nowrap px-6 py-4 font-medium"
      >
        {{ props.receipt.shop.name }}
      </td>
      <td
        v-else
        class="whitespace-nowrap px-6 py-4"
      >
        <input v-model="editedReceipt.shop.name" type="text" class="w-full" />
      </td>
      <td
        v-if="!isEditing"
        class="whitespace-nowrap px-6 py-4"
      >
        {{ props.receipt.shopping_group.name  }}
      </td>
      <td
        v-else
        class="whitespace-nowrap px-6 py-4"
      >
        <input v-model="editedReceipt.shopping_group.name" type="text" class="w-full" />
      </td>
      <td
        v-if="!isEditing"
        class="whitespace-nowrap px-6 py-4 font-medium"
      >
        {{ props.receipt.note }}
      </td>
      <td
        v-else
        class="whitespace-nowrap px-6 py-4"
      >
        <input v-model="editedReceipt.note" type="text" class="w-full" />
      </td>
      <td
        v-if="!isEditing"
        class="whitespace-nowrap px-6 py-4"
      >
        {{ props.receipt.user.name }}
      </td>
      <td
        v-else
        class="whitespace-nowrap px-6 py-4"
      >
        <input v-model="editedReceipt.user.name" type="text" class="w-full" />
      </td>
      <td class="whitespace-nowrap px-6 py-4">
        <button
          @click="editReceipt"
          v-if="!isEditing"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
        >
          Edit
        </button>
        <button
          @click="submitEdit"
          v-else
          class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
        >
          Submit
        </button>
        <button 
          @click="delete"
          v-if="!isEditing"
          class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-2"
        >
          Delete
        </button>
        <button
          @click="cancelEdit"
          v-else
          class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-2">
          Cancel
        </button>
      </td>

    </tr>
  </template>
 
<!-- <template>
    <tr class="border-b transition duration-300 ease-in-out hover:bg-neutral-100 dark:border-neutral-500 dark:hover:bg-neutral-600">
        <td class="whitespace-nowrap px-6 py-4 font-medium">{{ props.receipt.date }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ props.receipt.shopping_type.description }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ props.receipt.price }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ props.receipt.payment_method.name }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ props.receipt.shop.name }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ props.receipt.shopping_group.name }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ props.receipt.note }}</td>
        <td class="whitespace-nowrap px-6 py-4">{{ props.receipt.user.name }}</td>
        <td class="whitespace-nowrap px-6 py-4">
            <button @click="editReceipt" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</button>
            <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded ml-2">Delete</button>
        </td>

    </tr>
</template> -->