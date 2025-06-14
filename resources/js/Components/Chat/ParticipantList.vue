<script setup>
const props = defineProps({
    participants: Array,
    currentUserId: Number,
});

const emit = defineEmits(['add-participant']);
</script>

<template>
    <div class="w-64 border-r border-gray-200 flex flex-col">
        <div class="p-4 border-b border-gray-200">
            <h3 class="font-semibold">Participants</h3>
        </div>
        
        <div class="flex-1 overflow-y-auto p-2">
            <div 
                v-for="participant in participants" 
                :key="participant.user_id"
                class="flex items-center p-2 hover:bg-gray-100 rounded"
            >
                <img 
                    :src="participant.user.profile_photo_url" 
                    class="h-8 w-8 rounded-full mr-2"
                />
                <div>
                    <div class="text-sm font-medium">
                        {{ participant.user.name }}
                        <span v-if="participant.user_id === currentUserId"> (you)</span>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ participant.user.email }}
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-2 border-t border-gray-200">
            <button 
                @click="emit('add-participant')"
                class="w-full text-sm text-blue-500 hover:text-blue-700 flex items-center justify-center"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add participant
            </button>
        </div>
    </div>
</template>