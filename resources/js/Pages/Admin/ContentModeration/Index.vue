<template>
  <AdminLayout title="Content Moderation">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Content Moderation Dashboard
      </h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
          <div class="p-6">
            <!-- Tabs -->
            <div class="mb-6 border-b border-gray-200">
              <nav class="-mb-px flex space-x-8">
                <button
                  @click="activeTab = 'content'"
                  :class="{
                    'border-blue-500 text-blue-600': activeTab === 'content',
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'content'
                  }"
                  class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                >
                  Content Items
                </button>
                <button
                  @click="activeTab = 'comments'"
                  :class="{
                    'border-blue-500 text-blue-600': activeTab === 'comments',
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'comments'
                  }"
                  class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"
                >
                  Comments
                </button>
              </nav>
            </div>

            <!-- Content Items Tab -->
            <div v-show="activeTab === 'content'">
              <div class="flex justify-between items-center mb-4">
                <div class="flex space-x-2">
                  <select
                    v-model="filters.type"
                    @change="updateFilters"
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  >
                    <option value="">All Types</option>
                    <option value="post">Posts</option>
                    <option value="event">Events</option>
                    <option value="job_posting">Job Postings</option>
                    <option value="story">Stories</option>
                  </select>
                  <select
                    v-model="filters.status"
                    @change="updateFilters"
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  >
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="restricted">Restricted</option>
                  </select>
                </div>
                <div class="w-64">
                  <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search content..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    @input="debounceUpdateFilters"
                  >
                </div>
              </div>

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input
                          type="checkbox"
                          v-model="selectAllContent"
                          class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Title
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Author
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Type
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Created
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="item in content_items.data" :key="item.id">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <input
                          type="checkbox"
                          v-model="selectedContent"
                          :value="item.id"
                          class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ item.title || 'No title' }}</div>
                        <div class="text-sm text-gray-500">{{ item.excerpt }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900" v-if="item.alum">
                          <Link :href="route('admin.users.show', item.alum.id)" class="text-blue-600 hover:text-blue-900">
                            {{ item.alum.name }}
                          </Link>
                        </div>
                        <div class="text-sm text-gray-500" v-else>Unknown</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize" :class="typeBadgeClass(item.type)">
                          {{ item.type }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize" :class="statusBadgeClass(item.status)">
                          {{ item.status || 'pending' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ item.created_at }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <select
                          @change="updateContentStatus(item.id, $event.target.value)"
                          class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm"
                          :value="item.status || 'pending'"
                        >
                          <option value="pending">Pending</option>
                          <option value="approved">Approve</option>
                          <option value="rejected">Reject</option>
                          <option value="restricted">Restrict</option>
                        </select>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="mt-4 flex justify-between items-center">
                <div>
                  <button
                    v-if="selectedContent.length > 0"
                    @click="showBulkContentActionModal = true"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                  >
                    Bulk Actions ({{ selectedContent.length }})
                  </button>
                </div>
                <Pagination :links="content_items.links" />
              </div>
            </div>

            <!-- Comments Tab -->
            <div v-show="activeTab === 'comments'">
              <div class="flex justify-between items-center mb-4">
                <div class="flex space-x-2">
                  <select
                    v-model="filters.comment_status"
                    @change="updateFilters"
                    class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                  >
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                    <option value="deleted">Deleted</option>
                  </select>
                </div>
                <div class="w-64">
                  <input
                    v-model="filters.comment_search"
                    type="text"
                    placeholder="Search comments..."
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                    @input="debounceUpdateFilters"
                  >
                </div>
              </div>

              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input
                          type="checkbox"
                          v-model="selectAllComments"
                          class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Comment
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Author
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        On Content
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Created
                      </th>
                      <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="comment in comments.data" :key="comment.id">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <input
                          type="checkbox"
                          v-model="selectedComments"
                          :value="comment.id"
                          class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                        >
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">{{ comment.content }}</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900" v-if="comment.alum">
                          <Link :href="route('admin.users.show', comment.alum.id)" class="text-blue-600 hover:text-blue-900">
                            {{ comment.alum.name }}
                          </Link>
                        </div>
                        <div class="text-sm text-gray-500" v-else>Unknown</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900" v-if="comment.content_item">
                          <!-- <Link :href="route('admin.content-moderation.show', comment.content_item.id)" class="text-blue-600 hover:text-blue-900">
                            {{ comment.content_item.title || 'No title' }}
                          </Link> -->
                        </div>
                        <div class="text-sm text-gray-500" v-else>Unknown</div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize" :class="statusBadgeClass(comment.status)">
                          {{ comment.status || 'pending' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ comment.created_at }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <select
                          @change="updateCommentStatus(comment.id, $event.target.value)"
                          class="rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm"
                          :value="comment.status || 'pending'"
                        >
                          <option value="pending">Pending</option>
                          <option value="approved">Approve</option>
                          <option value="rejected">Reject</option>
                          <option value="deleted">Delete</option>
                        </select>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="mt-4 flex justify-between items-center">
                <div>
                  <button
                    v-if="selectedComments.length > 0"
                    @click="showBulkCommentActionModal = true"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                  >
                    Bulk Actions ({{ selectedComments.length }})
                  </button>
                </div>
                <Pagination :links="comments.links" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bulk Content Action Modal -->
    <Modal :show="showBulkContentActionModal" @close="showBulkContentActionModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">
          Apply Bulk Action to {{ selectedContent.length }} Content Items
        </h2>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Action</label>
          <select
            v-model="bulkContentAction"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
          >
            <option value="approved">Approve</option>
            <option value="rejected">Reject</option>
            <option value="restricted">Restrict</option>
          </select>
        </div>

        <div class="flex justify-end">
          <button
            type="button"
            @click="showBulkContentActionModal = false"
            class="mr-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="applyBulkContentAction"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
          >
            Apply Action
          </button>
        </div>
      </div>
    </Modal>

    <!-- Bulk Comment Action Modal -->
    <Modal :show="showBulkCommentActionModal" @close="showBulkCommentActionModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">
          Apply Bulk Action to {{ selectedComments.length }} Comments
        </h2>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700">Action</label>
          <select
            v-model="bulkCommentAction"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
          >
            <option value="approved">Approve</option>
            <option value="rejected">Reject</option>
            <option value="deleted">Delete</option>
          </select>
        </div>

        <div class="flex justify-end">
          <button
            type="button"
            @click="showBulkCommentActionModal = false"
            class="mr-2 inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring focus:ring-blue-200 active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="applyBulkCommentAction"
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
          >
            Apply Action
          </button>
        </div>
      </div>
    </Modal>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash';

const props = defineProps({
  content_items: Object,
  comments: Object,
  filters: Object
});

const activeTab = ref('content');
const selectedContent = ref([]);
const selectedComments = ref([]);
const showBulkContentActionModal = ref(false);
const showBulkCommentActionModal = ref(false);
const bulkContentAction = ref('approved');
const bulkCommentAction = ref('approved');
const filters = ref({
  search: props.filters.search || '',
  type: props.filters.type || '',
  status: props.filters.status || '',
  comment_search: props.filters.comment_search || '',
  comment_status: props.filters.comment_status || ''
});

// Debounced filter update
const debounceUpdateFilters = debounce(() => {
  updateFilters();
}, 500);

const updateFilters = () => {
  router.get(route('admin.content-moderation.index'), filters.value, {
    preserveState: true,
    replace: true
  });
};

// Select all content items
const selectAllContent = computed({
  get: () => {
    return selectedContent.value.length === props.content_items.data.length && 
           props.content_items.data.length > 0;
  },
  set: (value) => {
    selectedContent.value = value 
      ? props.content_items.data.map(item => item.id) 
      : [];
  }
});

// Select all comments
const selectAllComments = computed({
  get: () => {
    return selectedComments.value.length === props.comments.data.length && 
           props.comments.data.length > 0;
  },
  set: (value) => {
    selectedComments.value = value 
      ? props.comments.data.map(comment => comment.id) 
      : [];
  }
});

// Update content status
const updateContentStatus = (id, status) => {
  router.patch(route('admin.content-moderation.update-content', id), { status }, {
    preserveScroll: true,
    onSuccess: () => {
      // Remove from selected if needed
      const index = selectedContent.value.indexOf(id);
      if (index > -1) {
        selectedContent.value.splice(index, 1);
      }
    }
  });
};

// Update comment status
const updateCommentStatus = (id, status) => {
  router.patch(route('admin.content-moderation.update-comment', id), { status }, {
    preserveScroll: true,
    onSuccess: () => {
      // Remove from selected if needed
      const index = selectedComments.value.indexOf(id);
      if (index > -1) {
        selectedComments.value.splice(index, 1);
      }
    }
  });
};

// Apply bulk content action
const applyBulkContentAction = () => {
  router.post(route('admin.content-moderation.bulk-content'), {
    ids: selectedContent.value,
    status: bulkContentAction.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedContent.value = [];
      showBulkContentActionModal.value = false;
    }
  });
};

// Apply bulk comment action
const applyBulkCommentAction = () => {
  router.post(route('admin.content-moderation.bulk-comments'), {
    ids: selectedComments.value,
    status: bulkCommentAction.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      selectedComments.value = [];
      showBulkCommentActionModal.value = false;
    }
  });
};

// Badge styling
const typeBadgeClass = (type) => {
  const classes = {
    post: 'bg-blue-100 text-blue-800',
    event: 'bg-purple-100 text-purple-800',
    job_posting: 'bg-green-100 text-green-800',
    story: 'bg-yellow-100 text-yellow-800'
  };
  return classes[type] || 'bg-gray-100 text-gray-800';
};

const statusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    restricted: 'bg-orange-100 text-orange-800',
    deleted: 'bg-gray-100 text-gray-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<style scoped>
/* Custom styles for the content moderation interface */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-container {
  background-color: white;
  border-radius: 0.5rem;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.modal-header {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: #111827;
}

.modal-close {
  font-size: 1.5rem;
  color: #6b7280;
  cursor: pointer;
  background: none;
  border: none;
}

.modal-body {
  padding: 1rem;
}

.modal-footer {
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
}

/* Status badges */
.badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
}

/* Loading spinner */
.loading-spinner {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1rem;
  color: #3b82f6;
}

.loading-spinner i {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .modal-container {
    width: 95%;
  }
  
  table {
    display: block;
    overflow-x: auto;
    white-space: nowrap;
  }
}
</style>