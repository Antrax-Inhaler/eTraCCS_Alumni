  
<template>
  <AdminLayout title="Contact Alumni">
  <div class="max-w-5xl mx-auto mt-6 card">
    <!-- Search Box -->
    <div class="mb-5">
      <input
        type="text"
        v-model="searchQuery"
        @input="debouncedSearch"
        placeholder="Search by name or email..."
        class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
      />
    </div>

    <!-- Bulk Actions -->
<div class="flex flex-wrap gap-2 mb-4">
  <button
    @click="toggleAll"
    class="px-4 py-1.5 bg-blue-100 text-blue-700 rounded-full text-sm font-medium hover:bg-blue-200 transition flex items-center gap-1"
  >
    <i class="fas fa-toggle-on"></i>
    Toggle All
  </button>

  <button
    @click="toggleUsers"
    class="px-4 py-1.5 bg-green-100 text-green-700 rounded-full text-sm font-medium hover:bg-green-200 transition flex items-center gap-1"
  >
    <i class="fas fa-user-shield"></i>
    Toggle System Users
  </button>

  <button
    @click="toggleAlumni"
    class="px-4 py-1.5 bg-purple-100 text-purple-700 rounded-full text-sm font-medium hover:bg-purple-200 transition flex items-center gap-1"
  >
    <i class="fas fa-user-graduate"></i>
    Toggle Alumni
  </button>
</div>

 <!-- Compose Message Button -->
    <div class="mt-6 flex justify-end">
      <button
        @click="openModal"
        :disabled="form.recipients.length === 0"
        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
      >
        Compose Message ({{ form.recipients.length }} selected)
      </button>
    </div>
    <br>
    <!-- Recipient Table -->
     <div class="overflow-x-auto" style="color: white !important;">
      <table class="min-w-full table-auto text-sm">
        <thead class=" uppercase text-xs">
          <tr>
            <th class="p-3 border-b text-left"><input type="checkbox" v-model="selectAll" /></th>
            <th class="p-3 border-b text-left">Name</th>
            <th class="p-3 border-b text-left">Email</th>
            <th class="p-3 border-b text-left">Type</th>
            <th class="p-3 border-b text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="recipient in recipients.data"
            :key="recipient.type + '-' + recipient.id"
            class=""
          >
            <td class="p-3 border-t text-center">
              <input
                type="checkbox"
                :value="{ id: recipient.id, type: recipient.type }"
                v-model="form.recipients"
              />
            </td>
            <td class="p-3 border-t">{{ recipient.name }}</td>
            <td class="p-3 border-t">{{ recipient.email }}</td>
            <td class="p-3 border-t capitalize">{{ recipient.type === 'user' ? 'System User' : 'Alumni' }}</td>
            <td class="p-3 border-t text-center space-x-2">
              <button
                @click="openModalForRecipient(recipient)"
                class="text-green-600 hover:text-green-900 hover:underline font-medium"
              >
                Send
              </button>
              <template v-if="recipient.type === 'user'">
                <Link :href="route('user.show', { id: recipient.id })" class="text-blue-500 hover:underline">
                  View
                </Link>
              </template>
              <template v-else-if="recipient.type === 'alumni'">
                <Link :href="route('alumni.show', { id: recipient.id })" class="text-blue-500 hover:underline">
                  View
                </Link>
              </template>
            </td>
          </tr>
          <tr v-if="recipients.data && recipients.data.length === 0">
            <td colspan="5" class="p-4 text-center text-gray-500">No recipients found</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="recipients.links && recipients.links.length > 3" class="mt-6 flex justify-center">
      <nav class="flex items-center space-x-2">
        <template v-for="link in recipients.links" :key="link.label">
          <button
            v-if="link.url"
            v-html="link.label"
            class="px-3 py-1 rounded-xl text-sm font-medium transition"
            :class="{
              'bg-blue-600 text-white shadow-md': link.active,
              'text-gray-600 hover:bg-blue-100': !link.active
            }"
            @click="handlePagination(link.url)"
          />
          <span
            v-else
            v-html="link.label"
            class="px-3 py-1 text-gray-400"
          />
        </template>
      </nav>
    </div>

   

    <!-- Modal -->
 <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center p-6 z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl">
      <div class="p-6">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold text-gray-800">Compose Message</h2>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Recipient Count -->
        <p class="text-sm text-gray-500 mb-4">
          Sending to {{ form.recipients.length }} recipient{{ form.recipients.length !== 1 ? 's' : '' }}
          <span v-if="hasNonSystemAlumni" class="text-blue-600 ml-2">(Includes non-system alumni)</span>
        </p>

        <!-- Template Buttons -->
        <div class="mb-4 grid grid-cols-2 md:grid-cols-4 gap-2">
          <button
            @click="applyTemplate('invitation')"
            class="flex items-center justify-center gap-2 px-3 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition"
          >
            <i class="fas fa-envelope-open-text"></i>
            <span>Invitation</span>
          </button>
          <button
            @click="applyTemplate('welcome')"
            class="flex items-center justify-center gap-2 px-3 py-2 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition"
          >
            <i class="fas fa-handshake"></i>
            <span>Welcome</span>
          </button>
          <button
            @click="applyTemplate('event')"
            class="flex items-center justify-center gap-2 px-3 py-2 bg-purple-50 text-purple-600 rounded-lg hover:bg-purple-100 transition"
          >
            <i class="fas fa-calendar-alt"></i>
            <span>Event</span>
          </button>
          <button
            @click="applyTemplate('update')"
            class="flex items-center justify-center gap-2 px-3 py-2 bg-yellow-50 text-yellow-600 rounded-lg hover:bg-yellow-100 transition"
          >
            <i class="fas fa-bullhorn"></i>
            <span>Update</span>
          </button>
        </div>

        <!-- Message Form -->
        <form @submit.prevent="sendEmail">
          <div class="mb-4">
            <label class="block mb-1 text-gray-700 font-medium">Subject</label>
            <input
              v-model="form.subject"
              type="text"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
              placeholder="Enter subject..."
              required
            />
          </div>

          <div class="mb-6">
            <label class="block mb-1 text-gray-700 font-medium">Message</label>
            <textarea
              v-model="form.message"
              rows="8"
              class="w-full px-4 py-3 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
              placeholder="Type your message here..."
              required
            ></textarea>
          </div>

<div class="flex justify-between items-center gap-3">
  <!-- Left buttons -->
  <div class="flex gap-2">
    <button
      type="button"
      @click="closeModal"
      class="px-4 py-2 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-100 transition"
    >
      Cancel
    </button>
    <button
      type="button"
      @click="clearForm"
      class="px-4 py-2 border border-red-300 rounded-xl text-red-600 hover:bg-red-50 transition"
    >
      Clear
    </button>
  </div>

  <!-- Right button -->
  <button
    type="submit"
    class="px-6 py-2 bg-blue-600 text-white rounded-xl shadow hover:bg-blue-700 transition disabled:bg-blue-300"
    :disabled="form.processing"
  >
    <span v-if="form.processing">Sending...</span>
    <span v-else>Send Message</span>
  </button>
</div>

        </form>
      </div>
    </div>
  </div>
  </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { debounce } from 'lodash';
import AdminLayout from '@/Layouts/AdminLayout.vue';
const form = useForm({
  recipients: [], // Array of {id, type}
  subject: '',
  message: ''
});

const recipients = ref({ data: [] });
const searchQuery = ref('');
const currentPage = ref(1);
const showModal = ref(false);

// Debounced search function
const debouncedSearch = debounce(() => {
  fetchRecipients();
}, 500);

// Fetch recipients with search and pagination
const fetchRecipients = async (page = 1) => {
  try {
    const res = await axios.get('/api/recipients', {
      params: {
        search: searchQuery.value,
        page: page
      }
    });
    recipients.value = res.data.recipients;
  } catch (error) {
    console.error('Error fetching recipients:', error);
  }
};

// Handle pagination
const handlePagination = (url) => {
  const page = new URL(url).searchParams.get('page');
  currentPage.value = page;
  fetchRecipients(page);
};

// Modal controls
const openModal = () => {
  showModal.value = true;
};

const openModalForRecipient = (recipient) => {
  form.recipients = [{ id: recipient.id, type: recipient.type }];
  openModal();
};

const closeModal = () => {
  showModal.value = false;
};

onMounted(() => {
  fetchRecipients();
});

// Toggle all recipients
const selectAll = computed({
  get() {
    return form.recipients.length === recipients.value.data?.length;
  },
  set(value) {
    if (value) {
      form.recipients = recipients.value.data.map(r => ({ id: r.id, type: r.type }));
    } else {
      form.recipients = [];
    }
  }
});

// Toggle only system users
const toggleUsers = () => {
  const users = recipients.value.data?.filter(r => r.type === 'user') || [];
  const selectedUserIds = new Set(form.recipients.filter(r => r.type === 'user').map(r => r.id));

  if (users.every(u => selectedUserIds.has(u.id))) {
    // Unselect all users
    form.recipients = form.recipients.filter(r => r.type !== 'user');
  } else {
    // Select all users not already selected
    const newUsers = users
      .filter(u => !selectedUserIds.has(u.id))
      .map(u => ({ id: u.id, type: 'user' }));

    form.recipients = [...form.recipients, ...newUsers];
  }
};

// Toggle only alumni
const toggleAlumni = () => {
  const alumni = recipients.value.data?.filter(r => r.type === 'alumni') || [];
  const selectedAlumniIds = new Set(form.recipients.filter(r => r.type === 'alumni').map(r => r.id));

  if (alumni.every(a => selectedAlumniIds.has(a.id))) {
    form.recipients = form.recipients.filter(r => r.type !== 'alumni');
  } else {
    const newAlumni = alumni
      .filter(a => !selectedAlumniIds.has(a.id))
      .map(a => ({ id: a.id, type: 'alumni' }));

    form.recipients = [...form.recipients, ...newAlumni];
  }
};

// Toggle all manually
const toggleAll = () => {
  const selectedIds = new Set(form.recipients.map(r => `${r.type}-${r.id}`));
  const total = recipients.value.data?.length || 0;

  if (form.recipients.length === total) {
    form.recipients = [];
  } else {
    form.recipients = recipients.value.data?.map(r => ({ id: r.id, type: r.type })) || [];
  }
};

// Submit Form
const sendEmail = () => {
  form.transform(data => ({
    user_ids: data.recipients.filter(r => r.type === 'user').map(r => r.id),
    alumni_ids: data.recipients.filter(r => r.type === 'alumni').map(r => r.id),
    subject: data.subject,
    message: data.message
  })).post(route('email.send'), {
    onSuccess: () => {
      closeModal();
      form.reset();
    }
  });
};

const viewDetails = (recipient) => {
  if (recipient.type === 'user') {
    router.get(route('user.show', { id: recipient.id }));
  } else if (recipient.type === 'alumni') {
    router.get(route('alumni.show', { id: recipient.id }));
  }
};



// Check if any recipients are non-system alumni
const hasNonSystemAlumni = computed(() => {
  return form.recipients.some(r => r.type === 'alumni');
});

// Message templates
const messageTemplates = {
  invitation: {
    subject: 'Invitation to Join Our Alumni Portal',
    message: `Dear [Recipient's Name],

We're excited to invite you to join our official alumni portal! As a valued graduate of [Institution Name], we'd love to stay connected with you.

By joining our portal, you'll be able to:
- Connect with fellow alumni
- Stay updated on upcoming events
- Access exclusive resources
- Share your professional achievements

Click here to register: [Registration Link]

We look forward to welcoming you to our alumni community!

Best regards,
[Your Name]
[Your Position]
[Institution Name]`
  },
  welcome: {
    subject: 'Welcome to Our Alumni Community',
    message: `Dear [Recipient's Name],

Welcome to our alumni community! We're thrilled to have you as part of our growing network of accomplished graduates.

As a member, you'll enjoy:
- Networking opportunities with fellow alumni
- Access to career resources
- Invitations to exclusive events
- Updates on institutional developments

Feel free to update your profile and explore the platform. If you have any questions, don't hesitate to reach out.

Warm regards,
[Your Name]
[Your Position]
[Institution Name]`
  },
  event: {
    subject: 'Invitation: [Event Name]',
    message: `Dear [Recipient's Name],

You're cordially invited to attend our upcoming event:

Event: [Event Name]
Date: [Event Date]
Time: [Event Time]
Location: [Event Location/Virtual Link]

This special gathering will feature [brief description of event highlights]. It's a wonderful opportunity to reconnect with old classmates and make new connections.

Please RSVP by [RSVP Deadline] using this link: [RSVP Link]

We hope to see you there!

Best regards,
[Your Name]
[Event Organizing Committee]
[Institution Name]`
  },
  update: {
    subject: 'Important Alumni Network Update',
    message: `Dear [Recipient's Name],

We're writing to share some important updates from our alumni network:

1. [Update Point 1]
2. [Update Point 2]
3. [Update Point 3]

Additionally, we'd love to hear about your recent accomplishments! Please consider sharing your news with us by updating your profile or replying to this email.

Stay connected with us on [Social Media Links] for more frequent updates.

Thank you for being an active member of our alumni community!

Sincerely,
[Your Name]
[Your Position]
[Institution Name]`
  }
};

// Apply selected template
const applyTemplate = (templateKey) => {
  const template = messageTemplates[templateKey];
  form.subject = template.subject;
  form.message = template.message;
  
  // Special handling for non-system alumni
  if (hasNonSystemAlumni.value && templateKey === 'invitation') {
    form.message = form.message.replace('[Registration Link]', 'https://yourinstitution.edu/alumni/register');
  }
};
function clearForm() {
  form.subject = ''
  form.message = ''
}
</script>