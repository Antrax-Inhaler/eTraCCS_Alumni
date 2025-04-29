<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, onUnmounted } from 'vue';;
import { router } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import Timeline from './Timeline.vue';

import { Link } from '@inertiajs/vue3';


const getEmploymentTypeClass = (type) => {
  if (!type) return '';
  return `employment-type-${type.toLowerCase().replace('_', '-')}`;
};
const formatEmploymentType = (type) => {
  if (!type) return '';
  const types = {
    'full_time': 'Full-time',
    'part_time': 'Part-time',
    'contract': 'Contract',
    'freelance': 'Freelance',
    'internship': 'Internship',
    'temporary': 'Temporary'
  };
  return types[type] || type;
};
const props = defineProps({
  entities: {
    type: Array,
    required: true,
    default: () => [],
  },
  pagination: {
    type: Object,
    required: true,
  },
    employmentHistory: {
        type: Array,
        required: true,
        default: () => []
    },
    stats: {
    type: Object,
    required: true,
    default: () => ({
      post_count: 0,
      followers_count: 0,
      following_count: 0
    }),

  },
  posts: {
        type: Object,
        default: () => ({
            data: [],
            current_page: 1,
            last_page: 1,
            per_page: 10,
            total: 0,
            next_page_url: null
        })
    },
    user: {
        type: Object,
        required: true,
        default: () => ({
            id: null,
            name: '',
            email: '',
            first_name: '',
            middle_initial: '',
            last_name: '',
            profile_photo_url: '',
            full_name: ''
        })
    },
    educationalBackgrounds: Array,
    professionalExams: Array,
    trainingsAttended: Array,
    alumniLocation: Object,
    companyLocations: Array,
    currentEmployment: Object,
    pastEmployment: {
        type: Array,
        required: false,
        default: () => [] // Ensure pastEmployment is initialized as an array
    },
    auth_user_id: Number,
  is_following: Boolean,
  photos: {
        type: Array,
        default: () => []
    },
    friends: {
        type: Array,
        default: () => []
    }


});

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num);
};

const showFollowers = () => {
  // Implement followers list modal/show page
  console.log('Show followers list');
};

const showFollowing = () => {
  // Implement following list modal/show page
  console.log('Show following list');
};
const isFollowing = ref(props.is_following);

function toggleFollow() {
  // Optimistic UI update
  isFollowing.value = !isFollowing.value;
  
  router.post(route('follow.toggle', { user: props.user.id }), {}, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: () => {
      // Sync with server response
      isFollowing.value = props.is_following;
    },
    onError: () => {
      // Revert if error occurs
      isFollowing.value = !isFollowing.value;
    }
  });
}
const formatYear = (dateString) => {
  return dateString ? new Date(dateString).getFullYear() : '';
};

// const formatEmploymentType = (type) => {
//   const types = {
//     'full-time': 'Full Time',
//     'part-time': 'Part Time',
//     'contract': 'Contract',
//     'internship': 'Internship',
//     'freelance': 'Freelance'
//   };
//   return types[type] || type;
// };

// Navigation functions
const navigateToEducation = () => {
    router.visit(route('profile.educationalBackground.index', { id: props.user.encrypted_id }));
};
const navigateToEmploymentHistories = () => {
    if (!props.user.encrypted_id) {
        console.error('Encrypted ID is missing');
        return;
    }
    router.visit(route('profile.employmentHistory.index', { id: props.user.encrypted_id }));
};
const navigateToEmploymentStatus = () => {
    router.visit(route('profile.employmentStatus.index', { id: props.user.encrypted_id }));
};
const navigateToProfessionalExams = () => {
    router.visit(route('profile.professionalExam.index', { id: props.user.encrypted_id }));
};
const navigateToTrainingAttended = () => {
    router.visit(route('profile.trainingAttended.index', { id: props.user.encrypted_id }));
};
const navigateToCompanyLocations = () => {
    router.visit(route('profile.companyLocation.index', { id: props.user.encrypted_id }));
};
const activeTab = ref('about');
const showImagePreview = ref(false);
const currentImageIndex = ref(0);
const currentImage = ref(null);

const openImagePreview = (index) => {
    currentImageIndex.value = index;
    currentImage.value = props.photos[index];
    showImagePreview.value = true;
};

const navigateImages = (direction) => {
    if (direction === 'prev' && currentImageIndex.value > 0) {
        currentImageIndex.value--;
    } else if (direction === 'next' && currentImageIndex.value < props.photos.length - 1) {
        currentImageIndex.value++;
    }
    currentImage.value = props.photos[currentImageIndex.value];
};

const closePreview = () => {
    showImagePreview.value = false;
};

// Close preview when clicking outside
const clickOutside = (event) => {
    if (event.target.classList.contains('image-preview-overlay')) {
        closePreview();
    }
};

// Keyboard navigation
const handleKeyDown = (event) => {
    if (showImagePreview.value) {
        if (event.key === 'Escape') {
            closePreview();
        } else if (event.key === 'ArrowLeft') {
            navigateImages('prev');
        } else if (event.key === 'ArrowRight') {
            navigateImages('next');
        }
    }
};

// Add event listeners
onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
});


// Function to switch tabs
const switchTab = (tab) => {
  activeTab.value = tab;
  // Move the nav indicator
  const indicator = document.querySelector('.nav-indicator');
  const activeItem = document.querySelector(`.nav-item[data-tab="${tab}"]`);
  if (indicator && activeItem) {
    const itemRect = activeItem.getBoundingClientRect();
    const containerRect = activeItem.parentElement.getBoundingClientRect();
    indicator.style.width = `${itemRect.width}px`;
    indicator.style.left = `${itemRect.left - containerRect.left}px`;
  }
};
const postsEntities = ref(props.posts.data || []);
const postsPagination = ref({
    current_page: props.posts.current_page,
    has_more: props.posts.next_page_url !== null,
    total: props.posts.total,
    per_page: props.posts.per_page,
    last_page: props.posts.last_page
});

const loadMorePosts = async () => {
    if (!postsPagination.value.has_more) return;
    
    try {
        const response = await axios.get(props.posts.next_page_url);
        const newData = response.data.data; // Ensure this matches your API response
        
        postsEntities.value = [...postsEntities.value, ...newData];
        postsPagination.value = {
            current_page: response.data.current_page,
            has_more: response.data.next_page_url !== null,
            total: response.data.total,
            per_page: response.data.per_page,
            last_page: response.data.last_page
        };
    } catch (error) {
        console.error('Error loading more posts:', error);
        
    }
};
const getFullName = (friend) => {
    return [friend.first_name, friend.middle_name, friend.last_name]
        .filter(part => part && part.trim() !== '')
        .join(' ');
};
const downloadImage = () => {
    if (!currentImage.value?.url) return;
    
    // Create a temporary anchor element
    const link = document.createElement('a');
    link.href = currentImage.value.url;
    
    // Set the download filename
    const fileName = currentImage.value.caption 
        ? `${currentImage.value.caption.substring(0, 20)}.jpg` 
        : `photo-${currentImageIndex.value + 1}.jpg`;
    link.download = fileName.replace(/[^a-z0-9]/gi, '_').toLowerCase();
    
    // Trigger the download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
onMounted(() => {
  switchTab('about');
});
</script>

<template>
    
    <AppLayout>
        <div class="main-content">
  <!-- Profile Header with Gradient Overlay -->
  <div class="card profile-header">
    <div class="cover-photo-container">
      <div class="cover-photo">
        <img :src="user.cover_photo" :alt="user.full_name" @error="handleImageError" alt="Cover Photo">
        <div class="cover-gradient"></div>
      </div>

    </div>
    
    <div class="profile-info">
      <div class="profile-picture-container">
        <div class="profile-picture-outline">
          <span class="online-status" :class="user.is_online ? 'online' : 'offline'" 
                :title="user.is_online ? 'Online now' : 'Last seen 2h ago'"></span>
          <div class="profile-picture">
          <img :src="user.profile_photo_url" :alt="user.full_name" @error="handleImageError">
          
        </div>
        </div>
        
      </div>
      <div class="profile-actions">
  <button 
    v-if="user.id !== auth_user_id" 
    @click="toggleFollow" 
    class="action-button primary-button glow-on-hover"
  >
    {{ isFollowing ? 'Unfollow' : 'Follow' }}
    <span class="button-icon">{{ isFollowing ? '✓' : '+' }}</span>
  </button>
  
  <Link :href="route('profile.show', $page.props.auth.user.encrypted_id)">
    <button v-if="user.id === auth_user_id" class="action-button primary-button glow-on-hover">
      Edit Profile <i class="fas fa-cog"></i>
    </button>
  </Link>
  
  <button class="action-button secondary-button">
    <i class="fas fa-envelope"></i> Message
  </button>
</div>

      <div class="profile-details">
        <h1 class="profile-name">
          {{ user.full_name }}
          <span class="verification-badge" v-if="user.verified">
            <i class="fas fa-check-circle"></i>
          </span>
        </h1>
        <div class="profile-handle">@{{ user.name }}</div>
        <div class="profile-bio">Software Engineer | Tech Enthusiast | Building digital experiences that matter</div>
        
        <div class="profile-meta">
          <div class="meta-item">
            <i class="fas fa-map-marker-alt"></i> San Francisco, CA
          </div>
          <div class="meta-item">
            <i class="fas fa-link"></i> 
            <a href="#" class="profile-link">portfolio.com/{{ user.username }}</a>
          </div>
          <div class="meta-item">
            <i class="fas fa-calendar-alt"></i> Joined September 2018
          </div>
        </div>
        
        <div class="profile-stats">
    <div class="stat-item">
      <span class="stat-number">{{ formatNumber(stats.post_count) }}</span>
      <span class="stat-label">Posts</span>
    </div>
    <div class="stat-item" @click="showFollowers">
      <span class="stat-number">{{ formatNumber(stats.followers_count) }}</span>
      <span class="stat-label">Followers</span>
    </div>
    <div class="stat-item" @click="showFollowing">
      <span class="stat-number">{{ formatNumber(stats.following_count) }}</span>
      <span class="stat-label">Following</span>
    </div>
  </div>
      </div>
    </div>
  </div>
  
  <!-- Profile Navigation with Indicator -->
  <div class="card profile-nav-container">
        <div class="profile-nav">
          <div class="nav-item" 
               :class="{ active: activeTab === 'about' }" 
               data-tab="about"
               @click="switchTab('about')">
            <i class="fas fa-user-circle"></i>
            <span class="nav-text">About</span>
          </div>
          <div class="nav-item" 
               :class="{ active: activeTab === 'posts' }" 
               data-tab="posts"
               @click="switchTab('posts')">
            <i class="fas fa-stream"></i>
            <span class="nav-text">Posts</span>
            <!-- <span class="nav-count">{{ formatNumber(stats.post_count) }}</span> -->
          </div>
          <div class="nav-item" 
               :class="{ active: activeTab === 'photos' }" 
               data-tab="photos"
               @click="switchTab('photos')">
            <i class="fas fa-image"></i>
            <span class="nav-text">Photos</span>
            <!-- <span class="nav-count">{{ photos.length }}</span> -->
          </div>
          <div class="nav-item" 
               :class="{ active: activeTab === 'friends' }" 
               data-tab="friends"
               @click="switchTab('friends')">
            <i class="fas fa-users"></i>
            <span class="nav-text">Friends</span>
            <span class="nav-count">{{ friends.length }}</span>
          </div>
          <div class="nav-indicator"></div>
        </div>
      </div>
      

  <div v-if="activeTab === 'about'">
          <!-- About Sections with Enhanced Cards -->
  <div class="card profile-section">
    <div class="section-header">
      <div class="section-title-container">
        <i class="fas fa-graduation-cap section-icon"></i>
        <h2 class="section-title">Education</h2>
      </div>
      <button v-if="user.id === auth_user_id" @click="navigateToEducation" class="edit-button pulse-on-hover">
        <i class="fas fa-pencil-alt"></i> Edit
      </button>
    </div>
    
    <div v-if="educationalBackgrounds.length > 0" class="cards-container">
      <div class="education-card" v-for="background in educationalBackgrounds" :key="background.id">
        <div class="education-icon">
          <i class="fas fa-university"></i>
        </div>
        <div class="education-details">
          <div class="institution-name">{{ background.institution }}</div>
          <div class="degree-earned">{{ background.degree_earned }}</div>
          <div class="education-meta">
            <span class="education-years">
              <i class="fas fa-calendar"></i> {{ background.year_graduated }}
            </span>
            <span class="education-gpa" v-if="background.gpa">
              <i class="fas fa-star"></i> GPA: {{ background.gpa }}
            </span>
          </div>
          <div class="education-description" v-if="background.description">
            {{ background.description }}
          </div>
        </div>
      </div>
    </div>
    
    <div v-else class="empty-state">
      <div class="empty-icon">
        <i class="fas fa-graduation-cap"></i>
      </div>
      <div class="empty-text">No education information added yet</div>
      <button v-if="user.id === auth_user_id" @click="navigateToEducation" class="empty-button">
        <i class="fas fa-plus"></i> Add Education
      </button>
    </div>
  </div>
  
  <div class="card profile-section">
  <div class="section-header">
    <div class="section-title-container">
      <i class="fas fa-briefcase section-icon"></i>
      <h2 class="section-title">Employment</h2>
    </div>
    <button v-if="user.id === auth_user_id" @click="navigateToEmploymentHistories" class="edit-button pulse-on-hover">
      <i class="fas fa-pencil-alt"></i> Edit
    </button>
  </div>
  
  <div v-if="currentEmployment || pastEmployment.length > 0" class="employment-timeline">
  <div v-if="currentEmployment" class="timeline-item current">
    <div class="timeline-badge">
      <i class="fas fa-briefcase"></i>
    </div>
    <div class="timeline-content">
      <div class="job-title">{{ currentEmployment.job_title }}</div>
      <div class="company-name">{{ currentEmployment.company_name }}</div>
      <div class="employment-period">
        {{ formatYear(currentEmployment.start_date) }} - Present
        <span class="employment-type" :class="getEmploymentTypeClass(currentEmployment.employment_type)">
          {{ formatEmploymentType(currentEmployment.employment_type) }}
        </span>
      </div>
      <div class="job-description" v-if="currentEmployment.description">
        {{ currentEmployment.description }}
      </div>
    </div>
  </div>
  
  <div class="timeline-connector" v-if="currentEmployment && pastEmployment.length > 0"></div>
  
  <div v-for="job in pastEmployment" :key="job.id" class="timeline-item past">
    <div class="timeline-badge">
      <i class="fas fa-building"></i>
    </div>
    <div class="timeline-content">
      <div class="job-title">{{ job.job_title }}</div>
      <div class="company-name">{{ job.company_name }}</div>
      <div class="employment-period">
        {{ formatYear(job.start_date) }} - {{ job.end_date ? formatYear(job.end_date) : 'Present' }}
        <span v-if="job.employment_type" class="employment-type" :class="getEmploymentTypeClass(job.employment_type)">
          {{ formatEmploymentType(job.employment_type) }}
        </span>
      </div>
      <div class="job-description" v-if="job.description">
        {{ job.description }}
      </div>
    </div>
  </div>
</div>
  
  <div v-else class="empty-state">
    <div class="empty-icon">
      <i class="fas fa-briefcase"></i>
    </div>
    <div class="empty-text">No employment information added yet</div>
    <button v-if="user.id === auth_user_id" @click="navigateToEmploymentHistories" class="empty-button">
      <i class="fas fa-plus"></i> Add Employment
    </button>
  </div>
</div>

<!-- Professional Exams with Ribbon Effect -->
<div class="card profile-section">
  <div class="section-header">
    <div class="section-title-container">
      <i class="fas fa-award section-icon"></i>
      <h2 class="section-title">Professional Exams</h2>
    </div>
    <button v-if="user.id === auth_user_id" @click="navigateToProfessionalExams" class="edit-button pulse-on-hover">
      <i class="fas fa-pencil-alt"></i> Edit
    </button>
  </div>
  
  <div v-if="professionalExams.length > 0" class="certification-grid">
    <div class="exam-card" v-for="exam in professionalExams" :key="exam.id">
      <div class="exam-ribbon" v-if="exam.license_number">Certified</div>
      <div class="exam-icon">
        <i class="fas fa-certificate"></i>
      </div>
      <div class="exam-details">
        <div class="exam-name">{{ exam.exam_name }}</div>
        <div class="exam-meta">
          <span class="exam-date">
            <i class="fas fa-calendar"></i> {{ exam.exam_date }}
          </span>
          <span class="exam-score" v-if="exam.score">
            <i class="fas fa-star"></i> Score: {{ exam.score }}
          </span>
        </div>
        <div class="license-number" v-if="exam.license_number">
          <i class="fas fa-id-card"></i> License: {{ exam.license_number }}
        </div>
        <div class="exam-description" v-if="exam.description">
          {{ exam.description }}
        </div>
      </div>
    </div>
  </div>
  
  <div v-else class="empty-state">
    <div class="empty-icon">
      <i class="fas fa-award"></i>
    </div>
    <div class="empty-text">No professional exams added yet</div>
    <button v-if="user.id === auth_user_id" @click="navigateToProfessionalExams" class="empty-button">
      <i class="fas fa-plus"></i> Add Exam
    </button>
  </div>
</div>

<!-- Training Attended with Card Grid -->
<div class="card profile-section">
  <div class="section-header">
    <div class="section-title-container">
      <i class="fas fa-certificate section-icon"></i>
      <h2 class="section-title">Training Attended</h2>
    </div>
    <button v-if="user.id === auth_user_id" @click="navigateToTrainingAttended" class="edit-button pulse-on-hover">
      <i class="fas fa-pencil-alt"></i> Edit
    </button>
  </div>
  
  <div v-if="trainingAttendeds?.length > 0" class="training-grid">
    <div class="training-card" v-for="training in trainingAttendeds" :key="training.id">
      <div class="training-header">
        <div class="training-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="training-duration" v-if="training.duration">
          {{ training.duration }} hours
        </div>
      </div>
      <div class="training-content">
        <div class="training-name">{{ training.training_name }}</div>
        <div class="training-institution">{{ training.institution }}</div>
        <div class="training-meta">
          <span class="training-year">
            <i class="fas fa-calendar"></i> {{ training.year_attended }}
          </span>
          <span class="training-certificate" v-if="training.certificate_id">
            <i class="fas fa-certificate"></i> Certified
          </span>
        </div>
        <div class="training-description" v-if="training.description">
          {{ training.description }}
        </div>
      </div>
    </div>
  </div>
  
  <div v-else class="empty-state">
    <div class="empty-icon">
      <i class="fas fa-certificate"></i>
    </div>
    <div class="empty-text">No training information added yet</div>
    <button v-if="user.id === auth_user_id" @click="navigateToTrainingAttended" class="empty-button">
      <i class="fas fa-plus"></i> Add Training
    </button>
  </div>
</div>

<!-- Company Location with Interactive Map -->
<div class="card profile-section">
  <div class="section-header">
    <div class="section-title-container">
      <i class="fas fa-map-marked-alt section-icon"></i>
      <h2 class="section-title">Company Location</h2>
    </div>
    <button v-if="user.id === auth_user_id" @click="navigateToCompanyLocations" class="edit-button pulse-on-hover">
      <i class="fas fa-pencil-alt"></i> Edit
    </button>
  </div>
  
  <div class="interactive-map-container">
    <div class="map-placeholder">
      <i class="fas fa-map-marker-alt"></i>
      <div class="map-text">Interactive Map View</div>
      <div class="map-coordinates" v-if="companyLocation">
        {{ companyLocation.latitude }}, {{ companyLocation.longitude }}
      </div>
    </div>
    <div class="map-actions">
      <button class="map-button">
        <i class="fas fa-directions"></i> Get Directions
      </button>
      <button class="map-button">
        <i class="fas fa-street-view"></i> Street View
      </button>
    </div>
  </div>
</div>
      </div>
      
      <!-- Posts Section -->
      <div v-if="activeTab === 'posts'" class="posts-section">
        <div class="card">
            <div class="section-header">
                <h2 class="section-title">Posts</h2>
                <span class="posts-count">{{ formatNumber(stats.post_count) }} posts</span>
            </div>
            
            <div class="timeline-container">
                <Timeline
                    :initialEntities="postsEntities"
                    :initialPagination="postsPagination"
                    @load-more="loadMorePosts"
                />
            </div>
        </div>
    </div>
      
      <div v-if="activeTab === 'photos'" class="photos-section">
                <div class="card">
                    <div class="section-header">
                        <h2 class="section-title">Photos</h2>
                        <span class="photos-count">{{ photos.length }} photos</span>
                    </div>
                    
                    <div v-if="photos.length > 0" class="photos-grid">
                        <div v-for="(photo, index) in photos" 
                             :key="photo.id" 
                             class="photo-item"
                             @click="openImagePreview(index)">
                            <img :src="photo.url" 
                                 :alt="photo.caption || 'User photo'" 
                                 class="photo-thumbnail"
                                 @error="handleImageError">
                            <div class="photo-overlay">
                                <div v-if="photo.caption" class="photo-caption">
                                    {{ photo.caption.length > 50 ? photo.caption.substring(0, 50) + '...' : photo.caption }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="empty-text">No photos yet</div>
                    </div>
                </div>
            </div>
      
            <div v-if="activeTab === 'friends'" class="friends-section">
        <div class="card">
            <div class="section-header">
                <h2 class="section-title">Friends</h2>
                <span class="friends-count">{{ formatNumber(stats.friend_count) }} friends</span>
            </div>
            
            <div v-if="friends.length > 0" class="friends-grid">
                <div v-for="friend in friends" :key="friend.id" class="friend-card">
                    <Link :href="`/profile/${friend.encrypted_id}`" class="profile-link">
                        <div class="friend-avatar-container">
                            <img :src="friend.avatar" 
                                 :alt="getFullName(friend)" 
                                 class="friend-avatar"
                                 @error="handleImageError">
                            <span v-if="friend.online" class="friend-online-status" 
                                  :title="friend.online ? 'Online now' : 'Offline'"></span>
                        </div>
                    </Link>
                    <div class="friend-info">
                        <Link :href="`/profile/${friend.encrypted_id}`" class="profile-link">
                            <span class="friend-name">
                                {{ getFullName(friend) }}
                            </span>
                        </Link>
                        <span v-if="friend.mutual_count > 0" class="friend-mutual">
                            {{ friend.mutual_count }} mutual {{ friend.mutual_count === 1 ? 'friend' : 'friends' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div v-else class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="empty-text">No mutual friends yet</div>
            </div>
        </div>
    </div>

    <div v-if="showImagePreview" 
     class="image-preview-overlay" 
     @click="clickOutside">
    <div class="image-preview-container">
        <button class="close-preview" @click="closePreview">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- Download Button -->
        <button class="download-preview" @click.stop="downloadImage">
            <i class="fas fa-download"></i>
        </button>
        
        <div class="preview-image-wrapper">
            <img :src="currentImage.url" 
                 :alt="currentImage.caption || 'Photo preview'" 
                 class="preview-image"
                 @error="handleImageError">
        </div>
        <div class="preview-nav">
            <button class="nav-button prev-button" 
                    @click.stop="navigateImages('prev')"
                    :disabled="currentImageIndex === 0">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="preview-caption" v-if="currentImage.caption">
                {{ currentImage.caption }}
            </div>
            <button class="nav-button next-button" 
                    @click.stop="navigateImages('next')"
                    :disabled="currentImageIndex === photos.length - 1">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <div class="preview-counter">
            {{ currentImageIndex + 1 }} / {{ photos.length }}
        </div>
    </div>
</div>
</div>


    </AppLayout>
</template>
<!-- Employment Section with Timeline Effect -->

<style scoped>
/* ... (keep existing styles) */
.photos-section, .posts-section{
  animation: fadeIn 0.5s ease-out forwards;
}
/* Photos Grid */
.photos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
    padding: 15px;
}

.photo-item {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.photo-item:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.photo-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.photo-item:hover .photo-thumbnail {
    transform: scale(1.05);
}

.photo-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
    padding: 10px;
    color: white;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.photo-item:hover .photo-overlay {
    opacity: 1;
}

.photo-caption {
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.photos-count {
    font-size: 14px;
    color: #65676B;
    margin-left: 10px;
}

/* Image Preview Overlay */
.image-preview-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.9);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.image-preview-container {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    width: auto;
    height: auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.close-preview {
    position: absolute;
    top: -40px;
    right: 0;
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 5px;
    opacity: 0.7;
    transition: opacity 0.2s ease;
}

.close-preview:hover {
    opacity: 1;
}

.preview-image-wrapper {
    max-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.preview-image {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 4px;
}

.preview-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 20px;
    gap: 20px;
}

.nav-button {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease;
}

.nav-button:hover {
    background: rgba(255, 255, 255, 0.4);
}

.nav-button:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.preview-caption {
    color: white;
    text-align: center;
    max-width: 500px;
    padding: 0 20px;
    font-size: 16px;
}

.preview-counter {
    color: rgba(255, 255, 255, 0.7);
    font-size: 14px;
    margin-top: 10px;
}
.map-placeholder {
    height: 90vh;
    padding: 20px;
}
/* Responsive adjustments */
@media (max-width: 768px) {
    .photos-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .preview-nav {
        margin-top: 15px;
    }
    
    .preview-caption {
        font-size: 14px;
        max-width: 300px;
    }
}

@media (max-width: 480px) {
    .photos-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 5px;
        padding: 10px;
    }
    
    .image-preview-container {
        max-width: 95%;
    }
    
    .preview-image {
        max-height: 60vh;
    }
    
    .nav-button {
        width: 30px;
        height: 30px;
        font-size: 12px;
    }
    
    .preview-caption {
        font-size: 12px;
        max-width: 200px;
    }
}

.friends-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    padding: 15px;
}

.friend-card {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 8px;
    background: var(--card-bg);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.friend-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.friend-avatar-container {
    position: relative;
    width: 50px;
    height: 50px;
    margin-right: 12px;
    flex-shrink: 0;
}

.friend-avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border-color);
}

.friend-online-status {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
    background: #31a24c;
    border: 2px solid var(--card-bg);
    border-radius: 50%;
}

.friend-info {
    flex-grow: 1;
    min-width: 0;
}

.friend-name {
    font-weight: 600;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.friend-mutual {
    font-size: 12px;
    color: var(--text-secondary);
    display: block;
    margin-top: 2px;
}

.friend-relationship {
    display: flex;
    gap: 6px;
    margin-top: 4px;
}

.following-badge, .follows-you-badge {
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 10px;
}

.following-badge {
    background: rgba(24, 119, 242, 0.1);
    color: #1877f2;
}

.follows-you-badge {
    background: rgba(66, 183, 42, 0.1);
    color: #42b72a;
}

.profile-link {
    color: inherit;
    text-decoration: none;
}

.profile-link:hover {
    text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .friends-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 12px;
        padding: 12px;
    }
    
    .friend-avatar-container {
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 480px) {
    .friends-grid {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    
    .friend-card {
        padding: 10px;
    }
}

.friend-name {
    font-weight: 600;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-bottom: 4px;
}

.friend-mutual {
    font-size: 12px;
    color: var(--text-secondary);
    display: block;
}
.download-preview {
    position: absolute;
    top: -40px;
    right: 50px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 18px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    z-index: 10;
}

.download-preview:hover {
    background: rgba(255, 255, 255, 0.4);
    transform: scale(1.1);
}

/* Adjust close button position to make room for download */
.close-preview {
    right: 10px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .download-preview {
        top: -35px;
        right: 45px;
        width: 32px;
        height: 32px;
        font-size: 16px;
    }
}

@media (max-width: 480px) {
    .download-preview {
        top: -30px;
        right: 40px;
        width: 28px;
        height: 28px;
        font-size: 14px;
    }
}
.employment-timeline {
  position: relative;
  padding-left: 30px;
  margin-top: 20px;
}

.timeline-item {
  position: relative;
  padding-bottom: 20px;
}

.timeline-badge {
  position: absolute;
  left: -40px;
  top: 0;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: var(--primary);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
}

.timeline-connector {
  position: relative;
  height: 20px;
  margin-left: -10px;
}

.timeline-connector::before {
  content: '';
  position: absolute;
  left: -20px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: rgba(0,0,0,0.1);
}

.timeline-content {
  padding: 15px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.job-title {
  font-weight: bold;
  margin-bottom: 5px;
}

.company-name {
  color: #666;
  margin-bottom: 5px;
}

</style>