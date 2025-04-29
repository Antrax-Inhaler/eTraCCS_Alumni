<template>
    <transition-group name="toast">
      <div 
        v-for="toast in toasts" 
        :key="toast.id"
        class="toast-notification"
        :class="'toast-' + toast.type"
      >
        <div class="toast-icon">
          <i :class="iconClass(toast.type)"></i>
        </div>
        <div class="toast-content">
          <div class="toast-title">{{ toast.title }}</div>
          <div class="toast-message">{{ toast.message }}</div>
        </div>
        <button class="toast-close" @click="removeToast(toast.id)">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </transition-group>
  </template>
  
  <script setup>
  import { ref } from 'vue';
  
  const toasts = ref([]);
  let toastId = 0;
  
  const iconClass = (type) => {
    return {
      success: 'fas fa-check-circle',
      error: 'fas fa-exclamation-circle',
      info: 'fas fa-info-circle',
      warning: 'fas fa-exclamation-triangle'
    }[type] || 'fas fa-info-circle';
  };
  
  const showToast = (type, title, message, duration = 5000) => {
    const id = toastId++;
    toasts.value.push({ id, type, title, message });
    
    if (duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, duration);
    }
  };
  
  const removeToast = (id) => {
    toasts.value = toasts.value.filter(toast => toast.id !== id);
  };
  
  // Make functions available to other components
  defineExpose({ showToast });
  </script>
  
  <style scoped>
  .toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 350px;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    transition: all 0.3s ease;
    background: var(--card-bg);
    border-left: 4px solid;
  }
  
  .toast-success {
    border-left-color: var(--primary);
  }
  
  .toast-error {
    border-left-color: #f44336;
  }
  
  .toast-info {
    border-left-color: #2196F3;
  }
  
  .toast-warning {
    border-left-color: #ff9800;
  }
  
  .toast-icon {
    font-size: 24px;
    margin-right: 15px;
  }
  
  .toast-success .toast-icon {
    color: var(--primary);
  }
  
  .toast-error .toast-icon {
    color: #f44336;
  }
  
  .toast-info .toast-icon {
    color: #2196F3;
  }
  
  .toast-warning .toast-icon {
    color: #ff9800;
  }
  
  .toast-content {
    flex: 1;
  }
  
  .toast-title {
    font-weight: 600;
    margin-bottom: 5px;
    color: var(--text-primary);
  }
  
  .toast-message {
    font-size: 0.9em;
    color: var(--text-secondary);
  }
  
  .toast-close {
    background: transparent;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    margin-left: 10px;
    opacity: 0.7;
    transition: opacity 0.2s;
  }
  
  .toast-close:hover {
    opacity: 1;
  }
  
  /* Animation */
  .toast-enter-from,
  .toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
  }
  
  .toast-enter-to,
  .toast-leave-from {
    opacity: 1;
    transform: translateX(0);
  }
  
  .toast-enter-active,
  .toast-leave-active,
  .toast-move {
    transition: all 0.3s ease;
  }
  
  .toast-leave-active {
    position: absolute;
  }
  </style>