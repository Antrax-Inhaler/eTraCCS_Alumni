<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import Button from '@/Components/Button.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';

const props = defineProps({
    show: Boolean,
    batchYears: Array,
    degrees: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
    first_name: '',
    last_name: '',
    middle_initial: '',
    email: '',
    password: '',
    year_graduated: '',
    degree_earned: '',
    profile_photo: null,
});

const submit = () => {
    form.post(route('admin.alumni.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
            form.reset();
        },
    });
};
</script>

<template>
    <Modal :show="show" @close="emit('close')" max-width="2xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Alumni</h3>
                <button @click="emit('close')" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <form @submit.prevent="submit">
                    <div class="form-grid">
                        <div class="form-group">
                            <InputLabel for="first_name" value="First Name" />
                            <TextInput
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.first_name" />
                        </div>

                        <div class="form-group">
                            <InputLabel for="last_name" value="Last Name" />
                            <TextInput
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                required
                            />
                            <InputError :message="form.errors.last_name" />
                        </div>

                        <div class="form-group">
                            <InputLabel for="middle_initial" value="Middle Initial" />
                            <TextInput
                                id="middle_initial"
                                v-model="form.middle_initial"
                                type="text"
                                maxlength="1"
                            />
                            <InputError :message="form.errors.middle_initial" />
                        </div>

                        <div class="form-group">
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <div class="form-group">
                            <InputLabel for="password" value="Password" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="form-group">
                            <InputLabel for="year_graduated" value="Graduation Year" />
                            <SelectInput
                                id="year_graduated"
                                v-model="form.year_graduated"
                                required
                            >
                                <option value="">Select Year</option>
                                <option v-for="year in batchYears" :value="year">{{ year }}</option>
                            </SelectInput>
                            <InputError :message="form.errors.year_graduated" />
                        </div>

                        <div class="form-group">
                            <InputLabel for="degree_earned" value="Degree" />
                            <SelectInput
                                id="degree_earned"
                                v-model="form.degree_earned"
                                required
                            >
                                <option value="">Select Degree</option>
                                <option v-for="degree in degrees" :value="degree">{{ degree }}</option>
                            </SelectInput>
                            <InputError :message="form.errors.degree_earned" />
                        </div>

                        <div class="form-group">
                            <InputLabel for="profile_photo" value="Profile Photo" />
                            <input
                                id="profile_photo"
                                type="file"
                                @input="form.profile_photo = $event.target.files[0]"
                                class="file-input"
                            />
                            <InputError :message="form.errors.profile_photo" />
                        </div>

                        <div class="form-actions">
                            <Button type="button" @click="emit('close')" class="btn-outline">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="btn-primary">
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create Alumni</span>
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.modal-content {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    backdrop-filter: blur(12px);
    overflow: hidden;
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid var(--card-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
}

.modal-close {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: 18px;
    cursor: pointer;
    transition: color 0.2s;
}

.modal-close:hover {
    color: var(--primary);
}

.modal-body {
    padding: 20px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.file-input {
    width: 100%;
    padding: 10px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 14px;
}

.form-actions {
    grid-column: span 2;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        grid-column: span 1;
    }
}
</style>