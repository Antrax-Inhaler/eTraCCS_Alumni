<script setup>
import { ref, computed } from 'vue';
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
    },
    mindoroCampuses: {
        type: Array,
        default: () => ['Main', 'Calapan', 'Bongabong', 'Victoria', 'Roxas']
    },
    certifications: {
        type: Array,
        default: () => []
    }
});

const formData = ref({
    degree_earned: '',
    institution: '',
    from_mindoro_state: false,
    is_primary: false,
    college: 'College of Computer Studies',
    campus: '',
    year_graduated: '',
    currently_studying: false,
    is_graduate_study: false,
    reason_for_study: '',
    other_reason: ''
});

// New fields for certifications
const certificationForm = ref({
    type: '',
    other_type: '',
    license_number: '',
    date_obtained: ''
});

// New fields for reasons taking BSIT
const bsitReasons = ref({
    reasons: [],
    other_reason: ''
});

const editingId = ref(null);
const searchQuery = ref('');
const isSubmitting = ref(false);

// Certification options
const certificationOptions = [
    { value: 'civil_service', label: 'Civil Service Exam' },
    { value: 'prc', label: 'PRC License' },
    { value: 'tesda', label: 'TESDA NC II' },
    { value: 'comptia', label: 'CompTIA Certification' },
    { value: 'cisco', label: 'Cisco Certification' },
    { value: 'microsoft', label: 'Microsoft Certification' },
    { value: 'aws', label: 'AWS Certification' },
    { value: 'google_cloud', label: 'Google Cloud Certification' },
    { value: 'azure', label: 'Azure Certification' },
    { value: 'other', label: 'Other Certification' }
];

// Reasons for taking BSIT
const bsitReasonOptions = [
    { value: 'personal_interest', label: 'Personal interest' },
    { value: 'parental_decision', label: 'Parental decision' },
    { value: 'employment_opportunity', label: 'Employment opportunity' },
    { value: 'affordable_tuition', label: 'Affordable tuition' },
    { value: 'scholarship_availability', label: 'Scholarship availability' },
    { value: 'peers_influence', label: 'Influence of peers' },
    { value: 'other', label: 'Other reason' }
];

// Graduate study reasons
const graduateStudyReasons = [
    { value: 'career_advancement', label: 'Career advancement' },
    { value: 'promotion_requirement', label: 'Promotion requirement' },
    { value: 'academic_interest', label: 'Academic interest' },
    { value: 'job_requirement', label: 'Job requirement' },
    { value: 'other', label: 'Other reason' }
];

const mindoroStateDegrees = [
    'Bachelor of Science in Computer Engineering (BSCpE)',
    'Bachelor of Science in Information Technology (BSIT)',
    'Bachelor of Science in Computer Science (BSCS)',
    'Bachelor of Science in Information Systems (BSIS)'
];

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

const handleMindoroStateChange = (isChecked) => {
    formData.value.from_mindoro_state = isChecked;
    formData.value.institution = isChecked ? 'Mindoro State University' : '';
    formData.value.campus = isChecked ? 'Main' : '';
    
    if (!isChecked) {
        formData.value.is_primary = false;
    }
};

const handleCurrentlyStudyingChange = (isChecked) => {
    if (!isChecked && !formData.value.year_graduated) {
        formData.value.year_graduated = new Date().getFullYear();
    }
};

const handlePrimaryChange = (isPrimary) => {
    if (isPrimary && !formData.value.from_mindoro_state) {
        alert('Only Mindoro State University degrees can be marked as primary');
        formData.value.is_primary = false;
        return;
    }
    formData.value.is_primary = isPrimary;
};

const graduationYears = computed(() => {
    const currentYear = new Date().getFullYear();
    const startYear = 1950;
    const years = [];

    for (let year = currentYear; year >= startYear; year--) {
        years.push(year);
    }

    return years;
});

const submit = async () => {
    try {
        isSubmitting.value = true;
        
        if (formData.value.currently_studying) {
            formData.value.year_graduated = null;
        }
        
        const payload = {
            ...formData.value,
            bsit_reasons: bsitReasons.value,
            certifications: props.certifications
        };

        if (editingId.value) {
            await router.put(route('profile.educationalBackground.update', editingId.value), payload);
        } else {
            await router.post(route('profile.educationalBackground.store'), payload);
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
    formData.value = { 
        ...background,
        from_mindoro_state: background.institution === 'Mindoro State University',
        currently_studying: !background.year_graduated,
        is_primary: background.is_primary || false,
        is_graduate_study: background.is_graduate_study || false
    };
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
        from_mindoro_state: false,
        is_primary: false,
        college: 'College of Computer Studies',
        campus: '',
        year_graduated: '',
        currently_studying: false,
        is_graduate_study: false,
        reason_for_study: '',
        other_reason: ''
    };
    bsitReasons.value = {
        reasons: [],
        other_reason: ''
    };
    editingId.value = null;
};

const addCertification = () => {
    if (!certificationForm.value.type) return;
    
    const newCert = {
        type: certificationForm.value.type,
        other_type: certificationForm.value.type === 'other' ? certificationForm.value.other_type : null,
        license_number: certificationForm.value.license_number,
        date_obtained: certificationForm.value.date_obtained
    };
    
    router.post(route('profile.certifications.store'), newCert, {
        preserveScroll: true,
        onSuccess: () => {
            certificationForm.value = {
                type: '',
                other_type: '',
                license_number: '',
                date_obtained: ''
            };
        }
    });
};

const removeCertification = (id) => {
    router.delete(route('profile.certifications.destroy', id), {
        preserveScroll: true
    });
};

const getEduIcon = (degree) => {
    if (!degree) return 'fa-graduation-cap';
    if (degree.toLowerCase().includes('phd') || degree.toLowerCase().includes('doctor')) return 'fa-user-graduate';
    if (degree.toLowerCase().includes('master')) return 'fa-user-tie';
    if (degree.toLowerCase().includes('bachelor')) return 'fa-graduation-cap';
    if (degree.toLowerCase().includes('associate')) return 'fa-certificate';
    return 'fa-graduation-cap';
};

const hasAdditionalDegrees = computed(() => {
    return additionalDegrees.value.length > 0;
});

const primaryDegree = computed(() => {
    return props.educationalBackgrounds.find(edu => 
        edu.is_primary && edu.institution === 'Mindoro State University'
    );
});

const additionalDegrees = computed(() => {
    return props.educationalBackgrounds.filter(edu => 
        !edu.is_primary || edu.institution !== 'Mindoro State University'
    );
});

const graduateStudies = computed(() => {
    return props.educationalBackgrounds.filter(edu => edu.is_graduate_study);
});

const alumniStatus = computed(() => {
    if (!primaryDegree.value) return 'Non-Alumni';
    
    const gradYear = primaryDegree.value.year_graduated;
    if (!gradYear) return 'Current Student';
    
    const yearsSinceGrad = new Date().getFullYear() - gradYear;
    
    if (yearsSinceGrad <= 1) return 'Recent Graduate';
    if (yearsSinceGrad <= 5) return 'Early Career Alumni';
    if (yearsSinceGrad <= 10) return 'Mid-Career Alumni';
    return 'Established Alumni';
});
</script>

<template>
    <AppLayout>
        <div class="container-main">
            <div class="container">
                <!-- Header Section -->
                <!-- <div class="card header-card">
                    <div class="header-content">
                        <h1 class="section-title">Educational Background</h1>
                        <p class="section-subtitle">Your academic journey and qualifications</p>
                        
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-value">{{ educationalBackgrounds.length }}</div>
                                <div class="stat-label">Total Degrees</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ certifications.length }}</div>
                                <div class="stat-label">Certifications</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ primaryDegree ? primaryDegree.year_graduated || 'Current' : 'N/A' }}</div>
                                <div class="stat-label">Batch Year</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-value">{{ graduateStudies.length }}</div>
                                <div class="stat-label">Graduate Studies</div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <!-- Form Section -->
                <div class="card form-card">
                    <h2 class="form-title">{{ editingId ? 'Edit Education' : 'Add Education' }}</h2>
                    
                    <form @submit.prevent="submit" class="post-input">
                        <!-- Degree Information -->
                        <div class="form-section">
                            <h3 class="form-section-title">Degree Information</h3>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label class="checkbox-label">
                                        <input 
                                            type="checkbox" 
                                            v-model="formData.from_mindoro_state"
                                            @change="handleMindoroStateChange($event.target.checked)"
                                        />
                                        <span>Mindoro State University (College of Computer Studies)</span>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label for="degree_earned">Degree Earned</label>
                                    <input
                                        id="degree_earned"
                                        v-model="formData.degree_earned"
                                        placeholder="e.g., BS Information Technology"
                                        required
                                        list="degreeOptions"
                                    />
                                    <datalist id="degreeOptions">
                                        <option v-for="degree in mindoroStateDegrees" :key="degree" :value="degree"></option>
                                    </datalist>
                                </div>

                                <div class="form-group">
                                    <label for="institution">Institution</label>
                                    <input
                                        id="institution"
                                        v-model="formData.institution"
                                        :class="{ 'disabled-input': formData.from_mindoro_state }"
                                        :disabled="formData.from_mindoro_state"
                                        required
                                    />
                                </div>

                                <div v-if="formData.from_mindoro_state" class="form-group">
                                    <label for="campus">Campus</label>
                                    <select
                                        id="campus"
                                        v-model="formData.campus"
                                        required
                                        class="styled-select"
                                    >
                                        <option value="" disabled selected>Select campus</option>
                                        <option v-for="campus in mindoroCampuses" :value="campus">
                                            {{ campus }}
                                        </option>
                                    </select>
                                </div>

                                <div v-if="formData.from_mindoro_state" class="form-group full-width">
                                    <label class="checkbox-label">
                                        <input 
                                            type="checkbox" 
                                            v-model="formData.is_primary"
                                            @change="handlePrimaryChange($event.target.checked)"
                                            :disabled="!formData.from_mindoro_state"
                                        />
                                        <span>This is my primary degree from Mindoro State University</span>
                                    </label>
                                    <p class="help-text">
                                        * Your primary degree determines your alumni status and batch year
                                    </p>
                                </div>

                                <div class="form-group full-width">
                                    <label class="checkbox-label">
                                        <input 
                                            type="checkbox" 
                                            v-model="formData.is_graduate_study"
                                        />
                                        <span>This is a graduate study (Master's, PhD, etc.)</span>
                                    </label>
                                </div>

                                <div v-if="formData.is_graduate_study" class="form-group">
                                    <label for="reason_for_study">Reason for Graduate Study</label>
                                    <select
                                        id="reason_for_study"
                                        v-model="formData.reason_for_study"
                                        class="styled-select"
                                    >
                                        <option value="" disabled selected>Select reason</option>
                                        <option v-for="reason in graduateStudyReasons" :value="reason.value">
                                            {{ reason.label }}
                                        </option>
                                    </select>
                                </div>

                                <div v-if="formData.is_graduate_study && formData.reason_for_study === 'other'" class="form-group">
                                    <label for="other_reason">Other Reason</label>
                                    <input
                                        id="other_reason"
                                        v-model="formData.other_reason"
                                        placeholder="Please specify"
                                    />
                                </div>

                                <div class="form-group full-width">
                                    <label class="checkbox-label">
                                        <input 
                                            type="checkbox" 
                                            v-model="formData.currently_studying"
                                            @change="handleCurrentlyStudyingChange($event.target.checked)"
                                        />
                                        <span>I'm currently studying here</span>
                                    </label>
                                </div>

                                <div v-if="!formData.currently_studying" class="form-group">
                                    <label for="year_graduated">Year Graduated</label>
                                    <select
                                        id="year_graduated"
                                        v-model="formData.year_graduated"
                                        :required="!formData.currently_studying"
                                        class="form-control"
                                    >
                                        <option value="" disabled>Select Graduation Year</option>
                                        <option
                                            v-for="year in graduationYears"
                                            :key="year"
                                            :value="year"
                                        >
                                            {{ year }}–{{ year + 1 }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Reasons for Taking BSIT (only show for primary degree) -->
                        <div v-if="formData.is_primary && formData.degree_earned.includes('BSIT')" class="form-section">
                            <h3 class="form-section-title">Reasons for Taking BSIT</h3>
                            <div class="checkbox-group">
                                <div v-for="reason in bsitReasonOptions" :key="reason.value" class="checkbox-item">
                                    <label>
                                        <input 
                                            type="checkbox" 
                                            v-model="bsitReasons.reasons"
                                            :value="reason.value"
                                        />
                                        {{ reason.label }}
                                    </label>
                                </div>
                            </div>
                            <div v-if="bsitReasons.reasons.includes('other')" class="form-group">
                                <label for="other_bsit_reason">Other Reason</label>
                                <input
                                    id="other_bsit_reason"
                                    v-model="bsitReasons.other_reason"
                                    placeholder="Please specify"
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

                <!-- Certifications Section -->
                <div class="card form-card">
                    <h2 class="form-title">Professional Certifications</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="certification_type">Certification Type</label>
                            <select
                                id="certification_type"
                                v-model="certificationForm.type"
                                class="styled-select"
                            >
                                <option value="" disabled selected>Select certification</option>
                                <option v-for="cert in certificationOptions" :value="cert.value">
                                    {{ cert.label }}
                                </option>
                            </select>
                        </div>

                        <div v-if="certificationForm.type === 'other'" class="form-group">
                            <label for="other_certification">Other Certification</label>
                            <input
                                id="other_certification"
                                v-model="certificationForm.other_type"
                                placeholder="Specify certification"
                            />
                        </div>

                        <div class="form-group">
                            <label for="license_number">License Number (if applicable)</label>
                            <input
                                id="license_number"
                                v-model="certificationForm.license_number"
                                placeholder="Enter license number"
                            />
                        </div>

                        <div class="form-group">
                            <label for="date_obtained">Date Obtained</label>
                            <input
                                id="date_obtained"
                                v-model="certificationForm.date_obtained"
                                type="date"
                            />
                        </div>

                        <div class="form-group full-width">
                            <button 
                                type="button" 
                                class="btn btn-primary"
                                @click="addCertification"
                                :disabled="!certificationForm.type"
                            >
                                Add Certification
                            </button>
                        </div>
                    </div>

                    <div v-if="certifications.length > 0" class="certifications-list">
                        <h3 class="subsection-title">Your Certifications</h3>
                        <ul>
                            <li v-for="cert in certifications" :key="cert.id" class="certification-item">
                                <div class="cert-info">
                                    <span class="cert-name">
                                        {{ cert.type === 'other' ? cert.other_type : certificationOptions.find(c => c.value === cert.type)?.label }}
                                    </span>
                                    <span v-if="cert.license_number" class="cert-license">
                                        License: {{ cert.license_number }}
                                    </span>
                                    <span v-if="cert.date_obtained" class="cert-date">
                                        Obtained: {{ new Date(cert.date_obtained).toLocaleDateString() }}
                                    </span>
                                </div>
                                <button 
                                    class="action-btn danger"
                                    @click="removeCertification(cert.id)"
                                    title="Remove"
                                >
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Education List Section -->
                <div class="card data-card">
                    <div class="section-header">
                        <h2 class="section-title">Your Academic History</h2>
                        <div class="table-controls">
                            <!-- <div class="search-wrapper">
                                <i class="fas fa-search"></i>
                                <input 
                                    type="text" 
                                    class="search-input" 
                                    placeholder="Search education..." 
                                    v-model="searchQuery"
                                >
                            </div> -->
                        </div>
                    </div>

                    <!-- Alumni Status Information -->
                    <div v-if="primaryDegree" class="alumni-status">
                        <i class="fas fa-graduation-cap"></i>
                        <div>
                            <strong>Alumni Status:</strong> {{ alumniStatus }}
                            <span v-if="primaryDegree.year_graduated" class="batch-year">
                                (Batch {{ primaryDegree.year_graduated }})
                            </span>
                            <span v-else class="batch-year">
                                (Currently enrolled)
                            </span>
                        </div>
                    </div>
                    
                    <!-- Primary Degree Section -->
                    <div v-if="primaryDegree" class="primary-degree-section">
                        <h3 class="subsection-title">
                            <i class="fas fa-star"></i> Primary Degree
                        </h3>
                        <div class="degree-card primary">
                            <div class="degree-header">
                                <i class="fas" :class="getEduIcon(primaryDegree.degree_earned)"></i>
                                <h4>{{ primaryDegree.degree_earned }}</h4>
                                <span class="badge primary-badge">Primary</span>
                            </div>
                            <div class="degree-details">
                                <p><strong>Institution:</strong> {{ primaryDegree.institution }}</p>
                                <p v-if="primaryDegree.campus"><strong>Campus:</strong> {{ primaryDegree.campus }}</p>
                                <p v-if="primaryDegree.year_graduated">
                                    <strong>Graduated:</strong> {{ primaryDegree.year_graduated }}
                                </p>
                                <p v-else><strong>Status:</strong> Currently studying</p>
                                <p v-if="primaryDegree.is_graduate_study">
                                    <strong>Graduate Study Reason:</strong> 
                                    {{ graduateStudyReasons.find(r => r.value === primaryDegree.reason_for_study)?.label }}
                                    <span v-if="primaryDegree.other_reason">({{ primaryDegree.other_reason }})</span>
                                </p>
                            </div>
                            <div class="degree-actions">
                                <button @click="editItem(primaryDegree)" class="action-btn">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Graduate Studies Section -->
                    <div v-if="graduateStudies.length > 0" class="graduate-studies-section">
                        <h3 class="subsection-title">
                            <i class="fas fa-user-graduate"></i> Graduate Studies
                        </h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Program</th>
                                        <th>Institution</th>
                                        <th>Year</th>
                                        <th>Reason</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="study in graduateStudies" :key="study.id">
                                        <td>
                                            <div class="degree-info">
                                                <i class="fas" :class="getEduIcon(study.degree_earned)"></i>
                                                {{ study.degree_earned }}
                                            </div>
                                        </td>
                                        <td>{{ study.institution }}</td>
                                        <td>
                                            <span v-if="study.year_graduated" class="year-badge">
                                                {{ study.year_graduated }}
                                            </span>
                                            <span v-else class="studying-badge">Studying</span>
                                        </td>
                                        <td>
                                            {{ graduateStudyReasons.find(r => r.value === study.reason_for_study)?.label }}
                                            <span v-if="study.other_reason">({{ study.other_reason }})</span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button 
                                                    class="action-btn"
                                                    @click="editItem(study)"
                                                    title="Edit"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button 
                                                    class="action-btn"
                                                    @click="deleteItem(study.id)"
                                                    title="Delete"
                                                >
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Additional Degrees Section -->
                    <div v-if="hasAdditionalDegrees" class="additional-degrees-section">
                        <h3 class="subsection-title">
                            <i class="fas fa-plus-circle"></i> Additional Degrees
                        </h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Degree</th>
                                        <th>Institution</th>
                                        <th>Year</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="degree in additionalDegrees" :key="degree.id">
                                        <td>
                                            <div class="degree-info">
                                                <i class="fas" :class="getEduIcon(degree.degree_earned)"></i>
                                                {{ degree.degree_earned }}
                                            </div>
                                        </td>
                                        <td>{{ degree.institution }}</td>
                                        <td>
                                            <span v-if="degree.year_graduated" class="year-badge">
                                                {{ degree.year_graduated }}
                                            </span>
                                            <span v-else class="studying-badge">Studying</span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button 
                                                    class="action-btn"
                                                    @click="editItem(degree)"
                                                    title="Edit"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button 
                                                    class="action-btn"
                                                    @click="deleteItem(degree.id)"
                                                    title="Delete"
                                                >
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div v-else-if="!primaryDegree" class="empty-state">
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
    </AppLayout>
</template>

<style scoped>
/* Add your custom styles here */
.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.form-section-title {
    font-size: 1.1rem;
    margin-bottom: 1rem;
    color: #444;
}

.checkbox-group {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.checkbox-item {
    display: flex;
    align-items: center;
}

.certifications-list {
    margin-top: 1.5rem;
}

.certification-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    border-bottom: 1px solid #eee;
}

.cert-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.cert-name {
    font-weight: 500;
}

.cert-license, .cert-date {
    font-size: 0.85rem;
    color: #666;
}

.action-btn.danger {
    color: #dc3545;
}

.action-btn.danger:hover {
    color: #c82333;
}

.graduate-studies-section {
    margin-top: 2rem;
}
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
.degree-input-container {
    position: relative;
    flex: 1;
}

.alumni-status {
    background: rgba(255, 140, 0, 0.1);
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.alumni-status i {
    color: var(--primary);
}

.additional-degrees-section {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.subsection-title {
    font-size: 16px;
    margin: 0 0 15px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--primary);
}

.subsection-title i {
    font-size: 18px;
}

.degrees-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.degree-item {
    display: flex;
    flex-direction: column;
    padding: 10px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 6px;
}

.degree-name {
    font-weight: 500;
    margin-bottom: 4px;
}

.degree-info {
    display: flex;
    gap: 10px;
    font-size: 13px;
    color: var(--text-secondary);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    user-select: none;
}

.checkbox-label input[type="checkbox"] {
    appearance: none;
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    border: 1px solid var(--card-border);
    border-radius: 4px;
    background: rgba(255, 255, 255, 0.05);
    cursor: pointer;
    position: relative;
}

.checkbox-label input[type="checkbox"]:checked {
    background: var(--primary);
    border-color: var(--primary);
}

.checkbox-label input[type="checkbox"]:checked::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 12px;
}

.studying-badge {
    display: inline-block;
    background: rgba(0, 123, 255, 0.1);
    color: #007bff;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .checkbox-label span {
        font-size: 13px;
    }
}

.primary-degree-section {
    margin-bottom: 20px;
}

.degree-card {
    background: var(--card-bg);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    border: 1px solid var(--card-border);
}

.degree-card.primary {
    border-left: 5px solid var(--primary);
    background: rgba(255, 140, 0, 0.05);
}

.degree-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}

.degree-header i {
    font-size: 24px;
    color: var(--primary);
}

.degree-header h4 {
    margin: 0;
    flex: 1;
}

.badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.primary-badge {
    background: var(--primary);
    color: white;
}

.degree-details p {
    margin: 5px 0;
    font-size: 14px;
}

.degree-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px;
}

.subsection-title {
    font-size: 16px;
    margin: 0 0 15px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--primary);
}

.subsection-title i {
    font-size: 18px;
}

.batch-year {
    color: var(--text-secondary);
    font-size: 0.9em;
}

/* Help text */
.help-text {
    font-size: 12px;
    color: var(--text-secondary);
    margin: 5px 0 0;
    font-style: italic;
}

/* Full width form groups */
.full-width {
    grid-column: 1 / -1;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .degree-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .degree-actions {
        justify-content: flex-start;
    }
}
option {
      background-color: var(--bg-dark);
      color: var(--text-secondary);
    }

    /* Optional: style on focus */
    select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 5px var(--primary-light);
    }
    .additional-degree {
    padding: 4px 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

</style>