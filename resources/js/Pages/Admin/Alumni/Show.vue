<template>
  <div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
      <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <!-- Contact Info -->
        <div class="mb-8">
          <h3 class="text-lg font-medium text-gray-900">User Details</h3>
          <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm font-medium text-gray-500">Name</p>
              <p class="mt-1 text-sm text-gray-900">{{ user.name }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Email</p>
              <p class="mt-1 text-sm text-gray-900">{{ user.email }}</p>
            </div>
          </div>
        </div>

        <!-- Message History -->
        <h3 class="text-lg font-medium text-gray-900 mb-4">Message History</h3>

        <div v-if="messages.data.length > 0" class="space-y-4">
          <div v-for="message in messages.data" :key="message.id" class="border border-gray-200 rounded-lg p-4">
            <div class="flex justify-between items-start">
              <div>
                <h4 class="text-md font-medium text-gray-900">{{ message.subject }}</h4>
                <p class="text-sm text-gray-500 mt-1">Sent via {{ message.method }} • {{ message.sent_at }}</p>
              </div>
              <span 
                class="px-2 py-1 text-xs rounded-full"
                :class="{
                  'bg-green-100 text-green-800': message.status === 'sent',
                  'bg-red-100 text-red-800': message.status === 'failed'
                }"
              >
                {{ message.status }}
              </span>
            </div>
            <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">{{ message.content }}</div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No messages have been sent to this user yet.
        </div>

        <!-- Pagination -->
        <div class="mt-4">
          <Pagination :links="messages.links" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  id: { type: Number, required: true }
});

const user = ref(null);
const messages = ref({ data: [], links: [] });

onMounted(async () => {
  try {
    const res = await fetch(`/api/users/${props.id}`);
    const data = await res.json();

    user.value = data.user;
    messages.value = data.messages;
  } catch (err) {
    console.error('Failed to load user data:', err);
  }
});
</script>