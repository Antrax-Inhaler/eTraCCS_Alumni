<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: Boolean,
    alum: Object,
});

const emit = defineEmits(['close']);

const activeTab = ref('profile');
</script>

<template>
    <Modal :show="show" @close="emit('close')" max-width="4xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Alumni Details</h3>
                <button @click="emit('close')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="profile-header">
                    <div class="profile-photo">
                        <img :src="alum.profile_photo_url" alt="Profile Photo" class="avatar">
                    </div>
                    <div class="profile-info">
                        <h2 class="profile-name">{{ alum.full_name }}</h2>
                        <div class="profile-meta">
                            <span>{{ alum.educational_backgrounds[0]?.degree_earned }}, {{ alum.educational_backgrounds[0]?.year_graduated }}</span>
                            <span v-if="alum.alumni_location">{{ alum.alumni_location.city }}, {{ alum.alumni_location.country }}</span>
                        </div>
                        <div class="profile-status">
                            <span class="status" :class="alum.email_verified_at ? 'active' : 'inactive'">
                                {{ alum.email_verified_at ? 'Verified' : 'Unverified' }}
                            </span>
                            <span class="last-seen" v-if="alum.last_seen_at">
                                Last active: {{ new Date(alum.last_seen_at).toLocaleDateString() }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="tabs">
                    <button 
                        @click="activeTab = 'profile'" 
                        :class="{ 'active': activeTab === 'profile' }"
                        class="tab-button"
                    >
                        <i class="fas fa-user mr-2"></i> Profile
                    </button>
                    <button 
                        @click="activeTab = 'education'" 
                        :class="{ 'active': activeTab === 'education' }"
                        class="tab-button"
                    >
                        <i class="fas fa-graduation-cap mr-2"></i> Education
                    </button>
                    <button 
                        @click="activeTab = 'employment'" 
                        :class="{ 'active': activeTab === 'employment' }"
                        class="tab-button"
                    >
                        <i class="fas fa-briefcase mr-2"></i> Employment
                    </button>
                    <button 
                        @click="activeTab = 'trainings'" 
                        :class="{ 'active': activeTab === 'trainings' }"
                        class="tab-button"
                    >
                        <i class="fas fa-certificate mr-2"></i> Trainings
                    </button>
                </div>

                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div v-if="activeTab === 'profile'" class="profile-tab">
                        <div class="info-grid">
                            <div class="info-group">
                                <label>Email</label>
                                <p>{{ alum.email }}</p>
                            </div>
                            <div class="info-group">
                                <label>Contact Number</label>
                                <p>{{ alum.phone_number || 'N/A' }}</p>
                            </div>
                            <div class="info-group">
                                <label>Birthdate</label>
                                <p>{{ alum.birthdate || 'N/A' }}</p>
                            </div>
                            <div class="info-group">
                                <label>Address</label>
                                <p>{{ alum.address || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Education Tab -->
                    <div v-if="activeTab === 'education'" class="education-tab">
                        <div v-for="edu in alum.educational_backgrounds" :key="edu.id" class="education-item">
                            <h4 class="education-degree">{{ edu.degree_earned }}</h4>
                            <div class="education-meta">
                                <span>{{ edu.institution }}</span>
                                <span>{{ edu.year_graduated }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Tab -->
                    <div v-if="activeTab === 'employment'" class="employment-tab">
                        <div v-for="job in alum.employment_histories" :key="job.id" class="employment-item">
                            <h4 class="job-title">{{ job.job_title }}</h4>
                            <div class="job-meta">
                                <span>{{ job.company?.name || job.company_name }}</span>
                                <span>{{ job.start_date }} - {{ job.end_date || 'Present' }}</span>
                            </div>
                            <div class="job-type">
                                <span class="job-type-badge">{{ job.employment_type }}</span>
                                <span v-if="job.is_job_related_to_degree" class="job-related-badge">
                                    Related to degree
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Trainings Tab -->
                    <div v-if="activeTab === 'trainings'" class="trainings-tab">
                        <div v-for="training in alum.training_attendeds" :key="training.id" class="training-item">
                            <h4 class="training-name">{{ training.training_name }}</h4>
                            <div class="training-meta">
                                <span>{{ training.institution }}</span>
                                <span>{{ training.year_attended }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
/* Modal styles same as CreateModal */

.profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid var(--card-border);
}

.avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary);
}

.profile-info {
    flex: 1;
}

.profile-name {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.profile-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 10px;
    color: var(--text-secondary);
    font-size: 14px;
}

.profile-status {
    display: flex;
    gap: 10px;
    align-items: center;
}

.last-seen {
    font-size: 13px;
    color: var(--text-secondary);
}

.tabs {
    display: flex;
    border-bottom: 1px solid var(--card-border);
    margin-bottom: 20px;
}

.tab-button {
    padding: 10px 20px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);

}

.tab-button:hover {
    color: var(--text-primary);
}

.tab-button.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.tab-content {
    min-height: 200px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.info-group {
    margin-bottom: 15px;
}

.info-group label {
    display: block;
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.info-group p {
    font-size: 15px;
    color: var(--text-primary);
}

.education-item,
.employment-item,
.training-item {
    padding: 15px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    margin-bottom: 10px;
}

.education-degree,
.job-title,
.training-name {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.education-meta,
.job-meta,
.training-meta {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: var(--text-secondary);
}

.job-type {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}

.job-type-badge,
.job-related-badge {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.job-type-badge {
    background: rgba(255, 140, 0, 0.2);
    color: var(--primary);
}

.job-related-badge {
    background: rgba(76, 175, 80, 0.2);
    color: var(--success);
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .tabs {
        overflow-x: auto;
    }
}
</style>