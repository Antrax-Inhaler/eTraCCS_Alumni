<!-- resources/js/Pages/Themes/Index.vue -->

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    themes: Array,
    userTheme: Object
});

const form = useForm({
    theme_template_id: props.userTheme.theme_template_id,
    custom_colors: props.userTheme.custom_colors || {
        primary: '#ff8c00',
        primary_light: 'rgba(255, 140, 0, 0.1)',
        text_primary: '#ffffff',
        text_secondary: '#cccccc',
        bg_dark: '#1a1a1a',
        bg_darker: '#121212',
        card_bg: 'rgba(40, 40, 40, 0.7)',
        card_border: 'rgba(255, 255, 255, 0.1)'
    },
    is_custom: props.userTheme.is_custom
});

const submit = () => {
    form.put(route('user.theme.update'));
};

const previewTheme = (colors) => {
    Object.entries(colors).forEach(([key, value]) => {
        document.documentElement.style.setProperty(`--${key.replace('_', '-')}`, value);
    });
};

const resetPreview = () => {
    if (props.userTheme.is_custom) {
        previewTheme(props.userTheme.custom_colors);
    } else if (props.userTheme.template) {
        previewTheme(props.userTheme.template.colors);
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Theme Selection" />
        
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <h1 class="text-2xl font-bold mb-6">Customize Your Theme</h1>

                <div class="bg-card rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Select a Theme</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div v-for="theme in themes" :key="theme.id"
                             class="border rounded-lg p-4 cursor-pointer transition-all"
                             :class="{ 'ring-2 ring-primary': form.theme_template_id === theme.id }"
                             @click="form.theme_template_id = theme.id; form.is_custom = false; previewTheme(theme.colors)">
                            <div class="flex items-center mb-2">
                                <div class="h-6 w-6 rounded-full mr-3" :style="{ backgroundColor: theme.colors.primary }"></div>
                                <h3 class="font-medium">{{ theme.name }}</h3>
                            </div>
                            <p class="text-sm text-text-secondary">{{ theme.description }}</p>
                        </div>
                    </div>

                    <button @click="form.is_custom = true; form.theme_template_id = null"
                            class="btn-secondary mb-6">
                        Create Custom Theme
                    </button>

                    <div v-if="form.is_custom" class="mt-6">
                        <h2 class="text-xl font-semibold mb-4">Custom Theme Settings</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="(value, key) in form.custom_colors" :key="key">
                                <label class="block text-sm font-medium mb-1">{{ key.replace('_', ' ') }}</label>
                                <input type="color" v-model="form.custom_colors[key]"
                                       @input="previewTheme(form.custom_colors)"
                                       class="w-full h-10 cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end mt-6 space-x-3">
                        <button @click="resetPreview" class="btn-secondary">Reset Preview</button>
                        <button @click="submit" class="btn-primary" :disabled="form.processing">
                            Save Theme
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>