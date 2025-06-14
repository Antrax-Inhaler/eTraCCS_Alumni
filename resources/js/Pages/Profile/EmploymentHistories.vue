<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    employmentHistories: {
        type: Array,
        default: () => []
    },
    currentEmployment: {
        type: Object,
        default: null
    },
    pastEmployment: {
        type: Array,
        default: () => []
    },
    companies: {
        type: Array,
        default: () => []
    },
    user: {
        type: Object,
        required: true
    },
    jobHuntingDifficulties: {
        type: Array,
        default: () => []
    },
    jobSources: {
        type: Array,
        default: () => []
    },
    salaryRanges: {
        type: [Array, Object],
        default: () => ({})
    },
    employmentStatuses: {
        type: [Array, Object],
        default: () => ({})
    },
    employerTypes: {
        type: [Array, Object],
        default: () => ({})
    },
    competencyOptions: {
        type: Array,
        default: () => [
            { id: '1', name: 'Technical Skills' },
            { id: '2', name: 'Problem Solving' },
            { id: '3', name: 'Communication' },
            { id: '4', name: 'Teamwork' },
            { id: '5', name: 'Leadership' }
        ]
    },
    jobMaintenanceOptions: {
        type: Array,
        default: () => [
            { id: '1', name: 'On-the-job training' },
            { id: '2', name: 'Professional certifications' },
            { id: '3', name: 'Continuing education' },
            { id: '4', name: 'Mentorship' },
            { id: '5', name: 'Self-study' }
        ]
    },
    curriculumRelevance: {
        type: [Array, Object],
        default: () => ({})
    },
    errors: Object
});
const industryOptions = ref([
    'Information Technology',
    'Software Development',
    'Web Development',
    'Mobile App Development',
    'Data Science & Analytics',
    'Cybersecurity',
    'Cloud Computing',
    'Artificial Intelligence',
    'Machine Learning',
    'Network Administration',
    'Database Administration',
    'IT Consulting',
    'E-commerce',
    'Digital Marketing',
    'Game Development',
    'Telecommunications',
    'Banking & Finance (IT Dept)',
    'Healthcare IT',
    'Education Technology',
    'Government IT Services'
]);
// Normalize object props to arrays
const normalizeOptions = (options) => {
    if (Array.isArray(options)) return options;
    return Object.entries(options || {}).map(([value, label]) => ({ value, label }));
};
const salaryOptions = computed(() => normalizeOptions(props.salaryRanges));
const statusOptions = computed(() => normalizeOptions(props.employmentStatuses));
const employerOptions = computed(() => normalizeOptions(props.employerTypes));
const relevanceOptions = ref([
    { value: 1, label: 'Not Relevant' },
    { value: 2, label: 'Somewhat Relevant' },
    { value: 3, label: 'Relevant' },
    { value: 4, label: 'Very Relevant' }
]);

const companySearch = ref('');
const companySuggestions = ref([]);
const showCompanySuggestions = ref(false);
const companyAddress = ref('');
const companyLocation = ref({
    latitude: null,
    longitude: null,
    country: '',
    city: '',
    province: '',
    region: '',
    barangay: ''
});
const isGoogleMapsLoaded = ref(false);

// Form data structure
const formData = ref({
    company_name: '',
    company_id: null,
    job_title: '',
    start_date: '',
    end_date: null,
    employment_type: 'full-time',
    industry: '',
    is_first_job: false,
    is_current: false,
    months_to_first_job: null,
    first_job_salary: '',
    job_source: '',
    other_source: '',
    difficulties: [],
    other_difficulty: '',
    employment_status: '',
    employer_type: '',
    current_salary: '',
    has_promotion: false,
    has_awards: false,
    awards_details: '',
    job_maintenance: [],
    unemployment_reason: '',
    other_unemployment_reason: '',
    competencies: [],
    curriculum_relevance: null,
    program_suggestions: [],
    nature_of_industry: '', 
    ...companyLocation.value
});

watch(companySearch, (newVal) => {
    if (newVal.length > 2) {
        // Filter existing companies for suggestions
        companySuggestions.value = props.companies.filter(company => 
            company.name.toLowerCase().includes(newVal.toLowerCase())
        );
        showCompanySuggestions.value = true;
    } else {
        companySuggestions.value = [];
        showCompanySuggestions.value = false;
        
        // Clear company selection if search is cleared
        if (!newVal) {
            clearCompanySelection();
        }
    }
});

// Initialize Google Maps Autocomplete
let autocomplete;
const initAutocomplete = () => {
    const input = document.getElementById('company-address');
    if (!input) {
        console.error('Address input not found');
        return;
    }

    if (typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.places === 'undefined') {
        console.error('Google Maps API not fully loaded');
        // Retry after a short delay
        setTimeout(initAutocomplete, 500);
        return;
    }

    autocomplete = new google.maps.places.Autocomplete(input, {
        types: ['address'],
        fields: ['address_components', 'geometry', 'formatted_address']
    });

    autocomplete.addListener('place_changed', () => {
        const place = autocomplete.getPlace();
        if (!place.geometry) {
            console.log("No details available for input: '" + place.name + "'");
            return;
        }

        // Extract address components
        const addressComponents = {};
        place.address_components.forEach(component => {
            component.types.forEach(type => {
                addressComponents[type] = component.long_name;
            });
        });

        // Update company location
        companyLocation.value = {
            latitude: place.geometry.location.lat(),
            longitude: place.geometry.location.lng(),
            country: addressComponents.country || '',
            city: addressComponents.locality || 
                  addressComponents.administrative_area_level_2 || 
                  addressComponents.postal_town || '',
            province: addressComponents.administrative_area_level_1 || '',
            region: addressComponents.administrative_area_level_1 || '',
            barangay: addressComponents.neighborhood || 
                     addressComponents.sublocality_level_1 || 
                     addressComponents.sublocality || ''
        };

        // Update form data with location
        Object.assign(formData.value, companyLocation.value);
        companyAddress.value = place.formatted_address;
    });
    
    isGoogleMapsLoaded.value = true;
};

const loadGoogleMaps = () => {
    if (window.google && window.google.maps) {
        return Promise.resolve();
    }
    
    return new Promise((resolve) => {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=AIzaSyDhDxk7K-eY-Evo5-TPFj8iVacRXbsv1Ss&libraries=places`;
        script.onload = resolve;
        script.onerror = () => console.error('Google Maps script failed to load');
        document.head.appendChild(script);
    });
};

const editingId = ref(null);
const isSubmitting = ref(false);
const activeTab = ref('current');
const showUnemployedFields = ref(false);

// Predefined options
const monthsToFirstJobOptions = [
    { value: 'less_than_1', label: 'Less than 1 month' },
    { value: '1_to_3', label: '1-3 months' },
    { value: '4_to_6', label: '4-6 months' },
    { value: '7_to_12', label: '7-12 months' },
    { value: 'more_than_1', label: 'More than 1 year' },
    { value: 'never', label: 'Never employed' }
];

const unemploymentReasons = [
    { value: 'seeking', label: 'Still seeking employment' },
    { value: 'studying', label: 'Pursuing further studies' },
    { value: 'family', label: 'Family responsibilities' },
    { value: 'health', label: 'Health issues' },
    { value: 'not_interested', label: 'Not interested in employment' },
    { value: 'other', label: 'Other' }
];

// Computed properties
const currentJob = computed(() => props.currentEmployment);
const hasCurrentJob = computed(() => !!currentJob.value);
const isEditing = computed(() => !!editingId.value);
const isNewCompany = computed(() => {
    return !formData.value.company_id && companySearch.value.length > 0;
});

// Methods
const resetForm = () => {
    formData.value = {
        company_name: '',
        company_id: null,
        job_title: '',
        start_date: '',
        end_date: null,
        employment_type: 'full-time',
        industry: '',
        is_first_job: false,
        is_current: false,
        months_to_first_job: null,
        first_job_salary: '',
        job_source: '',
        other_source: '',
        difficulties: [],
        other_difficulty: '',
        employment_status: '',
        employer_type: '',
        current_salary: '',
        has_promotion: false,
        has_awards: false,
        awards_details: '',
        job_maintenance: [],
        nature_of_industry: '',
        unemployment_reason: '',
        other_unemployment_reason: '',
        competencies: [],
        curriculum_relevance: '',
        program_suggestions: [],
        latitude: null,
        longitude: null,
        country: '',
        city: '',
        province: '',
        region: '',
        barangay: ''
    };
    companySearch.value = '';
    companyAddress.value = '';
    companyLocation.value = {
        latitude: null,
        longitude: null,
        country: '',
        city: '',
        province: '',
        region: '',
        barangay: ''
    };
    editingId.value = null;
    showUnemployedFields.value = false;
};

const editItem = (employment) => {
    editingId.value = employment.id;
    formData.value = {
        ...employment,
        company_name: employment.company?.name || '',
        is_current: !employment.end_date,
        difficulties: employment.difficulties ? employment.difficulties.split(',') : [],
        job_maintenance: employment.job_maintenance ? employment.job_maintenance.split(',') : [],
        competencies: employment.competencies ? employment.competencies.split(',') : []
    };
    
    // Set company search and address if editing
    if (employment.company) {
        companySearch.value = employment.company.name;
        companyAddress.value = `${employment.company.city}, ${employment.company.country}`;
        companyLocation.value = {
            latitude: employment.company.latitude,
            longitude: employment.company.longitude,
            country: employment.company.country,
            city: employment.company.city,
            province: employment.company.province,
            region: employment.company.region,
            barangay: employment.company.barangay
        };
    }
    
    showUnemployedFields.value = !!employment.end_date && !employment.is_current;
    activeTab.value = 'form';
};

const submit = async () => {
    try {
        isSubmitting.value = true;
        
        // Prepare the payload with proper formatting
        const payload = {
            company_name: formData.value.company_name,
            job_title: formData.value.job_title,
            nature_of_industry: formData.value.nature_of_industry,
            start_date: formData.value.start_date,
            end_date: formData.value.end_date || null,
            employment_type: formData.value.employment_type,
            industry: formData.value.industry,
            is_first_job: formData.value.is_first_job,
            is_current: formData.value.is_current,
            months_to_first_job: formData.value.months_to_first_job,
            current_salary: formData.value.current_salary,
            job_source: formData.value.job_source,
            other_source: formData.value.other_source,
            difficulties: formData.value.difficulties,
            other_difficulty: formData.value.other_difficulty,
            curriculum_relevance: formData.value.curriculum_relevance || null,            // Convert arrays to comma-separated strings
            job_maintenance: formData.value.job_maintenance.join(','),
            competencies: formData.value.competencies.join(','),
            latitude: companyLocation.value.latitude,
            longitude: companyLocation.value.longitude,
            country: companyLocation.value.country,
            city: companyLocation.value.city,
            province: companyLocation.value.province,
            region: companyLocation.value.region,
            barangay: companyLocation.value.barangay
        };

        if (editingId.value) {
            await router.put(route('profile.employmentHistory.update', editingId.value), payload);
        } else {
            await router.post(route('profile.employmentHistory.store'), payload);
        }

        resetForm();
        activeTab.value = 'current';
    } catch (error) {
        console.error('Error saving employment:', error);
        if (error.response) {
            console.error('Error response:', error.response.data);
        }
    } finally {
        isSubmitting.value = false;
    }
};
const deleteItem = async (id) => {
    if (confirm('Are you sure you want to delete this employment record?')) {
        try {
            await router.delete(route('profile.employmentHistory.destroy', id));
        } catch (error) {
            console.error('Error deleting employment:', error);
        }
    }
};

const formatDate = (date) => {
    return date ? new Date(date).toLocaleDateString() : 'Present';
};

const selectCompany = (company) => {
    companySearch.value = company.name;
    formData.value.company_id = company.id;
    formData.value.company_name = company.name;
    formData.value.industry = company.industry || '';
    
    // Populate location from existing company data
    companyLocation.value = {
        latitude: company.latitude,
        longitude: company.longitude,
        country: company.country,
        city: company.city,
        province: company.province,
        region: company.region,
        barangay: company.barangay
    };
    
    // Update form data with location
    Object.assign(formData.value, companyLocation.value);
    
    // Set the address display
    companyAddress.value = [
        company.barangay,
        company.city,
        company.province,
        company.country
    ].filter(Boolean).join(', ');
    
    showCompanySuggestions.value = false;
};

const clearCompanySelection = () => {
    companySearch.value = '';
    formData.value.company_id = null;
    formData.value.company_name = '';
    formData.value.industry = '';
    companyAddress.value = '';
    
    // Reset all location fields
    companyLocation.value = {
        latitude: null,
        longitude: null,
        country: '',
        city: '',
        province: '',
        region: '',
        barangay: ''
    };
    
    // Clear location from form data
    Object.assign(formData.value, companyLocation.value);
    
    showCompanySuggestions.value = false;
};

// Initialize
onMounted(async () => {
    if (props.currentEmployment) {
        formData.value.is_current = true;
    }
    
    try {
        await loadGoogleMaps();
        initAutocomplete();
    } catch (error) {
        console.error('Failed to initialize Google Maps:', error);
    }
});

// Watch for when Google Maps loads
watch(isGoogleMapsLoaded, (loaded) => {
    if (loaded) {
        initAutocomplete();
    }
});
</script>

<template>
    <AppLayout>
        <div class="container">
            <h1>Employment History</h1>
            
            <!-- Error Display -->
            <div v-if="$page.props.errors" class="errors">
                <div v-for="(error, field) in $page.props.errors" :key="field" class="error">
                    {{ error }}
                </div>
            </div>
            
            <!-- Tabs Navigation -->
            <div class="tabs">
                <button :class="{ active: activeTab === 'current' }" @click="activeTab = 'current'">
                    Current Employment
                </button>
                <button :class="{ active: activeTab === 'past' }" @click="activeTab = 'past'">
                    Past Employment
                </button>
                <button :class="{ active: activeTab === 'form' }" @click="activeTab = 'form'">
                    {{ isEditing ? 'Edit' : 'Add' }} Employment
                </button>
            </div>

            <!-- Current Employment View -->
            <div v-if="activeTab === 'current'" class="card">
                <div v-if="hasCurrentJob" class="current-job">
                    <h2>{{ currentJob.job_title }}</h2>
                    <p class="company">{{ currentJob.company?.name || 'Unknown Company' }}</p>
                    <div class="details">
                        <p><strong>Started:</strong> {{ formatDate(currentJob.start_date) }}</p>
                        <p><strong>Status:</strong> {{ currentJob.employment_status }}</p>
                        <p><strong>Salary:</strong> {{ currentJob.current_salary }}</p>
                        <button @click="editItem(currentJob)" class="btn">Edit</button>
                    </div>
                </div>
                <div v-else class="empty-state">
                    <p>No current employment recorded</p>
                    <button @click="activeTab = 'form'" class="btn">Add Current Job</button>
                </div>
            </div>

            <!-- Past Employment View -->
            <div v-if="activeTab === 'past'" class="card">
                <div v-if="pastEmployment.length > 0" class="past-jobs">
                    <div v-for="job in pastEmployment" :key="job.id" class="job-item">
                        <h3>{{ job.job_title }}</h3>
                        <p class="company">{{ job.company?.name || 'Unknown Company' }}</p>
                        <div class="details">
                            <p>{{ formatDate(job.start_date) }} - {{ formatDate(job.end_date) }}</p>
                            <button @click="editItem(job)" class="btn">Edit</button>
                            <button @click="deleteItem(job.id)" class="btn danger">Delete</button>
                        </div>
                    </div>
                </div>
                <div v-else class="empty-state">
                    <p>No past employment recorded</p>
                </div>
            </div>

            <!-- Employment Form -->
            <div v-if="activeTab === 'form'" class="card">
                <h2>{{ isEditing ? 'Edit Employment' : 'Add Employment' }}</h2>
                
                <form @submit.prevent="submit">
                    <!-- Basic Job Info -->
                    <div class="form-section">
                        <h3>Job Information</h3>
                        
                        <div class="form-group">
                            <label>Company Name <span class="required">*</span></label>
                            <div class="company-search-container">
                                <input 
                                class="comapany-input"
                                    v-model="companySearch" 
                                    @input="formData.company_name = companySearch; formData.company_id = null"
                                    @focus="showCompanySuggestions = true"
                                    @blur="setTimeout(() => { showCompanySuggestions = false }, 200)"
                                    placeholder="Start typing company name..."
                                    required
                                />
                                <button 
                                    v-if="companySearch" 
                                    @click="clearCompanySelection" 
                                    class="clear-button"
                                    type="button"
                                >
                                    ×
                                </button>
                            </div>
                            
                            <div v-if="showCompanySuggestions && companySuggestions.length" class="suggestions">
                                <div 
                                    v-for="company in companySuggestions" 
                                    :key="company.id"
                                    @mousedown="selectCompany(company)"
                                    class="suggestion-item"
                                >
                                    {{ company.name }} ({{ company.city }})
                                </div>
                            </div>
                        </div>

                        <!-- Only show address field for new companies -->
                        <div class="form-group" v-if="isNewCompany">
                            <label>Company Address <span class="required">*</span></label>
                            <input 
                                id="company-address" 
                                v-model="companyAddress"
                                placeholder="Search for company address..."
                                :required="isNewCompany"
                                :disabled="!isGoogleMapsLoaded"
                            />
                            <div v-if="!isGoogleMapsLoaded" class="loading-notice">
                                Loading address search...
                            </div>
                            <div v-if="companyLocation.latitude && companyLocation.longitude" class="location-info">
                                <p>Location: {{ companyLocation.latitude }}, {{ companyLocation.longitude }}</p>
                                <p v-if="companyLocation.city">City: {{ companyLocation.city }}</p>
                                <p v-if="companyLocation.country">Country: {{ companyLocation.country }}</p>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Job Level/Position <span class="required">*</span></label>
                            <input v-model="formData.job_title" required />
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Start Date <span class="required">*</span></label>
                                <input v-model="formData.start_date" type="date" required />
                            </div>
                            
                            <div class="form-group">
                                <label>End Date (leave blank if current)</label>
                                <input v-model="formData.end_date" type="date" />
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input v-model="formData.is_current" type="checkbox" />
                                This is my current job
                            </label>
                        </div>
                    </div>

                    <!-- First Job Details -->
                    <div class="form-section" v-if="formData.is_first_job">
                        <h3>First Job After Graduation</h3>
                        
                        <div class="form-group">
                            <label>How long did it take to find this job?</label>
                            <select v-model="formData.months_to_first_job">
                                <option value="">Select duration</option>
                                <option v-for="option in monthsToFirstJobOptions" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                        </div>
                        
                        <div class="form-group">
    <label>Starting Salary</label>
    <select v-model="formData.first_job_salary">
        <option value="">Select salary range</option>
        <option value="below_10k">Below ₱10,000</option>
        <option value="10k-20k">₱10,000 - ₱20,000</option>
        <option value="20k-30k">₱20,000 - ₱30,000</option>
        <option value="30k-50k">₱30,000 - ₱50,000</option>
        <option value="above_50k">Above ₱50,000</option>
    </select>
</div>
                        <div class="form-group">
                            <label>How did you find this job?</label>
                            <select v-model="formData.job_source">
                                <option value="">Select source</option>
                                <option v-for="source in jobSources" :value="source.id">
                                    {{ source.name }}
                                </option>
                            </select>
                            <input 
                                v-if="formData.job_source === 'other'" 
                                v-model="formData.other_source" 
                                placeholder="Please specify" 
                            />
                        </div>
                        
                        <div class="form-group">
                            <label>Difficulties encountered</label>
                            <div class="checkbox-group">
                                <div v-for="difficulty in jobHuntingDifficulties" :key="difficulty.id">
                                    <label>
                                        <input 
                                            type="checkbox" 
                                            v-model="formData.difficulties" 
                                            :value="difficulty.id" 
                                        />
                                        {{ difficulty.name }}
                                    </label>
                                </div>
                            </div>
                            <input 
                                v-if="formData.difficulties.includes('other')" 
                                v-model="formData.other_difficulty" 
                                placeholder="Please specify" 
                            />
                        </div>
                    </div>

                    <!-- Current Job Details -->
                    <div class="form-section" v-if="formData.is_current">
                        <h3>Current Job Details</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Employment Status</label>
                                <select v-model="formData.employment_status">
                                    <option value="">Select status</option>
                                    <option v-for="status in statusOptions" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Employer Type</label>
                                <select v-model="formData.employer_type">
                                    <option value="">Select type</option>
                                    <option v-for="type in employerOptions" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
    <label>Current Salary</label>
    <select v-model="formData.current_salary">
        <option value="">Select salary range</option>
        <option value="below_10k">Below ₱10,000</option>
        <option value="10k-20k">₱10,000 - ₱20,000</option>
        <option value="20k-30k">₱20,000 - ₱30,000</option>
        <option value="30k-50k">₱30,000 - ₱50,000</option>
        <option value="above_50k">Above ₱50,000</option>
    </select>
</div>
                        
                        <div class="form-group">
                            <label>How do you maintain your job?</label>
                            <div class="checkbox-group">
            <div v-for="method in jobMaintenanceOptions" :key="method.id" class="checkbox-item">
                <input
                    type="checkbox"
                    :id="'method-' + method.id"
                    :value="method.id"
                    v-model="formData.job_maintenance"
                />
                <label :for="'method-' + method.id">{{ method.name }}</label>
            </div>
        </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    <input v-model="formData.has_promotion" type="checkbox" />
                                    Received promotion?
                                </label>
                            </div>
                            
                            <div class="form-group">
                                <label>
                                    <input v-model="formData.has_awards" type="checkbox" />
                                    Received awards?
                                </label>
                                <textarea 
                                    v-if="formData.has_awards" 
                                    v-model="formData.awards_details"
                                    placeholder="Please specify awards"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Unemployed Details -->
                    <div class="form-section" v-if="showUnemployedFields">
                        <h3>Unemployment Details</h3>
                        
                        <div class="form-group">
                            <label>Reason for unemployment</label>
                            <select v-model="formData.unemployment_reason">
                                <option value="">Select reason</option>
                                <option v-for="reason in unemploymentReasons" :value="reason.value">
                                    {{ reason.label }}
                                </option>
                            </select>
                            <input 
                                v-if="formData.unemployment_reason === 'other'" 
                                v-model="formData.other_unemployment_reason" 
                                placeholder="Please specify" 
                            />
                        </div>
                        
                        <div class="form-group">
                            <label>
                                <input v-model="formData.has_awards" type="checkbox" />
                                Received awards while unemployed?
                            </label>
                            <textarea 
                                v-if="formData.has_awards" 
                                v-model="formData.awards_details"
                                placeholder="Please specify awards"
                            ></textarea>
                        </div>
                    </div>
                    <div class="form-group">
    <label>Nature of Industry <span class="required">*</span></label>
    <input 
        list="industryOptions"
        v-model="formData.nature_of_industry"
        placeholder="Select or type industry nature"
        required
    />
    <datalist id="industryOptions">
        <option v-for="(industry, index) in industryOptions" :key="index" :value="industry">
            {{ industry }}
        </option>
    </datalist>
    <!-- <small class="form-hint">Recommended for BSIT alumni: IT, Software Development, Web Development, etc.</small> -->
</div>
                    <!-- Program Relevance -->
                    <div class="form-section">
                        <h3>Program Relevance</h3>
                        
                        <div class="form-group">
                            <label>Which competencies were most useful?</label>
                            <div class="checkbox-group">
            <div v-for="competency in competencyOptions" :key="competency.id" class="checkbox-item">
                <input
                    type="checkbox"
                    :id="'competency-' + competency.id"
                    :value="competency.id"
                    v-model="formData.competencies"
                />
                <label :for="'competency-' + competency.id">{{ competency.name }}</label>
            </div>
        </div>
                        </div>
                        
                        <div class="form-group">
    <label>How relevant is the BSIT curriculum?</label>
    <div class="rating-container">
        <div v-for="option in relevanceOptions" :key="option.value" class="rating-option">
            <input
                type="radio"
                :id="'relevance-' + option.value"
                :value="option.value"
                v-model="formData.curriculum_relevance"
            />
            <label :for="'relevance-' + option.value">
                <span class="rating-number">{{ option.value }}</span>
                <span class="rating-label">{{ option.label }}</span>
            </label>
        </div>
    </div>
</div>
                    </div>

                    <div class="form-actions">
                        <button type="button" @click="resetForm" class="btn">Cancel</button>
                        <button type="submit" :disabled="isSubmitting" class="btn primary">
                            {{ isSubmitting ? 'Saving...' : 'Save' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Add your custom styles here */
.errors {
    color: red;
    margin-bottom: 1rem;
}
.error {
    margin-bottom: 0.5rem;
}
.tabs {
    display: flex;
    margin-bottom: 1rem;
    border-bottom: 1px solid #ddd;
}

.tabs button {
    padding: 0.5rem 1rem;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
}

.tabs button.active {
    border-bottom-color: #4f46e5;
    font-weight: bold;
}

.card {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 1rem;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eee;
}

.form-group {
    margin-bottom: 1rem;
}

.form-row {
    display: flex;
    gap: 1rem;
}

.form-row .form-group {
    flex: 1;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 0.25rem;
    cursor: pointer;
}

.btn.primary {
    background: #4f46e5;
    color: white;
}

.btn.danger {
    background: #ef4444;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: #666;
}

.suggestions-dropdown {
    position: absolute;
    z-index: 1000;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    max-height: 300px;
    overflow-y: auto;
    width: 100%;
    margin-top: 4px;
}

.suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}

.suggestion-item:hover {
    background-color: #ff8800 !important;
}

.company-industry {
    font-size: 0.8em;
    color: #666;
}

.new-company-message {
    background-color: #f8f9fa;
    padding: 8px;
    border-radius: 4px;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 8px;}

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
    background-color: #333;
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
/* Custom Info Window Styles */
.gm-style .gm-style-iw {
  color: #333; /* Text color */
  font-family: 'Inter', sans-serif; /* Match your app font */
  padding: 0 !important; /* Remove default padding */
  max-width: 300px !important; /* Control max width */
  border-radius: 12px !important; /* Rounded corners */
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important; /* Soft shadow */
}

/* Info Window Content Container */
.gm-style .gm-style-iw-c {
  padding: 0 !important;
  border-radius: 12px !important;
  background: white !important; /* White background */
}

/* Close Button */
.gm-style .gm-ui-hover-effect {
  top: 8px !important;
  right: 8px !important;
  width: 24px !important;
  height: 24px !important;
  background: rgba(0, 0, 0, 0.1) !important;
  border-radius: 50% !important;
}

.gm-style .gm-ui-hover-effect span {
  background-color: #666 !important;
  height: 12px !important;
  width: 2px !important;
  margin: 6px 11px !important;
}

/* Custom Marker Icons */
.custom-marker {
  width: 32px;
  height: 32px;
  border-radius: 50% 50% 50% 0;
  background: var(--primary, #ff8c00);
  position: absolute;
  transform: rotate(-45deg);
  left: 50%;
  top: 50%;
  margin: -20px 0 0 -20px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.custom-marker::after {
  content: "";
  width: 24px;
  height: 24px;
  margin: 4px 0 0 4px;
  background: white;
  position: absolute;
  border-radius: 50%;
}

/* Different colors for different marker types */
.current-marker .custom-marker {
  background: #4CAF50; /* Green for current positions */
}

.company-marker .custom-marker {
  background: #ff8c00; /* Orange for companies */
}

.user-marker .custom-marker {
  background: #3b82f6; /* Blue for users */
}

/* Info Window Content Styling */
.info-window {
  padding: 16px;
  color: #333;
}

.info-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 12px;
  padding-bottom: 10px;
  border-bottom: 1px solid #eee;
}

.info-header i {
  color: var(--primary, #ff8c00);
  font-size: 20px;
}

.info-header h4 {
  margin: 0;
  font-size: 16px;
  font-weight: 600;
  color: #222;
}

.info-industry {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: #666;
  font-weight: 500;
}

.info-employees {
  margin: 12px 0 6px 0;
  font-size: 14px;
  font-weight: 600;
  color: #444;
}

.user-profiles {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-bottom: 12px;
}

.user-profile {
  position: relative;
  transition: all 0.2s;
}

.user-profile:hover {
  transform: translateY(-3px);
}

.profile-image {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  border: 2px solid white;
  object-fit: cover;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.more-users {
  font-size: 12px;
  color: #666;
  margin: 6px 0;
}

.no-employees {
  font-size: 13px;
  color: #999;
  font-style: italic;
  margin: 8px 0;
}

.btn-select {
  display: block;
  width: 100%;
  padding: 8px 12px;
  background: var(--primary, #ff8c00);
  color: white;
  border: none;
  border-radius: 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.2s;
  margin-top: 12px;
  text-align: center;
}

.btn-select:hover {
  background: #e67e00;
}

/* Animation for markers */
@keyframes bounce {
  0%, 100% {
    transform: translateY(0) rotate(-45deg);
  }
  50% {
    transform: translateY(-10px) rotate(-45deg);
  }
}

.custom-marker {
  animation: bounce 1.5s infinite;
}

/* Delay animations for multiple markers */
.custom-marker:nth-child(1) { animation-delay: 0s; }
.custom-marker:nth-child(2) { animation-delay: 0.3s; }
.custom-marker:nth-child(3) { animation-delay: 0.6s; }
.suggestions {
    position: absolute;
    z-index: 1000;
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
}

.suggestion-item:hover {
    background-color: #f5f5f5;
}

.location-info {
    margin-top: 8px;
    font-size: 0.9em;
    color: #666;
}

.form-group {
    position: relative;
}
.company-search-container {
    position: relative;
}

.clear-button {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 1.2em;
    cursor: pointer;
    color: #999;
}

.clear-button:hover {
    color: #333;
}

.suggestions {
    position: absolute;
    z-index: 1000;
    background: white;
    border: 1px solid #ddd;
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
}

.suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
}

.suggestion-item:hover {
    background-color: #f5f5f5;
}
.rating-container {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.rating-option {
    display: flex;
    align-items: center;
}

.rating-number {
    display: inline-block;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    text-align: center;
    line-height: 24px;
    margin-right: 0.5rem;
}

.rating-option input:checked + label .rating-number {
    background: #4CAF50;
}
datalist {
    position: absolute;
    background-color: white;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0.5rem;
    z-index: 10;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
}

datalist option {
    padding: 0.25rem 0.5rem;
    cursor: pointer;
}

datalist option:hover {
    background-color: #f3f4f6;
}
.comapany-input{
    width: 100%;
}
.checkbox-item{
    display: flex;
    align-items: center;
    gap: 5px;
}
</style>

