<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    conversation: Object,
});

const emit = defineEmits(['close']);

const form = useForm({
    name: props.conversation.name,
    profile_picture: null,
});

const previewImage = ref(props.conversation.profile_picture ? `/storage/${props.conversation.profile_picture}` : null);

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    form.profile_picture = file;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        previewImage.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(
        route('api.chat.update-group', {
            encryptedId: props.conversation.encryptedId
        }), // <-- properly close the route() function here
        {
            preserveScroll: true,
            onSuccess: () => {
                emit('close');
            },
        }
    );
};

</script>

<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Group Settings</h3>
                <button @click="emit('close')" class="text-gray-500 hover:text-gray-700">
                    &times;
                </button>
            </div>
            
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Group Name</label>
                    <input 
                        v-model="form.name" 
                        type="text" 
                        class="w-full border border-gray-300 rounded-md p-2"
                    />
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile Picture</label>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <img 
                                :src="previewImage || '/images/default-group.png'" 
                                class="h-16 w-16 rounded-full object-cover"
                            />
                            <input 
                                type="file" 
                                class="absolute inset-0 opacity-0 cursor-pointer"
                                @change="handleFileChange"
                                accept="image/*"
                            />
                        </div>
                        <span class="text-sm text-gray-500">Click to change</span>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button 
                        type="button" 
                        @click="emit('close')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                        :disabled="form.processing"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>