<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    company: Object,
});
</script>

<template>
    <AdminLayout>
        <Head :title="company ? `Company Details - ${company.name}` : 'Company Details'" />

        <div class="main-content">
            <h1 class="page-title">{{ company?.name || 'No Company Found' }}</h1>

            <!-- Company Details -->
            <div v-if="company" class="data-section">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="section-title">General Information</h3>
                        <p><strong>Industry:</strong> {{ company.industry || 'N/A' }}</p>
                        <p><strong>City:</strong> {{ company.city || 'N/A' }}</p>
                        <p><strong>Country:</strong> {{ company.country || 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="section-title">Employment Histories</h3>
                        <ul v-if="company.employmentHistories && company.employmentHistories.length > 0">
                            <li v-for="history in company.employmentHistories" :key="history.id">
                                {{ history.job_title }} - {{ history.user?.name || 'Unknown User' }}
                            </li>
                        </ul>
                        <p v-else>No employment histories found.</p>
                    </div>
                </div>
            </div>
            <div v-else>
                <p>No company data available.</p>
            </div>
        </div>
    </AdminLayout>
</template>