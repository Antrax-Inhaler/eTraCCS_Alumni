<template>
  <div class="h-screen flex flex-col justify-center items-center bg-gray-50">
    <h1 class="text-6xl font-bold text-red-600">404</h1>
    <p class="text-xl mt-4">Oops! Page not found.</p>
    <Link href="/" class="mt-6 text-blue-600 underline">Return to Home</Link>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
</script>
