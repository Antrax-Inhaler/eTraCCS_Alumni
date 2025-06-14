<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const currentStep = ref(1);
const form = useForm({
    first_name: '',
    last_name: '',
    middle_initial: '',
    name: '',
    email: '',
    gender: '',
    password: '',
    password_confirmation: '',
    profile_photo_path: null,
    cover_photo: null,
    terms: false,
});

const profilePhotoPreview = ref(null);
const coverPhotoPreview = ref(null);

const passwordsMatch = computed(() => {
    return form.password === form.password_confirmation && form.password !== '';
});

const nextStep = () => {
    if (currentStep.value === 1 && hasSpacesInUsername.value) {
        return; // Don't proceed if username has spaces
    }
    currentStep.value++;
};

const prevStep = () => {
    currentStep.value--;
};

const hasSpacesInUsername = computed(() => {
    return form.name.includes(' ');
});

const onProfilePhotoChange = (event) => {
    const file = event.target.files[0];
    form.profile_photo_path = file;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        profilePhotoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const onCoverPhotoChange = (event) => {
    const file = event.target.files[0];
    form.cover_photo = file;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        coverPhotoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const isStepActive = (step) => {
    return currentStep.value === step;
};

const isStepCompleted = (step) => {
    return currentStep.value > step;
};

const stepClasses = (step) => {
    return {
        'step': true,
        'active': isStepActive(step),
        'completed': isStepCompleted(step),
    };
};
</script>

<template>
    <Head title="Register" />
    <div class="container">
        <div class="register-container">
            <div class="register-header">
                <h2 class="register-title">Create your account</h2>
                <p class="register-subtitle">Join our community today</p>
            </div>

            <div class="form-container">
                <div class="progress-steps">
                    <div v-for="step in 3" :key="step" 
                         :class="stepClasses(step)" 
                         :data-step="step"></div>
                </div>

                <!-- Step 1: Personal Information -->
                <form v-show="currentStep === 1" @submit.prevent="nextStep" class="form-slide">
                    <div class="name-fields">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="form.first_name"
                                required
                                autofocus
                                autocomplete="given-name"
                            />
                            <InputError class="error-message" :message="form.errors.first_name" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="form.last_name"
                                required
                                autocomplete="family-name"
                            />
                            <InputError class="error-message" :message="form.errors.last_name" />
                        </div>
                        <div class="form-group">
                            <label class="form-label">MI</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="form.middle_initial"
                                maxlength="1"
                                autocomplete="additional-name"
                            />
                            <InputError class="error-message" :message="form.errors.middle_initial" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input
                            type="text"
                            class="form-control"
                            v-model="form.name"
                            required
                            autocomplete="name"
                            @input="form.name = form.name.replace(/\s/g, '')"
                        />
                        <template v-if="form.errors.name">
                            <InputError class="error-message" :message="form.errors.name" />
                        </template>
                        <template v-else-if="hasSpacesInUsername">
                            <div class="error-message">Username cannot contain spaces</div>
                        </template>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            v-model="form.email"
                            required
                            autocomplete="email"
                        />
                        <InputError class="error-message" :message="form.errors.email" />
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Gender</label>
                        <select
                            class="form-control"
                            v-model="form.gender"
                            required
                        >
                            <option value="" disabled>Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                        <InputError class="error-message" :message="form.errors.gender" />
                    </div>
                    
                    <div class="form-footer">
                        <div></div>
                        <PrimaryButton class="btn btn-primary" type="submit">
                            Continue
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Step 2: Account Security & Photos -->
                <form v-show="currentStep === 2" @submit.prevent="nextStep" class="form-slide">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input
                            type="password"
                            class="form-control"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        />
                        <InputError class="error-message" :message="form.errors.password" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input
                            type="password"
                            class="form-control"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                        <InputError class="error-message" :message="form.errors.password_confirmation" />
                        <div v-if="form.password_confirmation && !passwordsMatch" class="error-message">
                            Passwords do not match
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Profile Photo (Optional)</label>
                        <div class="file-upload">
                            <img v-if="profilePhotoPreview" :src="profilePhotoPreview" class="preview-image" alt="Profile preview">
                            <i v-else class="fas fa-camera"></i>
                            <span>{{ profilePhotoPreview ? 'Change photo' : 'Click to upload profile photo' }}</span>
                            <input
                                type="file"
                                @change="onProfilePhotoChange"
                                accept="image/*"
                            />
                        </div>
                        <InputError class="error-message" :message="form.errors.profile_photo_path" />
                    </div>

                    <div class="form-group">
                        <label class="form-label">Cover Photo (Optional)</label>
                        <label class="file-upload">
                            <img v-if="coverPhotoPreview" :src="coverPhotoPreview" class="preview-image rectangle" alt="Cover preview">
                            <i v-else class="fas fa-image"></i>
                            <span>{{ coverPhotoPreview ? 'Change photo' : 'Click to upload cover photo' }}</span>
                            <input
                                type="file"
                                @change="onCoverPhotoChange"
                                accept="image/*"
                            />
                        </label>
                        <InputError class="error-message" :message="form.errors.cover_photo" />
                    </div>
                    
                    <div class="form-footer">
                        <button type="button" class="btn btn-outline" @click="prevStep">
                            Back
                        </button>
                        <PrimaryButton class="btn btn-primary" type="submit" :disabled="!passwordsMatch">
                            Continue
                        </PrimaryButton>
                    </div>
                </form>

                <!-- Step 3: Terms & Final Step -->
                <form v-show="currentStep === 3" @submit.prevent="submit" class="form-slide">
                    <div class="terms-checkbox">
                        <input
                            type="checkbox"
                            id="terms"
                            v-model="form.terms"
                            required
                        />
                        <label for="terms" class="terms-text">
                            I agree to the <a :href="route('terms.show')" target="_blank" class="underline">Terms of Service</a> and 
                            <a :href="route('policy.show')" target="_blank" class="underline">Privacy Policy</a>.
                            I understand that my data will be processed in accordance with these policies.
                        </label>
                    </div>
                    <InputError class="error-message" :message="form.errors.terms" />

                    <div class="form-footer">
                        <button type="button" class="btn btn-outline" @click="prevStep">
                            Back
                        </button>
                        <PrimaryButton class="btn btn-primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Create Account
                        </PrimaryButton>
                    </div>
                </form>

                <div class="login-link">
                    Already have an account? <Link :href="route('login')" class="underline">Log in</Link>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
:root {
    --primary: #ff8c00;
    --primary-light: rgba(255, 140, 0, 0.1);
    --text-primary: #ffffff;
    --text-secondary: #cccccc;
    --bg-dark: #1a1a1a;
    --bg-darker: #121212;
    --card-bg: rgba(40, 40, 40, 0.7);
    --card-border: rgba(255, 255, 255, 0.1);
    --success: #4CAF50;
    --error: #F44336;
}
.body{
    align-items: center;
}
.container{
    display:flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
}
.register-container {
    width: 100%;
    max-width: 500px;
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.register-header {
    padding: 30px;
    text-align: center;
    border-bottom: 1px solid var(--card-border);
    position: relative;
}

.logo {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 10px;
}

.register-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.register-subtitle {
    color: var(--text-secondary);
    font-size: 14px;
}

.form-container {
    padding: 30px;
    position: relative;
    overflow: hidden;
}

.form-slide {
    transition: transform 0.5s ease-in-out;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: var(--text-primary);
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255,255,255,0.1);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 16px;
    transition: all 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(255, 140, 0, 0.2);
}

.name-fields {
    display: flex;
    gap: 15px;
}

.name-fields .form-group {
    flex: 1;
}

.name-fields .form-group:last-child {
    max-width: 60px;
}

.file-upload {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    border: 2px dashed var(--card-border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    text-align: center;
    position: relative;
}

.file-upload:hover {
    border-color: var(--primary);
    background: rgba(255, 140, 0, 0.05);
}

.file-upload i {
    font-size: 40px;
    color: var(--primary);
    margin-bottom: 10px;
}

.file-upload input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    cursor: pointer;
}
.rectangle{
    border-radius: 2px !important;
    width: 80% !important;
}

.preview-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 10px;
}

.form-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid var(--card-border);
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: #e67e00;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 140, 0, 0.3);
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--primary);
    color: var(--primary);
}

.btn-outline:hover {
    background: var(--primary-light);
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

.progress-steps {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 30px;
}

.step {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    transition: all 0.3s;
}

.step.active, .step.completed {
    background: var(--primary);
}

.step.active {
    width: 20px;
    border-radius: 5px;
}

.terms-checkbox {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-top: 20px;
}

.terms-checkbox input {
    margin-top: 3px;
}

.terms-text {
    font-size: 14px;
    color: var(--text-secondary);
}

.terms-text a {
    color: var(--primary);
    text-decoration: none;
}

.terms-text a:hover {
    text-decoration: underline;
}

.login-link {
    text-align: center;
    margin-top: 20px;
    color: var(--text-secondary);
}

.login-link a {
    color: var(--primary);
    text-decoration: none;
}

.login-link a:hover {
    text-decoration: underline;
}

.error-message {
    color: var(--error);
    font-size: 13px;
    margin-top: 5px;
}

.hidden {
    display: none;
}

@media (max-width: 576px) {
    .register-container {
        border-radius: 12px;
    }
    
    .register-header, .form-container {
        padding: 20px;
    }
    
    .name-fields {
        flex-direction: column;
        gap: 20px;
    }
    
    .name-fields .form-group:last-child {
        max-width: 100%;
    }
    .container{
        height: auto;
    }
}
option {
      background-color: var(--bg-dark);
      color: var(--text-secondary);
    }

    /* Optional: style on focus */
    select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 5px var(--primary-light);
    }
    .additional-degree {
    padding: 4px 0;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

</style>