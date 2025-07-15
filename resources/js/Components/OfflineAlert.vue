<template>
  <transition name="fade">
    <div v-if="!isOnline" class="no-internet-screen">
      <div class="container">
        <div class="world">
          <div class="globe"></div>
        </div>
        <h1>No Internet</h1>
        <p>The site will be right back when your internet connection is restored.</p>
        <div class="signal">
          <div class="bar"></div>
          <div class="bar"></div>
          <div class="bar"></div>
          <div class="bar"></div>
          <div class="bar"></div>
        </div>
        <div class="connection">Attempting to reconnect...</div>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const isOnline = ref(navigator.onLine);

const updateStatus = () => {
  isOnline.value = navigator.onLine;
};

onMounted(() => {
  window.addEventListener('online', updateStatus);
  window.addEventListener('offline', updateStatus);
});

onBeforeUnmount(() => {
  window.removeEventListener('online', updateStatus);
  window.removeEventListener('offline', updateStatus);
});
</script>

<style scoped>
.no-internet-screen {
  position: fixed;
  z-index: 9999;
  background-color: var(--bg-darker);
  color: var(--text-primary);
  top: 0;
  left: 0;
  width: 100%;
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 16px;
  text-align: center;
}

.container {
  display: flex;
  flex-direction: column;
  align-items: center;
  max-width: 90%;
}

h1 {
  font-size: 2rem;
  margin-bottom: 8px;
  color: var(--text-primary);
}

p {
  font-size: 1rem;
  margin-bottom: 16px;
  color: var(--text-secondary);
  line-height: 1.4;
}

.world {
  width: 80px;
  height: 80px;
  margin-bottom: 16px;
  position: relative;
}

.globe {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  background: rgba(255, 140, 0, 0.1);
  border: 2px solid var(--primary);
  position: relative;
  overflow: hidden;
}

.globe:before {
  content: '';
  position: absolute;
  width: 200%;
  height: 200%;
  top: -50%;
  left: -50%;
  background: radial-gradient(circle at center, transparent 40%, var(--primary) 41%, transparent 42%);
  animation: pulse 3s infinite linear;
}

.signal {
  width: 160px;
  height: 100px;
  position: relative;
  margin-bottom: 12px;
}

.signal .bar {
  position: absolute;
  bottom: 0;
  width: 22px;
  background: var(--primary);
  animation: equalize 1.5s infinite ease-in-out;
}

.signal .bar:nth-child(1) {
  left: 0;
  height: 30px;
  animation-delay: 0.1s;
}
.signal .bar:nth-child(2) {
  left: 32px;
  height: 60px;
  animation-delay: 0.2s;
}
.signal .bar:nth-child(3) {
  left: 64px;
  height: 90px;
  animation-delay: 0.3s;
}
.signal .bar:nth-child(4) {
  left: 96px;
  height: 40px;
  animation-delay: 0.4s;
}
.signal .bar:nth-child(5) {
  left: 128px;
  height: 20px;
  animation-delay: 0.5s;
}

.connection {
  font-size: 0.9rem;
  color: #FF5252;
  animation: blink 2s infinite;
}

@keyframes pulse {
  0% {
    transform: scale(0.5);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

@keyframes equalize {
  0%, 100% {
    height: 10px;
    background: #f44336;
  }
  25% {
    height: 40px;
    background: #FFC107;
  }
  50% {
    height: 80px;
    background: var(--primary);
  }
  75% {
    height: 30px;
    background: #2196F3;
  }
}

@keyframes blink {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.3;
  }
}

/* Responsive */
@media (max-width: 480px) {
  h1 {
    font-size: 1.5rem;
  }
  p {
    font-size: 0.95rem;
  }
  .world {
    width: 60px;
    height: 60px;
  }
  .signal {
    width: 120px;
    height: 80px;
  }
  .signal .bar {
    width: 16px;
  }
  .signal .bar:nth-child(2) { left: 24px; }
  .signal .bar:nth-child(3) { left: 48px; }
  .signal .bar:nth-child(4) { left: 72px; }
  .signal .bar:nth-child(5) { left: 96px; }
}
</style>
