<template>
  <div class="speed-monitor">
    <div class="signal-bars" :title="latencyTooltip">
      <div
        v-for="(bar, index) in 3"
        :key="index"
        class="bar"
        :class="{
          active: index < signalLevel,
          green: latencyMs < 100,
          orange: latencyMs >= 100 && latencyMs < 300,
          red: latencyMs >= 300,
          glow: latencyMs >= 300
        }"
        :style="{ height: `${(index + 1) * 8}px` }"
      ></div>
    </div>
    <div
      class="latency-display"
      :class="latencyStatusClass"
      :title="latencyTitle"
    >
      {{ latencyMs }} ms
    </div>
  </div>
</template>


<script>
export default {
  name: 'SpeedMonitor',
  data() {
    return {
      testFileSize: 500000,
      downloadTime: 0,
      speedKbps: 0,
      latencyMs: 0,
      speedInterval: null
    };
  },
  computed: {
    signalLevel() {
      if (this.latencyMs < 100) return 3;
      if (this.latencyMs < 300) return 2;
      return 1;
    },
    latencyStatusClass() {
      if (this.latencyMs < 100) return 'good';
      if (this.latencyMs < 300) return 'moderate';
      return 'poor';
    },
    latencyTitle() {
      if (this.latencyMs < 100) return '✅ Excellent connection, smooth experience.';
      if (this.latencyMs < 300) return '⚠️ Moderate connection, you may experience slight delays.';
      return '❌ Poor connection. System may lag, try refreshing or checking your network.';
    },
    latencyTooltip() {
      if (this.latencyMs < 100) return '✅ Fast connection, everything is optimal!';
      if (this.latencyMs < 300) return '⚠️ Fair connection, might experience some delay.';
      return '❌ Poor connection! Expect slowness, try to wait for data to sync or reconnect for better performance.';
    }
  },
  mounted() {
    this.measureSpeedAndLatency();
    this.speedInterval = setInterval(this.measureSpeedAndLatency, 3000);
  },
  beforeDestroy() {
    clearInterval(this.speedInterval);
  },
  methods: {
    async measureSpeedAndLatency() {
      const startTime = performance.now();
      const testFileUrl = `https://httpbin.org/bytes/${this.testFileSize}?${Date.now()}`;
      try {
        const response = await fetch(testFileUrl, { cache: 'no-store' });
        if (!response.ok) throw new Error('Network error');
        await response.blob();
        const endTime = performance.now();
        this.downloadTime = endTime - startTime;
        this.speedKbps = Math.round((this.testFileSize * 8) / this.downloadTime);
        this.latencyMs = Math.round(this.downloadTime);
      } catch (error) {
        console.error('Speed test failed:', error);
        this.latencyMs = Math.round(performance.now() - startTime);
        this.speedKbps = 0;
      }
    }
  }
};
</script>

<style scoped>
.speed-monitor {
  width: 60px;
  font-family: Arial, sans-serif;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.signal-bars {
  display: flex;
  align-items: flex-end; /* Grow bars upwards */
  gap: 2px;
  height: 30px;
  margin-bottom: 5px;
}

.bar {
  width: 6px;
  background-color: #ccc;
  border-radius: 2px;
  transition: background-color 0.3s, box-shadow 0.3s;
}

/* Active signal color classes */
.bar.active.green {
  background-color: #4CAF50;
}
.bar.active.orange {
  background-color: #FF9800;
}
.bar.active.red {
  background-color: #F44336;
}

/* Glow effect when connection is poor */
.bar.glow.active.red {
  box-shadow: 0 0 4px #F44336, 0 0 6px #F44336;
}

.latency-display {
  font-size: 12px;
  font-weight: bold;
  transition: color 0.3s;
}

.good {
  color: #4CAF50;
}
.moderate {
  color: #FF9800;
}
.poor {
  color: #F44336;
}

</style>
