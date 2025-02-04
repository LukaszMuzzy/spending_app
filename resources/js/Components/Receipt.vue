<script setup>
import {ref} from 'vue'

let props = defineProps(['receipt', 'shopping_types', 'payment_methods', 'shops', 'shopping_groups']);
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
        <!-- <input v-model="editedReceipt.date" type="text" class="w-full" /> -->
        <div>
          <label class="block mx-2 px-3" for="date" name="date">Date:</label>
          <input
              type="date"
              v-model="editedReceipt.date"
              class=" block
                      min-w-fit
                      border-gray-300
                      focus:border-indigo-300
                      focus:ring
                      focus:ring-indigo-200
                      focus:ring-opacity-50
                      rounded-md
                      shadow-sm
                      mx-2"
          />
          </div>
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
        <!-- <input v-model="editedReceipt.shopping_type.description" type="text" class="w-full" /> -->
        <div>
          <label class="block mx-2 px-3" for="shopping_type_id" name="shopping_type_id">Shopping Type:</label>
          <select  v-model="editedReceipt.shopping_type_id" id="shopping_type_id"
              class=" block
                      border-gray-300
                      focus:border-indigo-300
                      focus:ring
                      focus:ring-indigo-200
                      focus:ring-opacity-50
                      rounded-md
                      shadow-sm
                      mx-2">
          <option disabled value="">Shoping Type</option>
          <option v-for="shopping_type in props.shopping_types" :value="shopping_type.id">{{ shopping_type.description }}</option>
          </select>
      </div>
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
      <div>
        <label class="block mx-2 px-3 w-fit" for="payment_method_id" name="payment_method_id">Payment:</label>

        <select  v-model="editedReceipt.payment_method_id" id="payment_method_id"
            class=" block
                    border-gray-300
                    focus:border-indigo-300
                    focus:ring
                    focus:ring-indigo-200
                    focus:ring-opacity-50
                    rounded-md
                    shadow-sm
                    mx-2">
        <option disabled value="">Payment method</option>
        <option v-for="payment_method in props.payment_methods" :value="payment_method.id">{{ payment_method.name }}</option>
        </select>
    </div>
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
      <div>
        <label class="block mx-2 px-3 w-fit" for="shop_id" name="shop_id">Shop:</label>

        <select  v-model="editedReceipt.shop_id" id="shop_id"
            class=" block
                    border-gray-300
                    focus:border-indigo-300
                    focus:ring
                    focus:ring-indigo-200
                    focus:ring-opacity-50
                    rounded-md
                    shadow-sm
                    mx-2">
        <option disabled value="">Shop</option>
        <option v-for="shop in props.shops" :value="shop.id">{{ shop.name }}</option>
        </select>
    </div>
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
      <div>
        <label class="block mx-2 px-3 w-fit" for="shopping_group_id" name="shopping_group_id">Group:</label>

        <select  v-model="editedReceipt.shopping_group_id" id="shopping_group_id"
            class=" block
                    border-gray-300
                    focus:border-indigo-300
                    focus:ring
                    focus:ring-indigo-200
                    focus:ring-opacity-50
                    rounded-md
                    shadow-sm
                    mx-2">
        <option disabled value="">Shoping Group</option>
        <option v-for="shopping_group in props.shopping_groups" :value="shopping_group.id">{{ shopping_group.name }}</option>
        </select>
    </div>
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
        class="whitespace-nowrap px-6 py-4"
      >
        {{ props.receipt.user.name }}
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