<template>
    <div class="bg-white rounded-lg shadow p-4 text-center">
      <i :class="icon" class="text-4xl mb-2"></i>
      <h3 class="text-lg font-semibold">{{ title }}</h3>
      <p class="text-2xl font-bold">{{ value }}</p>
    </div>
  </template>
  
  <script setup>
  defineProps({
    title: String,
    value: [String, Number],
    icon: String,
  });
  </script>