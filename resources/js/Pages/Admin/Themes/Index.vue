<!-- resources/js/Pages/Admin/Themes/Index.vue -->

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    themes: Array
});
</script>

<template>
    <AdminLayout>
        <Head title="Theme Management" />
        
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Theme Management</h1>
                    <Link :href="route('admin.themes.create')" class="btn-primary">
                        Create New Theme
                    </Link>
                </div>

                <div class="bg-card rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-card-border">
                        <thead class="bg-bg-darker">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Description</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-card-border">
                            <tr v-for="theme in themes" :key="theme.id">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-4 w-4 rounded-full mr-2" :style="{ backgroundColor: theme.colors.primary }"></div>
                                        {{ theme.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">{{ theme.description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span v-if="theme.is_default" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Default
                                    </span>
                                    <span v-else class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Optional
                                    </span>
                                    <span v-if="theme.is_public" class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Public
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button v-if="!theme.is_default" 
                                            @click="$inertia.post(route('admin.themes.set-default', theme.id))"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        Set Default
                                    </button>
                                    <Link :href="route('admin.themes.edit', theme.id)" class="text-yellow-600 hover:text-yellow-900">
    Edit
</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>