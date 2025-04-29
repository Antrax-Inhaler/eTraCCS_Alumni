<template>
    <div id="map" style="height: 400px;"></div>
  </template>
  
  <script setup>
  import { onMounted } from 'vue';
  
  const props = defineProps({
    locations: Array,
  });
  
  onMounted(() => {
    const map = new google.maps.Map(document.getElementById('map'), {
      center: { lat: 14.676, lng: 121.0437 }, // Default to Philippines
      zoom: 6,
    });
  
    props.locations.forEach((location) => {
      const marker = new google.maps.Marker({
        position: { lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) },
        map: map,
        title: `${location.country}: ${location.total} alumni`,
      });
  
      const infoWindow = new google.maps.InfoWindow({
        content: `<strong>${location.country}</strong>: ${location.total} alumni`,
      });
  
      marker.addListener('click', () => {
        infoWindow.open(map, marker);
      });
    });
  });
  </script>