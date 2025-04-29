<template>
    <AdminLayout title="GIS/Spatial Reports">
      <div class="header">
        <div class="action-buttons">
          <button @click="downloadReport('pdf')" class="export-btn pdf">
            Export PDF
          </button>
          <button @click="downloadReport('excel')" class="export-btn excel">
            Export Excel
          </button>
        </div>
      </div>
  
      <div class="report-container">
        <!-- Filter Section -->
        <div class="filter-section">
          <div class="filter-group">
            <label>From Year</label>
            <input v-model="filters.year_from" type="number" min="1900" :max="new Date().getFullYear()" />
          </div>
          
          <div class="filter-group">
            <label>To Year</label>
            <input v-model="filters.year_to" type="number" min="1900" :max="new Date().getFullYear()" />
          </div>
          
          <div class="filter-group">
            <label>Degree Program</label>
            <select v-model="filters.degree_program">
              <option value="">All Programs</option>
              <option v-for="program in degreePrograms" :key="program" :value="program">
                {{ program }}
              </option>
            </select>
          </div>
          
          <div class="filter-group">
            <label>Country</label>
            <select v-model="filters.country">
              <option value="">All Countries</option>
              <option v-for="country in countries" :key="country" :value="country">
                {{ country }}
              </option>
            </select>
          </div>
          
          <div class="filter-group">
            <label>City</label>
            <select v-model="filters.city">
              <option value="">All Cities</option>
              <option v-for="city in cities" :key="city" :value="city">
                {{ city }}
              </option>
            </select>
          </div>
          
          <button @click="resetFilters" class="reset-btn">
            Reset Filters
          </button>
        </div>
  
        <!-- Loading Indicator -->
        <div v-if="isLoading" class="loading-indicator">
          <div class="spinner"></div>
          <span>Loading map data...</span>
        </div>
  
        <!-- Tab Navigation -->
        <div class="tabs">
          <button 
            @click="activeTab = 'heatmap'" 
            :class="{ active: activeTab === 'heatmap' }"
          >
            Alumni Heatmap
          </button>
          <button 
            @click="activeTab = 'clusters'" 
            :class="{ active: activeTab === 'clusters' }"
          >
            Industry Clusters
          </button>
          <button 
            @click="activeTab = 'migration'" 
            :class="{ active: activeTab === 'migration' }"
          >
            Migration Patterns
          </button>
        </div>
  
        <!-- Alumni Heatmap Report -->
        <div v-if="activeTab === 'heatmap'" class="report-section">
          <div class="section-header">
            <h2>Alumni Heatmap</h2>
            <p>Geographic concentration of alumni</p>
          </div>
          
          <div class="card">
            <div class="map-container">
              <GMapMap
                :center="mapCenter"
                :zoom="7"
                map-type-id="terrain"
                style="width: 100%; height: 500px"
              >
                <GMapHeatmap
                  :data="heatmapData"
                  :options="heatmapOptions"
                />
                <GMapMarker
                  v-for="(location, index) in mapData.alumniHeatmap"
                  :key="`alumni-${index}`"
                  :position="{ lat: parseFloat(location.latitude), lng: parseFloat(location.longitude) }"
                  :clickable="true"
                  @click="openMarker(location)"
                />
              </GMapMap>
            </div>
            
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Country</th>
                    <th>City</th>
                    <th>Alumni Count</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="mapData.alumniHeatmap.length === 0">
                    <td colspan="3" class="no-data">No alumni location data found</td>
                  </tr>
                  <tr v-for="(location, index) in mapData.alumniHeatmap" :key="index">
                    <td>{{ location.country }}</td>
                    <td>{{ location.city }}</td>
                    <td>{{ location.alumni_count }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
  
        <!-- Industry Clusters Report -->
        <div v-if="activeTab === 'clusters'" class="report-section">
          <div class="section-header">
            <h2>Industry Clusters</h2>
            <p>Companies employing many alumni</p>
          </div>
          
          <div class="card">
            <div class="map-container">
              <GMapMap
                :center="mapCenter"
                :zoom="7"
                map-type-id="terrain"
                style="width: 100%; height: 500px"
              >
                <GMapMarker
                  v-for="(company, index) in mapData.industryClusters"
                  :key="`company-${index}`"
                  :position="{ lat: parseFloat(company.latitude), lng: parseFloat(company.longitude) }"
                  :icon="{
                    url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                    scaledSize: { width: 32, height: 32 }
                  }"
                  :clickable="true"
                  @click="openMarker(company)"
                >
                  <GMapInfoWindow>
                    <div class="info-window">
                      <h3>{{ company.industry }}</h3>
                      <p>Employees: {{ company.employee_count }}</p>
                    </div>
                  </GMapInfoWindow>
                </GMapMarker>
              </GMapMap>
            </div>
            
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Industry</th>
                    <th>Employee Count</th>
                    <th>Location</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="mapData.industryClusters.length === 0">
                    <td colspan="3" class="no-data">No industry cluster data found</td>
                  </tr>
                  <tr v-for="(company, index) in mapData.industryClusters" :key="index">
                    <td>{{ company.industry }}</td>
                    <td>{{ company.employee_count }}</td>
                    <td>{{ company.latitude }}, {{ company.longitude }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
  
        <!-- Migration Patterns Report -->
        <div v-if="activeTab === 'migration'" class="report-section">
          <div class="section-header">
            <h2>Migration Patterns</h2>
            <p>Movement from campus to current locations</p>
          </div>
          
          <div class="card">
            <div class="map-container">
              <GMapMap
                ref="migrationMap"
                :center="mapCenter"
                :zoom="5"
                map-type-id="terrain"
                style="width: 100%; height: 500px"
              >
                <GMapPolyline
                  v-for="(pattern, index) in mapData.migrationPatterns"
                  :key="`migration-${index}`"
                  :path="getMigrationPath(pattern)"
                  :options="{
                    strokeColor: '#FF8C00',
                    strokeOpacity: 0.6,
                    strokeWeight: 2
                  }"
                />
                <GMapMarker
                  v-for="(pattern, index) in mapData.migrationPatterns"
                  :key="`destination-${index}`"
                  :position="getDestinationCoords(pattern)"
                  :icon="{
                    url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png',
                    scaledSize: { width: 32, height: 32 }
                  }"
                  :clickable="true"
                  @click="openMarker(pattern)"
                >
                  <GMapInfoWindow>
                    <div class="info-window">
                      <h3>{{ pattern.destination }}, {{ pattern.country }}</h3>
                      <p>Alumni Count: {{ pattern.count }}</p>
                      <p>Origin: {{ pattern.origin }}</p>
                    </div>
                  </GMapInfoWindow>
                </GMapMarker>
              </GMapMap>
            </div>
            
            <div class="table-container">
              <table>
                <thead>
                  <tr>
                    <th>Origin Campus</th>
                    <th>Destination</th>
                    <th>Country</th>
                    <th>Alumni Count</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="mapData.migrationPatterns.length === 0">
                    <td colspan="4" class="no-data">No migration pattern data found</td>
                  </tr>
                  <tr v-for="(pattern, index) in mapData.migrationPatterns" :key="index">
                    <td>{{ pattern.origin }}</td>
                    <td>{{ pattern.destination }}</td>
                    <td>{{ pattern.country }}</td>
                    <td>{{ pattern.count }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </AdminLayout>
  </template>
  
  <script setup>
  import { ref, watch, computed } from 'vue';
  import { router } from '@inertiajs/vue3';
  import debounce from 'lodash/debounce';
  import AdminLayout from '@/Layouts/AdminLayout.vue';
  
  const props = defineProps({
    mapData: Object,
    filters: Object,
    degreePrograms: Array,
    countries: Array,
    cities: Array
  });
  
  const filters = ref(props.filters);
  const isLoading = ref(false);
  const activeTab = ref('heatmap');
  const migrationMap = ref(null);
  
  // Default map center (adjust to your institution's location)
  const mapCenter = ref({ lat: 14.6760, lng: 121.0437 }); // Default to Philippines
  
  const heatmapData = computed(() => {
    return props.mapData.alumniHeatmap.map(location => ({
      location: new google.maps.LatLng(
        parseFloat(location.latitude),
        parseFloat(location.longitude)
      ),
      weight: location.alumni_count / 10
    }));
  });
  
  const heatmapOptions = {
    radius: 20,
    opacity: 0.6
  };
  
  const generateReport = debounce(() => {
    isLoading.value = true;
    router.get(route('admin.reports.gis-spatial.index'), filters.value, {
      preserveState: true,
      replace: true,
      onFinish: () => {
        isLoading.value = false;
      }
    });
  }, 500);
  
  watch(filters, generateReport, { deep: true });
  
  const downloadReport = (format) => {
    window.location.href = route('admin.reports.gis-spatial.export', {
      ...filters.value,
      format: format
    });
  };
  
  const resetFilters = () => {
    filters.value = {};
  };
  
  const openMarker = (data) => {
    // You can implement custom marker click behavior here
    console.log('Marker clicked:', data);
  };
  
  const getMigrationPath = (pattern) => {
    // This is a simplified example - you'd need actual coordinates for campuses
    const campusCoords = {
      'Main Campus': { lat: 14.6760, lng: 121.0437 },
      'North Campus': { lat: 14.6860, lng: 121.0537 },
      'South Campus': { lat: 14.6660, lng: 121.0337 }
    };
    
    const origin = campusCoords[pattern.origin] || campusCoords['Main Campus'];
    const destination = getDestinationCoords(pattern);
    
    return [origin, destination];
  };
  
  const getDestinationCoords = (pattern) => {
    // In a real app, you'd geocode the destination city
    // This is a simplified example using random nearby coordinates
    const baseLat = 14.6760;
    const baseLng = 121.0437;
    
    return {
      lat: baseLat + (Math.random() * 2 - 1),
      lng: baseLng + (Math.random() * 2 - 1)
    };
  };
  </script>
  
  <style scoped>
  /* Base Styles */
  :root {
    --primary: #ff8c00;
    --primary-light: rgba(255, 140, 0, 0.1);
    --text-primary: #ffffff;
    --text-secondary: #cccccc;
    --bg-dark: #1a1a1a;
    --bg-darker: #121212;
    --card-bg: rgba(40, 40, 40, 0.7);
    --card-border: rgba(255, 255, 255, 0.1);
  }
  
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 2rem;
    color: var(--text-primary);
  }
  
  .header h1 {
    font-size: 1.5rem;
    margin: 0;
  }
  
  .action-buttons {
    display: flex;
    gap: 0.75rem;
  }
  
  .export-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    color: white;
  }
  
  .export-btn.pdf {
    background-color: #F44336;
  }
  
  .export-btn.excel {
    background-color: #4CAF50;
  }
  
  .report-container {
    padding: 1.5rem;
    color: var(--text-primary);
  }
  
  /* Filter Section */
  .filter-section {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background-color: var(--card-bg);
    border-radius: 8px;
    border: 1px solid var(--card-border);
  }
  
  .filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .filter-group label {
    font-size: 0.875rem;
    color: var(--text-secondary);
  }
  
  .filter-group input,
  .filter-group select {
    padding: 0.5rem;
    border-radius: 4px;
    border: 1px solid var(--card-border);
    background-color: var(--bg-darker);
    color: var(--text-primary);
  }
  
  .reset-btn {
    grid-column: 1 / -1;
    padding: 0.5rem;
    background-color: transparent;
    color: var(--primary);
    border: 1px solid var(--primary);
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
  }
  
  .reset-btn:hover {
    background-color: var(--primary-light);
  }
  
  /* Loading Indicator */
  .loading-indicator {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    margin-bottom: 1rem;
    background-color: var(--card-bg);
    border-radius: 4px;
  }
  
  .spinner {
    width: 1.5rem;
    height: 1.5rem;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: var(--primary);
    animation: spin 1s ease-in-out infinite;
  }
  
  @keyframes spin {
    to { transform: rotate(360deg); }
  }
  
  /* Tabs */
  .tabs {
    display: flex;
    border-bottom: 1px solid var(--card-border);
    margin-bottom: 1.5rem;
  }
  
  .tabs button {
    padding: 0.75rem 1.5rem;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    color: var(--text-secondary);
    cursor: pointer;
    font-weight: 500;
  }
  
  .tabs button.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
  }
  
  /* Report Sections */
  .report-section {
    margin-bottom: 2rem;
  }
  
  .section-header {
    margin-bottom: 1rem;
  }
  
  .section-header h2 {
    margin: 0 0 0.25rem 0;
    color: var(--primary);
  }
  
  .section-header p {
    margin: 0;
    color: var(--text-secondary);
    font-size: 0.875rem;
  }
  
  /* Card */
  .card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 1rem;
    transition: all 0.3s;
  }
  
  .card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    transform: translateY(-2px);
  }
  
  /* Map Container */
  .map-container {
    margin-bottom: 1.5rem;
    border-radius: 8px;
    overflow: hidden;
  }
  
  /* Tables */
  .table-container {
    overflow-x: auto;
  }
  
  table {
    width: 100%;
    border-collapse: collapse;
  }
  
  th, td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--card-border);
  }
  
  th {
    background-color: var(--bg-darker);
    color: var(--text-primary);
    font-weight: 600;
  }
  
  tr:hover {
    background-color: var(--primary-light);
  }
  
  .no-data {
    text-align: center;
    padding: 1.5rem;
    color: var(--text-secondary);
  }
  
  /* Info Window Styles */
  .info-window {
    color: #333;
    padding: 0.5rem;
  }
  
  .info-window h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
  }
  
  .info-window p {
    margin: 0.25rem 0;
    font-size: 0.875rem;
  }
  
  /* Responsive */
  @media (max-width: 768px) {
    .filter-section {
      grid-template-columns: 1fr;
    }
    
    .header {
      flex-direction: column;
      gap: 1rem;
      align-items: flex-start;
    }
    
    .tabs {
      overflow-x: auto;
      white-space: nowrap;
    }
    
    .map-container {
      height: 300px;
    }
  }
  </style>