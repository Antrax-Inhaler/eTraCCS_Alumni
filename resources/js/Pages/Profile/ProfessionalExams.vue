<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    professionalExams: {
        type: Array,
        default: () => []
    },
    user: {
        type: Object,
        required: true
    }
});

const formData = ref({
    exam_name: '',
    exam_date: '',
    license_number: '',
});

const editingId = ref(null);
const searchQuery = ref('');
const isSubmitting = ref(false);

const filteredExams = computed(() => {
    if (!Array.isArray(props.professionalExams)) return [];
    if (!searchQuery.value) return props.professionalExams;
    
    return props.professionalExams.filter(exam => {
        if (!exam) return false;
        return (
            String(exam.exam_name || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            String(exam.license_number || '').toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    });
});

const submit = async () => {
    try {
        isSubmitting.value = true;
        
        if (editingId.value) {
            await router.put(route('profile.professionalExam.update', editingId.value), formData.value);
        } else {
            await router.post(route('profile.professionalExam.store'), formData.value);
        }
    } catch (error) {
        console.error('Submission error:', error);
    } finally {
        isSubmitting.value = false;
        resetForm();
    }
};

const editItem = (exam) => {
    editingId.value = exam.id;
    formData.value = { ...exam };
};

const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this professional exam?')) {
        router.delete(route('profile.professionalExam.destroy', id));
    }
};

const resetForm = () => {
    formData.value = {
        exam_name: '',
        exam_date: '',
        license_number: '',
    };
    editingId.value = null;
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
};
</script>

<template>
    <AppLayout>
        <div class="container-main">
            <div class="container">
                <!-- Header Section with Stats -->
                <div class="card header-card">
                    <div class="header-content">
                        <h1 class="section-title">Professional Certifications</h1>
                        <p class="section-subtitle">Manage your professional licenses and certifications</p>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">{{ professionalExams.length }}</div>
                                <div class="stat-label">Total Exams</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ new Date().getFullYear() }}</div>
                                <div class="stat-label">Current Year</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ professionalExams.filter(e => e.license_number).length }}</div>
                                <div class="stat-label">Licensed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="card form-card">
                    <h2 class="form-title">{{ editingId ? 'Edit Certification' : 'Add New Certification' }}</h2>
                    
                    <form @submit.prevent="submit" class="post-input">
                        <div class="post-input-header">
                            <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" class="post-avatar" >  
                            <input
                                id="exam_name"
                                v-model="formData.exam_name"
                                placeholder="What exam did you take? (e.g., CPA, PMP, Bar Exam)"
                                required
                            />
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="exam_date">Exam Date</label>
                                <input
                                    id="exam_date"
                                    v-model="formData.exam_date"
                                    type="date"
                                    :max="new Date().toISOString().split('T')[0]"
                                    required
                                />
                            </div>

                            <div class="form-group">
                                <label for="license_number">License Number (if applicable)</label>
                                <input
                                    id="license_number"
                                    v-model="formData.license_number"
                                    placeholder="Enter your license/certification number"
                                />
                            </div>
                        </div>

                        <div class="post-actions">
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
                                        {{ editingId ? 'Update Certification' : 'Save Certification' }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Exams List Section -->
                <div class="card data-card">
                    <div class="section-header">
                        <h2 class="section-title">Your Professional Certifications</h2>
                        <div class="table-controls">
                            <div class="search-wrapper">
                                <i class="fas fa-search"></i>
                                <input 
                                    type="text" 
                                    class="search-input" 
                                    placeholder="Search certifications..." 
                                    v-model="searchQuery"
                                >
                            </div>
                            <button class="btn btn-outline" @click="sortByYear">
                                <i class="fas fa-sort-amount-down"></i> Sort by Date
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table v-if="filteredExams.length > 0">
                            <thead>
                                <tr>
                                    <th>Exam Name</th>
                                    <th>Date Taken</th>
                                    <th>License Number</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="exam in filteredExams" :key="exam.id">
                                    <td>
                                        <div class="exam-name">
                                            {{ exam.exam_name }}
                                            <span v-if="exam.license_number" class="license-badge" title="Licensed">
                                                <i class="fas fa-id-card"></i>
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="date-badge">{{ formatDate(exam.exam_date) }}</span>
                                    </td>
                                    <td>
                                        <span v-if="exam.license_number" class="license-number">
                                            {{ exam.license_number }}
                                        </span>
                                        <span v-else class="no-license">N/A</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button 
                                                class="action-btn"
                                                @click="editItem(exam)"
                                                title="Edit"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button 
                                                class="action-btn"
                                                @click="deleteItem(exam.id)"
                                                title="Delete"
                                            >
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div v-else class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-id-card-alt"></i>
                            </div>
                            <h3>No professional exams found</h3>
                            <p>Add your first professional certification to get started</p>
                            <button class="btn btn-primary" @click="resetForm">
                                <i class="fas fa-plus"></i> Add Certification
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Reuse your existing card styles and add these specific styles */

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
.exam-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.license-badge {
    color: var(--primary);
    font-size: 14px;
}

.date-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.1);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.license-number {
    font-family: monospace;
    background: rgba(255, 255, 255, 0.05);
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 13px;
}

.no-license {
    color: var(--text-secondary);
    font-style: italic;
    font-size: 13px;
}

.empty-icon .fa-id-card-alt {
    color: var(--primary);
    font-size: 48px;
    margin-bottom: 20px;
}

/* Responsive table adjustments */
@media (max-width: 768px) {
    table {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    
    .exam-name {
        min-width: 150px;
    }
}

@media (max-width: 480px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .table-controls {
        width: 100%;
        flex-direction: column;
        gap: 10px;
    }
    
    .search-wrapper {
        max-width: 100%;
    }
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
</style>