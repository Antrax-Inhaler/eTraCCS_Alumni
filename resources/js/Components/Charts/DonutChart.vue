<!-- resources/js/Components/Charts/DonutChart.vue -->
<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    labels: Array,
    data: Array,
    label: String,
    colors: {
        type: Array,
        default: () => [
            'rgba(255, 99, 132, 0.7)',
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)'
        ]
    }
});

const chartRef = ref(null);
let chartInstance = null;

onMounted(() => {
    if (chartRef.value) {
        chartInstance = new Chart(chartRef.value, {
            type: 'doughnut',
            data: {
                labels: props.labels,
                datasets: [{
                    label: props.label,
                    data: props.data,
                    backgroundColor: props.colors,
                    borderColor: props.colors.map(color => color.replace('0.7', '1')),
                    borderWidth: 1,
                    cutout: '70%'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((acc, data) => acc + data, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
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