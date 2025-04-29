<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import ConfirmsPassword from '@/Components/ConfirmsPassword.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    requiresConfirmation: Boolean,
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(
    () => ! enabling.value && page.props.auth.user?.two_factor_enabled,
);

watch(twoFactorEnabled, () => {
    if (! twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then(response => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key')).then(response => {
        setupKey.value = response.data.secretKey;
    });
}

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then(response => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: "confirmTwoFactorAuthentication",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios
        .post(route('two-factor.recovery-codes'))
        .then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>
<template>
    <div class="profile-management-container">
      <div class="profile-card">
        <div class="section-header">
          <h2 class="section-title">Two Factor Authentication</h2>
          <p class="section-description">Add additional security to your account using two factor authentication.</p>
        </div>
  
        <div class="two-factor-section">
          <h3 v-if="twoFactorEnabled && !confirming">
            You have enabled two factor authentication.
          </h3>
          <h3 v-else-if="twoFactorEnabled && confirming">
            Finish enabling two factor authentication.
          </h3>
          <h3 v-else>
            You have not enabled two factor authentication.
          </h3>
  
          <p class="text-muted mt-3">
            When two factor authentication is enabled, you will be prompted for a secure, random token during authentication.
          </p>
  
          <div v-if="twoFactorEnabled">
            <div v-if="qrCode">
              <p v-if="confirming" class="text-muted mt-4">
                To finish enabling two factor authentication, scan the following QR code.
              </p>
              <p v-else class="text-muted mt-4">
                Two factor authentication is now enabled. Scan the following QR code.
              </p>
  
              <div class="qr-code-container" v-html="qrCode"></div>
  
              <div v-if="setupKey" class="text-muted mt-4">
                <p>Setup Key: <span v-html="setupKey"></span></p>
              </div>
  
              <div v-if="confirming" class="form-group mt-4">
                <label for="code" class="form-label">Code</label>
                <input id="code" v-model="confirmationForm.code" type="text" class="form-input" autofocus>
              </div>
            </div>
  
            <div v-if="recoveryCodes.length > 0 && !confirming" class="mt-4">
              <p class="text-muted">
                Store these recovery codes in a secure password manager.
              </p>
              <div class="recovery-codes">
                <div v-for="code in recoveryCodes" :key="code">{{ code }}</div>
              </div>
            </div>
          </div>
  
          <div class="form-group mt-5">
            <div v-if="!twoFactorEnabled">
              <button type="button" class="btn btn-primary" @click="enableTwoFactorAuthentication" :disabled="enabling">
                Enable
              </button>
            </div>
            <div v-else class="flex gap-4">
              <button v-if="confirming" type="button" class="btn btn-primary" @click="confirmTwoFactorAuthentication" :disabled="enabling">
                Confirm
              </button>
              <button v-if="recoveryCodes.length > 0 && !confirming" type="button" class="btn btn-secondary" @click="regenerateRecoveryCodes">
                Regenerate Codes
              </button>
              <button v-if="recoveryCodes.length === 0 && !confirming" type="button" class="btn btn-secondary" @click="showRecoveryCodes">
                Show Codes
              </button>
              <button v-if="confirming" type="button" class="btn btn-secondary" @click="cancelTwoFactorAuthentication">
                Cancel
              </button>
              <button type="button" class="btn btn-danger" @click="disableTwoFactorAuthentication" :disabled="disabling">
                Disable
              </button>
            </div>
          </div>
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