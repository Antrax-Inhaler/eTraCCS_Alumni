<template>
    <div class="bg-white rounded-lg shadow p-6 text-center">
      <div class="flex items-center justify-center mb-2">
        <span class="text-4xl mr-2">
          <i :class="icon"></i>
        </span>
        <div>
          <h3 class="text-lg font-semibold">{{ title }}</h3>
          <p v-if="trend" class="text-sm text-green-500 flex items-center">
            <i class="mdi mdi-arrow-up-thin text-green-500 mr-1"></i> {{ trend }}
          </p>
        </div>
      </div>
      <p class="text-3xl font-bold">{{ value }}</p>
    </div>
  </template>
  
  <script setup>
  defineProps({
    title: String,
    value: [String, Number],
    icon: String,
    trend: String,
  });
  </script>