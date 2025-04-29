<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import Welcome from '@/Components/Welcome.vue';
</script>

<template>
    <AppLayout title="Dashboard">
        <!-- <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template> -->
        <div class="container-main">
        <div class="profile-container">
                <!-- Cover Picture -->
                <div class="cover-picture">
                    <img src="cover-photo.png" alt="Cover Picture" class="cover-img">
                   
                </div>
                <!-- User Info -->
                <div class="user-info">
                    <div class="profile-main">
                        <div class="profile-picture-con">
                            <img src="profile-photo.png" alt="Profile Picture" class="profile-image">
                            
                        </div>

                    </div>
                   
                    <div class="user-info-item">
                        <h1>Joven Andrei R. Lagahit</h1>
                        <div class="profile-location"><i class="fa fa-map-marker-alt"></i> New York, USA</div>
                        <div class="university">
                            <div class="card-img">
                                <img src="school .png" alt="">
                            </div>
                            <div class="card-content">
                                <p class="card-title">Mindoro State University Main Campus - Collage of Computer Studies</p>
                            </div>
                        </div>
                       
                    </div>
        
            </div>
        </div>
    <div class="container">
        <!-- Header -->

                <!-- Stories Section -->

        <section class="stories" >
            <div class="story create-story">
                <img src="profile-photo.png" alt="">
                <div class="create-story-add">

                    <div class="add-story">
                        <div class="add-story-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="story">
                <img src="story4.jpg" alt="">


                <div class="story-user">
                    <div class="profile-story">
                        <img src="story1.jpg" alt="">
                    </div>
                    <div class="story-name">
                        Joven Andrei
                    </div>
                </div>
            </div>
            <div class="story">
                <img src="story3.jpg" alt="">

                <div class="story-user">
                    <div class="profile-story">
                        <img src="story1.jpg" alt="">
                    </div>
                    <div class="story-name">
                        Joven Andrei
                    </div>
                </div>
            </div>
            <div class="story">
                <img src="story5.jpg" alt="">

                <div class="story-user">
                    <div class="profile-story">
                        <img src="story1.jpg" alt="">
                    </div>
                    <div class="story-name">
                        Joven Andrei
                    </div>
                </div>
            </div>
            <div class="story">
                <img src="story1.jpg" alt="">

                <div class="story-user">
                    <div class="profile-story">
                        <img src="story1.jpg" alt="">
                    </div>
                    <div class="story-name">
                        Joven Andrei
                    </div>
                </div>
                
            </div>
            <div class="story">
            </div>
            <div class="story">
                <img src="story.jpg" alt="">
                <div class="story-user">
                    <div class="profile-story">
                        <img src="story1.jpg" alt="">
                    </div>
                    <div class="story-name">
                        Joven Andrei
                    </div>
                </div>
            </div>
        </section>

        <!-- Create Post Section -->
<section class="create-post">
    <div class="create-post-header">
        <img src="profile-photo.png" alt="Profile Picture" class="profile-pic">
        <input type="text" placeholder="What's on your mind?" class="post-input">
    </div>
    <div class="create-post-actions">
        <button><i class="fa fa-image"></i> Photo/Video</button>
        <button><i class="fa fa-smile"></i> Feeling/Activity</button>
        <button><i class="fa fa-map-marker-alt"></i> Check In</button>
    </div>
</section>
<br>
        <!-- Feed Section -->
        <section class="feed">
            <!-- Post 1 -->
            <div class="post">
                <div class="post-header">
                    <img src="profile-photo.png" alt="Profile Picture" class="profile-pic">
                    <div class="post-info">
                        <p class="username">Username 1</p>
                        <p class="timestamp">10m ago</p>
                    </div>
                </div>
                <p class="post-caption">This is the caption for post 1. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <div class="post-container">
                    <img src="story.jpg" alt="Post Image" class="post-image">
                </div>
                <div class="reaction-count">
                    <div class="reaction-container">
                        <span class="emoji" data-emoji="🎓">🎓</span>
                        <span class="emoji" data-emoji="❤️">❤️</span>
                        <span class="emoji" data-emoji="🎉">🎉</span>
                    </div>
                    <div class="reactors-names">
                        Joven Andrei R. Lagahit and 17k others
                    </div>
                    
                </div>
                <div class="post-actions">
                    <button class="like-btn"><span class="emoji">❤️</span> Love</button>
                    <button><i class="fa fa-comment"></i> Comment</button>
                    <button><i class="fa fa-share"></i> Share</button>
                </div>
                <!-- Comments Section -->
                <div class="comments-section">
                    <div class="comment">
                        <img src="profile-photo.png" alt="Profile Picture" class="comment-profile-pic">
                        <div class="comment-content">
                            <p class="comment-username">User A</p>
                            <p class="comment-text">This is a comment on post 1.</p>
                        </div>
                    </div>
                    <div class="comment">
                        <img src="profile-photo.png" alt="Profile Picture" class="comment-profile-pic">
                        <div class="comment-content">
                            <p class="comment-username">User B</p>
                            <p class="comment-text">Another comment on post 1.</p>
                        </div>
                    </div>
                </div>
                <!-- Comment Input -->
                <div class="comment-input">
                    <img src="profile-photo.png" alt="Profile Picture" class="comment-profile-pic">
                    <input type="text" id="comment-input" placeholder="Write a comment..." />
                    <button id="emoji-picker-btn">😊</button>
                    <button id="send-comment-btn"><i class="fa-regular fa-paper-plane"></i></button>
                </div>
            </div>

            <!-- Post 2 -->
            <div class="post">
                <div class="post-header">
                    <img src="profile-photo.png" alt="Profile Picture" class="profile-pic">
                    <div class="post-info">
                        <p class="username">Username 2</p>
                        <p class="timestamp">30m ago</p>
                    </div>
                </div>
                <p class="post-caption">This is the caption for post 2. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <div class="post-container">
                <img src="story3.jpg" alt="Post Image" class="post-image">
                </div>
                <div class="post-actions">
                    <button><i class="fa fa-thumbs-up"></i> Like</button>
                    <button><i class="fa fa-comment"></i> Comment</button>
                    <button><i class="fa fa-share"></i> Share</button>
                </div>
                <!-- Comments Section -->
                <div class="comments-section">
                    <div class="comment">
                        <img src="profile-photo.png" alt="Profile Picture" class="comment-profile-pic">
                        <div class="comment-content">
                            <p class="comment-username">User C</p>
                            <p class="comment-text">This is a comment on post 2.</p>
                        </div>
                    </div>
                </div>
                <!-- Comment Input -->
                <div class="comment-input">
                    <img src="profile-photo.png" alt="Profile Picture" class="comment-profile-pic">
                    <input  type="text" placeholder="Write a comment..." />

                </div>
            </div>

            <!-- Post 3 -->
            <div class="post">
                <div class="post-header">
                    <img src="profile-photo.png" alt="Profile Picture" class="profile-pic">
                    <div class="post-info">
                        <p class="username">Username 3</p>
                        <p class="timestamp">1h ago</p>
                    </div>
                </div>
                <p class="post-caption">This is the caption for post 3. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <img src="https://via.placeholder.com/600x400" alt="Post Image" class="post-image">
                <div class="post-actions">
                    <button><i class="fa fa-thumbs-up"></i> Like</button>
                    <button><i class="fa fa-comment"></i> Comment</button>
                    <button><i class="fa fa-share"></i> Share</button>
                </div>
                <!-- Comments Section -->
                <div class="comments-section">
                    <div class="comment">
                        <img src="profile-photo.png" alt="Profile Picture" class="comment-profile-pic">
                        <div class="comment-content">
                            <p class="comment-username">User D</p>
                            <p class="comment-text">This is a comment on post 3.</p>
                        </div>
                    </div>
                </div>
                <!-- Comment Input -->
                <div class="comment-input">
                    <img src="profile-photo.png" alt="Profile Picture" class="comment-profile-pic">
                    <input type="text" placeholder="Write a comment..." />
                </div>
            </div>
        </section>
    </div>
    <div class="container-left">
        
    </div>
</div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                </div>
            </div>
        </div>
    </AppLayout>
</template>
