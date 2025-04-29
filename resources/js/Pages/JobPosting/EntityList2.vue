<template>
    <div class="timeline">
      <div v-for="entity in entities" :key="`${entity.type}-${entity.id}`" class="timeline-item">
        <div class="entity-header">
          <h3>{{ entity.type === 'job' ? entity.job_title : entity.type === 'post' ? entity.title : entity.event_name }}</h3>
          <p v-if="entity.type === 'job'">Company: {{ entity.company_name }}</p>
          <p v-if="entity.type === 'event'">Date: {{ entity.date }}</p>
          <p>Location: {{ entity.location }}</p>
          <p>Score: {{ entity.score.toFixed(2) }}</p>
        </div>
  
        <div class="entity-content">
          <p>{{ entity.type === 'job' ? entity.description : entity.type === 'post' ? entity.body : entity.description }}</p>
        </div>
  
        <div class="media-files">
          <pre>{{ entity.media_files }}</pre> <!-- Debug: Check media_files structure -->
          <div v-for="file in entity.media_files" :key="file.id" class="media-item">
            <img v-if="isImage(file.file_type)" :src="`/storage/${file.file_path}`" alt="Media" class="media-image" />
            <video v-else-if="isVideo(file.file_type)" controls class="media-video">
              <source :src="`/storage/${file.file_path}`" :type="file.file_type" />
              Your browser does not support the video tag.
            </video>
            <p v-else>Unsupported file type: {{ file.file_type }}</p>
          </div>
        </div>
  
        <div class="reactions">
          <p>Reactions:</p>
          <ul>
            <li v-for="(count, type) in entity.reaction_counts" :key="type">
              {{ type }}: {{ count }}
            </li>
          </ul>
        </div>
  
        <div class="comments">
          <p>Comments:</p>
          <ul>
            <li v-for="comment in entity.comments" :key="comment.id">
              <strong>{{ comment.user?.full_name || 'Unknown User' }}:</strong> {{ comment.content }}
            </li>
          </ul>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { defineProps } from 'vue';
  
  // Props for the Timeline component
  defineProps({
    entities: {
      type: Array,
      required: true,
    },
  });
  
  // Check if a file is an image
  const isImage = (fileType) => {
    return fileType.startsWith('image/');
  };
  
  // Check if a file is a video
  const isVideo = (fileType) => {
    return fileType.startsWith('video/');
  };
  </script>
  
  <style scoped>
  /* Add your styles here */
  </style>
<script setup>
import { ref } from 'vue';
import axios from 'axios';

// Props for the component
const props = defineProps({
    entities: Array, // List of entities (Job Postings, Posts, Events)
    type: String,   // Type of entity ('job', 'post', 'event')
});
console.log('Entities:', props.entities);
const loading = ref(null); // Track which item is being processed
const newCommentContent = ref(''); // Content of the new comment
const replyTo = ref(null); // ID of the comment being replied to

// Define reaction types
const reactions = [
    { type: 1, emoji: '👍', label: 'Like' },
    { type: 2, emoji: '❤️', label: 'Love' },
    { type: 3, emoji: '😄', label: 'Happy' },
    { type: 4, emoji: '😢', label: 'Sad' },
    { type: 5, emoji: '😡', label: 'Angry' },
];

// React to an entity
const reactToEntity = async (entityType, entityId, reactionType) => {
    try {
        const requestData = {
            reaction_type: reactionType,
        };

        if (entityType === 'post') {
            requestData.post_id = entityId;
        } else if (entityType === 'event') {
            requestData.event_id = entityId;
        } else if (entityType === 'job') {
            requestData.job_id = entityId;
        }

        const response = await axios.post('/react', requestData);

        const entity = props.entities.find(e => e.id === entityId);
        if (entity) {
            entity.reaction_counts = response.data.reaction_counts;
        }
    } catch (error) {
        console.error('Error reacting:', error.response?.data || error.message);
    }
};

// Add a comment or reply
const addComment = async (entityId, parentId = null) => {
    try {
        const requestData = {
            content: newCommentContent.value,
            parent_id: parentId,
        };

        if (props.type === 'post') {
            requestData.post_id = entityId;
        } else if (props.type === 'event') {
            requestData.event_id = entityId;
        } else if (props.type === 'job') {
            requestData.job_id = entityId;
        }

        const response = await axios.post('/comment', requestData);

        const entity = props.entities.find(e => e.id === entityId);
        if (entity) {
            if (!Array.isArray(entity.comments)) {
                entity.comments = [];
            }

            const newComment = response.data.comment;

            if (parentId) {
                const parentComment = findCommentById(entity.comments, parentId);
                if (parentComment) {
                    if (!Array.isArray(parentComment.replies)) {
                        parentComment.replies = [];
                    }
                    parentComment.replies.push(newComment);
                }
            } else {
                entity.comments.push(newComment);
            }
        }

        newCommentContent.value = '';
        replyTo.value = null;
    } catch (error) {
        console.error('Error adding comment:', error.response?.data || error.message);
    }
};

// Find a comment by ID (recursive)
const findCommentById = (comments, id) => {
    for (const comment of comments) {
        if (comment.id === id) return comment;
        if (comment.replies && comment.replies.length > 0) {
            const found = findCommentById(comment.replies, id);
            if (found) return found;
        }
    }
    return null;
};

// Reply to a comment
const replyToComment = (parentId) => {
    replyTo.value = parentId;
};

// Delete an entity
const deleteEntity = async (entityId) => {
    try {
        const requestData = {};

        if (props.type === 'post') {
            requestData.post_id = entityId;
        } else if (props.type === 'event') {
            requestData.event_id = entityId;
        } else if (props.type === 'job') {
            requestData.job_id = entityId;
        }

        await axios.delete(`/delete`, { data: requestData });

        // Remove the entity from the frontend
        const index = props.entities.findIndex(e => e.id === entityId);
        if (index !== -1) {
            props.entities.splice(index, 1);
        }
    } catch (error) {
        console.error('Error deleting entity:', error.response?.data || error.message);
    }
};

// Helper function to check if a file is an image
const isImage = (fileType) => {
    return fileType && fileType.startsWith('image/');
};

// Helper function to check if a file is a video
const isVideo = (fileType) => {
    return fileType && fileType.startsWith('video/');
};
</script>