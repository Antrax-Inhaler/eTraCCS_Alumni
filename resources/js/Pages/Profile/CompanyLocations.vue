<script setup>
import { ref, defineProps } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    companyLocations: Array, // List of company locations
});

const formData = ref({
    company_name: '',
    latitude: '',
    longitude: '',
    industry: '',
});

const editingId = ref(null); // Track which item is being edited

// Submit the form (add or update)
const submit = () => {
    if (editingId.value) {
        // Update existing record
        router.put(route('profile.companyLocation.update', editingId.value), formData.value, {
            onSuccess: () => {
                resetForm();
            },
        });
    } else {
        // Add new record
        router.post(route('profile.companyLocation.store'), formData.value, {
            onSuccess: () => {
                resetForm();
            },
        });
    }
};

// Set form data for editing
const editItem = (location) => {
    editingId.value = location.id;
    formData.value = { ...location };
};

// Delete a company location
const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this company location?')) {
        router.delete(route('profile.companyLocation.destroy', id));
    }
};

// Reset the form after submission or cancel
const resetForm = () => {
    formData.value = {
        company_name: '',
        latitude: '',
        longitude: '',
        industry: '',
    };
    editingId.value = null;
};
</script>

<template>
    <AppLayout>
        <div>
            <h1>Company Locations</h1>

            <!-- Form for Adding/Editing -->
            <div>
                <h2>{{ editingId ? 'Edit' : 'Add' }} Company Location</h2>
                <form @submit.prevent="submit">
                    <input v-model="formData.company_name" placeholder="Company Name" required />
                    <input v-model="formData.latitude" type="number" step="0.00000001" placeholder="Latitude" required />
                    <input v-model="formData.longitude" type="number" step="0.00000001" placeholder="Longitude" required />
                    <input v-model="formData.industry" placeholder="Industry" required />
                    <button type="submit">{{ editingId ? 'Update' : 'Save' }}</button>
                    <button v-if="editingId" type="button" @click="resetForm">Cancel</button>
                </form>
            </div>

            <!-- List of Company Locations -->
            <table>
                <thead>
                    <tr>
                        <th>Company Name</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Industry</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="location in companyLocations" :key="location.id">
                        <td>{{ location.company_name }}</td>
                        <td>{{ location.latitude }}</td>
                        <td>{{ location.longitude }}</td>
                        <td>{{ location.industry }}</td>
                        <td>
                            <button @click="editItem(location)">Edit</button>
                            <button @click="deleteItem(location.id)">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>