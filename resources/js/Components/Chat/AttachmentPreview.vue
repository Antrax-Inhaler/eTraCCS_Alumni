<script setup>
const props = defineProps({
    preview: Object,
});

const emit = defineEmits(['remove']);
</script>

<template>
    <div class="relative">
        <div v-if="preview.type === 'image'" class="w-24 h-24">
            <img :src="preview.url" class="w-full h-full object-cover rounded" />
        </div>
        <div v-else-if="preview.type === 'video'" class="w-24 h-24 bg-black">
            <video :src="preview.url" class="w-full h-full object-cover rounded"></video>
        </div>
        <div v-else class="w-24 h-24 bg-gray-100 rounded flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span class="text-xs mt-1 text-gray-600 truncate w-full px-1 text-center">{{ preview.name }}</span>
        </div>
        <button 
            @click="emit('remove')"
            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center"
        >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>