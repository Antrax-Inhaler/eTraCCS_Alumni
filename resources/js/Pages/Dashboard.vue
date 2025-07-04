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
    <Link :href="`/profile/${user.encrypted_id}`" class="story-link">
      <div class="avatar-container">
        <img :src="user.profile_photo_url" class="story-avatar" alt="Story">
        <!-- Balloon Talk (Auto-sizing) -->
        <div class="balloon-talk" :class="{ wider: user.note?.length > 15 }">
          {{ user.note || "Active now" }}
        </div>
        <!-- Online Status -->
        <span class="status-indicator" :class="{ online: user.is_online }"></span>
      </div>
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
<div v-if="!isProfileComplete" class="profile-completion">
  <div class="completion-header">
    <h2>Complete Your Profile to Unlock Posting</h2>
    <p>Please provide the following information to help the university and unlock full social features</p>
  </div>

  <div class="card-container">
    <!-- Educational Background Card - Only show if education data is missing -->
    <div v-if="!hasEducation" class="completion-card">
      <div class="card-icon">
        <i class="fas fa-graduation-cap"></i>
      </div>
      <div class="card-content">
        <h3>Educational Background</h3>
        <p>Tell us about your education at Mindoro State University</p>
        <button v-if="user.id === auth_user_id" @click="navigateToEducation" class="card-button">
          Add Education
        </button>
      </div>
    </div>

    <!-- Employment Status Card - Only show if employment data is missing -->
 <div v-if="shouldShowEmploymentCard" class="completion-card">
      <div class="card-icon">
        <i class="fas fa-briefcase"></i>
      </div>
      <div class="card-content">
        <h3>Employment History</h3>
        <p>Share your current and past employment information</p>
        <button v-if="user.id === auth_user_id" @click="navigateToEmploymentHistories" class="card-button">
          Add Employment
        </button>
      </div>
    </div>
    <div v-if="shouldShowUnemploymentCard" class="completion-card">
      <div class="card-icon">
        <i class="fas fa-user-slash"></i>
      </div>
      <div class="card-content">
        <h3>Unemployment Status</h3>
        <p v-if="hasPastEmployment">
          It seems you're no longer working at your previous job. Let us know your current status.
        </p>
        <p v-else>
          Let us know if you're currently not employed
        </p>
        <button @click="openUnemploymentModal" class="card-button">
          Add Unemployment Details
        </button>
      </div>
    </div>

 <div v-if="shouldShowJobHuntingCard" class="completion-card">
      <div class="card-icon">
        <i class="fas fa-search"></i>
      </div>
      <div class="card-content">
        <h3>Job Hunting Experience</h3>
        <p v-if="hasEmploymentHistory">
          Share your experience finding new employment after your last job
        </p>
        <p v-else>
          Tell us about your job search journey after graduation
        </p>
        <button @click="openJobHuntingModal" class="card-button">
          Add Experience
        </button>
      </div>
    </div>

    <!-- Competencies Card - Only show if competencies are missing -->
    <!-- <div v-if="!hasCompetencies" class="completion-card">
      <div class="card-icon">
        <i class="fas fa-tools"></i>
      </div>
      <div class="card-content">
        <h3>Key Competencies</h3>
        <p>List the skills you gained from your BSIT program</p>
        <button @click="navigateToProfile" class="card-button">
          Add Skills
        </button>
      </div>
    </div> -->

    <!-- Location Card - Only show if location is missing -->
    <div v-if="!hasLocation" class="completion-card">
      <div class="card-icon">
        <i class="fas fa-map-marker-alt"></i>
      </div>
      <div class="card-content">
        <h3>Current Location</h3>
        <p>Help us connect you with alumni in your area</p>
        <button @click="navigateToProfile" class="card-button">
          Add Location
        </button>
      </div>
    </div>

    <!-- Program Suggestions Card - Only show if suggestions are missing -->
    <div v-if="!hasProgramSuggestions" class="completion-card">
      <div class="card-icon">
        <i class="fas fa-lightbulb"></i>
      </div>
      <div class="card-content">
        <h3>BSIT Program Suggestions</h3>
        <p>Help improve the BSIT program with your feedback</p>
        <button @click="openProgramSuggestionModal" class="card-button">
          Add Suggestions
        </button>
      </div>
    </div>
  </div>
</div>
      <div v-else  class="card post-input" @click="openModal">
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
      <VoerroTagsInput
        id="post-tags"
        v-model="formData.tags"
        placeholder="Add tags (press enter to add)"
        class="form-control"
        
      />
    </div>
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
      <label for="post-tags">Tags</label>
      <VoerroTagsInput
        id="post-tags"
        v-model="formData.tags"
        placeholder="Add tags (press enter to add)"
        class="form-control"
      />
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
              <!-- <div class="form-group">
                <label for="job-salary">Salary Range</label>
                <input 
                  id="job-salary" 
                  class="form-control" 
                  v-model="formData.salary_range" 
                  placeholder="e.g., $50,000 - $70,000"
                >
              </div> -->
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
      <label for="post-tags">Tags</label>
      <VoerroTagsInput
        id="post-tags"
        v-model="formData.tags"
        placeholder="Add tags (press enter to add)"
        class="form-control"
      />
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
            <button class="btn btn-primary" type="submit" :disabled="isSubmitting">
      <span v-if="!isSubmitting">Publish</span>
      <span v-else>
        <i class="fas fa-spinner fa-spin"></i> Publishing...
      </span>
    </button>          </div>
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
<!-- Job Hunting Experience Modal -->
<div class="modal-overlay" :class="{ active: showJobHuntingModal }">
  <div class="modal-container">
    <div class="modal-header">
      <h3 class="modal-title">Job Hunting Experience</h3>
      <button class="modal-close" @click="closeJobHuntingModal">&times;</button>
    </div>
    
    <div class="modal-body">
      <form @submit.prevent="submitJobHuntingExperience">
        <div class="content-form">
          <div class="form-group">
            <label>How long did it take to find your first job?</label>
            <select 
              v-model="jobHuntingData.time_to_first_job" 
              class="form-control"
              required
            >
              <option value="">Select duration</option>
              <option value="less_than_1_month">Less than 1 month</option>
              <option value="1_to_3_months">1-3 months</option>
              <option value="4_to_6_months">4-6 months</option>
              <option value="7_to_12_months">7-12 months</option>
              <option value="more_than_1_year">More than 1 year</option>
              <option value="never_employed">Never employed</option>
            </select>
             <span v-if="jobHuntingErrors.time_to_first_job" class="error-message">
            {{ jobHuntingErrors.time_to_first_job }}
          </span>
          </div>
          
          <div class="form-group">
            <label>What was your starting salary?</label>
            <select 
              v-model="jobHuntingData.starting_salary" 
              class="form-control"
            >
              <option value="">Select salary range</option>
              <option value="below_10k">Below ₱10,000</option>
              <option value="10k_15k">₱10,000 - ₱15,000</option>
              <option value="15k_20k">₱15,000 - ₱20,000</option>
              <option value="20k_30k">₱20,000 - ₱30,000</option>
              <option value="above_30k">Above ₱30,000</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>How did you find your job?</label>
            <select 
              v-model="jobHuntingData.job_source" 
              class="form-control"
            >
              <option value="">Select source</option>
              <option value="online_portals">Online job portals</option>
              <option value="walk_in">Walk-in application</option>
              <option value="referral">Referral</option>
              <option value="university_fair">University job fair</option>
              <option value="social_media">Social media</option>
              <option value="government">Government employment service</option>
              <option value="other">Other</option>
            </select>
            <input 
              v-if="jobHuntingData.job_source === 'other'"
              v-model="jobHuntingData.other_source"
              placeholder="Please specify"
              class="form-control mt-2"
            />
          </div>
          
          <div class="form-group">
            <label>What challenges did you face?</label>
            <div class="checkbox-container">
              <div 
                v-for="difficulty in [
                  'lack_of_opportunities',
                  'high_competition',
                  'qualification_mismatch',
                  'lack_of_experience',
                  'personal_issues'
                ]" 
                :key="difficulty" 
                class="checkbox-item"
              >
                <input
                  type="checkbox"
                  :id="`difficulty-${difficulty}`"
                  v-model="jobHuntingData.difficulties"
                  :value="difficulty"
                  class="form-checkbox"
                />
                <label :for="`difficulty-${difficulty}`">
                  {{ difficulty.replace(/_/g, ' ') }}
                </label>
              </div>
              <div class="checkbox-item">
                <input
                  type="checkbox"
                  id="difficulty-other"
                  v-model="jobHuntingData.difficulties"
                  value="other"
                  class="form-checkbox"
                />
                <label for="difficulty-other">Other</label>
              </div>
              <input 
                v-if="jobHuntingData.difficulties.includes('other')"
                v-model="jobHuntingData.other_difficulty"
                placeholder="Please specify"
                class="form-control mt-2"
              />
            </div>
          </div>
          
          <div class="modal-footer">
            <button type="button" @click="closeJobHuntingModal" class="btn btn-outline">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <span v-if="!isSubmitting">Save Experience</span>
              <span v-else>
                <i class="fas fa-spinner fa-spin"></i> Saving...
              </span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal-overlay" :class="{ active: showUnemploymentModal }">
    <div class="modal-container">
        <div class="modal-header">
            <h3 class="modal-title">Unemployment Details</h3>
            <button class="modal-close" @click="closeUnemploymentModal">&times;</button>
        </div>
        <div class="modal-body">
            <form @submit.prevent="submitUnemploymentDetails">
                <div class="form-group">
                    <label>Reason for Unemployment</label>
                    <div class="checkbox-container">
                        <div v-for="reason in unemploymentReasons" :key="reason.value" class="checkbox-item">
                            <input
                                type="checkbox"
                                :id="`reason-${reason.value}`"
                                v-model="unemploymentData.reasons"
                                :value="reason.value"
                                class="form-checkbox"
                            />
                            <label :for="`reason-${reason.value}`">{{ reason.label }}</label>
                        </div>
                    </div>
                    <input
                        v-if="unemploymentData.reasons.includes('other')"
                        v-model="unemploymentData.other_reason"
                        placeholder="Please specify other reason"
                        class="form-control mt-2"
                    />
                </div>

                <div class="form-group">
                    <label>
                        <input
                            type="checkbox"
                            v-model="unemploymentData.has_awards"
                            class="form-checkbox"
                        />
                        Received any awards while unemployed?
                    </label>
                    <textarea
                        v-if="unemploymentData.has_awards"
                        v-model="unemploymentData.awards_details"
                        placeholder="Describe your awards..."
                        class="form-control mt-2"
                        rows="3"
                    ></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" @click="closeUnemploymentModal" class="btn btn-outline">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                        <span v-if="!isSubmitting">Save Details</span>
                        <span v-else><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- BSIT Program Suggestions Modal -->
<div class="modal-overlay" :class="{ active: showProgramSuggestionModal }">
  <div class="modal-container">
    <div class="modal-header">
      <h3 class="modal-title">BSIT Program Suggestions</h3>
      <button class="modal-close" @click="closeProgramSuggestionModal">&times;</button>
    </div>
    
    <div class="modal-body">
      <form @submit.prevent="submitProgramSuggestion">
        <div class="content-form">
          <div class="form-group">
            <label>Suggestion Type</label>
            <select 
              v-model="programSuggestionData.suggestion_type" 
              class="form-control"
              required
            >
              <option value="">Select suggestion type</option>
              <option 
                v-for="type in suggestionTypes" 
                :key="type.value" 
                :value="type.value"
              >
                {{ type.label }}
              </option>
            </select>
             <span v-if="programSuggestionErrors.suggestion_type" class="error-message">
            {{ programSuggestionErrors.suggestion_type }}
          </span>
          </div>
          
          <div class="form-group">
            <label>Detailed Suggestion</label>
            <textarea 
              v-model="programSuggestionData.description"
              placeholder="Please provide detailed suggestions to improve the BSIT program..."
              class="form-control"
              required
              rows="4"
            ></textarea>
          </div>
          
          <div class="modal-footer">
            <button type="button" @click="closeProgramSuggestionModal" class="btn btn-outline">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <span v-if="!isSubmitting">Submit Suggestion</span>
              <span v-else>
                <i class="fas fa-spinner fa-spin"></i> Submitting...
              </span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
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
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import Timeline from './Timeline.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage, Link, router } from '@inertiajs/vue3'
import VoerroTagsInput from '@voerro/vue-tagsinput'
import '@voerro/vue-tagsinput/dist/style.css'
const page = usePage();
// Reactive state
const isSubmitting = ref(false);
const showModal = ref(false);
const showJobHuntingModal = ref(false);
const showProgramSuggestionModal = ref(false);
const activeTab = ref('completion');
const currentGuide = ref(null);
const validationErrors = ref({});

// Form data refs
const selectedType = ref('post');
const selectedFiles = ref({ event: [], job: [] });
const mapCenter = ref({ lat: 14.5995, lng: 120.9842 });
const markerPosition = ref(null);
const emit = defineEmits(['content-created']);
// const jobHuntingData = ref({
//     time_to_first_job: '',
//     difficulties: [],
//     other_difficulty: '',
//     job_source: '',
//     other_source: '',
//     starting_salary: ''
// });
// const programSuggestionData = ref({
//     suggestion_type: '',
//     description: ''
// });

// Constants
const contentTypes = [
  { label: 'Post', value: 'post', icon: 'fas fa-pencil-alt' },
  { label: 'Event', value: 'event', icon: 'fas fa-calendar-alt' },
  { label: 'Job', value: 'job_posting', icon: 'fas fa-briefcase' }
];

const suggestionTypes = [
    { value: 'industry_aligned_subjects', label: 'More industry-aligned subjects' },
    { value: 'hands_on_projects', label: 'More hands-on projects' },
    { value: 'updated_tools', label: 'Updated tools/technologies' },
    { value: 'stronger_internship', label: 'Stronger internship program' },
    { value: 'cert_prep', label: 'Certification preparation' },
    { value: 'career_services', label: 'Better career services' },
    { value: 'other', label: 'Other suggestions' }
];

// Props
const props = defineProps({
  current_user: Object,
  auth_user_id: Number,
  entities: Array,
  pagination: Object,
  userList: Array,
  user: Object,
  hasEducation: Boolean,
  hasEmployment: Boolean,
  hasJobHunting: Boolean,
  hasCompetencies: Boolean,
  hasLocation: Boolean,
  hasProgramSuggestions: Boolean,
});

// Form data
// Form data
const formData = ref({
  // Common fields
  privacy_setting: 'public',
  tags: [],
  // Post fields
  title: '',
  body: '',
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
  application_deadline: '',
  is_remote: false,
  location: ''
});
const submitContent = async () => {
  isSubmitting.value = true;
  const data = new FormData();
  validationErrors.value = {};

  try {
    data.append('type', selectedType.value);
    data.append('privacy_setting', formData.value.privacy_setting);

    // ✅ Extract tags safely and robustly
let tagsArray = [];

if (Array.isArray(formData.value.tags) && formData.value.tags.length > 0) {
  tagsArray = formData.value.tags.map(tag => tag.value).filter(Boolean);
}

console.log('Extracted Tags:', tagsArray);
data.append('tags', JSON.stringify(tagsArray));

    if (selectedType.value === 'post') {
      data.append('title', formData.value.title);
      data.append('body', formData.value.body);

    } else if (selectedType.value === 'event' || selectedType.value === 'job_posting') {
      // 📍 If coordinates exist, fetch city/country
      if (formData.value.latitude && formData.value.longitude) {
        try {
          const apiKey = "2f745fa85d563da5adb87b6cd4b81caf";
          const response = await axios.get(
            `https://api.openweathermap.org/data/2.5/weather?lat=${formData.value.latitude}&lon=${formData.value.longitude}&appid=${apiKey}`,
            { timeout: 5000 }
          );

          if (response.data.name && response.data.sys?.country) {
            data.append('city', response.data.name);
            data.append('country', response.data.sys.country);
            formData.value.city = response.data.name;
            formData.value.country = response.data.sys.country;
          }
        } catch (error) {
          console.error('Failed to fetch location data:', error);
        }
      }

      if (selectedType.value === 'event') {
        data.append('event_name', formData.value.event_name);
        data.append('date', formData.value.date);
        data.append('time', formData.value.time);
        data.append('organizer_name', formData.value.organizer_name);
        data.append('description', formData.value.description);
        data.append('registration_link', formData.value.registration_link);
        data.append('latitude', formData.value.latitude || '');
        data.append('longitude', formData.value.longitude || '');

        selectedFiles.value.event.forEach((file, index) => {
          if (file instanceof File) {
            data.append(`media_files[${index}]`, file);
          }
        });

      } else if (selectedType.value === 'job_posting') {
        data.append('job_title', formData.value.job_title);
        data.append('company_name', formData.value.company_name);
        data.append('job_type', formData.value.job_type);
        data.append('description', formData.value.description);
        data.append('application_deadline', formData.value.application_deadline);
        data.append('is_remote', formData.value.is_remote ? '1' : '0');
        data.append('location', formData.value.location || '');
        data.append('latitude', formData.value.latitude || '');
        data.append('longitude', formData.value.longitude || '');

        selectedFiles.value.job.forEach((file, index) => {
          if (file instanceof File) {
            data.append(`media_files[${index}]`, file);
          }
        });
      }
    }

    // 📤 Send the form data
    const response = await axios.post('/job-postings', data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      validateStatus: status => status < 500,
      timeout: 10000
    });

    if (response.status === 200 || response.status === 201) {
      console.log('Raw formData.tags:', formData.value.tags);
      resetForm();
      closeModal();
      emit('content-created');
      return;
    }

  } catch (error) {
    console.error('Submission Error:', error);

    if (error.response?.status === 422) {
      validationErrors.value = error.response.data.errors || {};
    }

  } finally {
    isSubmitting.value = false;
  }
};


const resetForm = () => {
  formData.value = {
    privacy_setting: 'public',
    title: '',
    body: '',
    tags: [],
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
    // salary_range: '',
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
// Computed properties
// const isProfileComplete = computed(() => {
//     return props.hasEducation && props.hasEmployment && 
//            props.hasJobHunting && props.hasCompetencies && 
//            props.hasLocation && props.hasProgramSuggestions;
// });

const user = usePage().props.auth.user;
const entities = ref(props.entities || []);
const pagination = ref(props.pagination || {});

// Methods
const openModal = () => {
  if (!isProfileComplete.value) return;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const selectContentType = (type) => {
  selectedType.value = type;
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



// Profile completion methods
const navigateToProfile = () => {
    router.visit(route('profile.show', props.user.encrypted_id));
};

const navigateToEducation = () => {
    router.visit(route('profile.educationalBackground.index', { 
        id: page.props.encrypted_id 
    }));
};

const navigateToEmploymentHistories = () => {
    router.visit(route('profile.employmentHistory.index', { 
        id: page.props.encrypted_id 
    }));
};

const openJobHuntingModal = () => {
    showJobHuntingModal.value = true;
};

const closeJobHuntingModal = () => {
    showJobHuntingModal.value = false;
};

const openProgramSuggestionModal = () => {
    showProgramSuggestionModal.value = true;
};

const closeProgramSuggestionModal = () => {
    showProgramSuggestionModal.value = false;
};

const jobHuntingData = ref({
    time_to_first_job: '',
    difficulties: [],
    other_difficulty: '',
    job_source: '',
    other_source: '',
    starting_salary: ''
});

// Program Suggestion Data
const programSuggestionData = ref({
    suggestion_type: '',
    description: ''
});
watch(() => page.props.flash, (newFlash) => {
    if (newFlash.success) {
        showNotification({
            type: 'success',
            message: newFlash.success
        });
    }
    if (page.props.errors.error) {
        showNotification({
            type: 'error',
            message: page.props.errors.error
        });
    }
}, { deep: true });
// Loading states
const isSubmittingJobHunting = ref(false);
const isSubmittingProgramSuggestion = ref(false);

// Error handling
const jobHuntingErrors = ref({});
const programSuggestionErrors = ref({});

// Submit Job Hunting Experience
const submitJobHuntingExperience = async () => {
    isSubmittingJobHunting.value = true;
    jobHuntingErrors.value = {};
    
    try {
        await router.post(route('profile.jobHunting.store'), {
            ...jobHuntingData.value,
            difficulties: jobHuntingData.value.difficulties.join(','),
            other_difficulty: jobHuntingData.value.difficulties.includes('other') 
                ? jobHuntingData.value.other_difficulty 
                : null
        }, {
            preserveScroll: true,
            onSuccess: () => {
                closeJobHuntingModal();
                resetJobHuntingForm();
                // Success notification will be handled by the page component
                // from the flashed session data
            },
            onError: (errors) => {
                jobHuntingErrors.value = errors;
            }
        });
    } catch (error) {
        console.error('Request failed:', error);
    } finally {
        isSubmittingJobHunting.value = false;
    }
};

const submitProgramSuggestion = async () => {
    isSubmittingProgramSuggestion.value = true;
    programSuggestionErrors.value = {};
    
    try {
        await router.post(route('profile.programSuggestions.store'), {
            ...programSuggestionData.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                closeProgramSuggestionModal();
                resetProgramSuggestionForm();
                // Success notification will be handled by the page component
            },
            onError: (errors) => {
                programSuggestionErrors.value = errors;
            }
        });
    } catch (error) {
        console.error('Request failed:', error);
    } finally {
        isSubmittingProgramSuggestion.value = false;
    }
};
// In your script setup
const showUnemploymentModal = ref(false);
const isSubmittingUnemployment = ref(false);
const unemploymentErrors = ref({});

const unemploymentReasons = [
    { value: 'seeking', label: 'Currently seeking employment' },
    { value: 'studying', label: 'Pursuing further studies' },
    { value: 'family_responsibilities', label: 'Family responsibilities' },
    { value: 'health_issues', label: 'Health issues' },
    { value: 'not_interested', label: 'Not interested in employment' },
    { value: 'other', label: 'Other' }
];

const unemploymentData = ref({
    reasons: [],
    other_reason: '',
    has_awards: false,
    awards_details: ''
});
const profileStatus = computed(() => {
    // Determine the user's employment status
    if (props.isCurrentlyEmployed) {
        return 'employed';
    } else if (props.hasUnemploymentDetails) {
        return 'unemployed-with-details';
    } else if (props.hasEmploymentHistory) {
        return 'unemployed-with-history';
    } else {
        return 'unemployed-no-history';
    }
});

const shouldShowEmploymentCard = computed(() => {
    // Only show if no employment history exists at all
    return !props.hasEmploymentHistory;
});
const shouldShowUnemploymentCard = computed(() => {
    // Show if:
    // 1. Not currently employed AND
    // 2. Either:
    //    a. Has past employment (end date exists) OR
    //    b. No employment history at all
    // 3. And hasn't filled unemployment details
    return !props.isCurrentlyEmployed && 
           (props.hasPastEmployment || !props.hasEmploymentHistory) &&
           !props.hasUnemploymentDetails;
});

const shouldShowJobHuntingCard = computed(() => {
    // Show if:
    // 1. Not currently employed AND
    // 2. Either has past employment or unemployment details AND
    // 3. Doesn't have job hunting info yet
    return !props.isCurrentlyEmployed && 
           (props.hasPastEmployment || props.hasUnemploymentDetails) && 
           !props.hasJobHunting;
});

const isProfileComplete = computed(() => {
    if (props.isCurrentlyEmployed) {
        return props.hasEducation && 
               props.hasJobHunting && 
               props.hasLocation && 
               props.hasProgramSuggestions;
    }
    else if (props.hasUnemploymentDetails) {
        return props.hasEducation && 
               props.hasLocation && 
               props.hasProgramSuggestions;
    }
    else if (props.hasPastEmployment) {
        return props.hasEducation && 
               props.hasJobHunting && 
               props.hasLocation && 
               props.hasProgramSuggestions;
    }
    else {
        return props.hasEducation && 
               props.hasLocation && 
               props.hasProgramSuggestions;
    }
});

const openUnemploymentModal = () => {
    showUnemploymentModal.value = true;
};

const closeUnemploymentModal = () => {
    showUnemploymentModal.value = false;
};

const submitUnemploymentDetails = async () => {
    isSubmitting.value = true;
    try {
        // Send reasons as array instead of comma-separated string
        await router.post(route('profile.unemployment.store'), {
            unemployment_reasons: unemploymentData.value.reasons, // already an array
            other_unemployment_reason: unemploymentData.value.reasons.includes('other') 
                ? unemploymentData.value.other_reason 
                : null,
            has_awards: unemploymentData.value.has_awards,
            awards_details: unemploymentData.value.awards_details
        }, {
            onSuccess: () => {
                closeUnemploymentModal();
                // Update local state
                props.isUnemployed = true;
                props.shouldShowJobHunting = false;
                showNotification({
                    type: 'success',
                    message: 'Unemployment details saved successfully'
                });
            },
            onError: (errors) => {
                unemploymentErrors.value = errors;
            }
        });
    } catch (error) {
        console.error('Error:', error);
        showNotification({
            type: 'error',
            message: 'Failed to save unemployment details'
        });
    } finally {
        isSubmitting.value = false;
    }
};
// Reset form data
const resetJobHuntingForm = () => {
    jobHuntingData.value = {
        time_to_first_job: '',
        difficulties: [],
        other_difficulty: '',
        job_source: '',
        other_source: '',
        starting_salary: ''
    };
};

const resetProgramSuggestionForm = () => {
    programSuggestionData.value = {
        suggestion_type: '',
        description: ''
    };
};

const showNotification = ({ type, message, duration }) => {
  console.log(`[${type}] ${message}`);
  alert(`[${type.toUpperCase()}] ${message}`);
};

const showGuide = (fieldName) => {
  const guides = {
    'post-title': {
      title: 'Post Title',
      message: 'Keep your title concise but descriptive (5-15 words). Avoid special characters.'
    },
    // ... other guide definitions
  };
  
  currentGuide.value = guides[fieldName] || null;
};

const getAddressFromCoordinates = async (latitude, longitude) => {
  try {
    const apiKey = 'YOUR_GOOGLE_API_KEY';
    const response = await axios.get(
      `https://maps.googleapis.com/maps/api/geocode/json?latlng=${latitude},${longitude}&key=${apiKey}`
    );

    if (response.data.results && response.data.results.length > 0) {
      return response.data.results[0].formatted_address;
    }
    return 'Address not found';
  } catch (error) {
    console.error('Error fetching address:', error.response?.data || error.message);
    return 'Error fetching address';
  }
};

const loadMoreData = async () => {
  try {
    const nextPage = pagination.value.current_page + 1; 
    const response = await axios.get('/api/feed', { params: { page: nextPage } });

    entities.value.push(...response.data.entities);
    pagination.value.current_page = nextPage;
    pagination.value.has_more = response.data.hasMore;
  } catch (error) {
    console.error('Error loading more data:', error.response?.data || error.message);
  }
};

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
.fa-spinner {
  margin-right: 8px;
}

/* Disabled state for buttons */
.btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
.completion-card.completed {
  border-left: 4px solid #4CAF50;
}

.completion-card.completed .card-button {
  background-color: #4CAF50;
}
/* Add this to your CSS */
.vue-input-tag-wrapper {
  border: 1px solid #ddd;
  border-radius: 4px;
  padding: 4px 8px;
  min-height: 38px;
}

.vue-input-tag-wrapper .input-tag {
  background-color: #e0f2fe;
  border: 1px solid #bae6fd;
  border-radius: 2px;
  padding: 2px 6px;
  margin-right: 4px;
  margin-bottom: 4px;
  display: inline-flex;
  align-items: center;
}

.vue-input-tag-wrapper .input-tag .remove {
  color: #7dd3fc;
  margin-left: 4px;
  cursor: pointer;
}

.vue-input-tag-wrapper .input-tag .remove:hover {
  color: #0ea5e9;
}

.vue-input-tag-wrapper .new-tag {
  border: none;
  outline: none;
  padding: 4px;
  min-width: 80px;
}
</style>