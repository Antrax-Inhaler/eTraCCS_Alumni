<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    conversation: Object,
    currentParticipants: Array,
});

const emit = defineEmits(['close']);

const searchQuery = ref('');
const form = useForm({
    user_id: null,
});

const users = ref([]);
const loadingUsers = ref(false);

const searchUsers = async () => {
    if (searchQuery.value.length < 2) {
        users.value = [];
        return;
    }
    
    loadingUsers.value = true;
    try {
        const response = await fetch(`/api/users/search?q=${encodeURIComponent(searchQuery.value)}`);
        const data = await response.json();
        users.value = data.filter(user => !props.currentParticipants.includes(user.id));
    } catch (error) {
        console.error('Search error:', error);
    } finally {
        loadingUsers.value = false;
    }
};

const addParticipant = (user) => {
    form.user_id = user.id;
    form.post(route('api.chat.add-participant', {
        encryptedId: props.conversation.encryptedId,
    }), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>

<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Add Participant</h3>
                <button @click="emit('close')" class="text-gray-500 hover:text-gray-700">
                    &times;
                </button>
            </div>
            
            <div class="mb-4">
                <input 
                    v-model="searchQuery" 
                    @input="searchUsers"
                    type="text" 
                    placeholder="Search users..."
                    class="w-full border border-gray-300 rounded-md p-2"
                />
            </div>
            
            <div v-if="loadingUsers" class="text-center py-4">
                Loading...
            </div>
            
            <div v-else-if="users.length === 0 && searchQuery.length >= 2" class="text-center py-4 text-gray-500">
                No users found
            </div>
            
            <div v-else class="max-h-64 overflow-y-auto">
                <div 
                    v-for="user in users" 
                    :key="user.id"
                    @click="addParticipant(user)"
                    class="flex items-center p-2 hover:bg-gray-100 rounded cursor-pointer"
                >
                    <img 
                        :src="user.profile_photo_url" 
                        class="h-8 w-8 rounded-full mr-2"
                    />
                    <div>
                        <div class="font-medium">{{ user.name }}</div>
                        <div class="text-xs text-gray-500">{{ user.email }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>