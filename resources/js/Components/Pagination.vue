<!-- resources/js/Components/Pagination.vue -->
<script setup>
import { computed } from 'vue';

const props = defineProps({
    links: Array,
});

const visibleLinks = computed(() => {
    return props.links.filter(link => {
        return link.label !== '&laquo; Previous' && link.label !== 'Next &raquo;';
    });
});
</script>

<template>
    <div v-if="links.length > 3" class="flex items-center justify-between mt-4">
        <div class="flex-1 flex justify-between sm:hidden">
            <a 
                v-if="links[0].url" 
                :href="links[0].url" 
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
                Previous
            </a>
            <a 
                v-if="links[links.length - 1].url" 
                :href="links[links.length - 1].url" 
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
            >
                Next
            </a>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ links[1].label }}</span> to <span class="font-medium">{{ links[links.length - 2].label }}</span> of results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <a 
                        v-for="(link, index) in visibleLinks" 
                        :key="index" 
                        :href="link.url || '#'" 
                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium whitespace-nowrap"
                        :class="{
                            'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                            'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active && link.url,
                            'bg-white border-gray-300 text-gray-300 cursor-not-allowed': !link.url
                        }"
                        v-html="link.label"
                    />
                </nav>
            </div>
        </div>
    </div>
</template>