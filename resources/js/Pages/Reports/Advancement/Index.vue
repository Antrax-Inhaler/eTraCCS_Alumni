<!-- resources/js/Pages/Admin/Reports/Advancement/Index.vue -->
<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import FilterBar from '@/Components/FilterBar.vue';
import StatsCard from '@/Components/StatsCard.vue';
import BarChart from '@/Components/Charts/BarChart.vue';

const props = defineProps({
    initialData: Object,
    filters: Object,
});

const data = ref(props.initialData);
const filters = ref(props.filters);
const isLoading = ref(false);
const activeTab = ref('degrees');

// Debounced report generation
const generateReport = debounce(() => {
    isLoading.value = true;
    router.get(route('admin.reports.advancement.index'), filters.value, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
}, 500);

watch(filters, generateReport, { deep: true });

const downloadReport = (format, type) => {
    window.location.href = route('admin.reports.advancement.export', {
        ...filters.value,
        format: format,
        reportType: type
    });
};

const resetFilters = () => {
    filters.value = {};
};
</script>

<template>
    <AdminLayout title="Educational/Professional Advancement Reports">
        <template #header>
            <div class="flex justify-between items-center">
                <div class="action-buttons">
  <button @click="downloadReport('pdf')" class="export-btn pdf">
    <i class="fas fa-file-pdf"></i> Export PDF
  </button>
  <button @click="downloadReport('excel')" class="export-btn excel">
    <i class="fas fa-file-excel"></i> Export Excel
  </button>
</div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Loading Indicator -->
                <div v-if="isLoading" class="mb-4 p-4 bg-blue-50 text-blue-800 rounded-lg flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading report data...
                </div>

                <!-- Filter Bar -->
                <FilterBar v-model="filters" @reset="resetFilters">
                    <template #default>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Search</label>
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Search by name"
                                />
                            </div>
                            
                            <div v-if="activeTab === 'degrees'">
                                <label class="block text-sm font-medium text-gray-700">Degree Type</label>
                                <select
                                    v-model="filters.degreeType"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="type in data.degreeTypes" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                            </div>
                            
                            <div v-if="activeTab === 'studies'">
                                <label class="block text-sm font-medium text-gray-700">Institution</label>
                                <input
                                    v-model="filters.institution"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Filter by institution"
                                />
                            </div>
                            
                            <div v-if="activeTab === 'certifications'">
                                <label class="block text-sm font-medium text-gray-700">Certification Type</label>
                                <select
                                    v-model="filters.trainingType"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="type in data.trainingTypes" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">From Year</label>
                                <input
                                    v-model="filters.yearFrom"
                                    type="number"
                                    min="1900"
                                    :max="new Date().getFullYear()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Start year"
                                />
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">To Year</label>
                                <input
                                    v-model="filters.yearTo"
                                    type="number"
                                    min="1900"
                                    :max="new Date().getFullYear()"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="End year"
                                />
                            </div>
                        </div>
                    </template>
                </FilterBar>

                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button
                            @click="activeTab = 'degrees'"
                            :class="{
                                'border-indigo-500 text-indigo-600': activeTab === 'degrees',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'degrees'
                            }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            Advanced Degrees
                        </button>
                        <button
                            @click="activeTab = 'studies'"
                            :class="{
                                'border-indigo-500 text-indigo-600': activeTab === 'studies',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'studies'
                            }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            Advanced Studies
                        </button>
                        <button
                            @click="activeTab = 'certifications'"
                            :class="{
                                'border-indigo-500 text-indigo-600': activeTab === 'certifications',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'certifications'
                            }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            Certifications
                        </button>
                        <button
                            @click="activeTab = 'training'"
                            :class="{
                                'border-indigo-500 text-indigo-600': activeTab === 'training',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'training'
                            }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                        >
                            Training Participation
                        </button>
                    </nav>
                </div>

                <!-- Advanced Degrees Report -->
                <div v-if="activeTab === 'degrees'" class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Alumni with Additional Baccalaureate Degrees
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            List of alumni who have earned additional undergraduate degrees
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Additional Degree
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Institution
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Year Earned
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="data.advancedDegrees.data.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No additional degrees found matching your criteria
                                    </td>
                                </tr>
                                <tr v-for="degree in data.advancedDegrees.data" :key="degree.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ degree.user.first_name }} {{ degree.user.last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ degree.degree_earned }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ degree.institution }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ degree.year_graduated }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination class="mt-4" :links="data.advancedDegrees.links" />
                </div>

                <!-- Advanced Studies Report -->
                <div v-if="activeTab === 'studies'" class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Alumni Pursuing Higher Education (Master's/PhD)
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            List of alumni who have pursued graduate studies
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Advanced Degree
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Institution
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Year Earned
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="data.advancedStudies.data.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No advanced studies found matching your criteria
                                    </td>
                                </tr>
                                <tr v-for="study in data.advancedStudies.data" :key="study.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ study.user.first_name }} {{ study.user.last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ study.degree_earned }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ study.institution }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ study.year_graduated }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination class="mt-4" :links="data.advancedStudies.links" />
                </div>

                <!-- Professional Certifications Report -->
                <div v-if="activeTab === 'certifications'" class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Professional Certifications Earned
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            List of professional certifications obtained by alumni
                        </p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Certification
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Institution
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Year Obtained
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="data.certifications.data.length === 0">
                                    <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No certifications found matching your criteria
                                    </td>
                                </tr>
                                <tr v-for="cert in data.certifications.data" :key="cert.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ cert.user.first_name }} {{ cert.user.last_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ cert.training_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ cert.institution }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ cert.year_attended }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <Pagination class="mt-4" :links="data.certifications.links" />
                </div>

                <!-- Training Participation Report -->
                <div v-if="activeTab === 'training'" class="mt-6">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200 bg-white rounded-t-lg">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Training Participation Statistics
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Frequency and types of trainings attended by alumni
                        </p>
                    </div>
                    <div class="bg-white shadow overflow-hidden sm:rounded-b-lg">
                        <div class="p-4">
                            <BarChart
                                :labels="data.trainingStats.map(item => item.training_name)"
                                :data="data.trainingStats.map(item => item.participant_count)"
                                label="Number of Participants"
                            />
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Training Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Participants
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            First Offered
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Last Offered
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="stat in data.trainingStats" :key="stat.training_name">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ stat.training_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ stat.participant_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ stat.earliest_year }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ stat.latest_year }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>