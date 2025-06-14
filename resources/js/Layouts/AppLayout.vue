<script setup>
import { ref, onMounted, computed} from 'vue';
import { Head, Link, router, usePage  } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import FollowSuggestions from '@/Components/FollowSuggestions.vue';
import 'https://cdn.lordicon.com/lordicon.js';
import ToastNotification from '@/Components/ToastNotification.vue';
import ChatBox from '@/Components/ChatBox.vue';
import { useChatStore } from '@/Stores/chatStore';

const chatStore = useChatStore();

const user = usePage().props.auth.user;
const showFeedbackModal = ref(false);
const feedbackSubmitted = ref(false);

// TAM metrics
const easeOfUse = ref(0);
const usefulness = ref(0);
const satisfaction = ref(0);
const intentionToUse = ref(0);
const comments = ref('');

// Check if we should show feedback modal
const checkFeedbackEligibility = () => {
    // Don't show if user opted out
    if (user.opt_out_feedback) return false;
    
    // Check if user never submitted or last submission was over 30 days ago
    const lastFeedbackDate = user.last_feedback_at ? new Date(user.last_feedback_at) : null;
    const thirtyDaysInMs = 30 * 24 * 60 * 60 * 1000;
    
    if (!lastFeedbackDate || (new Date() - lastFeedbackDate) > thirtyDaysInMs) {
        return true;
    }
    
    return false;
};

// Show feedback modal with probability and after delay
const maybeShowFeedback = () => {
    if (!checkFeedbackEligibility()) return;
    
    // Show after 5 minutes with 30% probability
    setTimeout(() => {
        if (Math.random() < 0.3) {
            showFeedbackModal.value = true;
        }
    }, 1000); // 5 minutes
};

// Submit feedback to server
const submitFeedback = async () => {
    try {
        await router.post(route('feedback.store'), {
            ease_of_use_rating: easeOfUse.value,
            usefulness_rating: usefulness.value,
            satisfaction_rating: satisfaction.value,
            intention_to_use_rating: intentionToUse.value,
            comments: comments.value
        });
        
        feedbackSubmitted.value = true;
        
        // Update local state and close after delay
        setTimeout(() => {
            showFeedbackModal.value = false;
            // Optionally refresh user data to get updated last_feedback_at
            router.reload({ only: ['auth.user'] });
        }, 3000);
        
    } catch (error) {
        console.error('Error submitting feedback:', error);
    }
};

// Opt out of future feedback requests
const optOutOfFeedback = async () => {
    try {
        await router.patch(route('profile.update'), {
            opt_out_feedback: true
        });
        showFeedbackModal.value = false;
        router.reload({ only: ['auth.user'] });
    } catch (error) {
        console.error('Error opting out of feedback:', error);
    }
};

const logout = () => {
    router.post(route('logout'));
};
const themeColors = computed(() => usePage().props.themeColors || {
    primary: '#ff8c00',
    primary_light: 'rgba(255, 140, 0, 0.1)',
    text_primary: '#ffffff',
    text_secondary: '#cccccc',
    bg_dark: '#1a1a1a',
    bg_darker: '#121212',
    card_bg: 'rgba(40, 40, 40, 0.7)',
    card_border: 'rgba(255, 255, 255, 0.1)'
});

const showTransition = ref(false);
const animationComplete = ref(false);

// Get user data

// Check if we should show the transition
const shouldShowTransition = computed(() => {
    if (!user?.last_seen_at) return true; // First visit
    
    const lastSeen = new Date(user.last_seen_at);
    const thirtyMinutesAgo = new Date(Date.now() - 30 * 60 * 1000);
    
    return lastSeen < thirtyMinutesAgo; // Not seen in last 30 mins
});

// Run the animation if needed
onMounted(() => {
    if (shouldShowTransition.value) {
        showTransition.value = true;
        setTimeout(() => {
            animationComplete.value = true;
        }, 2000); // Animation duration
    }
});
</script>

<template>
     <div :style="{
        '--primary': themeColors.primary,
        '--primary-light': themeColors.primary_light,
        '--text-primary': themeColors.text_primary,
        '--text-secondary': themeColors.text_secondary,
        '--bg-dark': themeColors.bg_dark,
        '--bg-darker': themeColors.bg_darker,
        '--card-bg': themeColors.card_bg,
        '--card-border': themeColors.card_border
    }"></div>
                <transition name="fade">
        <div v-if="showTransition && !animationComplete" class="transition-overlay">
            <div class="animation-container">
                <ApplicationMark class="animated-logo" />
                <div class="loading-bar">
                    <div class="loading-progress"></div>
                </div>
            </div>
        </div>
    </transition>
     <div class="body" :class="{ 'blur-content': showTransition && !animationComplete }">
<!-- 
        <Head :title="title" />

        <Banner /> -->
<MessageFloater />

        <ToastNotification ref="toast" />
        <button class="mobile-menu-toggle">
    <i class="fas fa-bars"></i>
  </button>
  
  <!-- Mobile Search -->
  <div class="mobile-search">
    <i class="fas fa-search mobile-search-icon"></i>
    <input type="text" placeholder="Search">
    <button class="close-search">
      <i class="fas fa-times"></i>
    </button>
  </div>
  
        <div class="container">
<ChatBox
            v-for="(chatUser, index) in chatStore.openChats"
            :key="chatUser.id"
            :user="chatUser"
            :index="index"
        />

    <div class="left-nav">
      <Link :href="`/job-postings`">
        <div class="logo">eTraCCS</div>
      </Link>
      
      <div class="nav-menu">
        <Link :href="route('job-postings.index')" class="nav-link">
      <div :class="['nav-item', { 'active': $page.url.startsWith('/job-postings') }]">
        <i class="fas fa-home"></i>
        <span>Home</span>
      </div>
    </Link>
    <Link :href="route('map.index')" class="nav-link">
      <div :class="['nav-item', { 'active': $page.url.startsWith('/map') }]">
        <i class="fas fa-map-marker-alt"></i>
        <span>Map</span>
      </div>
    </Link>

        <div class="nav-item">
          <i class="fas fa-bell"></i>
          <span>Notifications</span>
        </div>
        <Link :href="route('chat.index')" class="nav-link">
      <div :class="['nav-item', { 'active': $page.url.startsWith('/chat') }]">
        <i class="fas fa-envelope"></i>
        <span>Messages</span>
        <!-- <span v-if="unreadMessages > 0" class="notification-badge">{{ unreadMessages }}</span> -->
      </div>
    </Link>

        <!-- <div class="nav-item">
          <i class="fas fa-bookmark"></i>
          <span>Bookmarks</span>
        </div> -->
        <Link :href="route('profile.show', $page.props.auth.user.encrypted_id)" class="nav-link">
      <div :class="['nav-item', { 'active': $page.url.startsWith('/profile') }]">
        <i class="fas fa-user"></i>
        <span>Profile</span>
      </div>
    </Link>
        
        <!-- <div class="nav-item">
          <i class="fas fa-ellipsis-h"></i>
          <span>More</span>
        </div> -->
      </div>
      
      <!-- <button class="post-button">Post</button> -->
      <form method="POST" @submit.prevent="logout" class="post-button">
                                <ResponsiveNavLink as="button"  >
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

      <Link :href="`/profile/${$page.props.auth.user.encrypted_id}`">
          <div class="user-profile">
        <img :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" class="user-avatar" >  
        <div>
          <div class="user-name">{{ $page.props.auth.user.full_name }}</div>
          <div class="user-handle">@{{ $page.props.auth.user.name ?? '' }}</div>
        </div>
      </div>
    </Link>
    </div>
            <!-- Page Heading -->
            <!-- <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header> -->

            <!-- Page Content -->
             <div class="main-content">
                <main>
                <slot />
            </main>
             </div>
             <div class="right-sidebar">
      <div class="search-bar">
        <i class="fas fa-search search-icon"></i>
        <input type="text" placeholder="Search">
      </div>
      
      <div class="card trending-card">
        <div class="section-title">Trending Today</div>
        
        <div class="trending-item">
          <div class="trending-category">Technology · Trending</div>
          <div class="trending-tag">#AppleVisionPro</div>
          <div class="trending-count">24.5K posts</div>
        </div>
        
        <div class="trending-item">
          <div class="trending-category">Entertainment · Trending</div>
          <div class="trending-tag">#Oppenheimer</div>
          <div class="trending-count">18.2K posts</div>
        </div>
        
        <div class="trending-item">
          <div class="trending-category">Sports · Trending</div>
          <div class="trending-tag">#WorldCup2023</div>
          <div class="trending-count">15.7K posts</div>
        </div>
        
        <div class="trending-item">
          <div class="trending-category">Politics · Trending</div>
          <div class="trending-tag">#ClimateChange</div>
          <div class="trending-count">12.3K posts</div>
        </div>
        
        <div class="trending-item">
          <div class="trending-category">Business · Trending</div>
          <div class="trending-tag">#RemoteWork</div>
          <div class="trending-count">9.8K posts</div>
        </div>
      </div>
      
      <div class="follow-section">
        <FollowSuggestions />
  </div>
      <div class="footer-links">
        <a href="#" class="footer-link">Terms of Service</a>
        <a href="#" class="footer-link">Privacy Policy</a>
        <a href="#" class="footer-link">Cookie Policy</a>
        <a href="#" class="footer-link">Accessibility</a>
        <a href="#" class="footer-link">Ads info</a>
        <a href="#" class="footer-link">More</a>
        <span class="footer-link">© 2023 eTraCCS</span>
      </div>
    </div>
        </div>
      <!-- Mobile Navigation -->
  <nav class="mobile-nav">
    <ul class="mobile-nav-items">
      <Link :href="route('job-postings.index')" class="nav-link">
      <li class="mobile-nav-item active">
        <i class="fas fa-home"></i>
        <span>Home</span>
      </li>
    </Link>
    <Link :href="route('map.index')" class="nav-link">
      <li class="mobile-nav-item">
        <i class="fas fa-map-marker-alt"></i>
        <span>Map</span>
      </li>
    </Link>
      <li class="mobile-nav-item">
        <i class="fas fa-bell"></i>
        <span>Alerts</span>
      </li>
      <li class="mobile-nav-item">
        <i class="fas fa-envelope"></i>
        <span>Messages</span>
      </li>

      <Link :href="route('profile.show', $page.props.auth.user.encrypted_id)" class="nav-link">
        <li class="mobile-nav-item">
        <i class="fas fa-user"></i>
        <span>Profile</span>
      </li>
    </Link>
    </ul>
  </nav>
  
  <button class="mobile-post-button" @click="openModal">
    <i class="fas fa-plus"></i>
  </button>
</div>   
<div v-if="showFeedbackModal" class="modal-overlay" @click.self="showFeedbackModal = false">
            <div class="modal-container">
                <div class="modal-header">
                    <h3 class="modal-title">Help us improve!</h3>
                    <button @click="showFeedbackModal = false" class="modal-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="modal-body">
                    <div v-if="!feedbackSubmitted">
                        <p>We'd appreciate your feedback on your experience with our alumni system.</p>
                        
                        <div class="feedback-item">
                            <label>1. The system is easy to use</label>
                            <div class="rating-stars">
                                <span v-for="i in 5" :key="i" @click="easeOfUse = i" 
                                    :class="{'active': i <= easeOfUse}">
                                    <i class="fas fa-star"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="feedback-item">
                            <label>2. The system is useful for my needs</label>
                            <div class="rating-stars">
                                <span v-for="i in 5" :key="i" @click="usefulness = i" 
                                    :class="{'active': i <= usefulness}">
                                    <i class="fas fa-star"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="feedback-item">
                            <label>3. I'm satisfied with the system</label>
                            <div class="rating-stars">
                                <span v-for="i in 5" :key="i" @click="satisfaction = i" 
                                    :class="{'active': i <= satisfaction}">
                                    <i class="fas fa-star"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="feedback-item">
                            <label>4. I intend to use this system regularly</label>
                            <div class="rating-stars">
                                <span v-for="i in 5" :key="i" @click="intentionToUse = i" 
                                    :class="{'active': i <= intentionToUse}">
                                    <i class="fas fa-star"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="feedback-item">
                            <label>Additional comments (optional)</label>
                            <textarea v-model="comments" class="feedback-comment"></textarea>
                        </div>
                    </div>
                    
                    <div v-else class="thank-you-message">
                        <i class="fas fa-check-circle success-icon"></i>
                        <p>Thank you for your feedback!</p>
                    </div>
                </div>

                <div class="modal-footer" v-if="!feedbackSubmitted">
                  <button @click="optOutOfFeedback" class="btn btn-text">
                    Don't ask me again
                </button>
                  <button @click="showFeedbackModal = false" class="btn btn-secondary">
                    Maybe later
                </button>
                <button @click="submitFeedback" class="btn btn-primary" 
                        :disabled="!easeOfUse || !usefulness || !satisfaction || !intentionToUse">
                    Submit Feedback
                </button>
                </div>
            </div>
        </div>
</template>
<style scoped>
.modal-overlay{
  color: white;
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
    /* color: var(--text-secondary); */
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
.feedback-item {
    margin-bottom: 1.5rem;
}

.feedback-item label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.rating-stars {
    display: flex;
    gap: 0.5rem;
}

.rating-stars span {
    cursor: pointer;
    color: #ccc;
    font-size: 1.5rem;
    transition: color 0.2s;
}

.rating-stars span.active {
    color: #ffc107;
}

.rating-stars span:hover {
    color: #ffc107;
}

.feedback-comment {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--card-border);
    border-radius: 6px;
    min-height: 100px;
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
}

.thank-you-message {
    text-align: center;
    padding: 2rem 0;
}

.success-icon {
    font-size: 3rem;
    color: var(--success);
    margin-bottom: 1rem;
}
.transition-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--primary);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999999;
    flex-direction: column;
}

.animation-container {
    text-align: center;
    animation: fadeIn 0.5s ease-out;
}

.logo-animation {
    margin-bottom: 30px;
}

.animated-logo {
    animation: pulse 1.5s infinite alternate, float 3s ease-in-out infinite;
    transform-origin: center;
    width: 120px;
    height: 120px;
}

.loading-bar {
    width: 200px;
    height: 4px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
    overflow: hidden;
}

.loading-progress {
    height: 100%;
    width: 0;
    background: white;
    animation: loading 2s ease-in-out forwards;
}

.transition-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: var(--primary);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    flex-direction: column;
}

.animation-container {
    text-align: center;
}

.animated-logo {
    width: 100px;
    height: 100px;
    animation: pulse 1.5s infinite alternate;
}

.loading-bar {
    width: 200px;
    height: 4px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 2px;
    margin-top: 20px;
    overflow: hidden;
}

.loading-progress {
    height: 100%;
    width: 0;
    background: white;
    animation: loading 2s linear forwards;
}

/* Effects */
.blur-content {
    filter: blur(2px);
    pointer-events: none;
}

/* Animations */
@keyframes pulse {
    0% { transform: scale(1); opacity: 0.8; }
    100% { transform: scale(1.1); opacity: 1; }
}

@keyframes loading {
    0% { width: 0; }
    100% { width: 100%; }
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>