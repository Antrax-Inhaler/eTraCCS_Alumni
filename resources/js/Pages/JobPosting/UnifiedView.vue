<script setup>
defineProps({
    jobPostings: Array,
    posts: Array,
});

const isImage = (fileType) => fileType.startsWith('image/');
const isVideo = (fileType) => fileType.startsWith('video/');
</script>

<template>
    <div>
        <h1>Unified View</h1>

        <!-- Job Postings -->
        <section>
            <h2>Job Postings</h2>
            <div v-for="job in jobPostings" :key="job.id" class="job-posting">
                <h3>{{ job.job_title }} at {{ job.company_name }}</h3>
                <p><strong>Location:</strong> {{ job.location }}</p>
                <p><strong>Description:</strong> {{ job.description }}</p>
                <p><strong>Media Files:</strong></p>
                <ul>
                    <li v-for="media in job.media_files" :key="media.id">
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
        </section>

        <!-- General Posts -->
        <section>
            <h2>General Posts</h2>
            <div v-for="post in posts" :key="post.id" class="post">
                <h3>{{ post.title }}</h3>
                <p>{{ post.body }}</p>
                <p><strong>Media Files:</strong></p>
                <ul>
                    <li v-for="media in post.media_files" :key="media.id">
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
        </section>
    </div>
</template>