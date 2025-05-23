<template>
    <AdminLayout>
        <div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Display Total Income -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg dark:border-gray-600 h-32 md:h-64 flex flex-col items-center justify-center">
                    <span class="text-gray-700 dark:text-gray-200 font-bold text-lg">Total Income</span>
                    <span class="text-2xl font-extrabold text-green-600">
                        {{ formattedTotalIncome }}
                    </span>
                </div>
                <!-- Display Today's Income -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg dark:border-gray-600 h-32 md:h-64 flex flex-col items-center justify-center">
                    <span class="text-gray-700 dark:text-gray-200 font-bold text-lg">Today's Income</span>
                    <span class="text-2xl font-extrabold text-blue-600">
                        {{ formattedTodayIncome }}
                    </span>
                </div>
                <!-- Display Number of Customers -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg dark:border-gray-600 h-32 md:h-64 flex flex-col items-center justify-center">
                    <span class="text-gray-700 dark:text-gray-200 font-bold text-lg">Customers (Local)</span>
                    <span class="text-2xl font-extrabold text-purple-600">
                        {{ customerCount }}
                    </span>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-8">
                <canvas id="incomeLineChart"></canvas>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { initFlowbite } from 'flowbite'
import AdminLayout from './Components/AdminLayout.vue'
import Chart from 'chart.js/auto'

const props = defineProps({
    totalIncome: {
        type: [Number, String],
    
    },
    todayIncome: {
        type: [Number, String],
    
    },
    customerCount: {
        type: [Number, String],
   
    },
    stripeCustomerCount: {
        type: [Number, String],
  
    },
})

const formattedTotalIncome = computed(() =>
    Number(props.totalIncome).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })
)
const formattedTodayIncome = computed(() =>
    Number(props.todayIncome).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })
)

onMounted(() => {
    initFlowbite();
    const ctx = document.getElementById('incomeLineChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Total Income', 'Today Income', 'Customer(Local)'],
            datasets: [
                {
                    label: 'Total Income (₱)',
                    data: [400000, 700000, 1000000, 1300000, 1800000, Number(props.totalIncome)],
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    tension: 0.4,
                    yAxisID: 'y',
                },
                {
                    label: "Today's Income (₱)",
                    data: [100000, 250000, 400000, 500000, 800000, Number(props.todayIncome)],
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    tension: 0.4,
                    yAxisID: 'y',
                },
                {
                    label: "Customers (Local)",
                    data: [2, 3, 5, 8, 10, Number(props.customerCount)],
                    borderColor: 'rgba(168, 85, 247, 1)',
                    backgroundColor: 'rgba(168, 85, 247, 0.2)',
                    borderDash: [5, 5],
                    tension: 0.4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            if (context.dataset.label.includes('Income')) {
                                return `${context.dataset.label}: ₱${context.parsed.y.toLocaleString('en-PH')}`;
                            }
                            return `${context.dataset.label}: ${context.parsed.y}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Amount (₱)'
                    },
                    beginAtZero: true,
                    suggestedMin: 0,
                    suggestedMax: Number(props.totalIncome) + 500000,
                },
                y1: {
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Customers (Local)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    beginAtZero: true,
                    suggestedMin: 0,
                    suggestedMax: Number(props.customerCount) + 5,
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
});
</script>

<style scoped>
</style>