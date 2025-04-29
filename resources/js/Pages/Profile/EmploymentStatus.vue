<script setup>
import { ref, defineProps } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    employmentStatus: Array, // List of user employment statuses
    
});

const formData = ref({
    current_status: 'Employed',
    first_job_duration: '',
    occupation_classification: '',
    company_name: '',
    years_in_company: null,
    job_relevance_to_degree: 'Yes',
});

const editingId = ref(null); // Track which item is being edited

// Submit the form (add or update)
const submit = () => {
    if (editingId.value) {
        // Update existing record
        router.put(route('profile.employmentStatus.update', editingId.value), formData.value, {
            onSuccess: () => {
                resetForm();
            },
        });
    } else {
        // Add new record
        router.post(route('profile.employmentStatus.store'), formData.value, {
            onSuccess: () => {
                resetForm();
            },
        });
    }
};

// Set form data for editing
const editItem = (status) => {
    editingId.value = status.id;
    formData.value = { ...status };
};

// Delete an employment status
const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this employment status?')) {
        router.delete(route('profile.employmentStatus.destroy', id));
    }
};

// Reset the form after submission or cancel
const resetForm = () => {
    formData.value = {
        current_status: 'Employed',
        first_job_duration: '',
        occupation_classification: '',
        company_name: '',
        years_in_company: null,
        job_relevance_to_degree: 'Yes',
    };
    editingId.value = null;
};
</script>

<template>
    <AppLayout>
        <div class="container-main">
    <div class="container">
        <h1>Employment Status</h1>

        <!-- Form for Adding/Editing -->
        <div>
            <h2>{{ editingId ? 'Edit' : 'Add' }} Employment Status</h2>
            <form @submit.prevent="submit" class="profile-form">
                <!-- Current Employment Status -->
                <div class="form-group">
                    <label for="current_status">
                        Current Employment Status
                        <br />
                        <small>Select your current employment status.</small>
                    </label>
                    <select id="current_status" v-model="formData.current_status" required>
                        <option value="Employed">Employed</option>
                        <option value="Unemployed">Unemployed</option>
                        <option value="Self-Employed">Self-Employed</option>
                        <option value="Others">Others</option>
                    </select>
                </div>

                <!-- First Job Duration -->
                <div class="form-group">
                    <label for="first_job_duration">
                        First Job Duration (e.g., 2 years, 6 months)
                        <br />
                        <small>Enter how long you stayed at your first job.</small>
                    </label>
                    <input
                        id="first_job_duration"
                        class="post-input"
                        v-model="formData.first_job_duration"
                        placeholder="e.g., 2 years, 6 months"
                    />
                </div>

                <!-- Occupation Classification -->
                <div class="form-group">
                    <label for="occupation_classification">
                        Occupation Classification (e.g., Engineer, Manager)
                        <br />
                        <small>Enter your occupation classification.</small>
                    </label>
                    <input
                        id="occupation_classification"
                        class="post-input"
                        v-model="formData.occupation_classification"
                        placeholder="e.g., Software Engineer, Marketing Manager"
                    />
                </div>

                <!-- Company Name -->
                <div class="form-group">
                    <label for="company_name">
                        Company Name (e.g., TechCorp, Innovate Solutions)
                        <br />
                        <small>Enter the name of the company you currently work for or last worked at.</small>
                    </label>
                    <input
                        id="company_name"
                        class="post-input"
                        v-model="formData.company_name"
                        placeholder="Enter the company name"
                    />
                </div>

                <!-- Years in Company -->
                <div class="form-group">
                    <label for="years_in_company">
                        Years in Company (e.g., 3)
                        <br />
                        <small>Enter the number of years you have worked at this company.</small>
                    </label>
                    <input
                        id="years_in_company"
                        class="post-input"
                        v-model="formData.years_in_company"
                        type="number"
                        placeholder="e.g., 3"
                    />
                </div>

                <!-- Job Relevance to Degree -->
                <div class="form-group">
                    <label for="job_relevance_to_degree">
                        Is Your Job Relevant to Your Degree?
                        <br />
                        <small>Select whether your current job is related to your degree.</small>
                    </label>
                    <select id="job_relevance_to_degree" v-model="formData.job_relevance_to_degree" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <button type="submit">{{ editingId ? 'Update' : 'Save' }}</button>
                    <button v-if="editingId" type="button" @click="resetForm">Cancel</button>
                </div>
            </form>
        </div>

        <!-- List of Employment Statuses -->
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>First Job Duration</th>
                    <th>Occupation Classification</th>
                    <th>Company Name</th>
                    <th>Years in Company</th>
                    <th>Job Relevance to Degree</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="status in employmentStatus" :key="status.id">
                    <td>{{ status.current_status }}</td>
                    <td>{{ status.first_job_duration || 'N/A' }}</td>
                    <td>{{ status.occupation_classification || 'N/A' }}</td>
                    <td>{{ status.company_name || 'N/A' }}</td>
                    <td>{{ status.years_in_company || 'N/A' }}</td>
                    <td>{{ status.job_relevance_to_degree || 'N/A' }}</td>
                    <td>
                        <button @click="editItem(status)">Edit</button>
                        <button @click="deleteItem(status.id)">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
    </AppLayout>
</template>