<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const formData = ref({
    title: '',
    body: '',
    media_files: [],
});

// Handle file input change
const handleFileChange = (event) => {
    formData.value.media_files = Array.from(event.target.files);
};

// Submit the form
const submit = () => {
    const data = new FormData();
    data.append('title', formData.value.title);
    data.append('body', formData.value.body);

    formData.value.media_files.forEach((file) => {
        data.append('media_files[]', file);
    });

    router.post(route('posts.store'), data, {
        onSuccess: () => {
            formData.value = {
                title: '',
                body: '',
                media_files: [],
            };
        },
    });
};
</script>

<template>
    <AppLayout>
        <div>
            <h1>Create Post</h1>
            <form @submit.prevent="submit">
                <input v-model="formData.title" placeholder="Title" required />
                <textarea v-model="formData.body" placeholder="Body" required></textarea>
                <input type="file" multiple @change="handleFileChange" />
                <button type="submit">Post</button>
            </form>
        </div>
    </AppLayout>
</template>