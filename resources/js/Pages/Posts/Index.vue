<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

// ✅ Define props explicitly
const props = defineProps({
    posts: Array,
});

const loading = ref(null);

const reactToPost = async (post, reactionType) => {
    if (loading.value === post.id) return;
    loading.value = post.id;

    // Optimistically update UI
    post.reactions_count += 1;

    try {
        const response = await axios.post(`/posts/${post.id}/react`, { reaction_type: reactionType });
        post.reactions_count = response.data.reactions_count; // Update with server response
    } catch (error) {
        console.error("Reaction failed", error);
        post.reactions_count -= 1; // Revert if error
    }

    loading.value = null;
};

const deletePost = (id) => {
    if (confirm('Are you sure you want to delete this post?')) {
        router.delete(route('posts.destroy', id));
    }
};
</script>

<template>
    <div>
        <h1>Posts</h1>
        <a :href="route('posts.create')">Create New Post</a>
        <ul>
            <li v-for="post in props.posts" :key="post.id">
                <h2>{{ post.title }}</h2>
                <p>{{ post.body }}</p>

                <!-- Display Media -->
                <div v-if="post.media_files && post.media_files.length > 0">
                    <div v-for="media in post.media_files" :key="media.id">
                        <img v-if="media.file_type === 'image'" :src="'/storage/' + media.file_path" width="200" />
                        <video v-if="media.file_type === 'video'" controls width="300">
                            <source :src="'/storage/' + media.file_path" type="video/mp4">
                        </video>
                    </div>
                </div>

                <!-- Reaction button -->
                <button @click="reactToPost(post, 1)" :disabled="loading === post.id">
                    👍 Like ({{ post.reactions_count }})
                </button>

                <a :href="route('posts.edit', post.id)">Edit</a>
                <button @click="deletePost(post.id)">Delete</button>
            </li>
        </ul>
    </div>
</template>
