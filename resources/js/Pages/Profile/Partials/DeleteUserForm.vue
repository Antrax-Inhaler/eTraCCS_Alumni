<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>
<template>
    <div class="profile-management-container">
      <div class="profile-card">
        <div class="section-header">
          <h2 class="section-title">Delete Account</h2>
          <p class="section-description">Permanently delete your account.</p>
        </div>
  
        <div class="text-muted">
          Once your account is deleted, all of its resources and data will be permanently deleted.
        </div>
  
        <div class="form-group mt-5">
          <button class="btn btn-danger" @click="confirmUserDeletion">
            Delete Account
          </button>
        </div>
      </div>
    </div>
  </template>
    <style scoped>
    /* Base Variables */
  :root {
    --primary: #ff8c00;
    --primary-light: rgba(255, 140, 0, 0.1);
    --primary-dark: #e67e00;
    --danger: #f44336;
    --danger-light: rgba(244, 67, 54, 0.1);
    --success: #4CAF50;
    --success-light: rgba(76, 175, 80, 0.1);
    --text-primary: #2d3748;
    --text-secondary: #718096;
    --text-light: #f8f9fa;
    --card-bg: rgba(255, 255, 255, 0.9);
    --card-border: rgba(0, 0, 0, 0.1);
    --input-bg: rgba(255, 255, 255, 0.05);
    --section-gap: 1.5rem;
    --border-radius: 0.75rem;
    --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  }
  
  /* Base Styles */
  .profile-management-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: var(--section-gap);
  }
  
  /* Card Styles */
  .profile-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: var(--border-radius);
    padding: 1.5rem;
    box-shadow: var(--box-shadow);
    transition: all 0.3s ease;
  }
  
  .profile-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
  }
  
  /* Section Headers */
  .section-header {
    margin-bottom: 1.5rem;
  }
  
  .section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
  }
  
  .section-description {
    color: var(--text-secondary);
    font-size: 0.95rem;
  }
  
  /* Form Elements */
  .profile-form {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
  }
  
  .form-group {
    margin-bottom: 1.25rem;
  }
  
  .form-label {
    display: block;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-primary);
  }
  
  .form-input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--card-border);
    border-radius: var(--border-radius);
    background: var(--input-bg);
    transition: all 0.2s ease;
  }
  
  .form-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
  }
  
  /* Photo Upload */
  .photo-upload-container {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .photo-preview {
    width: 6rem;
    height: 6rem;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--card-border);
  }
  
  .photo-actions {
    display: flex;
    gap: 0.75rem;
  }
  
  /* Button Styles */
  .btn {
    padding: 0.75rem 1.25rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
  }
  
  .btn-primary {
    background: var(--primary);
    color: white;
  }
  
  .btn-primary:hover {
    background: var(--primary-dark);
  }
  
  .btn-secondary {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    color: var(--text-primary);
  }
  
  .btn-secondary:hover {
    background: rgba(0, 0, 0, 0.05);
  }
  
  .btn-danger {
    background: var(--danger);
    color: white;
  }
  
  .btn-danger:hover {
    background: #d32f2f;
  }
  
  .btn-outline {
    background: transparent;
    border: 1px solid var(--primary);
    color: var(--primary);
  }
  
  .btn-outline:hover {
    background: var(--primary-light);
  }
  
  /* Two Factor Auth */
  .two-factor-section {
    margin-top: 1.5rem;
  }
  
  .qr-code-container {
    padding: 1rem;
    background: white;
    display: inline-block;
    margin: 1rem 0;
    border-radius: var(--border-radius);
  }
  
  .recovery-codes {
    background: rgba(0, 0, 0, 0.05);
    padding: 1rem;
    border-radius: var(--border-radius);
    font-family: monospace;
    margin: 1rem 0;
  }
  
  /* Session Management */
  .session-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--card-border);
  }
  
  .session-icon {
    width: 2rem;
    height: 2rem;
    color: var(--text-secondary);
  }
  
  .session-details {
    flex: 1;
  }
  
  .session-actions {
    margin-top: 1.5rem;
  }
  
  /* Modal Styles */
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
  }
  
  .modal-container {
    background: white;
    border-radius: var(--border-radius);
    width: 90%;
    max-width: 500px;
    box-shadow: var(--box-shadow);
  }
  
  .modal-header {
    padding: 1.25rem;
    border-bottom: 1px solid var(--card-border);
  }
  
  .modal-body {
    padding: 1.25rem;
  }
  
  .modal-footer {
    padding: 1.25rem;
    border-top: 1px solid var(--card-border);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
  }
  
  /* Utility Classes */
  .text-success {
    color: var(--success);
  }
  
  .text-danger {
    color: var(--danger);
  }
  
  .text-muted {
    color: var(--text-secondary);
  }
  
  .mt-1 { margin-top: 0.25rem; }
  .mt-2 { margin-top: 0.5rem; }
  .mt-3 { margin-top: 0.75rem; }
  .mt-4 { margin-top: 1rem; }
  .mt-5 { margin-top: 1.25rem; }
  
  .mb-1 { margin-bottom: 0.25rem; }
  .mb-2 { margin-bottom: 0.5rem; }
  .mb-3 { margin-bottom: 0.75rem; }
  .mb-4 { margin-bottom: 1rem; }
  .mb-5 { margin-bottom: 1.25rem; }
  
  .flex {
    display: flex;
  }
  
  .items-center {
    align-items: center;
  }
  
  .justify-between {
    justify-content: space-between;
  }
  
  .gap-2 {
    gap: 0.5rem;
  }
  
  .gap-4 {
    gap: 1rem;
  }
  
  .hidden {
    display: none;
  }
  
  /* Responsive Adjustments */
  @media (max-width: 768px) {
    .profile-management-container {
      padding: 1rem;
    }
    
    .photo-actions {
      flex-direction: column;
      width: 100%;
    }
    
    .btn {
      width: 100%;
      justify-content: center;
    }
    
    .modal-footer {
      flex-direction: column;
    }
    
    .modal-footer .btn {
      width: 100%;
    }
  }
  </style>