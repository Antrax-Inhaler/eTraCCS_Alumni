<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    title: '',
    body: '',
    media_files: [], // Store multiple files
});
const mediaFiles = ref([]);

const handleFileChange = (event) => {
    mediaFiles.value = Array.from(event.target.files); // Store files in ref
};

const submit = () => {
    const formData = new FormData();
    formData.append('title', form.title);
    formData.append('body', form.body);

    if (mediaFiles.value.length > 0) {
        mediaFiles.value.forEach((file) => {
            formData.append('media_files[]', file); // Use array notation []
        });
    }

    axios.post(route('posts.store'), formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
    })
    .then(() => {
        alert('Post created successfully!');
        form.reset();
        mediaFiles.value = [];
    })
    .catch((error) => {
        console.error(error);
    });
};

</script>

<template>
    <div>
        <h1>Create Post</h1>
        <form @submit.prevent="submit" enctype="multipart/form-data">
            <label for="title">Title:</label>
            <input type="text" id="title" v-model="form.title" required />

            <label for="body">Body:</label>
            <textarea id="body" v-model="form.body" required></textarea>

            <label for="media">Upload Images or Videos:</label>
<input 
    type="file" 
    id="media" 
    multiple 
    @change="handleFileChange" 
    accept="image/*,video/*" 
/>

            <button type="submit">Create</button>
        </form>
    </div>
</template>
