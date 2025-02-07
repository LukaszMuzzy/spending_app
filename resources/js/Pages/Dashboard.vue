<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Bar, Pie } from 'vue-chartjs';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const props = defineProps(['totalByGroup', 'total', 'total1', 'total2', 'start_date', 'end_date']);

const totalByGroup = ref(props.totalByGroup);
const total = ref(props.total);

const currentMonth = new Date(props.start_date).getMonth();

const monthNames = [
  "January", "February", "March", "April", "May", "June",
  "July", "August", "September", "October", "November", "December"
];

const currentMonthName = monthNames[currentMonth];
const currentMonthName_1 = monthNames[(currentMonth - 1 + 12) % 12];
const currentMonthName_2 = monthNames[(currentMonth - 2 + 12) % 12];


const barChartData = computed(() => ({
  labels: [currentMonthName, currentMonthName_1, currentMonthName_2],
  datasets: [
    {
      label: 'Total Spendings',
      backgroundColor: '#42A5F5',
      data: [props.total, props.total1, props.total2]
    }
  ]
}));

const pieChartData = computed(() => ({
  labels: totalByGroup.value.map(group => group.shopping_type.description),
  datasets: [
    {
      label: 'Spendings by Shopping Type',
      backgroundColor: totalByGroup.value.map(() => `#${Math.floor(Math.random()*16777215).toString(16)}`),
      data: totalByGroup.value.map(group => group.total_price)
    }
  ]
}));

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">You're logged in!</div>
                </div>
            </div>
        </div>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex flex-wrap -mx-4">
                            <div class="w-full md:w-1/2 px-4">
                                <h3 class="text-lg font-semibold mb-4">Your total spendings across all spending groups</h3>
                                <div class="h-64">
                                    <Bar :data="barChartData" />
                                </div>
                            </div>
                            <div class="w-full md:w-1/2 px-4">
                                <h3 class="text-lg font-semibold mb-4">Breakdown by Shopping Type for {{ currentMonthName }}</h3>
                                <div class="h-64">
                                    <Pie :data="pieChartData" />
                                </div>
                            </div>
                        </div>
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Detailed Breakdown for {{ currentMonthName }}</h3>
                            <p>Total Spendings: {{ props.total }}</p>
                            <ul class="list-disc list-inside">
                                <li v-for="group in totalByGroup" :key="group.shopping_type.id">
                                    {{ group.shopping_type.description }}: {{ group.total_price }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
