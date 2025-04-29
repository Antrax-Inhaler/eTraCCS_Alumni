<!-- resources/js/Components/Charts/BarChart.vue -->

<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    labels: Array,
    data: Array,
    label: String,
});

const chartRef = ref(null);
let chartInstance = null;

onMounted(() => {
    if (chartRef.value) {
        chartInstance = new Chart(chartRef.value, {
            type: 'bar',
            data: {
                labels: props.labels,
                datasets: [{
                    label: props.label,
                    data: props.data,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>

<template>
    <canvas ref="chartRef"></canvas>
</template>