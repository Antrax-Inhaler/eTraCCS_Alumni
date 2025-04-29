<template>
    <div>
      <h1>Admin Login</h1>
      <form @submit.prevent="submit">
        <input v-model="form.email" placeholder="Email" />
        <input type="password" v-model="form.password" placeholder="Password" />
        <button type="submit">Login</button>
      </form>
    </div>
  </template>
  
  <script setup>
  import { useForm } from '@inertiajs/vue3'
  
  const form = useForm({
    email: '',
    password: '',
  })
  
  function submit() {
    form.post('/admin/login')
  }
  </script>
  