<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    educationalBackgrounds: {
        type: Array,
        default: () => []
    },
    user: {
        type: Object,
        required: true
    }
});

const formData = ref({
    degree_earned: '',
    institution: 'Mindoro State University - College of Computer Studies',
    campus: '',
    year_graduated: '',
});

const editingId = ref(null);
const searchQuery = ref('');
const isSubmitting = ref(false);

const filteredBackgrounds = computed(() => {
    if (!Array.isArray(props.educationalBackgrounds)) return [];
    if (!searchQuery.value) return props.educationalBackgrounds;
    
    return props.educationalBackgrounds.filter(background => {
        if (!background) return false;
        return (
            String(background.degree_earned || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            String(background.institution || '').toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            String(background.campus || '').toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    });
});

const submit = async () => {
    try {
        isSubmitting.value = true;
        
        if (editingId.value) {
            await router.put(route('profile.educationalBackground.update', editingId.value), formData.value);
        } else {
            await router.post(route('profile.educationalBackground.store'), formData.value);
        }
    } catch (error) {
        console.error('Submission error:', error);
    } finally {
        isSubmitting.value = false;
        resetForm();
    }
};

const editItem = (background) => {
    editingId.value = background.id;
    formData.value = { ...background };
};

const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this educational background?')) {
        router.delete(route('profile.educationalBackground.destroy', id));
    }
};

const resetForm = () => {
    formData.value = {
        degree_earned: '',
        institution: '',
        campus: '',
        year_graduated: '',
    };
    editingId.value = null;
};

const getEduIcon = (degree) => {
    if (!degree) return 'fa-graduation-cap';
    if (degree.toLowerCase().includes('phd') || degree.toLowerCase().includes('doctor')) return 'fa-user-graduate';
    if (degree.toLowerCase().includes('master')) return 'fa-user-tie';
    if (degree.toLowerCase().includes('bachelor')) return 'fa-graduation-cap';
    if (degree.toLowerCase().includes('associate')) return 'fa-certificate';
    return 'fa-graduation-cap';
};


</script>

<template>
    <AppLayout>
        <div class="container-main">
            <div class="container">
                <!-- Header Section with Stats -->
                <div class="card header-card">
                    <div class="header-content">
                        <h1 class="section-title">Educational Background</h1>
                        <p class="section-subtitle">Your academic journey and qualifications</p>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">{{ educationalBackgrounds.length }}</div>
                                <div class="stat-label">Total Degrees</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ new Date().getFullYear() }}</div>
                                <div class="stat-label">Current Year</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ Math.max(...educationalBackgrounds.map(e => e.year_graduated || 0)) }}</div>
                                <div class="stat-label">Latest Graduation</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <div class="card form-card">
                    <h2 class="form-title">{{ editingId ? 'Edit Education' : 'Add Education' }}</h2>
                    
                    <form @submit.prevent="submit" class="post-input">
                        <div class="post-input-header">
                            <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" class="post-avatar" >  
                            <input
                                id="degree_earned"
                                v-model="formData.degree_earned"
                                placeholder="What degree did you earn? (e.g., BS Computer Science)"
                                required
                            />
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
  <label for="institution">Institution</label>
  <input
    id="institution"
    v-model="formData.institution"
    value="Mindoro State University - College of Computer Studies"
    disabled
    readonly
    class="disabled-input"
  />
</div>

<div class="form-group">
  <label for="campus">Campus</label>
  <select
    id="campus"
    v-model="formData.campus"
    required
    class="campus-select"
  >
    <option value="" disabled selected>Select campus</option>
    <option value="Main">Main Campus</option>
    <option value="Calapan">Calapan Campus</option>
    <option value="Bongabong">Bongabong Campus</option>
  </select>
</div>

                            <div class="form-group">
                                <label for="year_graduated">Year Graduated</label>
                                <input
                                    id="year_graduated"
                                    v-model="formData.year_graduated"
                                    type="number"
                                    :max="new Date().getFullYear()"
                                    :min="1900"
                                    placeholder="Graduation year"
                                    required
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
                                        {{ editingId ? 'Update Education' : 'Save Education' }}
                                    </span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Education List Section -->
                <div class="card data-card">
                    <div class="section-header">
                        <h2 class="section-title">Your Academic History</h2>
                        <div class="table-controls">
                            <div class="search-wrapper">
                                <i class="fas fa-search"></i>
                                <input 
                                    type="text" 
                                    class="search-input" 
                                    placeholder="Search education..." 
                                    v-model="searchQuery"
                                >
                            </div>
                            <button class="btn btn-outline" @click="sortByYear">
                                <i class="fas fa-sort-amount-down"></i> Sort by Year
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table v-if="filteredBackgrounds.length > 0">
                            <thead>
                                <tr>
                                    <th>Degree</th>
                                    <th>Institution</th>
                                    <th>Campus</th>
                                    <th>Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="background in filteredBackgrounds" :key="background.id">
                                    <td>
                                        <div class="degree-info">
                                            <i class="fas" :class="getEduIcon(background.degree_earned)"></i>
                                            {{ background.degree_earned }}
                                        </div>
                                    </td>
                                    <td>{{ background.institution }}</td>
                                    <td>
                                        <span v-if="background.campus" class="campus-badge">
                                            {{ background.campus }}
                                        </span>
                                        <span v-else class="no-campus">N/A</span>
                                    </td>
                                    <td>
                                        <span class="year-badge">{{ background.year_graduated }}</span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button 
                                                class="action-btn"
                                                @click="editItem(background)"
                                                title="Edit"
                                            >
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button 
                                                class="action-btn"
                                                @click="deleteItem(background.id)"
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
                                <i class="fas fa-university"></i>
                            </div>
                            <h3>No educational background found</h3>
                            <p>Add your first degree or qualification to get started</p>
                            <button class="btn btn-primary" @click="resetForm">
                                <i class="fas fa-plus"></i> Add Education
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

.degree-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.degree-info i {
    color: var(--primary);
    font-size: 16px;
    width: 20px;
    text-align: center;
}

.campus-badge {
    display: inline-block;
    background: rgba(255, 255, 255, 0.1);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.no-campus {
    color: var(--text-secondary);
    font-style: italic;
    font-size: 13px;
}

.year-badge {
    display: inline-block;
    background: rgba(255, 140, 0, 0.1);
    color: var(--primary);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.empty-icon .fa-university {
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
    
    .degree-info {
        min-width: 200px;
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
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
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
.form-group input,
.form-group select {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--card-border);
    border-radius: 10px;
    padding: 12px 15px;
    color: var(--text-primary);
    font-size: 14px;
    width: 100%;
    transition: all 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.1);
}

/* Disabled input styling */
.disabled-input {
    background: rgba(255, 255, 255, 0.03) !important;
    color: var(--text-secondary);
    cursor: not-allowed;
}

/* Custom select arrow */
.styled-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23888888'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 16px;
    padding-right: 40px;
}

/* Option styling */
.styled-select option {
    background: var(--card-bg) !important;
    color: black !important;
}

.styled-select option:disabled {
    color: var(--text-secondary);
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