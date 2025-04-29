<template>
  <AppLayout>

    <div class="container-main">
      <!-- <div class="user-list">
    <div v-for="user in userList" :key="user.id" class="user-item">
      <img :src="user.profile_photo_url" :alt="user.full_name" class="profile-pic" />
      <span class="user-name">{{ user.full_name }}</span>
      <span
        class="status-indicator"
        :class="{ online: user.is_online, offline: !user.is_online }"
      ></span>
    </div>
  </div> -->
  <div class="stories">
    <div class="story" v-for="user in userList" :key="user.id">
        <Link :href="`/profile/${user.encrypted_id}`" class="add-story">
            <img :src="user.profile_photo_url" class="story-avatar" alt="Story">
            <span
                class="status-indicator"
                :class="{ online: user.is_online, offline: !user.is_online }"
            ></span>
        </Link>
        <span class="user-name">{{ user.full_name }}</span>
    </div>
</div>
  <!-- <div class="mt-6">
    <h2 class="text-lg font-bold mb-4">Recommended Users</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="user in recommendedUsers"
        :key="user.id"
        class="flex items-center bg-white p-4 rounded-lg shadow-md relative"
      >
        <img
          :src="user.profile_photo_url"
          class="w-12 h-12 rounded-full object-cover mr-4"
          :alt="user.full_name"
        />
        <div class="flex-1">
          <p class="font-semibold">{{ user.last_name }}, {{ user.first_name }}</p>
          <span
            class="inline-block w-2 h-2 rounded-full mt-1"
            :class="user.is_online ? 'bg-green-500' : 'bg-gray-400'"
            title="Online Status"
          ></span>
        </div>
        <button
          @click="toggleFollow(user)"
          class="ml-auto px-3 py-1 rounded text-sm font-medium"
          :class="user.is_following ? 'bg-gray-300 text-gray-700' : 'bg-blue-600 text-white'"
        >
          {{ user.is_following ? 'Following' : 'Follow' }}
        </button>
      </div>
    </div>
  </div> -->

      <div class="profile-container">
                <!-- Cover Picture -->
                <div class="cover-picture">

                </div>
                <!-- User Info -->
                <div class="user-info2" style="display: none;">
                    <div class="profile-main">
                        <div class="profile-picture-con">
                          <img :src="$page.props.auth.user.profile_photo_url"  :alt="$page.props.auth.user.name">  
                        </div>
                    </div>

                    <div class="user-info-item2">
                        <div class="university">
                            <div class="card-img">
                                <img src="" alt="">
                            </div>
                            <!-- <div class="card-content" v-if="user.educational_backgrounds.length">
                                <p         v-for="(edu, index) in user.educational_backgrounds"
                                :key="index" class="card-title">{{ edu.institution }} - <span v-if="edu.campus">({{ edu.campus }})</span></p>

                                <div v-if="currentUser" class="user-profile">
                  <div class="profile-picture">
                    <img 
                      :src="currentUser.profile_picture_url" 
                      :alt="currentUser.full_name" 
                      class="avatar"
                    >
                  </div>
                  <div class="profile-info">
                    <h2>{{ currentUser.full_name }}</h2>
                    <p v-if="currentUser.first_name || currentUser.last_name">
                      {{ currentUser.first_name }} {{ currentUser.middle_initial }} {{ currentUser.last_name }}
                    </p>
                    <p>{{ currentUser.email }}</p>
                  </div>
                </div>
                            </div> -->
                        </div>              
                    </div>
            </div>
        </div>
<div class="contaner">
      <!-- Unified Content Creation Form -->
      <div class="card post-input" @click="openModal">
        <div class="post-input-header">
          <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" class="post-avatar">  
          <input type="text" placeholder="Start a thread...">
        </div>
        <div class="post-actions">
          <div class="post-attachments">
            <button class="attachment-button"><i class="fas fa-image"></i></button>
            <button class="attachment-button"><i class="fas fa-video"></i></button>
            <button class="attachment-button"><i class="fas fa-link"></i></button>
            <button class="attachment-button"><i class="fas fa-map-marker-alt"></i></button>
          </div>
          <button class="post-button-small" disabled>Post</button>
        </div>
      </div>
      <!-- <div class="card post-input" @click="openModal">
    <div class="post-input-header">
      <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" class="post-avatar">  

      <input type="text" placeholder="Start a thread..." readonly>
    </div>
  </div> -->

  <!-- Create Content Modal -->
  <div class="modal-overlay" :class="{ active: showModal }">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title">Create New</h3>
        <button class="modal-close" @click="closeModal">&times;</button>
      </div>
      
      <div class="content-tabs">
        <button
          v-for="type in contentTypes"
          :key="type.value"
          class="content-tab"
          :class="{ active: selectedType === type.value }"
          @click="selectContentType(type.value)"
        >
          <i :class="type.icon"></i> {{ type.label }}
        </button>
      </div>
      
      <div class="modal-body">
        <!-- Post Form -->
        <div class="form-guide" :class="{ active: currentGuide }">
  <div class="guide-content">
    <h4>{{ currentGuide?.title }}</h4>
    <p>{{ currentGuide?.message }}</p>
  </div>
</div>
        <form @submit.prevent="submitContent">
          <div v-if="selectedType === 'post'" class="content-form">
            <div class="form-group">
              <label for="post-title">Title</label>
              <input 
                id="post-title" 
                class="form-control" 
                v-model="formData.title" 
                placeholder="What's the title of your post?"
                required
              >
            </div>
            
            <div class="form-group">
              <label for="post-content">Content</label>
              <textarea 
                id="post-content" 
                class="form-control" 
                v-model="formData.body" 
                placeholder="Share your thoughts..."
                required
              ></textarea>
            </div>
            
            <div class="form-group">
              <label for="post-tags">Tags</label>
              <div class="tags-container">
                <span 
                  v-for="(tag, index) in formData.tags" 
                  :key="index" 
                  class="tag"
                >
                  {{ tag }}
                  <button @click="removeTag(tag)" class="tag-remove">&times;</button>
                </span>
                <input
                  id="post-tags"
                  class="form-control"
                  v-model="formData.tagsInput"
                  placeholder="Add tags (comma separated)"
                  @keydown.enter.prevent="addTag"
                >
              </div>
            </div>
            
            <div class="form-group">
              <label for="post-privacy">Privacy</label>
              <select 
                id="post-privacy" 
                class="form-control" 
                v-model="formData.privacy_setting"
                required
              >
                <option value="public">Public</option>
                <option value="private">Private</option>
                <option value="batchmates">Batchmates Only</option>
                <option value="campus_only">Campus Only</option>
              </select>
            </div>
          </div>
          
          <!-- Event Form -->
          <div v-if="selectedType === 'event'" class="content-form">
            <div class="form-group">
              <label for="event-name">Event Name</label>
              <input 
                id="event-name" 
                class="form-control" 
                v-model="formData.event_name" 
                placeholder="What's the name of your event?"
                required
              >
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="event-date">Date</label>
                <input 
                  id="event-date" 
                  class="form-control" 
                  type="date"
                  v-model="formData.date"
                  required
                >
              </div>
              <div class="form-group">
                <label for="event-time">Time</label>
                <input 
                  id="event-time" 
                  class="form-control" 
                  type="time"
                  v-model="formData.time"
                >
              </div>
            </div>
            
            <div class="form-group">
              <label for="event-organizer">Organizer Name</label>
              <input 
                id="event-organizer" 
                class="form-control" 
                v-model="formData.organizer_name" 
                placeholder="Enter organizer name"
                required
              >
            </div>
            
            <div class="form-group">
              <label for="event-description">Description</label>
              <textarea 
                id="event-description" 
                class="form-control" 
                v-model="formData.description" 
                placeholder="Tell people about your event..."
              ></textarea>
            </div>
            
            <div class="form-group">
              <label for="event-registration">Registration Link</label>
              <input 
                id="event-registration" 
                class="form-control" 
                type="url"
                v-model="formData.registration_link" 
                placeholder="Optional registration link"
              >
            </div>
            
            <div class="form-group">
              <label for="event-privacy">Privacy</label>
              <select 
                id="event-privacy" 
                class="form-control" 
                v-model="formData.privacy_setting"
                required
              >
                <option value="public">Public</option>
                <option value="private">Private</option>
                <option value="batchmates">Batchmates Only</option>
                <option value="campus_only">Campus Only</option>
              </select>
            </div>
            
            <!-- Map Section -->
            <div class="form-group">
              <label>Location on Map</label>
              <GMapMap
                :center="mapCenter"
                :zoom="12"
                style="width: 100%; height: 300px;"
                @click="onMapClick"
              >
                <GMapMarker
                  v-if="markerPosition"
                  :position="markerPosition"
                  :draggable="true"
                  @dragend="onMarkerDragEnd"
                />
              </GMapMap>
            </div>
            
            <!-- File Upload for Event -->
            <div class="file-upload-container">
              <label class="file-upload-label" for="event-files">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Click to upload event media</span>
                <span>or drag and drop files here</span>
                <input 
                  id="event-files" 
                  class="file-upload-input" 
                  type="file" 
                  multiple 
                  accept="image/*,video/*" 
                  @change="handleFileUpload($event, 'event')"
                >
              </label>
              
              <div class="file-preview-container">
                <div class="file-preview-title">Selected files:</div>
                <div class="file-preview-list">
                  <div 
                    v-for="(file, index) in selectedFiles.event" 
                    :key="index" 
                    class="file-preview-item"
                  >
                    <img v-if="file.type.startsWith('image/')" :src="file.preview">
                    <i v-else class="fas fa-file"></i>
                    <div class="file-info">{{ file.name }}</div>
                    <button class="remove-btn" @click="removeFile('event', index)">&times;</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Job Form -->
          <div v-if="selectedType === 'job_posting'" class="content-form">
            <div class="form-group">
              <label for="job-title">Job Title</label>
              <input 
                id="job-title" 
                class="form-control" 
                v-model="formData.job_title" 
                placeholder="What position are you hiring for?"
                required
              >
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="job-company">Company</label>
                <input 
                  id="job-company" 
                  class="form-control" 
                  v-model="formData.company_name" 
                  placeholder="Company name"
                  required
                >
              </div>
              <div class="form-group">
                <label for="job-type">Job Type</label>
                <select 
                  id="job-type" 
                  class="form-control" 
                  v-model="formData.job_type"
                >
                  <option value="full-time">Full-time</option>
                  <option value="part-time">Part-time</option>
                  <option value="contract">Contract</option>
                  <option value="internship">Internship</option>
                </select>
              </div>
            </div>
            
            <div class="form-group">
              <label for="job-description">Description</label>
              <textarea 
                id="job-description" 
                class="form-control" 
                v-model="formData.description" 
                placeholder="Describe the job responsibilities..."
                required
              ></textarea>
              <span class="field-hint">Minimum 100 characters ({{ formData.description.length }}/100)</span>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="job-salary">Salary Range</label>
                <input 
                  id="job-salary" 
                  class="form-control" 
                  v-model="formData.salary_range" 
                  placeholder="e.g., $50,000 - $70,000"
                >
              </div>
              <div class="form-group">
                <label for="job-deadline">Application Deadline</label>
                <input 
                  id="job-deadline" 
                  class="form-control" 
                  type="date"
                  v-model="formData.application_deadline"
                  required
                >
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="job-location">Location</label>
                <input 
                  id="job-location" 
                  class="form-control" 
                  v-model="formData.location" 
                  placeholder="Where is the job located?"
                >
              </div>
              <div class="form-group">
                <label for="job-remote">Remote Work</label>
                <select 
                  id="job-remote" 
                  class="form-control" 
                  v-model="formData.is_remote"
                >
                  <option :value="false">No</option>
                  <option :value="true">Yes</option>
                </select>
              </div>
            </div>
            
            <div class="form-group">
              <label for="job-privacy">Privacy</label>
              <select 
                id="job-privacy" 
                class="form-control" 
                v-model="formData.privacy_setting"
                required
              >
                <option value="public">Public</option>
                <option value="private">Private</option>
                <option value="batchmates">Batchmates Only</option>
                <option value="campus_only">Campus Only</option>
              </select>
            </div>
            
            <!-- Map Section -->
            <div class="form-group">
              <label>Location on Map</label>
              <GMapMap
                :center="mapCenter"
                :zoom="12"
                style="width: 100%; height: 300px;"
                @click="onMapClick"
              >
                <GMapMarker
                  v-if="markerPosition"
                  :position="markerPosition"
                  :draggable="true"
                  @dragend="onMarkerDragEnd"
                />
              </GMapMap>
            </div>
            
            <div class="file-upload-container">
    <label class="file-upload-label" for="event-files">
      <i class="fas fa-cloud-upload-alt"></i>
      <span>Click to upload event media</span>
      <span>or drag and drop files here</span>
      <input 
        id="event-files" 
        class="file-upload-input" 
        type="file" 
        multiple 
        accept="image/*,video/*" 
        @change="handleFileUpload($event, 'event')"
      >
    </label>
    
    <div class="file-preview-container">
      <div class="file-preview-title">Selected files:</div>
      <div class="file-preview-list">
        <div 
          v-for="(file, index) in selectedFiles.event" 
          :key="index" 
          class="file-preview-item"
        >
          <img v-if="file.type.startsWith('image/')" :src="file.preview">
          <i v-else class="fas fa-file"></i>
          <div class="file-info">{{ file.name }}</div>
          <button class="remove-btn" @click="removeFile('event', index)">&times;</button>
        </div>
      </div>
    </div>
  </div>
          </div>
          
          <div class="modal-footer">
            <button class="btn btn-outline" @click="closeModal">Cancel</button>
            <button class="btn btn-primary" type="submit">Publish</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<br>
      <div class="">
        <Timeline
          :initialEntities="entities"
          :initialPagination="pagination"
        />
      </div>
    </div>
    <div class="container-left">
          <div class="mt-6">
    <h2 class="text-lg font-bold mb-4">Recommended Users</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="user in recommendedUsers"
        :key="user.id"
        class="flex items-center bg-white p-4 rounded-lg shadow-md relative"
      >
        <img
          :src="user.profile_photo_url"
          class="w-12 h-12 rounded-full object-cover mr-4"
          :alt="user.full_name"
        />
        <div class="flex-1">
          <p class="font-semibold">{{ user.last_name }}, {{ user.first_name }}</p>
          <span
            class="inline-block w-2 h-2 rounded-full mt-1"
            :class="user.is_online ? 'bg-green-500' : 'bg-gray-400'"
            title="Online Status"
          ></span>
        </div>
        <button
          @click="toggleFollow(user)"
          class="ml-auto px-3 py-1 rounded text-sm font-medium"
          :class="user.is_following ? 'bg-gray-300 text-gray-700' : 'bg-blue-600 text-white'"
        >
          {{ user.is_following ? 'Following' : 'Follow' }}
        </button>
      </div>
    </div>
  </div>
      </div>
  </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Timeline from './Timeline.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import SampleTable from './SampleTable.vue';
import { usePage, Link } from '@inertiajs/vue3'

const props = defineProps({
  current_user: {
    type: Object,
    default: null
  },
  entities: {
    type: Array,
    required: true,
    default: () => [],
  },
  pagination: {
    type: Object,
    required: true,
  },
  userList: {
    type: Array,
    default: () => [],
  },
});

// Reactive state
const showModal = ref(false);
const selectedType = ref('post');
const selectedFiles = ref({
  event: [],
  job: []
});
const mapCenter = ref({ lat: 14.5995, lng: 120.9842 }); // Default to Manila
const markerPosition = ref(null);
    
const contentTypes = [
  { label: 'Post', value: 'post', icon: 'fas fa-pencil-alt' },
  { label: 'Event', value: 'event', icon: 'fas fa-calendar-alt' },
  { label: 'Job', value: 'job_posting', icon: 'fas fa-briefcase' }
];
    
const formData = ref({
  // Common fields
  privacy_setting: 'public',
  
  // Post fields
  title: '',
  body: '',
  tags: [],
  tagsInput: '',
  
  // Event fields
  event_name: '',
  date: '',
  time: '',
  organizer_name: '',
  description: '',
  registration_link: '',
  latitude: '',
  longitude: '',
  
  // Job fields
  job_title: '',
  company_name: '',
  job_type: 'full-time',
  salary_range: '',
  application_deadline: '',
  is_remote: false,
  location: ''
});

// Methods
const openModal = () => {
  showModal.value = true;
};
    
const closeModal = () => {
  showModal.value = false;
};
    
const selectContentType = (type) => {
  selectedType.value = type;
};
    
const addTag = () => {
  if (formData.value.tagsInput) {
    const newTags = formData.value.tagsInput
      .split(',')
      .map(tag => tag.trim())
      .filter(tag => tag.length > 0);
    
    formData.value.tags = [...new Set([...formData.value.tags, ...newTags])];
    formData.value.tagsInput = '';
  }
};
    
const removeTag = (tagToRemove) => {
  formData.value.tags = formData.value.tags.filter(tag => tag !== tagToRemove);
};
    
const handleFileUpload = (event, type) => {
  const files = Array.from(event.target.files);
  selectedFiles.value[type] = files.map(file => {
    const fileWithPreview = file;
    if (file.type.startsWith('image/')) {
      fileWithPreview.preview = URL.createObjectURL(file);
    }
    return fileWithPreview;
  });
};
    
const removeFile = (type, index) => {
  selectedFiles.value[type].splice(index, 1);
};
    
const onMapClick = (e) => {
  markerPosition.value = { lat: e.latLng.lat(), lng: e.latLng.lng() };
  formData.value.latitude = e.latLng.lat();
  formData.value.longitude = e.latLng.lng();
};
    
const onMarkerDragEnd = (e) => {
  markerPosition.value = { lat: e.latLng.lat(), lng: e.latLng.lng() };
  formData.value.latitude = e.latLng.lat();
  formData.value.longitude = e.latLng.lng();
};
    
const submitContent = async () => {
  const data = new FormData();
  validationErrors.value = {}; // Reset previous validation errors

  // Append common fields
  data.append('type', selectedType.value);
  data.append('privacy_setting', formData.value.privacy_setting);
  
  // Append type-specific fields
  if (selectedType.value === 'post') {
    data.append('title', formData.value.title);
    data.append('body', formData.value.body);
    data.append('tags', JSON.stringify(formData.value.tags)); // Send as JSON
  } 
  else if (selectedType.value === 'event') {
    data.append('event_name', formData.value.event_name);
    data.append('date', formData.value.date);
    data.append('time', formData.value.time);
    data.append('organizer_name', formData.value.organizer_name);
    data.append('description', formData.value.description);
    data.append('registration_link', formData.value.registration_link);
    data.append('latitude', formData.value.latitude || '');
    data.append('longitude', formData.value.longitude || '');
    
    // Append event files with type validation
    selectedFiles.value.event.forEach((file, index) => {
      if (file instanceof File) {
        data.append(`media_files[${index}]`, file);
      }
    });
  } 
  else if (selectedType.value === 'job_posting') {
    data.append('job_title', formData.value.job_title);
    data.append('company_name', formData.value.company_name);
    data.append('job_type', formData.value.job_type);
    data.append('description', formData.value.description);
    data.append('salary_range', formData.value.salary_range || '');
    data.append('application_deadline', formData.value.application_deadline);
    data.append('is_remote', formData.value.is_remote ? '1' : '0');
    data.append('location', formData.value.location || '');
    data.append('latitude', formData.value.latitude || '');
    data.append('longitude', formData.value.longitude || '');
    
    // Append job files with type validation
    selectedFiles.value.job.forEach((file, index) => {
      if (file instanceof File) {
        data.append(`media_files[${index}]`, file);
      }
    });
  }
  
  try {
    const response = await axios.post('/job-postings', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      validateStatus: (status) => status < 500 // Don't throw for 422 errors
    });

    if (response.status === 201 || response.status === 200) {
      // Success handling with toast notification instead of alert
      showNotification({
        type: 'success',
        message: `${selectedType.value} created successfully!`,
        duration: 3000
      });
      
      resetForm();
      closeModal();
      
      // Emit event for parent component to refresh data
      emit('content-created');
    }
    
  } catch (error) {
    console.error('API Error:', error);

    if (error.response) {
      // Server responded with error status
      if (error.response.status === 422) {
        // Handle validation errors
        validationErrors.value = error.response.data.errors || {};
        
        // Find first error field and scroll to it
        const firstErrorField = Object.keys(validationErrors.value)[0];
        if (firstErrorField) {
          const fieldElement = document.getElementById(firstErrorField);
          if (fieldElement) {
            fieldElement.scrollIntoView({
              behavior: 'smooth',
              block: 'center'
            });
            fieldElement.focus();
            showGuide(firstErrorField);
          }
        }
        
        // Show toast for general error
        showNotification({
          type: 'error',
          message: 'Please fix the validation errors',
          duration: 5000
        });
        
      } else {
        // Other server errors
        showNotification({
          type: 'error',
          message: error.response.data.message || `Request failed (${error.response.status})`,
          duration: 5000
        });
      }
    } else if (error.request) {
      // No response received
      showNotification({
        type: 'error',
        message: 'No response from server. Please check your connection.',
        duration: 5000
      });
    } else {
      // Request setup error
      showNotification({
        type: 'error',
        message: `Request error: ${error.message}`,
        duration: 5000
      });
    }
  }
};

// Helper function for notifications (add this to your methods)
const showNotification = ({ type, message, duration }) => {
  // Implement your preferred notification system here
  // This could use a toast library or your custom solution
  console.log(`[${type}] ${message}`);
  // Example using alert for simplicity (replace with your UI solution)
  alert(`[${type.toUpperCase()}] ${message}`);
};

const resetForm = () => {
  formData.value = {
    privacy_setting: 'public',
    title: '',
    body: '',
    tags: [],
    tagsInput: '',
    event_name: '',
    date: '',
    time: '',
    organizer_name: '',
    description: '',
    registration_link: '',
    latitude: '',
    longitude: '',
    job_title: '',
    company_name: '',
    job_type: 'full-time',
    salary_range: '',
    application_deadline: '',
    is_remote: false,
    location: ''
  };
  
  selectedFiles.value = {
    event: [],
    job: []
  };
  
  markerPosition.value = null;
};
// Add to your script setup
const currentGuide = ref(null);
const validationErrors = ref({});

// Add this method
const showGuide = (fieldName) => {
  const guides = {
    'post-title': {
      title: 'Post Title',
      message: 'Keep your title concise but descriptive (5-15 words). Avoid special characters.'
    },
    'post-content': {
      title: 'Post Content',
      message: 'Share your thoughts in detail. Minimum 50 characters recommended.'
    },
    'post-tags': {
      title: 'Tags',
      message: 'Add relevant tags separated by commas (e.g., "career, programming, tips"). Max 5 tags.'
    },
    'event-name': {
      title: 'Event Name',
      message: 'The official name of your event. Include organization name if applicable.'
    },
    'event-date': {
      title: 'Event Date',
      message: 'Select a future date. Events cannot be created for past dates.'
    },
    'event-organizer': {
      title: 'Organizer',
      message: 'The person or organization responsible for this event.'
    },
    'event-registration': {
      title: 'Registration Link',
      message: 'Must be a valid URL (include https://). Leave blank if no registration required.'
    },
    'job-title': {
      title: 'Job Title',
      message: 'Standard job titles work best (e.g., "Senior Frontend Developer")'
    },
    'job-company': {
      title: 'Company Name',
      message: 'The official registered name of your company'
    },
    'job-description': {
      title: 'Job Description',
      message: 'Include responsibilities, requirements, and benefits. Minimum 100 characters.'
    },
    'job-salary': {
      title: 'Salary Range',
      message: 'Format like "$50,000 - $70,000" or "Competitive salary"'
    },
    'job-deadline': {
      title: 'Application Deadline',
      message: 'Must be a future date. Applications will close automatically.'
    },
    'job-location': {
      title: 'Job Location',
      message: 'City and country, or "Remote" if location-independent'
    },
    'media_files': {
      title: 'File Uploads',
      message: 'Allowed formats: JPG, PNG, PDF, MP4, MOV, AVI. Max 10MB per file.'
    }
  };
  
  currentGuide.value = guides[fieldName] || null;
};
// const onMapClick = (event) => {
//   markerPosition.value = event.latLng.toJSON();
//   formData.value.latitude = markerPosition.value.lat;
//   formData.value.longitude = markerPosition.value.lng;
// };

// // Handle marker drag end to update position
// const onMarkerDragEnd = async (event) => {
//   const newPosition = event.latLng.toJSON();
//   markerPosition.value = newPosition;
//   formData.value.latitude = newPosition.lat;
//   formData.value.longitude = newPosition.lng;

//   // Fetch address from coordinates
//   const address = await getAddressFromCoordinates(newPosition.lat, newPosition.lng);
//   formData.value.address = address; // Store the address in the form data
// };

// Function to fetch address from coordinates using Google Maps Geocoding API
const entities2 = ref(props.entities || []);

const getAddressFromCoordinates = async (latitude, longitude) => {
  try {
    const apiKey = 'AIzaSyDdgGammpJ-9nwjqAqWmnUhSBzbdnscs1g'; // Replace with your actual API key
    const response = await axios.get(
      `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${apiKey}`
    );

    if (response.data.results && response.data.results.length > 0) {
      return response.data.results[0].formatted_address; // Return the first result
    }
    return 'Address not found';
  } catch (error) {
    console.error('Error fetching address:', error.response?.data || error.message);
    return 'Error fetching address';
  }
};
const user = usePage().props.auth.user

// Initialize reactive state
const entities = ref(props.entities || []);
const pagination = ref(props.pagination || {});


const loadMoreData = async () => {
  try {
    const nextPage = page.value + 1; 
    const response = await axios.get('/api/feed', { params: { page: nextPage } });

    props.entities.push(...response.data.entities);

    page.value = nextPage;
    hasMore.value = response.data.hasMore;
  } catch (error) {
    console.error('Error loading more data:', error.response?.data || error.message);
  }
};

// Determine the next page based on your pagination strategy
const getNextPage = () => {
  return page.value + 1;
};

const currentUser = props.current_user;

const recommendedUsers = ref(props.recommendedUsers);

const toggleFollow = async (targetUser) => {
  try {
    const response = await axios.post('/follow/toggle', {
      followed_id: targetUser.id,
    });

    targetUser.is_following = response.data.is_following;
  } catch (error) {
    console.error('Follow toggle failed', error);
  }
};
</script>
<!-- 
<script>
export default {
  data() {
    return {
      mapCenter: { lat: 10.3157, lng: 123.8854 },
      markers: [
        { position: { lat: 10.3157, lng: 123.8854 }, draggable: true },
        { position: { lat: 10.3200, lng: 123.8900 }, draggable: false },
        { position: { lat: 10.3100, lng: 123.8800 }, draggable: true },
      ],
    };
  },
  methods: {
    onMarkerClick(marker) {
      alert(`Marker clicked at: ${marker.position.lat}, ${marker.position.lng}`);
    },
  },
};
</script> -->

<style scoped>
/* Tags container */
.tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    align-items: center;
}

/* Individual tag */
.tag-item {
    display: inline-flex;
    align-items: center;
    background-color: #f0f0f0;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 14px;
}

/* Remove button inside the tag */
.tag-remove-btn {
    background: none;
    border: none;
    margin-left: 5px;
    cursor: pointer;
    font-size: 14px;
    color: #888;
}

.tag-remove-btn:hover {
    color: #ff4d4d;
}

/* Input for adding tags */
.tags-input {
    flex-grow: 1;
    min-width: 150px;
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
.content-type-selector {
  margin-bottom: 20px;
}

.button-group {
  display: flex;
}

.content-type-button {
  transition: background-color 0.3s, border-color 0.3s;
  color: var(--text-secondary);
}

.content-type-button.active {
  color: var(--primary);
  font-weight: bolder;
}
.form {
  max-width: 600px;
  margin: 0 auto;
}

.create-post-actions {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
}

h3 {
  margin-top: 20px;
  font-size: 18px;
}

.form-group {
  margin-bottom: 15px;
}

label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}
.com-job{
  display: flex;
  gap: 5px;
}
.textarea-job{
  width: 100%;
}
.user-list {
  display: flex;
  flex-direction: column;
}
.user-item {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}
.profile-pic {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  margin-right: 10px;
}
.user-name {
  flex: 1;
}
.status-indicator {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  position: absolute;
  bottom: 0;
  right: 0;
}
.status-indicator.online {
  background-color: #00e100;
    border-radius: 50%;
    border: 3px solid var(--bg-dark);
}
.status-indicator.offline {
  background-color: transparent;
}
.profile-photo-container{
  position: relative;
  border: solid;
}
</style>