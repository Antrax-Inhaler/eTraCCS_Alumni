<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    companies: Array,
    filters: Object,
    industries: Array,
});

const isMobile = ref(false);
const showFilters = ref(false);
const gmap = ref(null);
const searchInput = ref(null);
const touchTimer = ref(null);
const lastTouch = ref(0);
const mapInstance = ref(null);
const directionsService = ref(null);
const directionsRenderer = ref(null);
const streetViewService = ref(null);
const streetViewPanorama = ref(null);
const showDirections = ref(false);
const showStreetView = ref(false);
const origin = ref('');
const selectedCompany = ref(null);
const search = ref(props.filters.search);
const industry = ref(props.filters.industry);

const validCompanies = computed(() => {
    return props.companies.filter((company) => {
        return typeof company.latitude === 'number' && 
               typeof company.longitude === 'number' &&
               company.users && 
               company.users.length > 0;
    });
});

const mapCenter = computed(() => {
    if (validCompanies.value.length > 0) {
        const firstCompany = validCompanies.value[0];
        return { lat: firstCompany.latitude, lng: firstCompany.longitude };
    }
    return { lat: 14.5995, lng: 120.9842 };
});

onMounted(() => {
  checkMobileStatus();
  window.addEventListener('resize', checkMobileStatus);
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener('resize', checkMobileStatus);
  document.removeEventListener('click', handleClickOutside);
});

const checkMobileStatus = () => {
  isMobile.value = window.innerWidth <= 768;
};

const handleClickOutside = (event) => {
  if (isMobile.value && showFilters.value && 
      !event.target.closest('.filter-panel') &&
      !event.target.closest('.filter-toggle')) {
    showFilters.value = false;
  }
};

const handleMarkerTouchStart = (company, event) => {
  if (!isMobile.value) return;
  event.stopPropagation();
  touchTimer.value = setTimeout(() => {
    openInfoWindow(company);
  }, 300);
  lastTouch.value = event.timeStamp;
};

const handleMarkerTouchEnd = (event) => {
  if (!isMobile.value) return;
  clearTimeout(touchTimer.value);
  if (event.timeStamp - lastTouch.value < 300) {
    openInfoWindow(company);
  }
};

const handleMapMove = () => {
  if (isMobile.value) {
    selectedCompany.value = null;
  }
};

const initializeServices = (map) => {
  directionsService.value = new window.google.maps.DirectionsService();
  directionsRenderer.value = new window.google.maps.DirectionsRenderer({
    map: map,
    suppressMarkers: true,
    panel: document.getElementById('directions-container')
  });
  streetViewService.value = new window.google.maps.StreetViewService();
};

const onMapLoad = (map) => {
  mapInstance.value = map;
  initializeServices(map);
  if (validCompanies.value.length > 0) {
    nextTick(() => {
      centerMapOnMarkers();
    });
  }
};

const centerMapOnMarkers = () => {
  if (!mapInstance.value || validCompanies.value.length === 0) return;

  const bounds = new window.google.maps.LatLngBounds();
  
  validCompanies.value.forEach(company => {
    bounds.extend(new window.google.maps.LatLng(
      company.latitude,
      company.longitude
    ));
  });

  if (validCompanies.value.length === 1) {
    mapInstance.value.panTo(bounds.getCenter());
    mapInstance.value.setZoom(14);
  } else {
    mapInstance.value.fitBounds(bounds, {
      top: 100,
      right: 100,
      bottom: 100,
      left: 100
    });
  }
};

const getDirections = () => {
  if (!selectedCompany.value || !directionsService.value) return;
  
  const destination = {
    lat: selectedCompany.value.latitude,
    lng: selectedCompany.value.longitude
  };

  const request = {
    origin: origin.value || 'current location',
    destination: destination,
    travelMode: window.google.maps.TravelMode.DRIVING
  };

  // Handle "current location" case
  if (request.origin === 'current location') {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          request.origin = new window.google.maps.LatLng(
            position.coords.latitude,
            position.coords.longitude
          );
          executeDirectionsRequest(request);
        },
        () => {
          alert("Could not get your current location. Please enter a starting address.");
          request.origin = '';
        }
      );
    } else {
      alert("Geolocation is not supported by this browser. Please enter a starting address.");
      request.origin = '';
    }
  } else {
    executeDirectionsRequest(request);
  }
};

const executeDirectionsRequest = (request) => {
  directionsService.value.route(request, (response, status) => {
    if (status === 'OK') {
      directionsRenderer.value.setMap(mapInstance.value);
      directionsRenderer.value.setDirections(response);
      showDirections.value = true;
      showStreetView.value = false;
      if (streetViewPanorama.value) {
        streetViewPanorama.value.setVisible(false);
      }
    } else {
      alert('Directions request failed: ' + status);
    }
  });
};

const toggleStreetView = () => {
  if (!selectedCompany.value || !mapInstance.value) return;
  
  showStreetView.value = !showStreetView.value;
  
  if (showStreetView.value) {
    const companyLocation = new window.google.maps.LatLng(
      selectedCompany.value.latitude,
      selectedCompany.value.longitude
    );
    
    streetViewService.value.getPanorama({
      location: companyLocation,
      radius: 50
    }, (data, status) => {
      if (status === 'OK') {
        if (!streetViewPanorama.value) {
          streetViewPanorama.value = new window.google.maps.StreetViewPanorama(
            document.getElementById('street-view-container'), {
              position: companyLocation,
              pov: { heading: 34, pitch: 10 },
              visible: true
            }
          );
        } else {
          streetViewPanorama.value.setPosition(companyLocation);
          streetViewPanorama.value.setVisible(true);
        }
        
        showDirections.value = false;
        directionsRenderer.value.setMap(null);
      } else {
        alert('Street View data not available for this location.');
        showStreetView.value = false;
      }
    });
  } else {
    if (streetViewPanorama.value) {
      streetViewPanorama.value.setVisible(false);
    }
  }
};

const openInfoWindow = (company) => {
    selectedCompany.value = company;
};

const filterCompanies = () => {
  router.get(route('map.index'), {
    search: search.value,
    industry: industry.value,
  }, {
    preserveState: true,
    replace: true,
    onSuccess: () => {
      if (validCompanies.value.length > 0) {
        nextTick(() => {
          centerMapOnMarkers();
        });
      }
    }
  });
};

const resetFilters = () => {
    search.value = '';
    industry.value = 'all';
    filterCompanies();
};

watch(validCompanies, (newCompanies) => {
  if (newCompanies.length > 0) {
    centerMapOnMarkers();
  }
}, { deep: true });
</script>

<template>
  <AppLayout>
    <div class="interactive-map-container">
      <div class="map-placeholder">
        <div class="map-header" style="display: flex; gap: 20px; align-items: center;">
          <i class="fas fa-map-marker-alt"></i>
          <div class="map-text">Interactive Map View</div>
        </div>
       
        <div class="map-container">
          <div class="filter-panel" :class="{ 'filter-panel-mobile': isMobile }">
            <button v-if="isMobile" @click="showFilters = !showFilters" class="filter-toggle">
              <i :class="showFilters ? 'fas fa-times' : 'fas fa-filter'"></i>
            </button>
            
            <div class="filter-content" :class="{ 'hidden': isMobile && !showFilters }">
              <div class="filter-group">
                <label>Search</label>
                <input
                  v-model="search"
                  @keyup.enter="filterCompanies"
                  type="text"
                  placeholder="Company or employee name"
                  ref="searchInput"
                >
              </div>
              
              <div class="filter-group">
                <label>Industry</label>
                <select v-model="industry" @change="filterCompanies">
                  <option value="all">All Industries</option>
                  <option v-for="(industry, index) in industries" :key="index" :value="industry">
                    {{ industry }}
                  </option>
                </select>
              </div>
              
              <div class="filter-actions">
                <button 
                  @click="filterCompanies"
                  class="btn-primary"
                >
                  Apply
                </button>
                <button 
                  @click="resetFilters"
                  class="btn-secondary"
                >
                  Reset
                </button>
              </div>
            </div>
          </div>

          <GMapMap 
            ref="gmap"
            @load="onMapLoad"
            :center="mapCenter" 
            :zoom="10" 
            :options="{
              gestureHandling: isMobile ? 'greedy' : 'auto',
              zoomControl: true,
              mapTypeControl: false,
              scaleControl: true,
              streetViewControl: false,
              rotateControl: true,
              fullscreenControl: true,
              styles: [
                {
                  elementType: 'labels.text.stroke',
                  stylers: [{ color: '#ffffff' }]
                },
                {
                  elementType: 'labels.text.fill',
                  stylers: [{ color: '#333333' }]
                },
                {
                  featureType: 'poi',
                  elementType: 'labels.text.fill',
                  stylers: [{ color: '#ff8c00' }]
                }
              ]
            }" 
            class="gmap"
            @bounds_changed="handleMapMove"
            @click="selectedCompany = null"
          >
            <div v-if="validCompanies.length === 0" class="no-results">
              <p>No companies found matching your criteria.</p>
            </div>

            <GMapMarker
              v-for="(company, index) in validCompanies"
              :key="index"
              :position="{ lat: company.latitude, lng: company.longitude }"
              :clickable="true"
              @click="openInfoWindow(company)"
              @touchstart="handleMarkerTouchStart(company, $event)"
              @touchend="handleMarkerTouchEnd"
              :icon="{
                path: 'M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z',
                fillColor: '#ff8c00',
                fillOpacity: 1,
                strokeWeight: 0,
                scale: isMobile ? 2 : 1.5,
                anchor: { x: 12, y: 24 }
              }"
            >
              <GMapInfoWindow 
                v-if="selectedCompany === company" 
                class="info-window"
                :closeclick="true"
                @closeclick="selectedCompany = null"
              >
                <div class="info-content">
                  <div class="info-header">
                    <i class="fas fa-building"></i>
                    <h3>{{ company.name }}</h3>
                  </div>
                  <p class="info-industry">{{ company.industry }}</p>
                  <p class="info-employees">Employees ({{ company.users.length }}):</p>
                  
                  <div class="user-profiles">
                    <template v-for="(user, userIndex) in company.users.slice(0, 5)" :key="user.id">
                      <Link 
                        :href="`/profile/${user.encrypted_id}`"
                        class="user-profile"
                        :style="{
                          'z-index': 5 - userIndex,
                          'margin-left': userIndex > 0 ? '-12px' : '0'
                        }"
                      >
                        <img 
                          :src="user.profile_photo_url" 
                          alt="Profile" 
                          class="profile-image"
                        />
                        <span class="profile-tooltip">{{ user.full_name }}</span>
                      </Link>
                    </template>
                  </div>
                  
                  <div class="info-footer">
                    <template v-if="company.users.length > 5">
                      <p class="more-users">
                        +{{ company.users.length - 5 }} more
                      </p>
                    </template>
                    
                    <Link 
                      v-if="company.users.length > 0"
                      :href="`/company/${company.id}/employees`"
                      class="view-all"
                    >
                      View all employees
                    </Link>
                  </div>
                </div>
              </GMapInfoWindow>
            </GMapMarker>
          </GMapMap>

          <div v-if="showDirections" class="directions-panel">
            <div class="directions-header">
              <h3>Get Directions</h3>
              <button @click="showDirections = false" class="close-btn">
                <i class="fas fa-times"></i>
              </button>
            </div>
            <div class="directions-input">
              <label>From:</label>
              <input 
                v-model="origin" 
                type="text" 
                placeholder="Enter starting location or 'current location'"
                @keyup.enter="getDirections"
              >
              <button @click="getDirections" class="btn-primary">
                <i class="fas fa-route"></i> Get Route
              </button>
            </div>
            <div id="directions-container"></div>
          </div>

          <div v-if="showStreetView" id="street-view-container" class="street-view"></div>
        </div>
      </div>
      <div class="map-actions">
        <button 
          class="map-button" 
          @click="getDirections"
          :disabled="!selectedCompany"
        >
          <i class="fas fa-directions"></i> Get Directions
        </button>
        <button 
          class="map-button" 
          @click="toggleStreetView"
          :disabled="!selectedCompany"
        >
          <i class="fas fa-street-view"></i> Street View
        </button>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.map-container {
  position: relative;
  height: 100vh;
  width: 100%;
}

.filter-panel {
  position: absolute;
  top: 20px;
  left: 20px;
  z-index: 10;
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  backdrop-filter: blur(12px);
  border-radius: 16px;
  padding: 16px;
  width: 280px;
  transition: all 0.3s;
}

.filter-group {
  margin-bottom: 16px;
}

.filter-group label {
  display: block;
  margin-bottom: 8px;
  color: var(--text-secondary);
  font-size: 14px;
}

.filter-group input,
.filter-group select {
  width: 100%;
  padding: 8px 12px;
  background: rgba(40, 40, 40, 0.8);
  border: 1px solid var(--card-border);
  border-radius: 8px;
  color: var(--text-primary);
  transition: all 0.3s;
}

.filter-group input:focus,
.filter-group select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px var(--primary-light);
}

.filter-actions {
  display: flex;
  justify-content: space-between;
  gap: 8px;
}

.btn-primary {
  background: var(--primary);
  color: var(--bg-darker);
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s;
}

.gm-style-iw-d{
  background-color: transparent !important;
  overflow: hidden;
}

.btn-primary:hover {
  opacity: 0.9;
}

.btn-secondary {
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-primary);
  border: none;
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s;
}

.gmap {
  transition: all 0.5s ease;
}

.centering::after {
  content: 'Centering...';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: var(--card-bg);
  padding: 8px 16px;
  border-radius: 20px;
  z-index: 10;
  opacity: 0.9;
}

.map-placeholder {
  height: 90vh;
  padding: 20px;
}

.interactive-map-container {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  backdrop-filter: blur(12px);
}

.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.2);
}

.gmap {
  width: 100%;
  height: 100%;
}

.no-results {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: var(--card-bg);
  padding: 16px 24px;
  border-radius: 8px;
  color: var(--text-primary);
  text-align: center;
}

.info-window {
  background: transparent !important;
  border: none;
  box-shadow: none;
}

.scrollable::-webkit-scrollbar-track {
  background: transparent !important;
}

.scrollable::-webkit-scrollbar-thumb {
  background-color: rgba(0, 0, 0, 0.3);
  border-radius: 4px;
}

.info-content {
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  backdrop-filter: blur(12px);
  border-radius: 16px;
  padding: 16px;
  width: 260px;
  color: var(--text-primary);
}

.info-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}

.info-header i {
  color: var(--primary);
  font-size: 18px;
}

.info-header h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.info-industry {
  margin: 0 0 12px 0;
  font-size: 14px;
  color: var(--text-secondary);
}

.info-employees {
  margin: 0 0 8px 0;
  font-size: 14px;
  font-weight: 500;
}

.user-profiles {
  display: flex;
  margin-bottom: 12px;
  padding-left: 8px;
}

.user-profile {
  position: relative;
  transition: all 0.3s;
}

.user-profile:hover {
  transform: translateY(-4px);
}

.profile-image {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid var(--bg-darker);
  object-fit: cover;
  transition: all 0.3s;
}

.user-profile:hover .profile-image {
  border-color: var(--primary);
}

.profile-tooltip {
  position: absolute;
  bottom: 100%;
  left: 50%;
  transform: translateX(-50%);
  background: var(--bg-darker);
  color: var(--text-primary);
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s;
  margin-bottom: 8px;
}

.user-profile:hover .profile-tooltip {
  opacity: 1;
  visibility: visible;
}

.info-footer {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.more-users {
  margin: 0;
  font-size: 12px;
  color: var(--text-secondary);
}

.view-all {
  font-size: 12px;
  color: var(--primary);
  text-decoration: none;
  transition: all 0.3s;
}

.view-all:hover {
  text-decoration: underline;
}

.map-actions {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 10px;
  z-index: 10;
}

.map-button {
  background: var(--primary);
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 20px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  transition: all 0.3s;
}

.map-button:disabled {
  background: #cccccc;
  cursor: not-allowed;
}

.map-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.directions-panel {
  position: absolute;
  top: 20px;
  right: 20px;
  background: var(--card-bg);
  border: 1px solid var(--card-border);
  border-radius: 8px;
  padding: 15px;
  width: 300px;
  z-index: 10;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.directions-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.close-btn {
  background: none;
  border: none;
  color: var(--text-secondary);
  cursor: pointer;
}

.directions-input {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 10px;
}

.directions-input input {
  padding: 8px;
  border: 1px solid var(--card-border);
  border-radius: 4px;
}

.street-view {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 5;
}

#directions-container {
  max-height: 300px;
  overflow-y: auto;
  padding: 5px;
}

.adp-placemark {
  color: var(--text-primary) !important;
  background-color: var(--card-bg) !important;
}

.adp-step {
  color: var(--text-primary) !important;
}

.adp-directions {
  color: var(--text-primary) !important;
}
</style>