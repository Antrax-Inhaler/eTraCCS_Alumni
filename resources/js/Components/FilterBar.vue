<!-- resources/js/Components/FilterBar.vue -->
<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: Object,
});

const emit = defineEmits(['update:modelValue', 'reset']);

const localFilters = ref({...props.modelValue});

watch(localFilters, (newValue) => {
    emit('update:modelValue', newValue);
}, { deep: true });

const resetFilters = () => {
    emit('reset');
};
</script>

<template>
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
            <div class="w-full md:flex-1">
                <slot />
            </div>
            
            <div class="mt-4 md:mt-0 md:ml-4 flex items-center">
                <slot name="actions" />
                
                <button
                    @click="resetFilters"
                    class="ml-2 inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Reset
                </button>
            </div>
        </div>
    </div>
</template>