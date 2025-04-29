<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    trainingAttendeds: {
        type: Array,
        default: () => []
    },
    user: {
        type: Object,
        default: () => ({})
    },
    errors: Object
});

const formData = ref({
    training_name: '',
    institution: '',
    year_attended: '',
    certificate: null,
});

const editingId = ref(null);
const searchQuery = ref('');
const isSubmitting = ref(false);
const certificateInput = ref(null);

const filteredTrainings = computed(() => {
    if (!searchQuery.value) return props.trainingAttendeds;
    return props.trainingAttendeds.filter(training => 
        training.training_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        training.institution.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const submit = () => {
    isSubmitting.value = true;
    const form = new FormData();
    
    Object.keys(formData.value).forEach(key => {
        if (formData.value[key] !== null) {
            form.append(key, formData.value[key]);
        }
    });

    if (editingId.value) {
        router.post(route('profile.trainingAttended.update', editingId.value), {
            _method: 'put',
            ...formData.value,
        }, {
            onSuccess: () => {
                resetForm();
                isSubmitting.value = false;
            },
            onError: () => {
                isSubmitting.value = false;
            }
        });
    } else {
        router.post(route('profile.trainingAttended.store'), form, {
            onSuccess: () => {
                resetForm();
                isSubmitting.value = false;
            },
            onError: () => {
                isSubmitting.value = false;
            }
        });
    }
};

const editItem = (training) => {
    editingId.value = training.id;
    formData.value = { ...training };
};

const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this training?')) {
        router.delete(route('profile.trainingAttended.destroy', id));
    }
};

const resetForm = () => {
    formData.value = {
        training_name: '',
        institution: '',
        year_attended: '',
        certificate: null,
    };
    editingId.value = null;
};

const addCertificate = () => {
    certificateInput.value.click();
};

const handleCertificateUpload = (e) => {
    const file = e.target.files[0];
    if (file) {
        formData.value.certificate = file;
    }
};

const viewCertificate = (training) => {
    // Implement certificate viewing logic
    window.open(training.certificate_url, '_blank');
};

const sortByYear = () => {
    props.trainingAttendeds.sort((a, b) => b.year_attended - a.year_attended);
};
</script>

<template>
    <AppLayout>
        <div class="container-main">
            <div class="container">
                <!-- Header Section with Stats -->
                <div class="card header-card">
                    <div class="header-content">
                        <h1 class="section-title">Training & Certifications</h1>
                        <p class="section-subtitle">Track your professional development journey</p>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">{{ trainingAttendeds.length }}</div>
                                <div class="stat-label">Total Trainings</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ new Date().getFullYear() }}</div>
                                <div class="stat-label">This Year</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ trainingAttendeds.filter(t => t.certificate).length }}</div>
                                <div class="stat-label">Certificates</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="card form-card">
                    <h2 class="form-title">{{ editingId ? 'Edit Training' : 'Add New Training' }}</h2>
                    
                    <form @submit.prevent="submit" class="post-input">
                        <div class="post-input-header">
                            <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" class="post-avatar" >  
                            <input
                                id="training_name"
                                v-model="formData.training_name"
                                placeholder="What training did you attend? (e.g., Leadership Workshop, Coding Bootcamp)"
                                required
                            />
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="institution">Institution</label>
                                <input
                                    id="institution"
                                    v-model="formData.institution"
                                    placeholder="Where was the training? (e.g., University of the Philippines, Coursera)"
                                    required
                                />
                            </div>

                            <div class="form-group">
                                <label for="year_attended">Year Attended</label>
                                <input
                                    id="year_attended"
                                    v-model="formData.year_attended"
                                    type="number"
                                    :max="new Date().getFullYear()"
                                    placeholder="When did you attend? (e.g., 2020)"
                                    required
                                />
                            </div>
                        </div>

                        <div v-if="formData.certificate" class="certificate-preview">
                            <i class="fas fa-file-certificate"></i>
                            <span>{{ formData.certificate.name || 'Certificate attached' }}</span>
                            <button type="button" class="remove-certificate" @click="formData.certificate = null">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <div class="post-actions">
                            <div class="post-attachments">
                                <button type="button" class="attachment-button" @click="addCertificate">
                                    <i class="fas fa-file-certificate"></i> 
                                    {{ formData.certificate ? 'Change Certificate' : 'Add Certificate' }}
                                </button>
                                <input 
                                    type="file" 
                                    ref="certificateInput" 
                                    style="display: none" 
                                    accept="image/*,.pdf"
                                    @change="handleCertificateUpload"
                                >
                            </div>
                            
                            <div class="form-actions">
                                <button 
                                    v-if="editingId" 
                                    type="button" 
                                    class="btn btn-outline" 
                                    @click="resetForm"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="submit" 
                                    class="post-button-small"
                                    :disabled="isSubmitting"
                                >
                                    <span v-if="isSubmitting">
                                        <i class="fas fa-spinner fa-spin"></i> Processing...
                                    </span>
                                    <span v-else>
                                        {{ editingId ? 'Update Training' : 'Save Training' }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Training List Section -->
                <div class="card data-card">
                    <div class="section-header">
                        <h2 class="section-title">Your Training History</h2>
                        <div class="table-controls">
                            <div class="search-wrapper">
                                <i class="fas fa-search"></i>
                                <input 
                                    type="text" 
                                    class="search-input" 
                                    placeholder="Search trainings..." 
                                    v-model="searchQuery"
                                >
                            </div>
                            <button class="btn btn-outline" @click="sortByYear">
                                <i class="fas fa-sort-amount-down"></i> Sort by Year
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table v-if="filteredTrainings.length > 0">
                            <thead>
                                <tr>
                                    <th>Training Name</th>
                                    <th>Institution</th>
                                    <th>Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="training in filteredTrainings" :key="training.id">
                                    <td>
                                        <div class="training-name">
                                            {{ training.training_name }}
                                            <span v-if="training.certificate" class="certificate-badge" title="Has certificate">
                                                <i class="fas fa-certificate"></i>
                                            </span>
                                        </div>
                                    </td>
                                    <td>{{ training.institution }}</td>
                                    <td>
                                        <span class="year-badge">{{ training.year_attended }}</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button 
                                                class="action-btn"
                                                @click="editItem(training)"
                                                title="Edit"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button 
                                                class="action-btn"
                                                @click="deleteItem(training.id)"
                                                title="Delete"
                                            >
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <button 
                                                v-if="training.certificate"
                                                class="action-btn"
                                                @click="viewCertificate(training)"
                                                title="View Certificate"
                                            >
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div v-else class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3>No training records found</h3>
                            <p>Start by adding your first professional training or certification</p>
                            <button class="btn btn-primary" @click="resetForm">
                                <i class="fas fa-plus"></i> Add Training
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.container-main {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Header Card */
.header-card {
    background: linear-gradient(135deg, var(--primary) 0%, #ff8c00 100%);
    color: white;
    padding: 25px;
    border: none;
}

.header-content {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.section-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0;
}

.section-subtitle {
    font-size: 16px;
    opacity: 0.9;
    margin: 0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.stat-card {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    padding: 15px;
    text-align: center;
    backdrop-filter: blur(5px);
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    opacity: 0.9;
}

/* Form Card */
.form-card {
    padding: 25px;
}

.form-title {
    font-size: 20px;
    margin-bottom: 20px;
    color: var(--text-primary);
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-top: 15px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 14px;
    color: var(--text-secondary);
}

.form-group input {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--card-border);
    border-radius: 10px;
    padding: 12px 15px;
    color: var(--text-primary);
    font-size: 14px;
    width: 100%;
    transition: all 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.1);
}

.certificate-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 140, 0, 0.1);
    padding: 10px 15px;
    border-radius: 8px;
    margin-top: 10px;
    font-size: 14px;
}

.certificate-preview i {
    color: var(--primary);
}

.remove-certificate {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    margin-left: auto;
}

.remove-certificate:hover {
    color: var(--danger);
}

/* Data Card */
.data-card {
    padding: 25px;
}

.section-header {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-bottom: 20px;
}

@media (min-width: 768px) {
    .section-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.table-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.search-wrapper {
    position: relative;
    flex: 1;
    max-width: 300px;
}

.search-wrapper i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
}

.search-input {
    padding: 8px 15px 8px 35px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 14px;
    width: 100%;
    transition: all 0.3s;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary);
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--card-border);
}

th {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 14px;
    white-space: nowrap;
}

tr:hover td {
    background: rgba(255, 255, 255, 0.03);
}

.training-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.certificate-badge {
    color: var(--primary);
    font-size: 14px;
}

.year-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.1);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
}

.empty-icon {
    font-size: 48px;
    color: var(--text-secondary);
    margin-bottom: 20px;
}

.empty-state h3 {
    font-size: 18px;
    margin-bottom: 10px;
    color: var(--text-primary);
}

.empty-state p {
    color: var(--text-secondary);
    margin-bottom: 20px;
    max-width: 400px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .table-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .search-wrapper {
        max-width: 100%;
    }
    
    th, td {
        padding: 10px 8px;
        font-size: 13px;
    }
    
    .action-buttons {
        flex-wrap: wrap;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .post-actions {
        flex-direction: column;
        gap: 15px;
    }
    
    .form-actions {
        width: 100%;
        display: flex;
        gap: 10px;
    }
    
    .post-button-small, .btn {
        flex: 1;
        text-align: center;
    }
}
</style>