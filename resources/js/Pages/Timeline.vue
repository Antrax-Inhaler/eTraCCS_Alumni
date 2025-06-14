<template>
  
  <div class="timeline">
  <div v-for="entity in entities" :key="`${entity.type}-${entity.id}`" class="card post">
    <!-- Post Header -->
    <div class="post-header">
      <div class="post-user">
        <!-- Profile Picture -->
        <div class="profile-picture-wrapper">
          <template v-if="entity.creator && entity.creator.profile_photo_url">
            <Link :href="`/profile/${entity.creator.encrypted_id}`" class="profile-link">
              <img :src="entity.creator.profile_photo_url" alt="Profile Picture" class="post-avatar" />
            </Link>
          </template>
          <template v-else>
            <Link :href="`/profile/${entity.creator.encrypted_id}`" class="profile-link">
              <div class="placeholder-profile-picture">
                {{ entity.creator.full_name.charAt(0).toUpperCase() }}
              </div>
            </Link>
          </template>
        </div>
        
        <div>
          <div class="post-username">{{ entity.creator.full_name }}</div>
          <div class="post-userhandle">
            <span>{{ formatTimeAgo(entity.created_at) }}</span>&nbsp;
            <span
              :class="getPrivacyIcon(entity.privacy_setting)"
              :title="getPrivacyTitle(entity.privacy_setting)"
              aria-hidden="true"
            ></span>
            <span class="entity-type-badge">{{ entity.type === 'post' ? 'Post' : entity.type === 'event' ? 'Event' : 'Job' }}</span>
          </div>
        </div>
      </div>
      
      <!-- Options Button -->
      <div v-if="isCurrentUserPost(entity)" class="options-button">
        <button @click="toggleOptions(entity)">
          <i class="fas fa-ellipsis-h" style="color: var(--text-secondary); cursor: pointer;"></i>
        </button>
        <ul v-if="showOptions[entity.id]" class="dropdown-menu">
          <li @click="editPost(entity)">Edit Post</li>
          <li @click="changePublicity(entity)">Change Publicity</li>
        </ul>
      </div>
    </div>

    <!-- Post Content -->
    <div class="post-content">
      <h3>{{ getEntityTitle(entity) }}</h3>
      <p>{{ getEntityContent(entity) }}</p>
    </div>

    <!-- Entity Meta Information -->
    <div class="entity-meta">
      <!-- Event-Specific Fields -->
      <div v-if="entity.type === 'event' && entity.date" class="entity-meta-item">
        <i class="fas fa-calendar-day"></i>
        <span>{{ formatDate(entity.date) }}</span>
      </div>
      <div v-if="entity.type === 'event' && entity.organizer_name" class="entity-meta-item">
        <i class="fas fa-user-tie"></i>
        <span>{{ entity.organizer_name }}</span>
      </div>
      <div v-if="entity.type === 'event' && entity.registration_link" class="entity-meta-item">
        <i class="fas fa-link"></i>
        <span><a :href="entity.registration_link" target="_blank">{{ entity.registration_link }}</a></span>
      </div>
      
      <!-- Job Posting-Specific Fields -->
      <div v-if="entity.type === 'job_posting'" class="entity-meta-item">
        <i class="fas fa-building"></i>
        <span>{{ entity.company_name }}</span>
      </div>
      <div v-if="entity.type === 'job_posting' && entity.salary_range" class="entity-meta-item">
        <i class="fas fa-money-bill-wave"></i>
        <span>{{ entity.salary_range }}</span>
      </div>
      <div v-if="entity.type === 'job_posting'" class="entity-meta-item">
        <i class="fas fa-calendar-times"></i>
        <span>{{ entity.application_deadline }}</span>
      </div>
      
      <!-- Post-Specific Fields -->
      <div v-if="entity.type === 'post' && entity.tags" class="entity-meta-item">
        <i class="fas fa-tags"></i>
        <span>{{ entity.tags.join(', ') }}</span>
      </div>
      
      <!-- Common Fields -->
      <div v-if="entity.latitude && entity.longitude" class="entity-meta-item">
        <i class="fas fa-map-marker-alt"></i>
        <span>{{ entity.city }}, {{ entity.country }}</span>
      </div>
      <div class="entity-meta-item">
        <i class="fas fa-star"></i>
        <span>{{ (entity.score || 0).toFixed(2) }}</span>
      </div>
      
      <!-- Status Fields -->
      <div v-if="entity.type === 'event'" class="entity-meta-item">
        <i class="fas fa-hourglass-half"></i>
        <span>{{ getEventStatus(entity.date) }}</span>
      </div>
      <div v-if="entity.type === 'event' && getEventStatus(entity.date) !== 'Past'" class="entity-meta-item">
        <span>{{ getEventCountdown(entity.date) }}</span>
      </div>
      <div v-if="entity.type === 'job_posting'" class="entity-meta-item">
        <i class="fas fa-hourglass-half"></i>
        <span>{{ getJobStatus(entity.application_deadline) }}</span>
      </div>
      <div v-if="entity.type === 'job_posting' && getJobStatus(entity.application_deadline) !== 'Closed'" class="entity-meta-item">
        <span>{{ getJobCountdown(entity.application_deadline) }}</span>
      </div>
    </div>

    <!-- Media Carousel -->
    <div v-if="entity.media_files && entity.media_files.length > 0" class="carousel-container"
      @touchstart="onTouchStart(entity.id, $event)"
      @touchend="onTouchEnd(entity.id, $event)">
      
      <div class="carousel" :style="{ transform: `translateX(-${currentIndexMap[entity.id] * 100}%)` }">
        <div v-for="(media, index) in entity.media_files" :key="media.id" class="carousel-item">
          <div class="post-media" v-if="isImage(media.file_type)">
            <img :src="`/storage/${media.file_path}`" alt="Media" />
          </div>
          <div class="post-media" v-else-if="isVideo(media.file_type)">
            <video-player controls :options="playerOptions" style="max-width: 100% important;" :src="`/storage/${media.file_path}`" :type="media.file_type" >
            </video-player>
          </div>
          <div v-else class="post-media">
            <a :href="`/storage/${media.file_path}`" target="_blank">Download File</a>
          </div>
        </div>
      </div>

      <!-- Navigation Dots -->
      <div class="carousel-navigation">
        <button
          v-for="(media, index) in entity.media_files"
          :key="index"
          :class="['dot', { active: currentIndexMap[entity.id] === index }]"
          @click="setCurrentIndex(entity.id, index)"
        ></button>
      </div>

      <!-- Navigation Arrows -->
      <button
        class="nav-arrow prev"
        @click="prevSlide(entity.id)"
        :disabled="currentIndexMap[entity.id] === 0"
      >
        ❮
      </button>
      <button
        class="nav-arrow next"
        @click="nextSlide(entity.id)"
        :disabled="currentIndexMap[entity.id] === entity.media_files.length - 1"
      >
        ❯
      </button>
    </div>

    <!-- Post Actions -->
    <div class="post-actions-bar">
      <div class="reaction-container2">
      <button class="like-btn">
        <span class="emoji" :class="entity.user_reaction ? '' : 'fas fa-heart'">
          {{ entity.user_reaction ? getReactionEmoji(entity.user_reaction) : '' }}
        </span>
        <span v-if="entity.reaction_counts && getTotalReactions(entity.reaction_counts)">
          {{ getTotalReactions(entity.reaction_counts) }}
        </span>
      </button>
      <div class="reactions">
        <button 
          v-for="type in reactionTypes" 
          :key="type.id"
          class="reaction"
          :class="{ active: entity.user_reaction === type.id }"
          @click="handleReaction(entity, type.id)"
          tabindex="-1"
        >
          {{ type.label }}
        </button>
      </div>
    </div>
      <div class="post-action" @click="focusCommentInput(entity)">
        <i class="far fa-comment"></i>
        <span>{{ getCommentCountText(entity.comment_count) }}</span>
      </div>
      <div class="post-action">
        <i class="fas fa-retweet"></i>
        <span>{{ entity.share_count || 0 }}</span>
      </div>
      <div class="post-action">
        <i class="far fa-share-square"></i>
      </div>
    </div>

    <div class="comments-section">
  <button 
    v-if="entity.has_more_comments"
    @click="viewMoreComments(entity)" 
    class="view-more-btn"
  >
    View More Comments ({{ entity.all_comments.length - entity.visible_comments.length }})
  </button>
  
  <ul v-if="entity.visible_comments && entity.visible_comments.length > 0" class="comments-list">
    <li 
      v-for="comment in entity.visible_comments" 
      :key="comment.id" 
      class="comment-item"
      :class="{ 'has-replies': comment.replies && comment.replies.length > 0 }"
    >
      <div class="comment">
        <template v-if="comment.user.profile_picture_url">
          <img :src="comment.user.profile_picture_url" alt="Profile Picture" class="commenter-profile-picture" />
        </template>
        <template v-else>
          <div class="placeholder-profile-picture">
            {{ comment.user.full_name.charAt(0).toUpperCase() }}
          </div>
        </template>
        
        <div class="comment-column">
          <div class="comment-balloon">
            <div class="comment-header">
              <strong>{{ comment.user.full_name }}</strong>
              <span class="comment-time">2h ago</span>
            </div>
            <p>{{ comment.content }}</p>
          </div>
          <div class="comment-nav">
            <button class="reply-btn" @click="startReply(entity, comment)">
              <i class="fas fa-reply"></i> Reply
            </button>
            <span class="like-count" v-if="comment.likes_count > 0">
              <i class="fas fa-heart"></i> {{ comment.likes_count }}
            </span>
          </div>
          
          <!-- Nested Replies -->
          <ul v-if="comment.replies && comment.replies.length > 0" class="reply-list">
            <li 
              v-for="reply in comment.replies" 
              :key="reply.id" 
              class="reply-item"
            >
              <div class="comment">
                <div class="reply-line"></div>
                <template v-if="reply.user.profile_picture_url">
                  <img :src="reply.user.profile_picture_url" alt="Profile Picture" class="commenter-profile-picture" />
                </template>
                <template v-else>
                  <div class="placeholder-profile-picture">
                    {{ reply.user.full_name.charAt(0).toUpperCase() }}
                  </div>
                </template>
                
                <div class="comment-column">
                  <div class="comment-balloon reply-balloon">
                    <div class="comment-header">
                      <strong>{{ reply.user.full_name }}</strong>
                      <span class="comment-time">1h ago</span>
                    </div>
                    <p>{{ reply.content }}</p>
                  </div>
                  <div class="comment-nav">
                    <button class="reply-btn" @click="startReply(entity, reply)">
                      <i class="fas fa-reply"></i> Reply
                    </button>
                    <span class="like-count" v-if="reply.likes_count > 0">
                      <i class="fas fa-heart"></i> {{ reply.likes_count }}
                    </span>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </li>
  </ul>

  <!-- Comment Input -->
  <div class="comment-input">
    <div class="input-container">
      <textarea
          :id="`comment-input-${entity.id}`"
          v-model="newComment[entity.id]"
          :placeholder="getReplyPlaceholder(entity)"
          rows="1"
          @keyup.enter="addComment(entity)"
      ></textarea>
      <button 
        @click="addComment(entity)"
        :disabled="isPosting[entity.id]"
        class="send-button"
      >
        <i v-if="!isPosting[entity.id]" class="fas fa-paper-plane"></i>
        <span v-else class="loading-indicator">
          <i class="fas fa-circle-notch fa-spin"></i>
        </span>
      </button>
    </div>
    <div v-if="replyingTo[entity.id]" class="replying-to-indicator">
      Replying to {{ getReplyingToName(entity) }}
      <button @click="cancelReply(entity)" class="cancel-reply">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>
</div>


  </div>

  <!-- Loading Indicator -->
  <div v-if="isLoading" class="loading-indicator">
    Loading more content...
  </div>

  <div v-if="!pagination.has_more && entities.length > 0" class="no-data-message">
    <p>No more new content to show.</p>
    <p>We're showing you previously viewed items in random order.</p>
  </div>

  <div v-if="pagination.has_more" class="sentinel" style="height: 1px;"></div>
</div>
</template>
<script setup>
import axios from 'axios';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import { VideoPlayer } from '@videojs-player/vue'
import 'video.js/dist/video-js.css'

const playerOptions = {
  fluid: true,
  responsive: true,
  playbackRates: [0.5, 1, 1.5, 2]
}
// Extend day.js with the relativeTime plugin
dayjs.extend(relativeTime);

// Function to format the date as "time ago"
// const formatTimeAgo = (date) => {
//   if (!date) return 'Unknown time'; // Fallback for missing dates
//   return dayjs(date).fromNow();
// };
const formattedTimes = ref({});

const updateFormattedTimes = () => {
  console.log("props.entities:", props.entities);
  if (props.entities) {
    formattedTimes.value = props.entities.reduce((acc, entity) => {
      acc[entity.id] = formatTimeAgo(entity.created_at);
      return acc;
    }, {});
  } else {
    formattedTimes.value = {}; // Initialize formattedTimes as empty object
  }
};
// Initialize and set up periodic updates
onMounted(() => {
  updateFormattedTimes(); // Initial update
  const intervalId = setInterval(updateFormattedTimes, 60000); // Update every minute
  onUnmounted(() => clearInterval(intervalId)); // Cleanup on component unmount
});
// const formatDate = (date) => {
//   if (!date) return 'Unknown date'; // Fallback for missing dates
//   return dayjs(date).format('MMMM D, YYYY'); // Format: "January 1, 2023"
// };
const props = defineProps({
  initialEntities: {
    type: Array,
    required: true,
    default: () => [],
  },

  initialPagination: {
    type: Object,
    required: true,
    default: () => ({
      current_page: 1,
      last_page: 1,
      total: 0,
      per_page: 5,
      has_more: false,
    }),
  },
});
const emit = defineEmits(['update:entities']);
const viewMoreComments = (entity) => {
  entity.visible_comments = entity.all_comments; // Show all comments
  entity.has_more_comments = false; // Hide the "View More Comments" button
};
const entities = ref(props.initialEntities || []);
const pagination = ref(props.initialPagination || {});
const isLoading = ref(false);

const loadMore = async () => {
  if (isLoading.value || !pagination.value.has_more) return;

  isLoading.value = true;
  try {
    const nextPage = pagination.value.current_page + 1;
    console.log(`Fetching data from: /api/job-postings?page=${nextPage}`);
    const response = await axios.get('/api/job-postings', {
      params: { page: nextPage },
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    if (!response.data || !Array.isArray(response.data.entities)) {
      console.error('Invalid response structure:', response.data);
      return;
    }

    const newEntities = response.data.entities;
    if (newEntities.length > 0) {
      entities.value = [...entities.value, ...newEntities];

      pagination.value = response.data.pagination;
    }
  } catch (error) {
    console.error('Error loading more data:', error.response?.data || error.message);
  } finally {
    isLoading.value = false;
  }
};
const handleScroll = () => {
  const sentinel = document.querySelector('.sentinel');
  if (!sentinel) return;

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting && pagination.value.has_more) {
        loadMore();
      }
    });
  });

  observer.observe(sentinel);

  onUnmounted(() => {
    observer.disconnect();
  });
};
const commentInputRefs = ref({});

const focusCommentInput = (entity) => {
    // Scroll to the comment section
    const commentSection = document.getElementById(`comment-section-${entity.id}`);
    if (commentSection) {
        commentSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Focus the textarea after a small delay to ensure it's visible
    setTimeout(() => {
        const textarea = document.getElementById(`comment-input-${entity.id}`);
        if (textarea) {
            textarea.focus();
        }
    }, 100);
};

// Helper to get comment count text
const getCommentCountText = (count) => {
    if (count === 0) return 'No comments';
    if (count === 1) return '1';
    return `${count} `;
};
onMounted(() => {
  handleScroll();
});

const isImage = (fileType) => {
    return fileType && fileType.startsWith('image/');
};

// Helper function to check if a file is a video
const isVideo = (fileType) => {
    return fileType && fileType.startsWith('video/');
};
console.log(isImage('image/jpeg')); // true
console.log(isVideo('video/mp4'));  // true
console.log(isImage('application/pdf')); // false
const getEntityTitle = (entity) => {
  if (entity.type === 'job_posting') return entity.job_title;
  if (entity.type === 'post') return entity.title;
  if (entity.type === 'event') return entity.event_name;
  return 'Unknown Entity';
};

const getEntityContent = (entity) => {
  if (entity.type === 'job_posting') return entity.description;
  if (entity.type === 'post') return entity.body;
  if (entity.type === 'event') return entity.description;
  return '';
};

const formatLocation = (latitude, longitude) => {
  if (!latitude || !longitude) return 'Location not provided';
  return `Latitude: ${latitude}, Longitude: ${longitude}`;
};
const reactionTypes = [
  { id: 1, label: '🎉' },
  { id: 2, label: '😊' },
  { id: 3, label: '🥲' },
  { id: 4, label: '🏆' },
  { id: 5, label: '😄' },
  { id: 6, label: '🚀' },
];

const getReactionEmoji = (reactionId) => {
  const reaction = reactionTypes.find(r => r.id === reactionId);
  return reaction ? reaction.label : '';
};

const handleReaction = async (entity, reactionType) => {
  try {
    const isRemoving = entity.user_reaction === reactionType;
    
    const response = await axios.post('/api/reactions', {
      content_item_id: entity.id,
      reaction_type: isRemoving ? null : reactionType,
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    if (response.status === 200) {
      // Safely update entities
      if (!props.entities) return;
      
      const updatedEntities = props.entities.map(item => {
        if (item.id === entity.id) {
          const updatedItem = {
            ...item,
            user_reaction: response.data.user_reaction,
          };
          
          // Update reaction counts if they exist
          if (response.data.counts) {
            updatedItem.reaction_counts = response.data.counts;
          } else if (item.reaction_counts) {
            // Fallback to local count update if backend doesn't return counts
            updatedItem.reaction_counts = updateReactionCounts(
              item.reaction_counts,
              response.data.action,
              item.user_reaction,
              response.data.user_reaction
            );
          }
          
          return updatedItem;
        }
        return item;
      });

      emit('update:entities', updatedEntities);
    }
  } catch (error) {
    console.error('Error handling reaction:', error.response?.data || error.message);
  }
};


// Helper function to update reaction counts
const updateReactionCounts = (currentCounts, action, oldReaction, newReaction) => {
  const counts = { ...currentCounts };
  
  switch (action) {
    case 'added':
      counts[newReaction] = (counts[newReaction] || 0) + 1;
      break;
    case 'updated':
      counts[oldReaction] = Math.max(0, (counts[oldReaction] || 0) - 1);
      counts[newReaction] = (counts[newReaction] || 0) + 1;
      break;
    case 'removed':
      counts[oldReaction] = Math.max(0, (counts[oldReaction] || 0) - 1);
      break;
  }
  
  return counts;
};
// Add this to your methods
const getTotalReactions = (counts) => {
  return Object.values(counts).reduce((sum, count) => sum + count, 0);
};
const newComment = ref({});
const replyingTo = ref({});
const isPosting = ref({}); // Track which comment is being replied to

const startReply = (entity, comment) => {
  const fullName = getFullName(comment.user);
  newComment.value[entity.id] = `@${fullName} `;
  replyingTo.value[entity.id] = comment.id;
  // Focus the textarea
  nextTick(() => {
    document.getElementById(`comment-input-${entity.id}`)?.focus();
  });
};

// Cancel reply
const cancelReply = (entity) => {
  newComment.value[entity.id] = '';
  replyingTo.value[entity.id] = null;
};

// Add a comment or reply
const addComment = async (entity) => {
  const content = newComment.value[entity.id]?.trim();
  if (!content) return;

  isPosting.value[entity.id] = true;

  try {
    const response = await axios.post('/api/comments', {
      content_item_id: entity.id,
      content: content,
      parent_id: replyingTo.value[entity.id] || null,
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    if (response.status === 200) {
      const newCommentData = response.data.comment;
      
      // Add to local state
      if (newCommentData.parent_id) {
        const parentComment = entity.visible_comments.find(
          c => c.id === newCommentData.parent_id
        );
        if (parentComment) {
          parentComment.replies = parentComment.replies || [];
          parentComment.replies.push(newCommentData);
        }
      } else {
        entity.visible_comments.push(newCommentData);
      }

      // Reset input
      newComment.value[entity.id] = '';
      replyingTo.value[entity.id] = null;
    }
  } catch (error) {
    console.error('Error adding comment:', error);
    // Optionally show error to user
  } finally {
    isPosting.value[entity.id] = false;
  }
};
const getFullName = (user) => {
  const firstName = user?.first_name || '';
  const middleName = user?.middle_name ? `${user.middle_name} ` : '';
  const lastName = user?.last_name || '';
  return `${firstName} ${middleName}${lastName}`;
};

// Get name of user being replied to
const getReplyingToName = (entity) => {
  if (!replyingTo.value[entity.id]) return '';
  const comment = entity.visible_comments.find(c => c.id === replyingTo.value[entity.id]) ||
                 entity.visible_comments.flatMap(c => c.replies || []).find(r => r.id === replyingTo.value[entity.id]);
  return comment?.user?.full_name || '';
};


// Toggle replies visibility
const toggleReplies = (commentId) => {
  showReplies.value[commentId] = !showReplies.value[commentId];
};
// Get placeholder for comment input
const getReplyPlaceholder = (entity) => {
  return replyingTo.value[entity.id]
    ? 'Write your reply...'
    : 'Write a comment...';
};




const showOptions = ref({});

// Toggle the visibility of the dropdown menu
const toggleOptions = (entity) => {
  showOptions.value[entity.id] = !showOptions.value[entity.id];
};

// Check if the current user owns the post
const isCurrentUserPost = (entity) => {
  const user = JSON.parse(localStorage.getItem('user')); // Assuming user data is stored in localStorage
  return entity.user_id === user?.id;
};

// Handle Edit Post
const editPost = (entity) => {
  alert(`Editing post: ${entity.title}`);
  toggleOptions(entity); // Close the dropdown after selecting an option
};

// Handle Change Publicity
const changePublicity = async (entity) => {
  try {
    const newPrivacy = prompt('Enter new privacy setting (public/private):', entity.privacy_setting);
    if (!newPrivacy || !['public', 'private'].includes(newPrivacy)) {
      alert('Invalid privacy setting.');
      return;
    }

    // Send request to backend to update privacy
    await axios.put(`/api/posts/${entity.id}/privacy`, {
      privacy_setting: newPrivacy,
    }, {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token')}`,
      },
    });

    // Update local state
    entity.privacy_setting = newPrivacy;
    alert(`Privacy updated to: ${newPrivacy}`);
    toggleOptions(entity); // Close the dropdown after selecting an option
  } catch (error) {
    console.error('Error updating privacy:', error.response?.data || error.message);
  }
};
const currentIndexMap = ref({});

// Initialize the index for a specific entity if not already set
const initializeIndex = (entityId) => {
  if (!(entityId in currentIndexMap.value)) {
    currentIndexMap.value[entityId] = 0;
  }
};

// Navigate to the previous slide for a specific entity
const prevSlide = (entityId) => {
  initializeIndex(entityId);

  if (!props.entities || props.entities.length === 0) return;

  if (currentIndexMap.value[entityId] > 0) {
    currentIndexMap.value[entityId]--;
  }
};

// Navigate to the next slide for a specific entity
const nextSlide = (entityId) => {
  initializeIndex(entityId);

  if (!props.entities || props.entities.length === 0) return;

  const entity = props.entities.find((entity) => entity.id === entityId);
  if (!entity || !entity.media_files) return;

  const mediaCount = entity.media_files.length;
  if (currentIndexMap.value[entityId] < mediaCount - 1) {
    currentIndexMap.value[entityId]++;
  }
};

// Set the current slide index for a specific entity
const setCurrentIndex = (entityId, index) => {
  initializeIndex(entityId);
  currentIndexMap.value[entityId] = index;
};
// State to track touch positions
const touchStartX = ref(null);

// Handle touch start event
const onTouchStart = (entityId, event) => {
  touchStartX.value = event.touches[0].clientX;
};

// Handle touch end event
const onTouchEnd = (entityId, event) => {
  if (touchStartX.value === null) return;

  const touchEndX = event.changedTouches[0].clientX;
  const deltaX = touchStartX.value - touchEndX;

  // Swipe threshold (e.g., 50 pixels)
  const swipeThreshold = 50;

  if (deltaX > swipeThreshold) {
    // Swiped left -> Next slide
    nextSlide(entityId);
  } else if (deltaX < -swipeThreshold) {
    // Swiped right -> Previous slide
    prevSlide(entityId);
  }

  // Reset touch position
  touchStartX.value = null;
};

// Function to get the appropriate Font Awesome icon for privacy settings
const getPrivacyIcon = (privacySetting) => {
  switch (privacySetting) {
    case 'public':
      return 'fas fa-globe'; // Globe icon for public
    case 'private':
      return 'fas fa-lock'; // Lock icon for private
    case 'batchmates':
      return 'fas fa-users'; // Users icon for batchmates
    case 'campus':
      return 'fas fa-university'; // University icon for campus
    case 'friends':
      return 'fas fa-user-friends'; // User friends icon for friends
    case 'followers':
      return 'fas fa-user-check'; // User check icon for followers
    default:
      return 'fas fa-question-circle'; // Default question mark icon
  }
};

// Function to get the tooltip title for privacy settings
const getPrivacyTitle = (privacySetting) => {
  switch (privacySetting) {
    case 'public':
      return 'Public: Visible to everyone';
    case 'private':
      return 'Private: Only visible to you';
    case 'batchmates':
      return 'Batchmates: Visible to your batchmates';
    case 'campus':
      return 'Campus: Visible to your campus community';
    case 'friends':
      return 'Friends: Visible to your friends';
    case 'followers':
      return 'Followers: Visible to your followers';
    default:
      return 'Unknown privacy setting';
  }
};

const formatDate = (date) => {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(date).toLocaleDateString(undefined, options);
};

const formatTimeAgo = (date) => {
  const now = new Date();
  const then = new Date(date);
  const diffInSeconds = Math.floor((now - then) / 1000);

  if (diffInSeconds < 60) return `${diffInSeconds} seconds ago`;
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
  return `${Math.floor(diffInSeconds / 86400)} days ago`;
};

const getEventStatus = (eventDate) => {
  const today = new Date();
  const event = new Date(eventDate);

  if (event < today) return 'Past';
  if (event.toDateString() === today.toDateString()) return 'Ongoing';
  return 'Upcoming';
};

const getEventCountdown = (eventDate) => {
  const today = new Date();
  const event = new Date(eventDate);

  if (event < today) return 'Event has ended';

  const diffInSeconds = Math.floor((event - today) / 1000);
  const days = Math.floor(diffInSeconds / 86400);
  const hours = Math.floor((diffInSeconds % 86400) / 3600);
  const minutes = Math.floor((diffInSeconds % 3600) / 60);

  return `${days}d ${hours}h ${minutes}m`;
};

const getJobStatus = (deadline) => {
  const today = new Date();
  const dueDate = new Date(deadline);

  if (dueDate < today) return 'Closed';
  if (dueDate.toDateString() === today.toDateString()) return 'Deadline Today';
  return 'Open';
};

const getJobCountdown = (deadline) => {
  const today = new Date();
  const dueDate = new Date(deadline);

  if (dueDate < today) return 'Applications are closed';

  const diffInSeconds = Math.floor((dueDate - today) / 1000);
  const days = Math.floor(diffInSeconds / 86400);
  const hours = Math.floor((diffInSeconds % 86400) / 3600);
  const minutes = Math.floor((diffInSeconds % 3600) / 60);

  return `${days}d ${hours}h ${minutes}m`;
};
</script>
<style scoped>
.timeline {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.timeline-item {

}

.entity-header h3 {
  margin: 0;
  font-size: 1.2rem;
}

.entity-content p {
  margin: 10px 0 0;
}

.media-files {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.media-image {
  max-width: 100px;
  max-height: 100px;
}

.media-video {
  max-width: 200px;
  max-height: 150px;
}

.loading-indicator {
  text-align: center;
  margin: 20px 0;
}

.no-data-message {
  text-align: center;
  color: #666;
}
.reaction-button.active {
  filter: drop-shadow(0 0 8px rgba(0, 0, 0, 0.562));
}
.comments-section ul {
  list-style-type: none;
  padding-left: 0;
  max-height: 200px;
  overflow-y: auto;
}

.comments-section li {
  margin-bottom: 10px;
}


.comment-item {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 15px;
}


.commenter-profile-picture {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  object-fit: cover;
}

.reply-item {
  margin-left: 20px;
}

.options-button {
  position: relative;
  display: inline-block;
  float: right; /* Align to the right */
}

.options-button button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 20px;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  background-color: white;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  list-style: none;
  margin: 0;
  padding: 0;
  z-index: 1000;
}

.dropdown-menu li {
  padding: 8px 16px;
  cursor: pointer;
}

.dropdown-menu li:hover {
  background-color: #f0f0f0;
}
.creator-info {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.profile-picture {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.creator-info strong {
  font-size: 16px;
  color: #ddd;;
}
.view-more-button {
  background-color: #007bff; /* Example color */
  color: white;
  border: none;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
}

.view-more-button:hover {
  background-color: #0056b3; /* Darker shade on hover */
}
.profile-picture-wrapper,


.profile-picture,
.commenter-profile-picture,
.placeholder-profile-picture {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  object-fit: cover;
}

.placeholder-profile-picture {
  background-color: #007bff; /* Example color */
  color: white;
  font-size: 18px;
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
}

.active-indicator {

  width: 100px;
  height: 100px;
  background-color: #4caf50; /* Green color for active */
  border: 2px solid white; /* Border to make it stand out */
  border-radius: 50%;
}
.carousel-container {
  position: relative;
  max-width: 600px;
  margin: auto;
  overflow: hidden;
}

.carousel {
  display: flex;
  transition: transform 0.5s ease-in-out;
}

.carousel-item {
  min-width: 100%;
  box-sizing: border-box;
}

.post-container {
  width: 100%;
  height: 400px;
  background-color: #65676b;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.post-image {
  max-width: 100%;
  max-height: 100%;
  object-fit: cover;
}

.carousel-navigation {
  text-align: center;
  margin-top: 10px;
}

.dot {
  height: 10px;
  width: 10px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  margin: 0 5px;
  cursor: pointer;
}

.dot.active {
  background-color: #717171;
}

.nav-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  color: white;
  border: none;
  cursor: pointer;
  padding: 10px;
  border-radius: 50%;
  font-size: 18px;
  filter: drop-shadow(0 0 8px rgba(0, 0, 0, 0.562));

}

.nav-arrow.prev {
  left: 10px;
}

.nav-arrow.next {
  right: 10px;
}

.nav-arrow:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
.post-caption i {
  margin-left: 5px; /* Add spacing between text and icon */
  font-size: 18px; /* Adjust icon size */
  color: #333; /* Set icon color */
}

.post-caption i.fa-globe {
  color: #28a745; /* Green for public */
}

.post-caption i.fa-lock {
  color: #dc3545; /* Red for private */
}

.post-caption i.fa-users {
  color: #007bff; /* Blue for batchmates */
}

.post-caption i.fa-university {
  color: #ffc107; /* Yellow for campus */
}

.post-caption i.fa-user-friends {
  color: #17a2b8; /* Teal for friends */
}

.post-caption i.fa-user-check {
  color: #6f42c1; /* Purple for followers */
}

</style>