<template>
    <Modal :show="show" @close="emit('close')" max-width="2xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Edit Alumni</h3>
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
                            <InputLabel for="password" value="Password (Leave blank to keep current)" />
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
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
                            <div class="photo-upload">
                                <img :src="alum.profile_photo_url" alt="Current Photo" class="current-photo">
                                <input
                                    id="profile_photo"
                                    type="file"
                                    @input="form.profile_photo = $event.target.files[0]"
                                    class="file-input"
                                />
                            </div>
                            <InputError :message="form.errors.profile_photo" />
                        </div>

                        <div class="form-actions">
                            <Button type="button" @click="emit('close')" class="btn-outline">
                                Cancel
                            </Button>
                            <Button type="submit" :disabled="form.processing" class="btn-primary">
                                <span v-if="form.processing">Updating...</span>
                                <span v-else>Update Alumni</span>
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </Modal>
</template>
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
    alum: Object,
    batchYears: Array,
    degrees: Array,
});

const emit = defineEmits(['close']);

const form = useForm({
    first_name: props.alum.first_name,
    last_name: props.alum.last_name,
    middle_initial: props.alum.middle_initial,
    email: props.alum.email,
    password: '',
    year_graduated: props.alum.educational_backgrounds[0]?.year_graduated,
    degree_earned: props.alum.educational_backgrounds[0]?.degree_earned,
    profile_photo: null,
});

const submit = () => {
    form.post(route('admin.alumni.update', props.alum.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('close');
        },
    });
};
</script>



<style scoped>
/* Reuse modal styles from CreateModal */

.photo-upload {
    display: flex;
    align-items: center;
    gap: 15px;
}

.current-photo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
}

.file-input {
    flex: 1;
    padding: 8px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    color: var(--text-primary);
    font-size: 14px;
}
</style>