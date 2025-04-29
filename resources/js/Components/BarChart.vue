<template>
    <div>
      <canvas ref="chart"></canvas>
    </div>
  </template>
  
  <script setup>
  import { onMounted, ref } from 'vue';
  import { Chart } from 'chart.js/auto'; // Corrected import
  
  const props = defineProps({
    data: Array,
  });
  
  const chart = ref(null);
  
  onMounted(() => {
    new Chart(chart.value, {
      type: 'bar',
      data: {
        labels: props.data.map(item => item.employment_type),
        datasets: [{
          label: 'Number of Graduates',
          data: props.data.map(item => item.total),
          backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
        }],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      },
    });
  });
  </script>