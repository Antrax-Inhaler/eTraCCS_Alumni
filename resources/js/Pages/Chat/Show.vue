<script setup>
import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

// Extend dayjs with plugins
dayjs.extend(relativeTime);

// Props passed from the backend
const props = defineProps({
    conversation: Object, // The selected conversation
});

// Reactive state
const messages = ref(props.conversation.messages || []);
const selectedConversation = ref(props.conversation);

// Helper Function to Format Message Timestamp
const formatMessageTimestamp = (timestamp) => {
    const now = dayjs();
    const messageTime = dayjs(timestamp);

    // If the message is older than 7 days, show the full date
    if (now.diff(messageTime, 'days') > 7) {
        return messageTime.format('lll'); // e.g., "Oct 1, 2023 12:34 PM"
    }

    // Otherwise, show relative time (e.g., "5 minutes ago")
    return messageTime.fromNow();
};

// Optionally, fetch additional messages if needed
onMounted(() => {
    console.log('Selected Conversation:', selectedConversation.value);
});
</script>

<template>
    <div class="message-view" v-if="selectedConversation">
        <h3>{{ selectedConversation.name }}</h3>
        <div class="messages">
            <div v-for="message in messages" :key="message.id" class="message">
                <strong>{{ message.sender.name }}</strong>
                <p>{{ message.content }}</p>
                <span class="message-timestamp">{{ formatMessageTimestamp(message.created_at) }}</span>
            </div>
        </div>
        <div class="message-input">
            <textarea placeholder="Type a message..."></textarea>
            <button>Send</button>
        </div>
    </div>
</template>

<style scoped>
.message-view {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.messages {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

.message {
    margin-bottom: 10px;
}

.message strong {
    font-weight: bold;
    color: #333;
}

.message-timestamp {
    font-size: 12px;
    color: #888;
    margin-left: 10px;
}

.message-input {
    display: flex;
    padding: 10px;
    border-top: 1px solid #ccc;
}

.message-input textarea {
    flex: 1;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    resize: none;
}

.message-input button {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-left: 10px;
}
</style>