<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const formData = ref({
    company_name: '',
    job_title: '',
    location: '',
    description: '',
    media_files: [],
});

// Handle file input change
const handleFileChange = (event) => {
    formData.value.media_files = Array.from(event.target.files);
};

// Submit the form
const submit = () => {
    const data = new FormData();
    data.append('company_name', formData.value.company_name);
    data.append('job_title', formData.value.job_title);
    data.append('location', formData.value.location);
    data.append('description', formData.value.description);

    formData.value.media_files.forEach((file) => {
        data.append('media_files[]', file);
    });

    router.post(route('job-postings.store'), data, {
        onSuccess: () => {
            formData.value = {
                company_name: '',
                job_title: '',
                location: '',
                description: '',
                media_files: [],
            };
        },
    });
};
</script>

<template>
    <AppLayout>
        <div>
            <h1>Create Job Posting</h1>
            <form @submit.prevent="submit">
                <input v-model="formData.company_name" placeholder="Company Name" required />
                <input v-model="formData.job_title" placeholder="Job Title" required />
                <input v-model="formData.location" placeholder="Location" required />
                <textarea v-model="formData.description" placeholder="Description" required></textarea>
                <input type="file" multiple @change="handleFileChange" />
                <button type="submit">Post Job</button>
            </form>
        </div>
    </AppLayout>
</template>