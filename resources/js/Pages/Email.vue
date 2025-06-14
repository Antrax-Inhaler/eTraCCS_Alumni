<template>
  <div>
    <button @click="sendEmail" class="btn btn-primary">Send Email</button>
  </div>
</template>

<script setup>
import axios from 'axios';

const sendEmail = async () => {
  try {
    const res = await axios.post('/api/send-email');
    alert(res.data.message);
  } catch (error) {
    console.error(error);
    alert('Failed to send email');
  }
};
</script>
