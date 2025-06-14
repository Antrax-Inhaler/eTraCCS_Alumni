<template>
  <div class="overflow-x-auto">
    <table class="min-w-full border-collapse">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 border"><input type="checkbox" v-model="selectAll" /></th>
          <th class="p-2 border">Name</th>
          <th class="p-2 border">Email</th>
          <th class="p-2 border">Type</th>
          <th class="p-2 border">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="recipient in recipients" :key="recipient.type + '-' + recipient.id">
          <td class="p-2 border text-center">
            <input
              type="checkbox"
              :value="{ id: recipient.id, type: recipient.type }"
              v-model="selectedRecipients"
            />
          </td>
          <td class="p-2 border">{{ recipient.name }}</td>
          <td class="p-2 border">{{ recipient.email }}</td>
          <td class="p-2 border capitalize">
            {{ recipient.type === 'user' ? 'System User' : 'Alumni' }}
          </td>
          <td class="p-2 border text-center">
            <button
              @click="viewDetails(recipient)"
              class="text-blue-500 hover:text-blue-700"
            >
              Show
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  recipients: {
    type: Array,
    required: true
  },
  selectedRecipientsModel: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['update:selectedRecipients']);

const selectedRecipients = computed({
  get() {
    return props.selectedRecipientsModel;
  },
  set(value) {
    emit('update:selectedRecipients', value);
  }
});

const selectAll = computed({
  get() {
    return selectedRecipients.value.length === props.recipients.length;
  },
  set(value) {
    if (value) {
      emit(
        'update:selectedRecipients',
        props.recipients.map(r => ({ id: r.id, type: r.type }))
      );
    } else {
      emit('update:selectedRecipients', []);
    }
  }
});

const viewDetails = (recipient) => {
  if (recipient.type === 'alumni') {
    router.get(route('alumni.show', recipient.id));
  }
};
</script>