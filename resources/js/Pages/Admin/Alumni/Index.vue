<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { pickBy, throttle } from 'lodash';

const props = defineProps({
    alumni: Object,
    filters: Object,
    batchYears: Array,
    degrees: Array,
});

const search = ref(props.filters.search);
const batchYear = ref(props.filters.batch_year);
const degree = ref(props.filters.degree);
const employmentStatus = ref(props.filters.employment_status);
const verificationStatus = ref(props.filters.verification_status);

const filterAlumni = throttle(() => {
    router.get(route('admin.alumni.index'), pickBy({
        search: search.value,
        batch_year: batchYear.value,
        degree: degree.value,
        employment_status: employmentStatus.value,
        verification_status: verificationStatus.value,
    }), {
        preserveState: true,
        replace: true,
    });
}, 500);

const resetFilters = () => {
    search.value = '';
    batchYear.value = '';
    degree.value = '';
    employmentStatus.value = '';
    verificationStatus.value = '';
    router.get(route('admin.alumni.index'));
};
import CreateModal from '@/Components/Alumni/CreateModal.vue';
import ShowModal from '@/Components/Alumni/ShowModal.vue';
import EditModal from '@/Components/Alumni/EditModal.vue';

const showCreateModal = ref(false);
const showShowModal = ref(false);
const showEditModal = ref(false);
const selectedAlum = ref(null);

const openShowModal = (alum) => {
    selectedAlum.value = alum;
    showShowModal.value = true;
};

const openEditModal = (alum) => {
    selectedAlum.value = alum;
    showEditModal.value = true;
};
</script>

<template>
    <Head title="Manage Alumni" />
    
    <AdminLayout>
        <div class="data-section">
            <div class="section-header">
                <h2 class="section-title">Alumni Management</h2>
                <div class="flex items-center gap-3">
                    <CreateModal 
        :show="showCreateModal" 
        @close="showCreateModal = false"
        :batch-years="batchYears"
        :degrees="degrees"
    />
                    <button @click="resetFilters" class="btn btn-outline">
                        <i class="fas fa-filter-circle-xmark mr-2"></i> Reset Filters
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-container">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input 
                        v-model="search"
                        @input="filterAlumni"
                        type="text" 
                        placeholder="Search by name or email..."
                    >
                </div>

                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Batch Year</label>
                        <select v-model="batchYear" @change="filterAlumni">
                            <option value="">All Years</option>
                            <option v-for="year in batchYears" :value="year">{{ year }}</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Degree</label>
                        <select v-model="degree" @change="filterAlumni">
                            <option value="">All Degrees</option>
                            <option v-for="degree in degrees" :value="degree">{{ degree }}</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Employment</label>
                        <select v-model="employmentStatus" @change="filterAlumni">
                            <option value="">All Statuses</option>
                            <option value="employed">Employed</option>
                            <option value="unemployed">Unemployed</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label>Verification</label>
                        <select v-model="verificationStatus" @change="filterAlumni">
                            <option value="">All Statuses</option>
                            <option value="verified">Verified</option>
                            <option value="unverified">Unverified</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Alumni Table -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Batch Year</th>
                            <th>Degree</th>
                            <th>Employment</th>
                            <th>Verification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="alum in alumni.data" :key="alum.id">
                            <td>
                                <Link :href="route('admin.alumni.show', alum.encrypted_id)">
    {{ alum.first_name }} {{ alum.last_name }}
</Link>
                                <div class="user-cell">
                                    <img :src="alum.profile_photo_url" class="user-avatar" alt="Profile">
                                    <div>
                                        <div class="font-medium">{{ alum.full_name }}</div>
                                        <div class="text-xs text-gray-400">{{ alum.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ alum.educational_backgrounds[0]?.year_graduated || 'N/A' }}
                            </td>
                            <td>
                                {{ alum.educational_backgrounds[0]?.degree_earned || 'N/A' }}
                            </td>
                            <td>
                                <span v-if="alum.employment_histories.length > 0" class="status active">
                                    {{ alum.employment_histories[0].job_title }} at {{ alum.employment_histories[0].company?.name || 'N/A' }}
                                </span>
                                <span v-else class="status inactive">Not specified</span>
                            </td>
                            <td>
                                <span class="status" :class="alum.email_verified_at ? 'active' : 'inactive'">
                                    {{ alum.email_verified_at ? 'Verified' : 'Unverified' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <ShowModal 
        v-if="selectedAlum"
        :show="showShowModal" 
        @close="showShowModal = false"
        :alum="selectedAlum"
    />
    
    <EditModal 
        v-if="selectedAlum"
        :show="showEditModal" 
        @close="showEditModal = false"
        :alum="selectedAlum"
        :batch-years="batchYears"
        :degrees="degrees"
    />
                                    <button v-if="!alum.email_verified_at" 
                                        @click="() => router.post(route('admin.alumni.verify', alum.id))"
                                        class="action-btn text-green-500 hover:text-green-400" 
                                        title="Verify">
                                        <i class="fas fa-check-circle"></i>
                                    </button>
                                    <button v-else
                                        @click="() => router.post(route('admin.alumni.unverify', alum.id))"
                                        class="action-btn text-yellow-500 hover:text-yellow-400" 
                                        title="Unverify">
                                        <i class="fas fa-times-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper" v-if="alumni.links.length > 3">
                <div class="pagination">
                    <template v-for="link in alumni.links">
                        <Link 
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="pagination-link"
                            :class="{
                                'active': link.active,
                                'disabled': !link.url
                            }"
                        />
                        <span v-else v-html="link.label" class="pagination-disabled" />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Filters Container */
.filters-container {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    backdrop-filter: blur(12px);
}

.search-bar {
    position: relative;
    margin-bottom: 15px;
}

.search-bar input {
    width: 100%;
    padding: 10px 15px 10px 40px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 14px;
    transition: all 0.3s;
}

.search-bar input:focus {
    border-color: var(--primary);
    outline: none;
}

.search-bar i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-secondary);
}

.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-size: 13px;
    color: var(--text-secondary);
}

.filter-group select {
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 14px;
    transition: all 0.3s;
}

.filter-group select:focus {
    border-color: var(--primary);
    outline: none;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--card-border);
}

th {
    font-weight: 600;
    color: var(--text-secondary);
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: rgba(255, 255, 255, 0.05);
}

tr:hover td {
    background: rgba(255, 255, 255, 0.03);
}

.user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status.active {
    background: rgba(76, 175, 80, 0.2);
    color: var(--success);
}

.status.inactive {
    background: rgba(244, 67, 54, 0.2);
    color: var(--danger);
}

/* Pagination */
.pagination-wrapper {
    margin-top: 20px;
}

.pagination {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.pagination-link,
.pagination-disabled {
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
}

.pagination-link {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--card-border);
    transition: all 0.2s;
}

.pagination-link:hover {
    background: var(--primary-light);
    border-color: var(--primary);
}

.pagination-link.active {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.pagination-disabled {
    background: transparent;
    color: var(--text-secondary);
}

/* Responsive */
@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    th, td {
        padding: 10px 12px;
    }
}
</style>