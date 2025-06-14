<template>
  <AdminLayout title="Import Alumni Data">
    <div class="px-4 py-6 sm:px-6 lg:px-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <h1 class="text-xl font-semibold text-gray-900"></h1>
          <p class="mt-2 text-sm text-gray-700">
            Upload an Excel file containing alumni information to import into the system.
          </p>
        </div>
      </div>

      <div class="mt-8 bg-white shadow rounded-lg p-6 card">
        <div 
          class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center"
          :class="{
            'border-blue-500 bg-blue-50': isDragging,
            'border-green-500 bg-green-50': isSuccess
          }"
          @dragover.prevent="isDragging = true"
          @dragleave="isDragging = false"
          @drop.prevent="handleDrop"
        >
          <input
            type="file"
            ref="fileInput"
            class="hidden"
            accept=".xlsx,.xls,.csv"
            @change="handleFileSelect"
          />

          <div v-if="!isUploading && !isSuccess">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <div class="mt-4 flex text-sm ">
              <label for="file-upload" class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                <span>Upload a file</span>
                <input id="file-upload" type="file" class="sr-only" @change="handleFileSelect" accept=".xlsx,.xls,.csv" />
              </label>
              <p class="pl-1">or drag and drop</p>
            </div>
            <p class="text-xs  mt-2">
              Excel (.xlsx, .xls) or CSV files up to 2MB
            </p>
          </div>

          <div v-else-if="isUploading" class="space-y-4">
            <div class="flex justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </div>
            <p class="text-sm text-gray-600">
              Processing your file... Please wait.
            </p>
            <p class="text-xs ">
              {{ uploadProgress }}% completed
            </p>
          </div>

          <div v-else class="space-y-4">
            <div class="flex justify-center">
              <svg class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <p class="text-sm text-green-600 font-medium">
              File imported successfully!
            </p>
            <div class="bg-gray-50 p-4 rounded-md text-left">
              <h3 class="text-sm font-medium text-gray-900">Import Summary</h3>
              <ul class="mt-2 space-y-1 text-sm text-gray-600">
                <li class="flex justify-between">
                  <span>Alumni Records:</span>
                  <span>{{ importStats.alumni || 0 }}</span>
                </li>
                <li class="flex justify-between">
                  <span>Education Records:</span>
                  <span>{{ importStats.education || 0 }}</span>
                </li>
                <li class="flex justify-between">
                  <span>Employment Records:</span>
                  <span>{{ importStats.employment || 0 }}</span>
                </li>
                <li class="flex justify-between">
                  <span>Companies Added:</span>
                  <span>{{ importStats.companies || 0 }}</span>
                </li>
                <li class="flex justify-between">
                  <span>Professional Exams:</span>
                  <span>{{ importStats.exams || 0 }}</span>
                </li>
                <li class="flex justify-between">
                  <span>Program Suggestions:</span>
                  <span>{{ importStats.suggestions || 0 }}</span>
                </li>
              </ul>
            </div>
            <button
              type="button"
              @click="resetForm"
              class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              Import Another File
            </button>
          </div>
        </div>

        <div v-if="errorMessage" class="mt-4 p-4 bg-red-50 rounded-md">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">Error importing file</h3>
              <div class="mt-2 text-sm text-red-700">
                <p>{{ errorMessage }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 instruction">
          <h3 class="text-sm font-medium">Import Instructions</h3>
          <ol class="mt-2 text-sm  list-decimal pl-5 space-y-1">
            <li>Ensure your Excel file follows the required format</li>
            <li>Column headers must match exactly (case insensitive)</li>
            <li>File size should not exceed 2MB</li>
            <li>Only .xlsx, .xls, or .csv files are accepted</li>
            <li>Review data before importing</li>
          </ol>
          <div class="mt-4">
            <a 
              href="/templates/alumni-import-template.xlsx" 
              download
              class="text-sm font-medium text-blue-600 hover:text-blue-500"
            >
              Download Template File
            </a>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const fileInput = ref(null);
const isDragging = ref(false);
const isUploading = ref(false);
const isSuccess = ref(false);
const uploadProgress = ref(0);
const errorMessage = ref('');
const importStats = ref({});

const form = useForm({
  excel_file: null,
});

const handleDrop = (e) => {
  isDragging.value = false;
  if (e.dataTransfer.files.length) {
    form.excel_file = e.dataTransfer.files[0];
    submitForm();
  }
};

const handleFileSelect = (e) => {
  if (e.target.files.length) {
    form.excel_file = e.target.files[0];
    submitForm();
  }
};

const submitForm = () => {
  if (!form.excel_file) return;

  isUploading.value = true;
  errorMessage.value = '';
  isSuccess.value = false;

  form.post(route('admin.alumni.import'), {
    forceFormData: true,
    onProgress: (progress) => {
      uploadProgress.value = Math.round(progress.percentage);
    },
    onSuccess: (response) => {
      isUploading.value = false;
      isSuccess.value = true;
      importStats.value = response.props.stats;
    },
    onError: (errors) => {
      isUploading.value = false;
      errorMessage.value = errors.excel_file || errors.message || 'An error occurred during import.';
    },
  });
};

const resetForm = () => {
  form.reset();
  isSuccess.value = false;
  errorMessage.value = '';
  uploadProgress.value = 0;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};
</script>
<style scoped>
.instruction{
  color: var(--text-primary) !important;
}
</style>