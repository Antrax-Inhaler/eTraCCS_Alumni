<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    alumni: Object,
    filters: Object,
    batchYears: Array,
    demographics: Object
});

const search = ref(props.filters.search);
const batchYear = ref(props.filters.batch_year);

const filter = () => {
    router.get(route('admin.alumni.index'), {
        search: search.value,
        batch_year: batchYear.value
    }, {
        preserveState: true,
        replace: true
    });
};

const unverifyAlumni = (userId) => {
    if (confirm('Are you sure you want to unverify this alumni?')) {
        router.post(route('admin.alumni.unverify', userId), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Alumni Management" />
    
    <AdminLayout>
        <div class="main-content">
            <h1 class="page-title">Alumni Management</h1>

            <!-- Filters Section -->
            <div class="data-section">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label>Search Alumni</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by name or email..."
                            @keyup.enter="filter"
                        />
                    </div>
                    <div class="filter-group">
                        <label>Batch Year</label>
                        <select v-model="batchYear" @change="filter">
                            <option value="">All Batch Years</option>
                            <option v-for="year in batchYears" :value="year">{{ year }}</option>
                        </select>
                    </div>
                    <button @click="filter" class="btn btn-primary">
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Demographic Analytics -->
            <div class="stats-cards">
                <div class="stat-card">
                    <h3 class="stat-title">Gender Distribution</h3>
                    <div v-for="item in demographics.gender" :key="item.gender" class="stat-item">
                        <div class="stat-label">
                            {{ item.gender || 'Not specified' }}
                            <span class="stat-percent">{{ Math.round((item.count / alumni.total) * 100) }}%</span>
                        </div>
                        <div class="stat-bar">
                            <div 
                                class="stat-bar-fill" 
                                :style="{ width: `${(item.count / alumni.total) * 100}%` }"
                            ></div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <h3 class="stat-title">Top Countries</h3>
                    <div v-for="item in demographics.location" :key="item.country" class="stat-item">
                        <div class="stat-label">
                            {{ item.country }}
                            <span class="stat-count">{{ item.count }}</span>
                        </div>
                        <div class="stat-bar">
                            <div 
                                class="stat-bar-fill" 
                                :style="{ width: `${(item.count / alumni.total) * 100}%` }"
                            ></div>
                        </div>
                    </div>
                </div>

                <div class="stat-card">
                    <h3 class="stat-title">Batch Distribution</h3>
                    <div v-for="item in demographics.batch_distribution" :key="item.year_graduated" class="stat-item">
                        <div class="stat-label">
                            {{ item.year_graduated }}
                            <span class="stat-count">{{ item.count }}</span>
                        </div>
                        <div class="stat-bar">
                            <div 
                                class="stat-bar-fill" 
                                :style="{ width: `${(item.count / alumni.total) * 100}%` }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alumni Table -->
            <div class="data-section">
                <div class="section-header">
                    <h2 class="section-title">Alumni Records</h2>
                    <div class="results-count">
                        Showing {{ alumni.from }} to {{ alumni.to }} of {{ alumni.total }} alumni
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Batch Year</th>
                                <th>Contact</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="alum in alumni.data" :key="alum.id">
                                <td>
                                    <div class="user-cell">
                                        <img class="user-avatar" :src="alum.profile_photo_url" alt="">
                                        <div>
                                            <div class="user-name">{{ alum.first_name }} {{ alum.last_name }}</div>
                                            <div class="user-email">{{ alum.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ alum.educational_backgrounds[0]?.year_graduated || 'N/A' }}
                                </td>
                                <td>
                                    {{ alum.email }}
                                </td>
                                <td>
                                    <span v-if="alum.alumni_location">
                                        {{ alum.alumni_location.city }}, {{ alum.alumni_location.country }}
                                    </span>
                                    <span v-else class="text-muted">
                                        Not specified
                                    </span>
                                </td>
                                <td>
                                    <span class="status" :class="alum.is_verified ? 'active' : 'inactive'">
                                        {{ alum.is_verified ? 'Verified' : 'Unverified' }}
                                    </span>
                                </td>
                                <td>
                                    <button 
                                        @click="unverifyAlumni(alum.id)"
                                        class="action-btn danger"
                                        v-if="alum.is_verified"
                                    >
                                        <i class="fas fa-times-circle"></i> Unverify
                                    </button>
                                    <!-- <Link 
                                        :href="route('admin.alumni.show', alum.id)" 
                                        class="action-btn"
                                    >
                                        <i class="fas fa-eye"></i> View
                                    </Link> -->
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <div class="pagination-info">
                        Page {{ alumni.current_page }} of {{ alumni.last_page }}
                    </div>
                    <div class="pagination-links">
                        <template v-for="(link, index) in alumni.links" :key="index">
                            <Link 
                                v-if="link.url"
                                :href="link.url"
                                v-html="link.label"
                                class="pagination-link"
                                :class="{ active: link.active }"
                            />
                            <span v-else v-html="link.label" class="pagination-disabled"></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* Main Content */
.main-content {
    overflow-y: auto;
}

.page-title {
    font-size: 24px;
    margin-bottom: 20px;
    color: var(--text-primary);
}

/* Data Sections */
.data-section {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    backdrop-filter: blur(12px);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
}

.results-count {
    font-size: 13px;
    color: var(--text-secondary);
}

/* Filter Grid */
.filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.filter-group input,
.filter-group select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    padding: 8px 12px;
    color: var(--text-primary);
    width: 100%;
}

.filter-group input::placeholder {
    color: var(--text-secondary);
    opacity: 0.7;
}

/* Stats Cards */
.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: rgba(40, 40, 40, 0.5);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 15px;
}

.stat-title {
    font-size: 16px;
    margin-bottom: 15px;
    color: var(--text-primary);
}

.stat-item {
    margin-bottom: 12px;
}

.stat-label {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    margin-bottom: 5px;
    color: var(--text-secondary);
}

.stat-percent, .stat-count {
    color: var(--text-primary);
    font-weight: 500;
}

.stat-bar {
    width: 100%;
    height: 4px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 2px;
    overflow: hidden;
}

.stat-bar-fill {
    height: 100%;
    background: var(--primary);
    border-radius: 2px;
}

/* Tables */
.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    color: var(--text-secondary);
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
}

tr:hover td {
    background: rgba(255, 255, 255, 0.05);
}

/* User Cells */
.user-cell {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.user-name {
    font-weight: 500;
    color: var(--text-primary);
}

.user-email {
    font-size: 12px;
    color: var(--text-secondary);
}

.text-muted {
    color: var(--text-secondary);
    font-style: italic;
}

/* Status Badges */
.status {
    display: inline-block;
    padding: 4px 8px;
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

/* Action Buttons */
.action-btn {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 13px;
    padding: 4px 8px;
    border-radius: 4px;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.1);
}

.action-btn.danger {
    color: var(--danger);
}

.action-btn.danger:hover {
    background: rgba(244, 67, 54, 0.1);
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid var(--card-border);
}

.pagination-info {
    font-size: 13px;
    color: var(--text-secondary);
}

.pagination-links {
    display: flex;
    gap: 5px;
}

.pagination-link,
.pagination-disabled {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 13px;
}

.pagination-link {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border: 1px solid var(--card-border);
}

.pagination-link:hover {
    background: rgba(255, 255, 255, 0.2);
}

.pagination-link.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.pagination-disabled {
    color: var(--text-secondary);
}

/* Responsive */
@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-cards {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .pagination {
        flex-direction: column;
        gap: 10px;
    }
}
</style>