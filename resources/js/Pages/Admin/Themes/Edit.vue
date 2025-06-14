<!-- resources/js/Pages/Admin/Themes/Edit.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    theme: Object
});

const form = useForm({
    name: props.theme.name,
    description: props.theme.description,
    is_public: props.theme.is_public,
    colors: props.theme.colors
});

const submit = () => {
    form.put(route('admin.themes.update', props.theme.id));
};
</script>

<template>
    <AdminLayout>
        <Head title="Edit Theme" />
        
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Edit Theme</h1>
                    <Link :href="route('admin.themes.index')" class="btn-secondary">
                        Back to Themes
                    </Link>
                </div>

                <div class="bg-card rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Theme Name</label>
                            <input v-model="form.name" type="text" class="w-full" required>
                            <p v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</p>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1">Description</label>
                            <textarea v-model="form.description" class="w-full"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="flex items-center">
                                <input v-model="form.is_public" type="checkbox" class="mr-2">
                                <span>Make this theme available to users</span>
                            </label>
                        </div>

                        <h2 class="text-xl font-semibold mb-4">Color Settings</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div v-for="(value, key) in form.colors" :key="key">
                                <label class="block text-sm font-medium mb-1">{{ key.replace('_', ' ') }}</label>
                                <input type="color" v-model="form.colors[key]" class="w-full h-10 cursor-pointer">
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <Link :href="route('admin.themes.index')" class="btn-secondary">
                                Cancel
                            </Link>
                            <button type="submit" class="btn-primary" :disabled="form.processing">
                                Update Theme
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>