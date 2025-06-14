<template>
  <AdminLayout>
    <Head :title="`${user.full_name} - Alumni Profile`" />
    
    <div class="profile-container">
      <!-- Cover Photo Section -->
      <div class="cover-photo" :style="{ backgroundImage: `url(${user.cover_photo})` }">
        <div class="profile-actions">
          <button class="btn btn-outline">
            <i class="fas fa-ellipsis-h"></i>
          </button>
        </div>
      </div>
      
      <!-- Profile Header -->
      <div class="profile-header">
        <div class="avatar-container">
          <img :src="user.profile_photo_url" class="profile-avatar" alt="Profile Photo">
          <span class="online-status" :class="{ 'online': user.is_online }"></span>
        </div>
        
        <div class="profile-info">
          <h1 class="profile-name">{{ user.full_name }}</h1>
          <div class="profile-meta">
            <span v-if="user.primary_education">
              {{ user.primary_education.degree_earned }} • 
              {{ user.primary_education.year_graduated || 'Graduation year not specified' }}
            </span>
            <span v-if="user.alumniLocation">
              <i class="fas fa-map-marker-alt"></i> 
              {{ user.alumniLocation.city }}, {{ user.alumniLocation.country }}
            </span>
          </div>
          
          <div class="profile-stats">
            <div class="stat-item">
              <span class="stat-number">{{ stats.post_count }}</span>
              <span class="stat-label">Posts</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">{{ stats.followers_count }}</span>
              <span class="stat-label">Followers</span>
            </div>
            <div class="stat-item">
              <span class="stat-number">{{ stats.following_count }}</span>
              <span class="stat-label">Following</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Profile Navigation -->
      <nav class="profile-nav">
        <button 
          class="nav-item" 
          :class="{ 'active': activeTab === 'overview' }"
          @click="switchTab('overview')"
        >
          Overview
        </button>
        <button 
          class="nav-item" 
          :class="{ 'active': activeTab === 'education' }"
          @click="switchTab('education')"
        >
          Education
        </button>
        <button 
          class="nav-item" 
          :class="{ 'active': activeTab === 'employment' }"
          @click="switchTab('employment')"
        >
          Employment
        </button>
        <button 
          class="nav-item" 
          :class="{ 'active': activeTab === 'posts' }"
          @click="switchTab('posts')"
        >
          Posts
        </button>
        <button 
          class="nav-item" 
          :class="{ 'active': activeTab === 'connections' }"
          @click="switchTab('connections')"
        >
          Connections
        </button>
      </nav>
      
      <!-- Main Content -->
      <div class="profile-content">
        <!-- Overview Tab -->
        <div v-if="activeTab === 'overview'" class="tab-content overview-tab">
          <div class="left-column">
            <!-- About Card -->
            <div class="card">
              <div class="card-header">
                <h3>About</h3>
                <button class="btn-icon">
                  <i class="fas fa-pencil-alt"></i>
                </button>
              </div>
              <div class="card-body">
                <div class="about-item" v-if="user.alumniLocation">
                  <i class="fas fa-map-marker-alt"></i>
                  <div>
                    <div class="about-label">Location</div>
                    <div>{{ user.alumniLocation.city }}, {{ user.alumniLocation.country }}</div>
                  </div>
                </div>
                
                <div class="about-item" v-if="user.currentEmployment">
                  <i class="fas fa-briefcase"></i>
                  <div>
                    <div class="about-label">Current Job</div>
                    <div>
                      {{ user.currentEmployment.job_title }} at 
                      {{ user.currentEmployment.company?.name || 'Unknown company' }}
                    </div>
                  </div>
                </div>
                
                <div class="about-item" v-if="user.primary_education">
                  <i class="fas fa-graduation-cap"></i>
                  <div>
                    <div class="about-label">Education</div>
                    <div>
                      {{ user.primary_education.degree_earned }} • 
                      {{ user.primary_education.institution }}
                      <span v-if="user.primary_education.year_graduated">
                        ({{ user.primary_education.year_graduated }})
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Education Card -->
            <div class="card">
              <div class="card-header">
                <h3>Education</h3>
                <button class="btn-icon">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <div class="card-body">
                <div v-for="edu in educationalBackgrounds" :key="edu.id" class="education-item">
                  <div class="education-icon">
                    <i class="fas fa-graduation-cap"></i>
                  </div>
                  <div class="education-details">
                    <h4>{{ edu.degree_earned }}</h4>
                    <div class="education-meta">
                      <span>{{ edu.institution }}</span>
                      <span v-if="edu.campus">• {{ edu.campus.name }}</span>
                      <span v-if="edu.year_graduated">• {{ edu.year_graduated }}</span>
                    </div>
                    <div class="education-status" v-if="edu.currently_studying">
                      Currently Studying
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Skills Card -->
            <div class="card">
              <div class="card-header">
                <h3>Skills & Competencies</h3>
                <button class="btn-icon">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <div class="card-body">
                <div class="skills-container">
                  <div v-for="(job, index) in employmentHistory" :key="index">
                    <div v-if="job.competencies && job.competencies.length" class="job-skills">
                      <div class="job-title">{{ job.job_title }}</div>
                      <div class="skill-tags">
                        <span v-for="(comp, i) in job.competencies" :key="i" class="skill-tag">
                          {{ getCompetencyName(comp) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="right-column">
            <!-- Employment Card -->
            <div class="card employment-history-card">
              <div class="card-header">
                <h3>Employment History</h3>
                <button class="btn-icon">
                  <i class="fas fa-plus"></i>
                </button>
              </div>
              <div class="card-body">
                <!-- Current Employment -->
                <div v-if="currentEmployment" class="employment-item current">
                  <div class="employment-icon">
                    <i class="fas fa-briefcase"></i>
                  </div>
                  <div class="employment-details">
                    <h4>{{ currentEmployment.job_title }}</h4>
                    <div class="employment-meta">
                      <span>{{ currentEmployment.company?.name || 'Unknown company' }}</span>
                      <span>• {{ currentEmployment.employment_type }}</span>
                      <span v-if="currentEmployment.start_date">
                        • {{ formatDate(currentEmployment.start_date) }} - Present
                      </span>
                    </div>
                    <div class="employment-salary" v-if="currentEmployment.current_salary">
                      Salary: {{ formatSalary(currentEmployment.current_salary) }}
                    </div>
                    <div class="employment-skills" v-if="currentEmployment.competencies">
                      <span class="skill-tag" v-for="(comp, index) in currentEmployment.competencies" :key="index">
                        {{ getCompetencyName(comp) }}
                      </span>
                    </div>
                  </div>
                </div>
                
                <!-- Past Employment -->
                <div v-for="job in pastEmployment" :key="job.id" class="employment-item">
                  <div class="employment-icon">
                    <i class="fas fa-briefcase"></i>
                  </div>
                  <div class="employment-details">
                    <h4>{{ job.job_title }}</h4>
                    <div class="employment-meta">
                      <span>{{ job.company?.name || 'Unknown company' }}</span>
                      <span>• {{ job.employment_type }}</span>
                      <span v-if="job.start_date && job.end_date">
                        • {{ formatDate(job.start_date) }} - {{ formatDate(job.end_date) }}
                      </span>
                    </div>
                    <div class="employment-skills" v-if="job.competencies">
                      <span class="skill-tag" v-for="(comp, index) in job.competencies" :key="index">
                        {{ getCompetencyName(comp) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Job Hunting Experience -->
            <div class="card" v-if="jobHuntingExperiences.length">
              <div class="card-header">
                <h3>Job Hunting Experience</h3>
              </div>
              <div class="card-body">
                <div v-for="(exp, index) in jobHuntingExperiences" :key="index" class="job-hunting-item">
                  <!-- ... existing job hunting content ... -->
                </div>
              </div>
            </div>
            
            <!-- Professional Development -->
            <div class="card" v-if="professionalExams.length || trainingsAttended.length">
              <div class="card-header">
                <h3>Professional Development</h3>
              </div>
              <div class="card-body">
                <!-- ... existing professional development content ... -->
              </div>
            </div>
          </div>
        </div>
        
        <!-- Education Tab -->
        <div v-if="activeTab === 'education'" class="tab-content education-tab">
          <div class="full-width-column">
            <div class="card">
              <div class="card-header">
                <h3>Education Details</h3>
              </div>
              <div class="card-body">
                <!-- Detailed education view -->
                <div v-for="edu in educationalBackgrounds" :key="edu.id" class="detailed-education-item">
                  <h4>{{ edu.degree_earned }}</h4>
                  <div class="education-meta">
                    <span><strong>Institution:</strong> {{ edu.institution }}</span>
                    <span v-if="edu.campus"><strong>Campus:</strong> {{ edu.campus.name }}</span>
                    <span v-if="edu.year_graduated"><strong>Year Graduated:</strong> {{ edu.year_graduated }}</span>
                    <span v-if="edu.currently_studying" class="status-badge">Currently Studying</span>
                  </div>
                  <div v-if="edu.major" class="education-field">
                    <strong>Major/Field:</strong> {{ edu.major }}
                  </div>
                  <div v-if="edu.honors" class="education-honors">
                    <strong>Honors/Awards:</strong> {{ edu.honors }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Employment Tab -->
        <div v-if="activeTab === 'employment'" class="tab-content employment-tab">
          <div class="full-width-column">
            <div class="card">
              <div class="card-header">
                <h3>Employment Details</h3>
              </div>
              <div class="card-body">
                <!-- Detailed employment view -->
                <div class="employment-timeline">
                  <div v-if="currentEmployment" class="timeline-item current">
                    <div class="timeline-date">
                      {{ formatDate(currentEmployment.start_date) }} - Present
                    </div>
                    <div class="timeline-content">
                      <h4>{{ currentEmployment.job_title }}</h4>
                      <div class="company-name">{{ currentEmployment.company?.name || 'Unknown company' }}</div>
                      <div class="employment-type">{{ currentEmployment.employment_type }}</div>
                      <div v-if="currentEmployment.job_description" class="job-description">
                        {{ currentEmployment.job_description }}
                      </div>
                    </div>
                  </div>
                  
                  <div v-for="job in pastEmployment" :key="job.id" class="timeline-item">
                    <div class="timeline-date">
                      {{ formatDate(job.start_date) }} - {{ formatDate(job.end_date) }}
                    </div>
                    <div class="timeline-content">
                      <h4>{{ job.job_title }}</h4>
                      <div class="company-name">{{ job.company?.name || 'Unknown company' }}</div>
                      <div class="employment-type">{{ job.employment_type }}</div>
                      <div v-if="job.job_description" class="job-description">
                        {{ job.job_description }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Posts Tab -->
        <div v-if="activeTab === 'posts'" class="tab-content posts-tab">
          <div class="full-width-column">
            <div class="card">
              <div class="card-header">
                <h3>Posts</h3>
              </div>
              <div class="card-body">
                <div v-if="posts.data.length" class="post-list">
                  <div v-for="post in posts.data" :key="post.id" class="post-item">
                    <div class="post-header">
                      <img :src="user.profile_photo_url" class="post-author-avatar" alt="Author">
                      <div class="post-meta">
                        <div class="post-author">{{ user.full_name }}</div>
                        <div class="post-date">{{ formatDate(post.created_at) }}</div>
                      </div>
                    </div>
                    <div class="post-content">
                      {{ post.content }}
                    </div>
                  </div>
                </div>
                <div v-else class="empty-state">
                  No posts to display
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Connections Tab -->
<!-- Connections Tab -->
<div v-if="activeTab === 'connections'" class="profile-section">
    <div class="card">
        <h2 class="section-title">Connections</h2>
        <div class="connections-container">
            <div v-if="friends.length" class="connections-grid">
                <div v-for="friend in friends" :key="friend.id" class="connection-item">
                    <Link :href="route('admin.alumni.show', friend.encrypted_id)" class="connection-link">
                        <img :src="friend.profile_photo_url" class="connection-avatar" alt="Connection">
                        <div class="connection-details">
                            <div class="connection-name">{{ getFullName(friend) }}</div>
                            <div class="connection-meta" v-if="friend.current_employment">
                                {{ friend.current_employment.job_title }} at {{ friend.current_employment.company?.name || 'Unknown' }}
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
            <div v-else class="empty-state">
                <i class="fas fa-user-friends text-4xl mb-4"></i>
                <p>No connections yet</p>
            </div>
        </div>
    </div>
</div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ref, onMounted } from 'vue';
const activeTab = ref('overview');

const switchTab = (tab) => {
  activeTab.value = tab;
  // You can add additional logic here like loading data for the tab
  console.log(`Switched to ${tab} tab`);
  
  // If you want to scroll to sections:
  if (tab === 'education') {
    document.querySelector('.education-card')?.scrollIntoView({ behavior: 'smooth' });
  }
  // Add similar for other tabs
};

// Initialize on mount if needed
onMounted(() => {
  // You could check URL hash or other state to set initial tab
  const hash = window.location.hash.substring(1);
  if (['overview', 'education', 'employment', 'posts', 'connections'].includes(hash)) {
    activeTab.value = hash;
  }
});
const props = defineProps({
  user: Object,
  educationalBackgrounds: Array,
  professionalExams: Array,
  trainingsAttended: Array,
  employmentHistory: Array,
  currentEmployment: Object,
  pastEmployment: Array,
  alumniLocation: Object,
  jobHuntingExperiences: Array,
  unemployedDetails: Object,
  stats: Object,
  dropdownData: Object,
  posts: Object,
  connections: Array,
});
const friends = computed(() => props.connections || []);

// Add this method
const getFullName = (friend) => {
    return `${friend.first_name} ${friend.last_name}`;
};
// Formatting functions
const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'short' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const formatSalary = (salaryRange) => {
  const ranges = {
    'below_10k': 'Below ₱10,000',
    '10k-20k': '₱10,000 - ₱20,000',
    '20k-30k': '₱20,000 - ₱30,000',
    '30k-50k': '₱30,000 - ₱50,000',
    'above_50k': 'Above ₱50,000'
  };
  return ranges[salaryRange] || salaryRange;
};

const formatTimeToJob = (time) => {
  const times = {
    'less_than_1_month': 'Less than 1 month',
    '1_to_3_months': '1-3 months',
    '4_to_6_months': '4-6 months',
    '7_to_12_months': '7-12 months',
    'more_than_1_year': 'More than 1 year',
    'never_employed': 'Never employed'
  };
  return times[time] || time;
};

const formatJobSource = (source) => {
  const sources = {
    'online_portals': 'Online job portals',
    'walk_in': 'Walk-in application',
    'referral': 'Referral',
    'university_fair': 'University job fair',
    'social_media': 'Social media',
    'government': 'Government program',
    'other': 'Other'
  };
  return sources[source] || source;
};

const formatUnemploymentReason = (reason) => {
  const reasons = {
    'seeking': 'Seeking employment',
    'studying': 'Currently studying',
    'family_responsibilities': 'Family responsibilities',
    'health_issues': 'Health issues',
    'not_interested': 'Not interested in employment',
    'other': 'Other'
  };
  return reasons[reason] || reason;
};

const getCompetencyName = (id) => {
  const comp = props.dropdownData.jobMaintenanceMethods.find(m => m.id == id);
  return comp ? comp.name : `Skill ${id}`;
};

const getDifficultyName = (id) => {
  const diff = props.dropdownData.jobDifficulties.find(d => d.id == id);
  return diff ? diff.name : `Difficulty ${id}`;
};
</script>

<style scoped>
.alumni-profile-container {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: var(--text-primary);
  padding: 20px;
}

/* Cover Photo Section */
.cover-photo-section {
  position: relative;
  margin-bottom: 80px;
}

.cover-photo {
  height: 180px;
  background-size: cover;
  background-position: center;
  border-radius: 8px;
  position: relative;
}

.profile-photo-container {
  position: absolute;
  bottom: -50px;
  left: 30px;
}

.profile-photo {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  border: 5px solid var(--bg-dark);
  object-fit: cover;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

/* Profile Header */
.profile-header {
  margin-bottom: 30px;
  padding-left: 200px;
}

.profile-name {
  font-size: 2.2rem;
  margin: 0;
  color: var(--text-primary);
}

.profile-meta {
  display: flex;
  gap: 20px;
  margin-top: 10px;
  color: var(--text-primary) !important;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 5px;
}

.meta-item i {
  font-size: 0.9rem;
}

.meta-item .online {
  color: #4CAF50;
}

.meta-item .offline {
  color: #9E9E9E;
}

/* Main Content Layout */
.main-content {
  display: grid;
  grid-template-columns: 1fr 1.5fr;
  gap: 20px;
}

.left-column, .right-column {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

/* Card Styles */
.card {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  backdrop-filter: blur(12px);
  border-radius: 16px;
  padding: 20px;
  transition: all 0.3s;
}

.card:hover {
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
  transform: translateY(-2px);
}

.section-title {
  font-size: 1.3rem;
  margin-top: 0;
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  color: var(--primary);
}

/* About Section */
.about-content {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.about-item h3 {
  margin-top: 0;
  margin-bottom: 10px;
  font-size: 1.1rem;
}

.about-item p {
  margin: 5px 0;
}

/* Education Section */
.education-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.education-item {
  padding-bottom: 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.education-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.education-item h3 {
  margin: 0 0 5px 0;
  font-size: 1.1rem;
}

.institution, .campus, .year {
  margin: 3px 0;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.badge {
  display: inline-block;
  background: var(--primary-light);
  color: var(--primary);
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.8rem;
  margin-top: 5px;
}

/* Professional Exams & Trainings */
.exams-list, .trainings-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.exam-item, .training-item {
  padding-bottom: 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.exam-item:last-child, .training-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.exam-item h3, .training-item h3 {
  margin: 0 0 5px 0;
  font-size: 1.1rem;
}

/* Employment Section */
.employment-item {
  padding: 15px;
  margin-bottom: 15px;
  border-radius: 8px;
  background: rgba(0, 0, 0, 0.2);
}

.employment-item.current {
  border-left: 4px solid var(--primary);
}

.employment-item.past {
  opacity: 0.8;
}

.employment-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.employment-header h4 {
  margin: 0;
  font-size: 1.1rem;
}

.employment-type {
  font-size: 0.8rem;
  background: rgba(255, 255, 255, 0.1);
  padding: 3px 8px;
  border-radius: 4px;
}

.company {
  margin: 5px 0;
  color: var(--text-secondary);
}

.duration {
  margin: 5px 0;
  font-size: 0.9rem;
}

.location {
  margin: 5px 0;
  font-size: 0.9rem;
  color: var(--text-secondary);
  display: flex;
  align-items: center;
  gap: 5px;
}

/* Map Section */
.map-container {
  position: relative;
  height: 300px;
  border-radius: 8px;
  overflow: hidden;
}

.map {
  width: 100%;
  height: 100%;
}

.map-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.7);
  padding: 10px;
  font-size: 0.9rem;
}

.map-info i {
  color: var(--primary);
}

/* Stats Section */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 15px;
  text-align: center;
}

.stat-item {
  padding: 15px;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 8px;
}

.stat-value {
  font-size: 1.8rem;
  font-weight: bold;
  color: var(--primary);
}

.stat-label {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

/* Responsive Design */
@media (max-width: 768px) {
  .main-content {
    grid-template-columns: 1fr;
  }
  
  .profile-header {
    padding-left: 0;
    margin-top: 60px;
  }
  
  .profile-photo-container {
    left: 50%;
    transform: translateX(-50%);
  }
}
/* Base Styles */
.profile-container {
  color: var(--text-primary);
  min-height: 100vh;
  padding-bottom: 2rem;
}

/* Cover Photo Section */
.cover-photo {
  height: 180px;
  background-size: cover;
  background-position: center;
  position: relative;
  border-radius: 16px 16px;
  overflow: hidden;
}

.profile-actions {
  position: absolute;
  top: 1rem;
  right: 1rem;
}

.btn-outline {
  background-color: transparent;
  color: var(--text-primary);
  border: 1px solid var(--card-border);
  border-radius: 8px;
  padding: 8px 12px;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-outline:hover {
  background-color: var(--primary-light);
}

/* Profile Header */
.profile-header {
  display: flex;
  gap: 2rem;
  padding: 0 2rem;
  margin-top: -75px;
  margin-bottom: 2rem;
  position: relative;
  z-index: 1;
  margin-bottom: 0px;
  overflow: visible;
}

.avatar-container {
  position: relative;
      position: relative;
    width: 175px;
    height: 100px;
}

.profile-avatar {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  border: 4px solid var(--bg-dark);
  object-fit: cover;
  background-color: var(--bg-darker);
  top: 37px;
}

.online-status {
  position: absolute;
      top: 145px;
  right: 10px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background-color: #ccc;
  border: 2px solid var(--bg-dark);
}

.online-status.online {
  background-color: #4CAF50;
}

.profile-info {
  flex: 1;
  padding-top: 50px;
  padding-bottom: 0px;
}

.profile-name {
  font-size: 2rem;
  margin: 0 0 0.5rem 0;
  color: var(--text-primary);
text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
}

.profile-meta {
  display: flex;
  gap: 1.5rem;
  color: var(--text-primary);
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);

  margin-bottom: 1.5rem;
}

.profile-meta i {
  margin-right: 5px;
}

.profile-stats {
  display: flex;
  gap: 2rem;
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
}

.stat-number {
  font-size: 1.2rem;
  font-weight: bold;
  color: var(--text-primary);
}

.stat-label {
  font-size: 0.9rem;
  color: var(--text-secondary);
}

/* Profile Navigation */
.profile-nav {
  display: flex;
  border-bottom: 1px solid var(--card-border);
  margin: 0 2rem 2rem;
}
.profile-nav button{
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8) !important;
}
.nav-item {
  padding: 1rem 1.5rem;
  background: transparent;
  border: none;
  color: var(--text-secondary);
  font-weight: 500;
  cursor: pointer;
  position: relative;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
  transition: all 0.3s;
  color: var(--text-primary);
}

.nav-item:hover {
  color: var(--text-primary);
}

.nav-item.active {
  color: var(--primary);
}

.nav-item.active::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  height: 3px;
  background-color: var(--primary);
}

/* Profile Content */
.profile-content {
  padding: 0 2rem;
}

.tab-content {
  display: flex;
  gap: 2rem;
}

.left-column {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.right-column {
  flex: 2;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.full-width-column {
  width: 100%;
}

/* Card Styles */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid var(--card-border);
}

.card-header h3 {
  margin: 0;
  font-size: 1.2rem;
  color: var(--text-primary);
}

.btn-icon {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s;
}

.btn-icon:hover {
  background-color: var(--primary-light);
  color: var(--primary);
}

.card-body {
  color: var(--text-secondary);
}

/* About Section */
.about-item {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.about-item i {
  color: var(--primary);
  margin-top: 3px;
}

.about-label {
  font-weight: 500;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}

/* Education Section */
.education-item {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.education-icon i {
  color: var(--primary);
}

.education-details h4 {
  margin: 0 0 0.25rem 0;
  color: var(--text-primary);
}

.education-meta {
  color: var(--text-secondary);
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.education-meta span {
  margin-right: 0.5rem;
}

.education-status {
  display: inline-block;
  background-color: var(--primary-light);
  color: var(--primary);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
}

/* Skills Section */
.skills-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.job-title {
  font-weight: 500;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
}

.skill-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.skill-tag {
  background-color: var(--primary-light);
  color: var(--primary);
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.85rem;
}

/* Employment Section */
.employment-item {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--card-border);
}

.employment-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.employment-icon i {
  color: var(--primary);
}

.employment-details h4 {
  margin: 0 0 0.25rem 0;
  color: var(--text-primary);
}

.employment-meta {
  color: var(--text-secondary);
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.employment-meta span {
  margin-right: 0.5rem;
}

.employment-salary {
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.employment-skills {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

/* Timeline Styles */
.employment-timeline {
  position: relative;
  padding-left: 30px;
}

.employment-timeline::before {
  content: '';
  position: absolute;
  top: 0;
  bottom: 0;
  left: 10px;
  width: 2px;
  background-color: var(--primary);
  opacity: 0.3;
}

.timeline-item {
  position: relative;
  padding-bottom: 2rem;
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: -30px;
  top: 5px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background-color: var(--primary);
}

.timeline-item.current::before {
  background-color: #4CAF50;
}

.timeline-date {
  font-size: 0.9rem;
  color: var(--text-secondary);
  margin-bottom: 0.5rem;
}

.timeline-content h4 {
  margin: 0 0 0.25rem 0;
  color: var(--text-primary);
}

.company-name {
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.employment-type {
  color: var(--text-secondary);
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.job-description {
  line-height: 1.5;
}

/* Posts Section */
.post-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.post-item {
  padding-bottom: 1.5rem;
  border-bottom: 1px solid var(--card-border);
}

.post-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.post-header {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1rem;
}

.post-author-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  object-fit: cover;
}

.post-meta {
  flex: 1;
}

.post-author {
  font-weight: 500;
  color: var(--text-primary);
}

.post-date {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.post-content {
  line-height: 1.5;
}

/* Connections Section */
.connections-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;
}

.connection-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.connection-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  margin-bottom: 0.75rem;
}

.connection-name {
  font-weight: 500;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
}

.connection-meta {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 2rem;
  color: var(--text-secondary);
}

/* Responsive Adjustments */
@media (max-width: 992px) {
  .profile-header {
    flex-direction: column;
    align-items: center;
    text-align: center;
    margin-top: -100px;
  }
  
  .profile-info {
    padding-top: 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  
  .profile-meta {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .tab-content {
    flex-direction: column;
  }
}

@media (max-width: 768px) {
  .profile-nav {
    overflow-x: auto;
    padding-bottom: 0.5rem;
  }
  
  .nav-item {
    white-space: nowrap;
  }
  
  .cover-photo {
    height: 180px;
  }
}
.employment-history-card{
  height: 100%;
}
</style>