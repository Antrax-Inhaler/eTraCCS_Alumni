<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    unverifiedUsers: Object,
    filters: Object,
    matchThreshold: Number,
});
const testModal = ref(false);
const verifyingId = ref(null);
const manualVerifyId = ref(null);
const rejectingId = ref(null);
const selectedRecord = ref(null);
const matchDetailsModal = ref(false);
const currentMatches = ref([]);
const verificationMessage = ref({
    show: false,
    type: 'success',
    text: ''
});

const form = ref({
    student_number: '',
    notes: '',
    reason: '',
});

const verifyUser = (id) => {
  verifyingId.value = id;
  verificationMessage.value.show = false;

  router.post(`/admin/alumni/verify/${id}`, {}, {
    preserveScroll: true,
    onSuccess: (page) => {
      verificationMessage.value = {
        show: true,
        type: 'success',
        text: page.props.flash?.success || 'User verified successfully.'
      };
      verifyingId.value = null;
    },
    onError: (errors) => {
      verificationMessage.value = {
        show: true,
        type: 'error',
        text: errors?.message || 'Failed to verify user. Please try again.'
      };
      verifyingId.value = null;
    }
  });
};

const openManualVerify = (user) => {
    manualVerifyId.value = user.id;
    form.value.student_number = user.student_number || '';
    form.value.notes = '';
};

const submitManualVerify = () => {
    router.post(`/admin/alumni/manual-verify/${manualVerifyId.value}`, form.value, {
        preserveScroll: true,
        onSuccess: (page) => {
            verificationMessage.value = {
                show: true,
                type: 'success',
                text: page.props.flash?.success || 'User manually verified successfully.'
            };
            manualVerifyId.value = null;
        },
        onError: () => {
            verificationMessage.value = {
                show: true,
                type: 'error',
                text: 'Failed to verify user. Please try again.'
            };
        }
    });
};

const openReject = (user) => {
    rejectingId.value = user.id;
    form.value.reason = '';
};

const submitReject = () => {
    router.post(`/admin/alumni/reject/${rejectingId.value}`, {
        reason: form.value.reason
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            verificationMessage.value = {
                show: true,
                type: 'success',
                text: page.props.flash?.success || 'User verification rejected.'
            };
            rejectingId.value = null;
        },
        onError: () => {
            verificationMessage.value = {
                show: true,
                type: 'error',
                text: 'Failed to reject user. Please try again.'
            };
        }
    });
};

const showMatchDetails = (user) => {
    axios.get(route('admin.alumni.match-details', { user: user.id }))
        .then(response => {
            currentMatches.value = response.data.matches;
            selectedRecord.value = user;
            matchDetailsModal.value = true;
        })
        .catch(error => {
            verificationMessage.value = {
                show: true,
                type: 'error',
                text: 'Failed to load match details.'
            };
        });
};

const getMatchPercentage = (user) => {
    if (!user.match_score) return 'N/A';
    return `${Math.round(user.match_score)}%`;
};

const getMatchColor = (score) => {
    if (!score) return 'bg-gray-600';
    if (score >= 80) return 'bg-green-600';
    if (score >= 50) return 'bg-yellow-600';
    return 'bg-red-600';
};
</script>

<template>
    <AdminLayout title="Alumni Verification">
        <div class="py-6 px-4 sm:px-6 lg:px-8" style=" color: var(--text-primary);">
            <!-- Verification Message -->
            <div v-if="verificationMessage.show" 
                 :class="{
                   'bg-green-900 border-green-700 text-green-100': verificationMessage.type === 'success',
                   'bg-red-900 border-red-700 text-red-100': verificationMessage.type === 'error'
                 }" 
                 class="border px-4 py-3 rounded relative mb-4">
                {{ verificationMessage.text }}
                <button @click="verificationMessage.show = false" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                    <svg class="fill-current h-6 w-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/>
                    </svg>
                </button>
            </div>

            <div class="shadow rounded-lg p-6" style="background-color: var(--bg-darker);">
                <!-- Search and Filters -->
                <div class="mb-6">
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Search by name, email or student number"
                        class="w-full rounded-md shadow-sm focus:ring-2 focus:ring-orange-500"
                        style="background-color: var(--card-bg); border-color: var(--card-border); color: var(--text-primary);"
                        @input="router.get('/admin/alumni/verification', { search: filters.search }, { preserveState: true })"
                    />
                </div>

                <!-- Verification Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y" style="border-color: var(--card-border);">
                        <thead>
                            <tr style="background-color: var(--bg-dark);">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Profile</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Student #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Education</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Match Score</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: var(--text-secondary);">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y" style="border-color: var(--card-border);">
                            <tr v-for="user in unverifiedUsers.data" :key="user.id" style="background-color: var(--card-bg);">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <img :src="user.profile_photo_url" 
                                         class="h-10 w-10 rounded-full"
                                         alt="Profile Photo">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium" style="color: var(--text-primary);">{{ user.name }}</div>
                                    <div class="text-sm" style="color: var(--text-secondary);">{{ user.email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm" style="color: var(--text-primary);">{{ user.student_number || 'Not provided' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="user.educational_backgrounds?.length" class="text-sm" style="color: var(--text-primary);">
                                        <div v-for="edu in user.educational_backgrounds" :key="edu.id" class="mb-2">
                                            <p><strong>{{ edu.degree }}</strong> ({{ edu.year_graduated }})</p>
                                            <p>{{ edu.school_name }} - {{ edu.campus }}</p>
                                        </div>
                                    </div>
                                    <div v-else class="text-sm" style="color: var(--text-secondary);">No education data</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button 
                                        @click="showMatchDetails(user)"
                                        :class="getMatchColor(user.match_score)"
                                        class="px-3 py-1 rounded-full text-xs font-medium cursor-pointer hover:opacity-80 text-white"
                                    >
                                        {{ getMatchPercentage(user) }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="{
                                        'bg-yellow-600 text-white': user.verification_status === 'pending',
                                        'bg-green-600 text-white': user.verification_status === 'verified',
                                        'bg-red-600 text-white': user.verification_status === 'rejected',
                                        'bg-gray-600 text-white': user.verification_status === 'unverified'
                                    }" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                        {{ user.verification_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button
                                        @click="verifyUser(user.id)"
                                        :disabled="verifyingId === user.id"
                                        class="text-orange-400 hover:text-orange-300 mr-3"
                                    >
                                        <span v-if="verifyingId === user.id">Verifying...</span>
                                        <span v-else>Auto Verify</span>
                                    </button>
                                    <button
                                        @click="openManualVerify(user)"
                                        class="text-green-400 hover:text-green-300 mr-3"
                                    >
                                        Manual Verify
                                    </button>
                                    <button
                                        @click="openReject(user)"
                                        class="text-red-400 hover:text-red-300"
                                    >
                                        Reject
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <Pagination :links="unverifiedUsers.links" class="mt-4" />
            </div>
        </div>

        <!-- Manual Verify Modal -->
        <div v-if="manualVerifyId" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center">
            <div class="rounded-lg p-6 max-w-md w-full" style="background-color: var(--bg-darker);">
                <h3 class="text-lg font-medium mb-4" style="color: var(--text-primary);">Manual Verification</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Student Number</label>
                    <input v-model="form.student_number" type="text" class="mt-1 block w-full rounded-md shadow-sm" style="background-color: var(--card-bg); border-color: var(--card-border); color: var(--text-primary);">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Notes</label>
                    <textarea v-model="form.notes" class="mt-1 block w-full rounded-md shadow-sm" style="background-color: var(--card-bg); border-color: var(--card-border); color: var(--text-primary);"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button @click="manualVerifyId = null" class="px-4 py-2 rounded-md" style="background-color: var(--card-bg); color: var(--text-primary);">Cancel</button>
                    <button @click="submitManualVerify" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Verify</button>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div v-if="rejectingId" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center">
            <div class="rounded-lg p-6 max-w-md w-full" style="background-color: var(--bg-darker);">
                <h3 class="text-lg font-medium mb-4" style="color: var(--text-primary);">Reject Verification</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" style="color: var(--text-secondary);">Reason</label>
                    <textarea v-model="form.reason" class="mt-1 block w-full rounded-md shadow-sm" style="background-color: var(--card-bg); border-color: var(--card-border); color: var(--text-primary);" required></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button @click="rejectingId = null" class="px-4 py-2 rounded-md" style="background-color: var(--card-bg); color: var(--text-primary);">Cancel</button>
                    <button @click="submitReject" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Reject</button>
                </div>
            </div>
        </div>

        <!-- Match Details Modal -->
        <div v-if="matchDetailsModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4">
            <div class="rounded-lg p-6 w-full max-w-6xl max-h-[90vh] overflow-y-auto" style="background-color: var(--bg-darker);">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold" style="color: var(--primary);">Alumni Match Comparison</h3>
                    <button @click="matchDetailsModal = false" class="text-gray-400 hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div v-if="currentMatches.length > 0" class="space-y-8">
                    <div v-for="(match, index) in currentMatches" :key="index" class="relative">
                        <!-- Match Score Badge -->
                        <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 z-10">
                            <span :class="getMatchColor(match.score)" class="px-4 py-1 rounded-full text-sm font-bold text-white shadow-lg">
                                Score: {{ Math.round(match.score) }}%
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Current User Profile Card -->
                            <div class="border rounded-lg p-6" style="background-color: var(--card-bg); border-color: var(--card-border);">
                                <div class="flex items-center mb-4">
                                    <img :src="selectedRecord.profile_photo_url" class="h-16 w-16 rounded-full mr-4" alt="Profile Photo">
                                    <div>
                                        <h4 class="text-lg font-bold" style="color: var(--primary);">Current User</h4>
                                        <p class="text-sm" style="color: var(--text-primary);">{{ selectedRecord.name }}</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-sm font-medium" style="color: var(--text-secondary);">Student #:</span>
                                        <p style="color: var(--text-primary);">{{ selectedRecord.student_number || 'Not provided' }}</p>
                                    </div>
                                    
                                    <div v-if="selectedRecord.educational_backgrounds?.length">
                                        <span class="text-sm font-medium" style="color: var(--text-secondary);">Education:</span>
                                        <div v-for="edu in selectedRecord.educational_backgrounds" :key="edu.id" class="mt-1">
                                            <p style="color: var(--text-primary);"><strong>{{ edu.degree }}</strong></p>
                                            <p style="color: var(--text-primary);">{{ edu.school_name }} - {{ edu.campus }}</p>
                                            <p style="color: var(--text-primary);">Graduated: {{ edu.year_graduated }}</p>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <span class="text-sm font-medium" style="color: var(--text-secondary);">Education:</span>
                                        <p style="color: var(--text-secondary);">No education data</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Matched Alumni Profile Card -->
                            <div class="border rounded-lg p-6" style="background-color: var(--card-bg); border-color: var(--card-border);">
                                <div class="flex items-center mb-4">
                                    <div class="h-16 w-16 rounded-full mr-4 flex items-center justify-center" style="background-color: var(--primary-light);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" style="color: var(--primary);" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold" style="color: var(--primary);">Alumni Record</h4>
                                        <p class="text-sm" style="color: var(--text-primary);">{{ match.record.first_name }} {{ match.record.last_name }}</p>
                                    </div>
                                </div>
                                
                                <div class="space-y-3">
                                    <div>
                                        <span class="text-sm font-medium" style="color: var(--text-secondary);">Student #:</span>
                                        <p style="color: var(--text-primary);">{{ match.record.student_number }}</p>
                                    </div>
                                    
                                    <div>
                                        <span class="text-sm font-medium" style="color: var(--text-secondary);">Degree:</span>
                                        <p style="color: var(--text-primary);">{{ match.record.degree_earned }}</p>
                                    </div>
                                    
                                    <div>
                                        <span class="text-sm font-medium" style="color: var(--text-secondary);">Campus:</span>
                                        <p style="color: var(--text-primary);">{{ match.record.campus }}</p>
                                    </div>
                                    
                                    <div>
                                        <span class="text-sm font-medium" style="color: var(--text-secondary);">Year Graduated:</span>
                                        <p style="color: var(--text-primary);">{{ match.record.year_graduated }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Matching Details -->
                        <div class="mt-6 p-4 rounded-lg" style="">
                            <h4 class="font-bold mb-3" style="color: var(--primary);">Matching Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div v-if="match.details.name_match" class="flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span style="color: var(--text-primary);">Name matches exactly</span>
                                </div>
                                <div v-if="match.details.student_number_match" class="flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span style="color: var(--text-primary);">Student number matches</span>
                                </div>
                                <div v-if="match.details.degree_match" class="flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span style="color: var(--text-primary);">Degree matches</span>
                                </div>
                                <div v-if="match.details.campus_match" class="flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span style="color: var(--text-primary);">Campus matches</span>
                                </div>
                                <div v-if="match.details.year_match" class="flex items-center">
                                    <svg class="h-5 w-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span style="color: var(--text-primary);">Graduation year matches</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8" style="color: var(--text-secondary);">
                    No potential matches found in alumni records
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style>
:root {
    --primary: #ff8c00;
    --primary-light: rgba(255, 140, 0, 0.1);
    --text-primary: #ffffff;
    --text-secondary: #cccccc;
    --bg-dark: #1a1a1a;
    --bg-darker: #121212;
    --card-bg: rgba(40, 40, 40, 0.7);
    --card-border: rgba(255, 255, 255, 0.1);
}

body {
    background-color: var(--bg-dark);
    color: var(--text-primary);
}

input, textarea, select {
    background-color: var(--card-bg);
    border-color: var(--card-border);
    color: var(--text-primary);
}

input:focus, textarea:focus, select:focus {
    border-color: var(--primary);
    ring-color: var(--primary);
}
</style>