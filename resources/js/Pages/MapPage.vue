<template>
  <GMapMap
    :center="mapCenter"
    :zoom="12"
    style="width: 100%; height: 400px;"
  >
    <GMapMarker
      v-for="(marker, index) in markers"
      :key="index"
      :position="marker.position"
      :clickable="true"
      :draggable="marker.draggable"
      @click="onMarkerClick(marker)"
    />
  </GMapMap>
  <div id="app">
  <sample-data></sample-data>
</div>
  <div id="app">
    <SampleTable />
  </div>
</template>

<script>
export default {
  data() {
    return {
      mapCenter: { lat: 10.3157, lng: 123.8854 },
      markers: [
        { position: { lat: 10.3157, lng: 123.8854 }, draggable: true },
        { position: { lat: 10.3200, lng: 123.8900 }, draggable: false },
        { position: { lat: 10.3100, lng: 123.8800 }, draggable: true },
      ],
    };
  },
  methods: {
    onMarkerClick(marker) {
      alert(`Marker clicked at: ${marker.position.lat}, ${marker.position.lng}`);
    },
  },
};
</script>



<script setup>
import SampleTable from './SampleTable.vue'; // Adjust the path as needed
</script>