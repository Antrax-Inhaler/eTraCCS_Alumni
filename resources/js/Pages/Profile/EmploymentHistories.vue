<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import debounce from 'lodash/debounce';
const props = defineProps({
    employmentHistories: {
        type: Array,
        default: () => []
    },
    companyLocations: {
        type: Array,
        default: () => []
    },
    currentEmployment: Object,
    pastEmployment: {
        type: Array,
        default: () => []
    },
    user: {
        type: Object,
        required: true
    },
    pastEmployment: Array,
    companyLocations: Array,
    employmentHistories: Array
});

// Form and UI state
const formData = ref({
    company_name: '',
    job_title: '',
    start_date: '',
    end_date: null,
    latitude: null,
    longitude: null,
    industry: '',
    is_first_job: false,
    months_to_first_job: null,
    institution: '',
    campus: ''
});

const editingId = ref(null);
const searchQuery = ref('');
const isSubmitting = ref(false);
const selectedCompany = ref(null);
const showNewCompanyMessage = ref(false);
const companySuggestions = ref([]);
const markers = ref([]);
const map = ref(null);
const activeTab = ref('current');

const isSearching = ref(false);

const filteredCompanies = computed(() => {
    if (!searchQuery.value) return props.companyLocations;
    return props.companyLocations.filter(company => 
        company.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
});
const searchCompanies = debounce(async (query) => {
    if (query.length < 3) {
        companySuggestions.value = [];
        showNewCompanyMessage.value = false;
        return;
    }

    isSearching.value = true;
    try {
        const response = await axios.get(route('profile.company.suggestions'), {
            params: { query }
        });
        companySuggestions.value = response.data;
        showNewCompanyMessage.value = response.data.length === 0;
    } catch (error) {
        console.error('Search error:', error);
    } finally {
        isSearching.value = false;
    }
}, 300);

const selectCompany = (company) => {
    formData.value = {
        ...formData.value,
        company_name: company.name,
        latitude: company.latitude,
        longitude: company.longitude,
        industry: company.industry
    };
    companySuggestions.value = [];
    showNewCompanyMessage.value = false;
    
    // Center map on selected company
    if (company.latitude && company.longitude) {
        mapCenter.value = { 
            lat: company.latitude, 
            lng: company.longitude 
        };
        markers.value = [{
            position: { lat: company.latitude, lng: company.longitude },
            draggable: true
        }];
    }
};

watch(() => formData.value.company_name, (newVal) => {
    searchCompanies(newVal);
});
const mapCenter = computed(() => {
    // Default center
    const defaultCenter = { lat: 10.3157, lng: 123.8854 };
    
    // If editing and has valid coordinates
    if (editingId.value && formData.value.latitude && formData.value.longitude) {
        const lat = Number(formData.value.latitude);
        const lng = Number(formData.value.longitude);
        if (!isNaN(lat) && !isNaN(lng)) {
            return { lat, lng };
        }
    }
    
    // If valid companies exist
    if (props.companyLocations?.length > 0) {
        const firstCompany = props.companyLocations[0];
        const lat = Number(firstCompany.latitude);
        const lng = Number(firstCompany.longitude);
        if (!isNaN(lat) && !isNaN(lng)) {
            return { lat, lng };
        }
    }
    
    return defaultCenter;
});
const getSafePosition = (lat, lng) => {
    const numLat = Number(lat);
    const numLng = Number(lng);
    return {
        lat: !isNaN(numLat) ? numLat : 0,
        lng: !isNaN(numLng) ? numLng : 0
    };
};
const employmentDuration = (start, end) => {
    if (!start) return 'N/A';
    const startDate = new Date(start);
    const endDate = end ? new Date(end) : new Date();
    
    const years = endDate.getFullYear() - startDate.getFullYear();
    const months = endDate.getMonth() - startDate.getMonth();
    
    let duration = '';
    if (years > 0) duration += `${years} ${years === 1 ? 'year' : 'years'}`;
    if (months > 0) duration += `${duration ? ', ' : ''}${months} ${months === 1 ? 'month' : 'months'}`;
    
    return duration || 'Less than 1 month';
};


const onMapClick = (event) => {
    try {
        if (showNewCompanyMessage.value || !formData.value.company_name) {
            const lat = event.latLng.lat();
            const lng = event.latLng.lng();
            
            if (typeof lat === 'number' && typeof lng === 'number') {
                markers.value = [{
                    position: { lat, lng },
                    draggable: true
                }];
                formData.value.latitude = lat;
                formData.value.longitude = lng;
                showNewCompanyMessage.value = false;
            }
        }
    } catch (error) {
        console.error('Map click error:', error);
    }
};

const openInfoWindow = (company) => {
    selectedCompany.value = company;
};

const selectCompanyFromMap = (company) => {
    selectCompany(company);
    selectedCompany.value = null;
};

const editItem = (history) => {
    editingId.value = history.id;
    formData.value = { ...history };
    activeTab.value = 'form';
};

const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this employment history?')) {
        router.delete(route('profile.employmentHistory.destroy', id));
    }
};
const handleDragEnd = (e) => {
    try {
        const lat = e.latLng.lat();
        const lng = e.latLng.lng();
        
        if (typeof lat === 'number' && typeof lng === 'number') {
            formData.value.latitude = lat;
            formData.value.longitude = lng;
            markers.value = [{
                position: { lat, lng },
                draggable: true
            }];
        }
    } catch (error) {
        console.error('Drag end error:', error);
    }
};
const resetForm = () => {
    formData.value = {
        company_name: '',
        job_title: '',
        start_date: '',
        end_date: null,
        latitude: null,
        longitude: null,
        industry: '',
        is_first_job: false,
        months_to_first_job: null,
        institution: '',
        campus: ''
    };
    editingId.value = null;
    markers.value = [];
    activeTab.value = 'current';
};

const submit = async () => {
    try {
        isSubmitting.value = true;
        const method = editingId.value ? 'put' : 'post';
        const url = editingId.value ? 
            route('profile.employmentHistory.update', editingId.value) : 
            route('profile.employmentHistory.store');
        
        await router[method](url, formData.value);
        resetForm();
    } catch (error) {
        console.error('Submission error:', error);
    } finally {
        isSubmitting.value = false;
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'Present';
    const options = { year: 'numeric', month: 'short' };
    return new Date(dateString).toLocaleDateString(undefined, options);
};

// Initialize map markers
// onMounted(() => {
//     // Initialize with existing company locations
//     if (props.companyLocations && props.companyLocations.length > 0) {
//         markers.value = props.companyLocations.map(company => ({
//             position: { lat: company.latitude, lng: company.longitude },
//             draggable: false
//         }));
        
//         // Center map on first company if editing
//         if (editingId.value && formData.value.latitude && formData.value.longitude) {
//             mapCenter.value = {
//                 lat: formData.value.latitude,
//                 lng: formData.value.longitude
//             };
//             markers.value = [{
//                 position: { lat: formData.value.latitude, lng: formData.value.longitude },
//                 draggable: true
//             }];
//         }
//     }
// });

onMounted(() => {
    if (props.companyLocations?.length > 0) {
        markers.value = props.companyLocations.map(company => ({
            position: getSafePosition(company.latitude, company.longitude),
            draggable: false
        }));
    }
    
    // If editing existing with coordinates
    if (editingId.value && formData.value.latitude && formData.value.longitude) {
        markers.value = [{
            position: getSafePosition(formData.value.latitude, formData.value.longitude),
            draggable: true
        }];
    }
});

watch(() => formData.value.latitude, (newVal, oldVal) => {
    if (newVal && formData.value.longitude && (!oldVal || formData.value.longitude !== oldVal)) {
        markers.value = [{
            position: { 
                lat: formData.value.latitude, 
                lng: formData.value.longitude 
            },
            draggable: true
        }];
    }
});
</script>

<template>
    <AppLayout>
        <div class="container-main">
            <div class="container">
                <!-- Header Section -->
                <div class="card header-card">
                    <div class="header-content">
                        <h1 class="section-title">Employment History</h1>
                        <p class="section-subtitle">Track your professional journey and accomplishments</p>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">{{ currentEmployment ? 1 : 0 }}/1</div>
                                <div class="stat-label">Current Position</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ pastEmployment.length }}</div>
                                <div class="stat-label">Past Positions</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ employmentHistories.length }}</div>
                                <div class="stat-label">Total Entries</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="tabs">
                    <button 
                        :class="{ active: activeTab === 'current' }"
                        @click="activeTab = 'current'"
                    >
                        Current Position
                    </button>
                    <button 
                        :class="{ active: activeTab === 'past' }"
                        @click="activeTab = 'past'"
                    >
                        Past Employment
                    </button>
                    <button 
                        :class="{ active: activeTab === 'form' }"
                        @click="activeTab = 'form'"
                    >
                        {{ editingId ? 'Edit' : 'Add' }} Employment
                    </button>
                </div>

                <!-- Current Employment -->
                <div v-if="activeTab === 'current'" class="card employment-card">
                    <div v-if="currentEmployment" class="current-employment">
                        <div class="employment-header">
                            <div class="company-logo">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="employment-details">
                                <h3>{{ currentEmployment.job_title }}</h3>
                                <p class="company-name">{{ currentEmployment.company_name }}</p>
                                <div class="employment-meta">
                                    <span class="employment-dates">
                                        {{ formatDate(currentEmployment.start_date) }} - Present
                                    </span>
                                    <span v-if="currentEmployment.industry" class="industry-badge">
                                        {{ currentEmployment.industry }}
                                    </span>
                                </div>
                                <div v-if="currentEmployment.is_first_job" class="first-job-badge">
                                    <i class="fas fa-star"></i> First Job
                                    <span v-if="currentEmployment.months_to_first_job">
                                        (Found in {{ currentEmployment.months_to_first_job }} months)
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-edit" @click="editItem(currentEmployment)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                    <div v-else class="empty-state">
                        <i class="fas fa-briefcase"></i>
                        <h3>No current employment</h3>
                        <p>Add your current position to showcase your professional status</p>
                        <button class="btn btn-primary" @click="activeTab = 'form'">
                            <i class="fas fa-plus"></i> Add Current Position
                        </button>
                    </div>
                </div>

                <!-- Past Employment -->
                <div v-if="activeTab === 'past'" class="card employment-card">
                    <div v-if="pastEmployment.length > 0" class="timeline">
                        <div v-for="(job, index) in pastEmployment" :key="job.id" class="timeline-item">
                            <div class="timeline-marker">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div class="timeline-content">
                                <h4>{{ job.job_title }}</h4>
                                <p class="company-name">{{ job.company_name }}</p>
                                <div class="employment-meta">
                                    <span class="employment-dates">
                                        {{ formatDate(job.start_date) }} - {{ formatDate(job.end_date) }}
                                    </span>
                                    <span class="employment-duration">
                                        ({{ employmentDuration(job.start_date, job.end_date) }})
                                    </span>
                                </div>
                                <div v-if="job.is_first_job" class="first-job-badge">
                                    <i class="fas fa-star"></i> First Job
                                    <span v-if="job.months_to_first_job">
                                        (Found in {{ job.months_to_first_job }} months)
                                    </span>
                                </div>
                                <div class="timeline-actions">
                                    <button class="btn btn-edit" @click="editItem(job)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button class="btn btn-delete" @click="deleteItem(job.id)">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="empty-state">
                        <i class="fas fa-history"></i>
                        <h3>No past employment</h3>
                        <p>Add your previous positions to complete your professional history</p>
                        <button class="btn btn-primary" @click="activeTab = 'form'">
                            <i class="fas fa-plus"></i> Add Past Position
                        </button>
                    </div>
                </div>

                <!-- Employment Form -->
                <div v-if="activeTab === 'form'" class="card form-card">
                    <h2 class="form-title">{{ editingId ? 'Edit Employment' : 'Add Employment' }}</h2>
                    
                    <form @submit.prevent="submit" class="employment-form">
                        <!-- Company Search Section -->
                        <div class="form-section">
                            <h3 class="section-title">Company Information</h3>
                            <div class="form-group">
    <label for="company_name">Company Name</label>
    <div class="search-container">
      <input
        id="company_name"
        v-model="formData.company_name"
        placeholder="Search for company or add new"
        required
        @blur="setTimeout(() => { companySuggestions = [] }, 200)"
      />
      <i class="fas fa-search"></i>
      <div v-if="isSearching" class="search-loading">
        <i class="fas fa-spinner fa-spin"></i>
      </div>
    </div>
    
    <!-- Suggestions dropdown -->
    <div v-if="companySuggestions.length > 0" class="suggestions-dropdown">
      <div 
        v-for="company in companySuggestions" 
        :key="company.id"
        class="suggestion-item"
        @mousedown="selectCompany(company)"
      >
        <i class="fas fa-building"></i>
        <div class="suggestion-details">
          <div class="company-name">{{ company.name }}</div>
          <div v-if="company.industry" class="company-industry">
            {{ company.industry }}
          </div>
        </div>
      </div>
    </div>
    
    <!-- New company message -->
    <div v-if="showNewCompanyMessage && formData.company_name.length >= 3" 
         class="new-company-message">
      <i class="fas fa-info-circle"></i>
      <p>Company not found. It will be created when you save.</p>
    </div>
  </div>
  <div class="form-group map-container">
    <label>Company Location</label>
    <p class="map-instructions">
      {{
        formData.latitude && formData.longitude ? 
        'Location selected' : 
        showNewCompanyMessage ? 
        'Click on the map to select company location' : 
        'Select a company from search or click on the map'
      }}
    </p>
    <GMapMap
      ref="map"
      :center="mapCenter"
      :zoom="formData.latitude && formData.longitude ? 14 : 12"
      style="width: 100%; height: 300px; border-radius: 8px;"
      @click="onMapClick"
      :options="{
        gestureHandling: 'greedy',
        styles: [
          // ... your existing styles ...
        ]
      }"
    >
      <!-- Draggable marker -->
      <GMapMarker
        v-for="(marker, index) in markers"
        :key="'marker-'+index"
        :position="marker.position"
        :draggable="marker.draggable"
        @dragend="handleDragEnd"
        :icon="{
          path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
          fillColor: '#4CAF50',
          fillOpacity: 1,
          strokeWeight: 0,
          scale: 1.5,
          anchor: { x: 12, y: 24 }
        }"
      />

      <!-- Existing company markers -->
      <GMapMarker
        v-for="company in props.companyLocations"
        :key="'company-'+company.id"
        :position="getSafePosition(company.latitude, company.longitude)"
        :clickable="true"
        @click="openInfoWindow(company)"
        :icon="{
          path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
          fillColor: '#ff8c00',
          fillOpacity: 1,
          strokeWeight: 0,
          scale: 1.5,
          anchor: { x: 12, y: 24 }
        }"
      >
        <!-- Info window content -->
        <GMapInfoWindow v-if="selectedCompany === company">
          <!-- ... your existing info window content ... -->
        </GMapInfoWindow>
      </GMapMarker>
    </GMapMap>
  </div>

                        </div>

                        <!-- Job Details Section -->
                        <div class="form-section">
                            <h3 class="section-title">Job Details</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="job_title">Job Title</label>
                                    <input
                                        id="job_title"
                                        v-model="formData.job_title"
                                        placeholder="Your position at the company"
                                        required
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="industry">Industry</label>
                                    <input
                                        id="industry"
                                        v-model="formData.industry"
                                        placeholder="Company industry/sector"
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input
                                        id="start_date"
                                        v-model="formData.start_date"
                                        type="date"
                                        required
                                    />
                                </div>
                                <div class="form-group">
                                    <label for="end_date">End Date (leave blank if current)</label>
                                    <input
                                        id="end_date"
                                        v-model="formData.end_date"
                                        type="date"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- First Job Section -->
                        <div class="form-section">
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input 
                                        type="checkbox" 
                                        v-model="formData.is_first_job"
                                        class="checkbox-input"
                                    />
                                    <span>This is my first job after graduation?</span>
                                </label>
                            </div>

                            <div v-if="formData.is_first_job" class="form-grid">
                                <div class="form-group">
                                    <label for="months_to_first_job">Months to find first job</label>
                                    <input
                                        id="months_to_first_job"
                                        v-model="formData.months_to_first_job"
                                        type="number"
                                        min="0"
                                        max="120"
                                        placeholder="How many months it took to find this job"
                                    />
                                </div>
                                <div class="form-group" v-if="showInstitutionField">
                                    <label for="institution">Educational Institution</label>
                                    <input
                                        id="institution"
                                        v-model="formData.institution"
                                        placeholder="Where you graduated from"
                                    />
                                </div>
                                <div class="form-group" v-if="formData.institution.includes('Mindoro State University')">
                                    <label for="campus">Campus</label>
                                    <select
                                        id="campus"
                                        v-model="formData.campus"
                                    >
                                        <option value="">Select campus</option>
                                        <option v-for="campus in campusOptions" :value="campus">{{ campus }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button 
                                type="button" 
                                class="btn btn-outline"
                                @click="resetForm"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit" 
                                class="btn btn-primary"
                                :disabled="isSubmitting"
                            >
                                <i v-if="isSubmitting" class="fas fa-spinner fa-spin"></i>
                                {{ isSubmitting ? 'Saving...' : (editingId ? 'Update' : 'Save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.container-main {
    padding: 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    backdrop-filter: blur(12px);
    border-radius: 14px;
    padding: 20px;
    transition: all 0.3s;
    margin-bottom: 15px;
}

.card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    transform: translateY(-2px);
}

/* Header Card */
.header-card {
    background: linear-gradient(135deg, var(--primary) 0%, #ff8c00 100%);
    color: white;
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

/* Tabs Navigation */
.tabs {
    display: flex;
    gap: 10px;
    border-bottom: 1px solid var(--card-border);
    padding-bottom: 10px;
}

.tabs button {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    background: transparent;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 500;
}

.tabs button.active {
    background: var(--primary-light);
    color: var(--primary);
    font-weight: 600;
}

.tabs button:hover:not(.active) {
    background: rgba(255,255,255,0.05);
}

/* Employment Cards */
.employment-card {
    padding: 25px;
}

.current-employment {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
}

.employment-header {
    display: flex;
    gap: 15px;
}

.company-logo {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: rgba(255, 140, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
    font-size: 24px;
}

.employment-details {
    flex: 1;
}

.employment-details h3 {
    margin: 0 0 5px 0;
    font-size: 18px;
}

.company-name {
    margin: 0 0 10px 0;
    font-weight: 500;
    color: var(--text-primary);
}

.employment-meta {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.employment-dates {
    font-size: 14px;
    color: var(--text-secondary);
}

.industry-badge {
    background: rgba(255, 140, 0, 0.1);
    color: var(--primary);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.first-job-badge {
    background: rgba(75, 192, 192, 0.1);
    color: #4bc0c0;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 8px;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
    border-left: 2px solid var(--card-border);
    padding-left: 30px;
}

.timeline-item:last-child {
    border-left: 2px solid transparent;
}

.timeline-marker {
    position: absolute;
    left: -16px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: rgba(255, 140, 0, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary);
}

.timeline-content {
    padding-top: 5px;
}

.timeline-content h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
}

.timeline-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

/* Form Styles */
.form-card {
    padding: 25px;
}

.form-title {
    font-size: 20px;
    margin-bottom: 20px;
    color: var(--text-primary);
}

.employment-form {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.form-section {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.section-title {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 5px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: 14px;
    color: var(--text-primary);
    font-weight: 500;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-input {
    width: 16px;
    height: 16px;
}
.checkbox-input:checked{
    color: black !important;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

input, select {
    padding: 12px 15px;
    border: 1px solid var(--card-border);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    font-size: 14px;
    transition: all 0.2s;
}

input:focus, select:focus {
    outline: none;
    border-color: var(--primary);
    background: rgba(255, 255, 255, 0.1);
}

.search-container {
    position: relative;
}

.search-container i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
}

.suggestions-dropdown {
    position: absolute;
    width: 100%;
    max-height: 300px;
    overflow-y: auto;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 0 0 10px 10px;
    z-index: 10;
    margin-top: -10px;
}

.suggestion-item {
    padding: 12px 15px;
    display: flex;
    gap: 10px;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s;
}

.suggestion-item:hover {
    background: rgba(255, 140, 0, 0.1);
}

.suggestion-item strong {
    display: block;
    font-size: 14px;
}

.suggestion-item small {
    font-size: 12px;
    color: var(--text-secondary);
}

.new-company-message {
    display: flex;
    gap: 10px;
    align-items: center;
    background: rgba(255, 140, 0, 0.1);
    padding: 12px 15px;
    border-radius: 10px;
    margin-top: 10px;
}

.new-company-message i {
    color: var(--primary);
}

.map-container {
    margin-top: 20px;
}

.map-instructions {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 10px;
}

.info-window {
    padding: 10px;
    max-width: 200px;
}

.info-window h4 {
    margin: 0 0 5px 0;
    font-size: 14px;
}

.info-window p {
    margin: 0 0 10px 0;
    font-size: 12px;
    color: var(--text-secondary);
}

/* Buttons */
.btn {
    padding: 10px 16px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: #e67e00;
    transform: translateY(-1px);
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--primary);
    color: var(--primary);
}

.btn-outline:hover {
    background: var(--primary-light);
}

.btn-edit {
    background: rgba(255, 140, 0, 0.1);
    color: var(--primary);
}

.btn-edit:hover {
    background: rgba(255, 140, 0, 0.2);
}

.btn-delete {
    background: rgba(244, 67, 54, 0.1);
    color: var(--danger);
}

.btn-delete:hover {
    background: rgba(244, 67, 54, 0.2);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

/* Empty States */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
}

.empty-state i {
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

/* Responsive Adjustments */
@media (max-width: 768px) {
    .current-employment {
        flex-direction: column;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .tabs {
        overflow-x: auto;
        padding-bottom: 5px;
    }
    
    .tabs button {
        white-space: nowrap;
    }
}

@media (max-width: 480px) {
    .employment-header {
        flex-direction: column;
    }
    
    .company-logo {
        margin-bottom: 15px;
    }
    
    .timeline-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
.info-window {
  color: #333;
  background: white;
  padding: 12px;
  border-radius: 8px;
  min-width: 250px;
}

.info-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.info-header i {
  color: #ff8c00;
  font-size: 18px;
}

.info-header h4 {
  margin: 0;
  font-size: 16px;
}

.info-industry {
  margin: 0 0 8px 0;
  font-size: 14px;
  color: #666;
}

.info-employees {
  margin: 8px 0 4px 0;
  font-size: 14px;
  font-weight: 500;
}

.user-profiles {
  display: flex;
  margin-bottom: 8px;
  padding-left: 8px;
}

.user-profile {
  position: relative;
  transition: all 0.3s;
}

.user-profile:hover {
  transform: translateY(-4px);
}

.profile-image {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid white;
  object-fit: cover;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-tooltip {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: #333;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s;
  margin-bottom: 8px;
}

.user-profile:hover .profile-tooltip {
  opacity: 1;
  visibility: visible;
}

.more-users {
  font-size: 12px;
  color: #666;
  margin: 4px 0;
}

.no-employees {
  font-size: 13px;
  color: #666;
  margin: 8px 0;
  font-style: italic;
}

.btn-select {
  margin-top: 8px;
  width: 100%;
  background: #ff8c00;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-select:hover {
  background: #e67e00;
}
</style>