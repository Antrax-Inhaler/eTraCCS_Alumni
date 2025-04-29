<!-- resources/js/Components/Charts/PieChart.vue -->

<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    labels: Array,
    data: Array,
});

const chartRef = ref(null);
let chartInstance = null;

onMounted(() => {
    if (chartRef.value) {
        chartInstance = new Chart(chartRef.value, {
            type: 'pie',
            data: {
                labels: props.labels,
                datasets: [{
                    data: props.data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
            }
        });
    }
});
</script>

<template>
    <canvas ref="chartRef"></canvas>
</template>