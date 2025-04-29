<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';

const props = defineProps({
    alumni: Array,
    filters: Object
});

const activeTab = ref('degrees');
const expandedUsers = ref([]);
const search = ref(props.filters.search || '');
const degreeLevel = ref(props.filters.degree_level || '');
const certification = ref(props.filters.certification || '');
const training = ref(props.filters.training || '');

const degreeLevels = [
    { value: '', label: 'All Degrees' },
    { value: 'bachelor', label: 'Bachelor' },
    { value: 'master', label: 'Master' },
    { value: 'phd', label: 'PhD' },
    { value: 'doctorate', label: 'Doctorate' }
];

const toggleUser = (userId) => {
    const index = expandedUsers.value.indexOf(userId);
    if (index === -1) {
        expandedUsers.value.push(userId);
    } else {
        expandedUsers.value.splice(index, 1);
    }
};

const filter = () => {
    router.get(route('admin.educational-tracking'), {
        search: search.value,
        degree_level: degreeLevel.value,
        certification: certification.value,
        training: training.value
    }, {
        preserveState: true,
        replace: true
    });
};

// Debounce the search input
watch(search, debounce(() => {
    filter();
}, 300));

// Immediate filter for other fields
watch([degreeLevel, certification, training], () => {
    filter();
});
</script>

<template>
    <AdminLayout>
        <div class="main-content">
            <h1 class="page-title">Educational Tracking System</h1>

            <!-- Search and Filters -->
            <div class="data-section">
                <div class="filter-grid">
                    <!-- Search -->
                    <div class="filter-group">
                        <label>Search</label>
                        <input
                            type="text"
                            v-model="search"
                            placeholder="Search by name or email"
                        />
                    </div>

                    <!-- Degree Level Filter -->
                    <div class="filter-group">
                        <label>Degree Level</label>
                        <select v-model="degreeLevel">
                            <option v-for="level in degreeLevels" :value="level.value">{{ level.label }}</option>
                        </select>
                    </div>

                    <!-- Certification Filter -->
                    <div class="filter-group" v-if="activeTab === 'certifications'">
                        <label>Certification</label>
                        <input
                            type="text"
                            v-model="certification"
                            placeholder="Filter by certification"
                        />
                    </div>

                    <!-- Training Filter -->
                    <div class="filter-group" v-if="activeTab === 'trainings'">
                        <label>Training</label>
                        <input
                            type="text"
                            v-model="training"
                            placeholder="Filter by training"
                        />
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tab-nav">
                <button @click="activeTab = 'degrees'" :class="{ active: activeTab === 'degrees' }">
                    Degree Documentation
                </button>
                <button @click="activeTab = 'advanced'" :class="{ active: activeTab === 'advanced' }">
                    Advanced Studies
                </button>
                <button @click="activeTab = 'certifications'" :class="{ active: activeTab === 'certifications' }">
                    Certifications
                </button>
                <button @click="activeTab = 'trainings'" :class="{ active: activeTab === 'trainings' }">
                    Training Programs
                </button>
            </div>

            <!-- Results Count -->
            <div class="results-count">
                Showing {{ alumni.length }} {{ alumni.length === 1 ? 'result' : 'results' }}
            </div>

            <!-- Degrees Tab -->
            <div v-if="activeTab === 'degrees'" class="space-y-4">
                <div v-for="user in alumni" :key="user.id" class="user-card">
                    <div class="user-header" @click="toggleUser(user.id)">
                        <div class="user-info">
                            <img :src="user.profile_photo_url" class="user-avatar">
                            <div>
                                <h3>{{ user.name }}</h3>
                                <p>{{ user.email }}</p>
                            </div>
                        </div>
                        <div class="toggle-btn">
                            {{ expandedUsers.includes(user.id) ? 'Hide' : 'Show' }} Details
                        </div>
                    </div>

                    <div v-if="expandedUsers.includes(user.id)" class="user-details">
                        <div class="degree-section">
                            <h4>Primary Degree</h4>
                            <div v-if="user.primary_degree" class="degree-card primary">
                                <p><span>Degree:</span> {{ user.primary_degree.degree_earned }}</p>
                                <p><span>Institution:</span> {{ user.primary_degree.institution }}</p>
                                <p><span>Campus:</span> {{ user.primary_degree.campus || 'N/A' }}</p>
                                <p><span>Year Graduated:</span> {{ user.primary_degree.year_graduated }}</p>
                            </div>
                            <div v-else class="no-data">
                                No primary degree recorded
                            </div>
                        </div>

                        <div v-if="user.additional_degrees.length > 0" class="degree-section">
                            <h4>Additional Degrees</h4>
                            <div class="degrees-grid">
                                <div v-for="(degree, index) in user.additional_degrees" :key="index" class="degree-card">
                                    <p><span>Degree:</span> {{ degree.degree_earned }}</p>
                                    <p><span>Institution:</span> {{ degree.institution }}</p>
                                    <p><span>Campus:</span> {{ degree.campus || 'N/A' }}</p>
                                    <p><span>Year Graduated:</span> {{ degree.year_graduated }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Advanced Studies Tab -->
            <div v-if="activeTab === 'advanced'" class="space-y-4">
                <div v-for="user in alumni" :key="user.id" class="user-card">
                    <div class="user-header" @click="toggleUser(user.id)">
                        <div class="user-info">
                            <img :src="user.profile_photo_url" class="user-avatar">
                            <div>
                                <h3>{{ user.name }}</h3>
                                <p>{{ user.email }}</p>
                            </div>
                        </div>
                        <div class="toggle-btn">
                            {{ expandedUsers.includes(user.id) ? 'Hide' : 'Show' }} Details
                        </div>
                    </div>

                    <div v-if="expandedUsers.includes(user.id)" class="user-details">
                        <div v-if="user.advanced_studies.length > 0" class="degrees-grid">
                            <div v-for="(study, index) in user.advanced_studies" :key="index" class="degree-card advanced">
                                <p><span>Degree:</span> {{ study.degree_earned }}</p>
                                <p><span>Institution:</span> {{ study.institution }}</p>
                                <p><span>Campus:</span> {{ study.campus || 'N/A' }}</p>
                                <p><span>Year Graduated:</span> {{ study.year_graduated }}</p>
                            </div>
                        </div>
                        <div v-else class="no-data">
                            No advanced studies recorded
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certifications Tab -->
            <div v-if="activeTab === 'certifications'" class="space-y-4">
                <div v-for="user in alumni" :key="user.id" class="user-card">
                    <div class="user-header" @click="toggleUser(user.id)">
                        <div class="user-info">
                            <img :src="user.profile_photo_url" class="user-avatar">
                            <div>
                                <h3>{{ user.name }}</h3>
                                <p>{{ user.email }}</p>
                            </div>
                        </div>
                        <div class="toggle-btn">
                            {{ expandedUsers.includes(user.id) ? 'Hide' : 'Show' }} Details
                        </div>
                    </div>

                    <div v-if="expandedUsers.includes(user.id)" class="user-details">
                        <div v-if="user.professional_exams.length > 0" class="certifications-grid">
                            <div v-for="(exam, index) in user.professional_exams" :key="index" class="certification-card">
                                <p><span>Exam:</span> {{ exam.exam_name }}</p>
                                <p><span>Date:</span> {{ exam.exam_date }}</p>
                                <p v-if="exam.license_number"><span>License #:</span> {{ exam.license_number }}</p>
                            </div>
                        </div>
                        <div v-else class="no-data">
                            No professional certifications recorded
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trainings Tab -->
            <div v-if="activeTab === 'trainings'" class="space-y-4">
                <div v-for="user in alumni" :key="user.id" class="user-card">
                    <div class="user-header" @click="toggleUser(user.id)">
                        <div class="user-info">
                            <img :src="user.profile_photo_url" class="user-avatar">
                            <div>
                                <h3>{{ user.name }}</h3>
                                <p>{{ user.email }}</p>
                            </div>
                        </div>
                        <div class="toggle-btn">
                            {{ expandedUsers.includes(user.id) ? 'Hide' : 'Show' }} Details
                        </div>
                    </div>

                    <div v-if="expandedUsers.includes(user.id)" class="user-details">
                        <div v-if="user.trainings_attended.length > 0" class="trainings-grid">
                            <div v-for="(training, index) in user.trainings_attended" :key="index" class="training-card">
                                <p><span>Training:</span> {{ training.training_name }}</p>
                                <p><span>Institution:</span> {{ training.institution }}</p>
                                <p><span>Year Attended:</span> {{ training.year_attended }}</p>
                            </div>
                        </div>
                        <div v-else class="no-data">
                            No training programs recorded
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Main Content */
.main-content {
    padding: 20px;
}

.page-title {
    font-size: 24px;
    margin-bottom: 20px;
    color: var(--text-primary);
}

/* Data Sections */
.data-section {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    backdrop-filter: blur(12px);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

/* Filter Grid */
.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.filter-group input,
.filter-group select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    padding: 8px 12px;
    color: var(--text-primary);
    width: 100%;
}

.filter-group input::placeholder {
    color: var(--text-secondary);
    opacity: 0.7;
}

/* Tabs */
.tab-nav {
    display: flex;
    border-bottom: 1px solid var(--card-border);
    margin-bottom: 20px;
}

.tab-nav button {
    padding: 10px 20px;
    background: none;
    border: none;
    color: var(--text-secondary);
    font-weight: 500;
    cursor: pointer;
    position: relative;
}

.tab-nav button.active {
    color: var(--primary);
}

.tab-nav button.active::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 2px;
    background: var(--primary);
}

/* Results Count */
.results-count {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 15px;
}

/* User Cards */
.user-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    overflow: hidden;
}

.user-header {
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background 0.2s;
}

.user-header:hover {
    background: rgba(255, 255, 255, 0.05);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.user-info h3 {
    font-size: 15px;
    font-weight: 500;
    color: var(--text-primary);
}

.user-info p {
    font-size: 13px;
    color: var(--text-secondary);
}

.toggle-btn {
    font-size: 13px;
    color: var(--primary);
    cursor: pointer;
}

.user-details {
    padding: 15px;
    border-top: 1px solid var(--card-border);
}

.degree-section {
    margin-bottom: 20px;
}

.degree-section h4 {
    font-size: 16px;
    margin-bottom: 10px;
    color: var(--text-primary);
}

/* Degree Cards */
.degrees-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.degree-card {
    background: rgba(40, 40, 40, 0.5);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    padding: 15px;
}

.degree-card.primary {
    background: rgba(255, 140, 0, 0.1);
    border-color: rgba(255, 140, 0, 0.3);
}

.degree-card.advanced {
    background: rgba(103, 58, 183, 0.1);
    border-color: rgba(103, 58, 183, 0.3);
}

.degree-card p {
    margin-bottom: 5px;
    font-size: 14px;
}

.degree-card span {
    font-weight: 500;
    color: var(--primary);
}

/* Certification Cards */
.certifications-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.certification-card {
    background: rgba(40, 40, 40, 0.5);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    padding: 15px;
}

.certification-card p {
    margin-bottom: 5px;
    font-size: 14px;
}

.certification-card span {
    font-weight: 500;
    color: var(--primary);
}

/* Training Cards */
.trainings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.training-card {
    background: rgba(40, 40, 40, 0.5);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    padding: 15px;
}

.training-card p {
    margin-bottom: 5px;
    font-size: 14px;
}

.training-card span {
    font-weight: 500;
    color: var(--primary);
}

/* No Data Message */
.no-data {
    color: var(--text-secondary);
    font-style: italic;
    padding: 15px;
    text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .degrees-grid,
    .certifications-grid,
    .trainings-grid {
        grid-template-columns: 1fr;
    }
    
    .user-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .toggle-btn {
        align-self: flex-end;
    }
}
</style>