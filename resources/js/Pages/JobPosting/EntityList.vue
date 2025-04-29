<template>
    <div>
        <h1>All Entities</h1>
        <div v-for="entity in entities" :key="entity.id" class="entity">
            <!-- Render entity-specific details -->
            <h3>{{ entity.title }}</h3>
            <p><strong>Type:</strong> {{ entity.type }}</p>
            <p><strong>Description:</strong> {{ entity.description }}</p>
            <p><strong>Date:</strong> {{ entity.date }}</p>

            <!-- Media Files -->
            <div v-if="entity.media_files && entity.media_files.length > 0">
                <p><strong>Media Files:</strong></p>
                <ul>
                    <li v-for="media in entity.media_files" :key="media.id">
                        <span v-if="isImage(media.file_type)">
                            <img :src="`/storage/${media.file_path}`" alt="Media" style="max-width: 200px;" />
                        </span>
                        <span v-else-if="isVideo(media.file_type)">
                            <video controls style="max-width: 300px;">
                                <source :src="`/storage/${media.file_path}`" :type="media.file_type" />
                                Your browser does not support the video tag.
                            </video>
                        </span>
                        <span v-else>
                            <a :href="`/storage/${media.file_path}`" target="_blank">Download File</a>
                        </span>
                    </li>
                </ul>
            </div>

            <!-- Reactions -->
            <div>
                <p><strong>Reactions:</strong></p>
                <ul>
                    <li v-for="(count, type) in entity.reaction_counts" :key="type">
                        Reaction Type {{ type }}: {{ count }}
                    </li>
                </ul>
            </div>

            <!-- Comments -->
            <div>
                <h4>Comments</h4>
                <div v-for="comment in entity.comments" :key="comment.id" class="comment">
                    <p><strong>{{ comment.user?.name || 'Unknown User' }}:</strong> {{ comment.content }}</p>
                </div>
            </div>

            <!-- Delete Button -->
            <button @click="deleteEntity(entity.id)">Delete</button>
        </div>
    </div>
</template>
<script setup>
import { defineProps } from 'vue';
import axios from 'axios';

defineProps({
    entities: {
        type: Array,
        default: () => [],
    },
});

// Helper function to check if a file is an image
const isImage = (fileType) => {
    return fileType.startsWith('image/');
};

// Helper function to check if a file is a video
const isVideo = (fileType) => {
    return fileType.startsWith('video/');
};

// Delete an entity
const deleteEntity = async (entityId) => {
    try {
        await axios.delete(`/entities/${entityId}`);
        alert('Entity deleted successfully!');
    } catch (error) {
        console.error('Error deleting entity:', error.response?.data || error.message);
    }
};
</script>