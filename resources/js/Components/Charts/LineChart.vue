<script setup>
import { onMounted, ref } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    labels: {
        type: Array,
        default: () => []
    },
    data: {
        type: Array,
        default: () => []
    },
    label: {
        type: String,
        default: 'Dataset'
    },
    color: {
        type: String,
        default: '#FF8C00'
    }
});

const chartRef = ref(null);
let chartInstance = null;

onMounted(() => {
    if (chartRef.value) {
        renderChart();
    }
});

const renderChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }

    const ctx = chartRef.value.getContext('2d');
    
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: props.labels,
            datasets: [{
                label: props.label,
                data: props.data,
                borderColor: props.color,
                backgroundColor: `${props.color}20`,
                borderWidth: 2,
                tension: 0.1,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
};
</script>

<template>
    <div class="chart-container">
        <canvas ref="chartRef"></canvas>
    </div>
</template>

<style scoped>
.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}
</style>