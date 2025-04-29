<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import { Loader } from '@googlemaps/js-api-loader';

const props = defineProps({
    mapData: Object,
    filters: Object
});

const map = ref(null);
const markers = ref([]);
const heatmap = ref(null);
const infoWindow = ref(null);
const activeTab = ref('alumni');
const selectedIndustry = ref(props.filters.industry || '');
const selectedCountry = ref(props.filters.country || '');
const searchRadius = ref(props.filters.radius || 50);
const searchQuery = ref(props.filters.search || '');

// Initialize the map when component mounts
onMounted(() => {
    initMap();
});

// Watch for filter changes
watch([selectedIndustry, selectedCountry, searchRadius, searchQuery], () => {
    updateMap();
});
const topCountry = computed(() => {
    if (!props.mapData.regions || Object.keys(props.mapData.regions).length === 0) {
        return { name: 'N/A', count: 0 };
    }
    const sorted = Object.entries(props.mapData.regions).sort((a,b) => b[1] - a[1]);
    return {
        name: sorted[0][0],
        count: sorted[0][1]
    };
});
const initMap = async () => {
    const loader = new Loader({
        apiKey: import.meta.env.VITE_GOOGLE_MAPS_API_KEY,
        version: "weekly",
        libraries: ["visualization"]
    });

    await loader.load();
    
    // Create map centered on first alumni or default to world view
    const firstAlumni = props.mapData.alumni[0];
    const center = firstAlumni ? 
        { lat: parseFloat(firstAlumni.lat), lng: parseFloat(firstAlumni.lng) } : 
        { lat: 20, lng: 0 };
    
    map.value = new google.maps.Map(document.getElementById("map"), {
        center,
        zoom: 3,
        styles: [
            {
                "featureType": "administrative",
                "elementType": "labels.text.fill",
                "stylers": [
                    { "color": "#444444" }
                ]
            },
            {
                "featureType": "landscape",
                "elementType": "all",
                "stylers": [
                    { "color": "#f2f2f2" }
                ]
            },
            {
                "featureType": "poi",
                "elementType": "all",
                "stylers": [
                    { "visibility": "off" }
                ]
            },
            {
                "featureType": "road",
                "elementType": "all",
                "stylers": [
                    { "saturation": -100 },
                    { "lightness": 45 }
                ]
            },
            {
                "featureType": "road.highway",
                "elementType": "all",
                "stylers": [
                    { "visibility": "simplified" }
                ]
            },
            {
                "featureType": "road.arterial",
                "elementType": "labels.icon",
                "stylers": [
                    { "visibility": "off" }
                ]
            },
            {
                "featureType": "transit",
                "elementType": "all",
                "stylers": [
                    { "visibility": "off" }
                ]
            },
            {
                "featureType": "water",
                "elementType": "all",
                "stylers": [
                    { "color": "#46bcec" },
                    { "visibility": "on" }
                ]
            }
        ]
    });

    infoWindow.value = new google.maps.InfoWindow();
    updateMap();
};

const updateMap = () => {
    // Clear existing markers
    markers.value.forEach(marker => marker.setMap(null));
    markers.value = [];
    
    if (heatmap.value) {
        heatmap.value.setMap(null);
    }

    // Filter data based on selections
    let filteredAlumni = [...props.mapData.alumni];
    let filteredCompanies = [...props.mapData.companies];

    if (selectedIndustry.value) {
        filteredCompanies = filteredCompanies.filter(c => c.industry === selectedIndustry.value);
    }

    if (selectedCountry.value) {
        filteredAlumni = filteredAlumni.filter(a => a.country === selectedCountry.value);
        filteredCompanies = filteredCompanies.filter(c => c.country === selectedCountry.value);
    }

    // Add markers based on active tab
    if (activeTab.value === 'alumni') {
        addAlumniMarkers(filteredAlumni);
    } else if (activeTab.value === 'companies') {
        addCompanyMarkers(filteredCompanies);
    } else if (activeTab.value === 'heatmap') {
        createEmploymentHeatmap(filteredCompanies);
    } else if (activeTab.value === 'network') {
        if (searchQuery.value) {
            setupProximitySearch(filteredAlumni);
        } else {
            addAlumniMarkers(filteredAlumni);
        }
    }
};

const addAlumniMarkers = (alumni) => {
    alumni.forEach(alum => {
        const marker = new google.maps.Marker({
            position: { lat: parseFloat(alum.lat), lng: parseFloat(alum.lng) },
            map: map.value,
            icon: {
                url: alum.photo || 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png',
                scaledSize: new google.maps.Size(40, 40),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(20, 20)
            }
        });

        const content = `
            <div class="info-window">
                <div class="flex items-center gap-3 mb-2">
                    <img src="${alum.photo || 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png'}" 
                         class="w-10 h-10 rounded-full">
                    <div>
                        <h3 class="font-bold">${alum.name}</h3>
                        <p class="text-sm">${alum.city}, ${alum.country}</p>
                    </div>
                </div>
                <p class="text-sm"><a href="mailto:${alum.email}" class="text-blue-500">${alum.email}</a></p>
            </div>
        `;

        marker.addListener('click', () => {
            infoWindow.value.setContent(content);
            infoWindow.value.open(map.value, marker);
        });

        markers.value.push(marker);
    });
};

const addCompanyMarkers = (companies) => {
    companies.forEach(company => {
        const marker = new google.maps.Marker({
            position: { lat: parseFloat(company.lat), lng: parseFloat(company.lng) },
            map: map.value,
            icon: {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: '#FF0000',
                fillOpacity: 0.8,
                strokeColor: '#FFFFFF',
                strokeWeight: 1,
                scale: 6 + (company.employee_count / 5)
            }
        });

        const content = `
            <div class="info-window">
                <h3 class="font-bold mb-1">${company.name}</h3>
                <p class="text-sm mb-1"><strong>Industry:</strong> ${company.industry}</p>
                <p class="text-sm mb-1"><strong>Employees:</strong> ${company.employee_count}</p>
                <p class="text-sm">${company.city}, ${company.country}</p>
            </div>
        `;

        marker.addListener('click', () => {
            infoWindow.value.setContent(content);
            infoWindow.value.open(map.value, marker);
        });

        markers.value.push(marker);
    });
};

const createEmploymentHeatmap = (companies) => {
    const heatmapData = companies.map(company => {
        return {
            location: new google.maps.LatLng(company.lat, company.lng),
            weight: company.employee_count
        };
    });

    heatmap.value = new google.maps.visualization.HeatmapLayer({
        data: heatmapData,
        map: map.value,
        radius: 30
    });

    heatmap.value.set('gradient', [
        'rgba(0, 255, 255, 0)',
        'rgba(0, 255, 255, 1)',
        'rgba(0, 191, 255, 1)',
        'rgba(0, 127, 255, 1)',
        'rgba(0, 63, 255, 1)',
        'rgba(0, 0, 255, 1)',
        'rgba(0, 0, 223, 1)',
        'rgba(0, 0, 191, 1)',
        'rgba(0, 0, 159, 1)',
        'rgba(0, 0, 127, 1)',
        'rgba(63, 0, 91, 1)',
        'rgba(127, 0, 63, 1)',
        'rgba(191, 0, 31, 1)',
        'rgba(255, 0, 0, 1)'
    ]);
};

const setupProximitySearch = (alumni) => {
    // Find the alumni matching the search query
    const searchedAlumni = alumni.find(alum => 
        alum.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
        alum.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    );

    if (searchedAlumni) {
        // Center map on this alumni
        map.value.setCenter({ lat: parseFloat(searchedAlumni.lat), lng: parseFloat(searchedAlumni.lng) });
        map.value.setZoom(10);

        // Add a special marker for the searched alumni
        const mainMarker = new google.maps.Marker({
            position: { lat: parseFloat(searchedAlumni.lat), lng: parseFloat(searchedAlumni.lng) },
            map: map.value,
            icon: {
                url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
            }
        });

        markers.value.push(mainMarker);

        // Add circle to show search radius
        const circle = new google.maps.Circle({
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#FF0000",
            fillOpacity: 0.2,
            map: map.value,
            center: { lat: parseFloat(searchedAlumni.lat), lng: parseFloat(searchedAlumni.lng) },
            radius: searchRadius.value * 1000 // Convert km to meters
        });

        // Find alumni within radius
        const nearbyAlumni = alumni.filter(alum => {
            if (alum.id === searchedAlumni.id) return false;
            
            const distance = calculateDistance(
                searchedAlumni.lat, searchedAlumni.lng,
                alum.lat, alum.lng
            );
            
            return distance <= searchRadius.value;
        });

        // Add markers for nearby alumni
        addAlumniMarkers(nearbyAlumni);
    }
};

// Haversine formula to calculate distance between two points
const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371; // Radius of the Earth in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c; // Distance in km
};
</script>

<template>
    <AdminLayout>
        <Head title="GIS Mapping" />
        
        <div class="main-content">
            <h1 class="page-title">Alumni Geographic Analytics</h1>

            <!-- Map Controls -->
            <div class="data-section">
                <div class="flex flex-col md:flex-row gap-4 mb-4">
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="filter-group">
                            <label>View Mode</label>
                            <select v-model="activeTab" class="w-full">
                                <option value="alumni">Alumni Distribution</option>
                                <option value="companies">Companies</option>
                                <option value="heatmap">Employment Heatmap</option>
                                <option value="network">Proximity Network</option>
                            </select>
                        </div>

                        <div class="filter-group" v-if="activeTab !== 'heatmap'">
                            <label>Country</label>
                            <select v-model="selectedCountry" class="w-full">
                                <option value="">All Countries</option>
                                <option v-for="(count, country) in mapData.regions" :value="country">
                                    {{ country }} ({{ count }})
                                </option>
                            </select>
                        </div>

                        <div class="filter-group" v-if="activeTab === 'companies' || activeTab === 'heatmap'">
                            <label>Industry</label>
                            <select v-model="selectedIndustry" class="w-full">
                                <option value="">All Industries</option>
                                <option v-for="(count, industry) in mapData.industries" :value="industry">
                                    {{ industry }} ({{ count }})
                                </option>
                            </select>
                        </div>

                        <div class="filter-group" v-if="activeTab === 'network'">
                            <label>Search Alumni</label>
                            <input 
                                v-model="searchQuery" 
                                type="text" 
                                placeholder="Name or email..."
                                class="w-full"
                            >
                        </div>

                        <div class="filter-group" v-if="activeTab === 'network' && searchQuery">
                            <label>Search Radius (km)</label>
                            <input 
                                v-model="searchRadius" 
                                type="range" 
                                min="1" 
                                max="500" 
                                class="w-full"
                            >
                            <span>{{ searchRadius }} km</span>
                        </div>
                    </div>
                </div>

                <!-- The Map -->
                <div id="map" class="w-full h-[600px] rounded-lg border border-gray-700"></div>
            </div>

            <!-- Analytics Summary -->
            <div class="data-section">
                <h2 class="section-title">Geographic Analytics</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="stat-card">
                        <h3 class="stat-title">Total Alumni Locations</h3>
                        <div class="stat-value">{{ mapData.alumni.length }}</div>
                        <div class="stat-subtitle">across {{ Object.keys(mapData.regions).length }} countries</div>
                    </div>

                    <div class="stat-card">
                        <h3 class="stat-title">Companies</h3>
                        <div class="stat-value">{{ mapData.companies.length }}</div>
                        <div class="stat-subtitle">in {{ new Set(mapData.companies.map(c => c.industry)).size }} industries</div>
                    </div>

                    <div class="stat-card">
        <h3 class="stat-title">Top Country</h3>
        <div class="stat-value">
            {{ topCountry.name }}
        </div>
        <div class="stat-subtitle">
            with {{ topCountry.count }} alumni
        </div>
    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.main-content {
    padding: 20px;
}

.page-title {
    font-size: 24px;
    margin-bottom: 20px;
    color: var(--text-primary);
}

.data-section {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    backdrop-filter: blur(12px);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
}

.filter-group label {
    font-size: 13px;
    color: var(--text-secondary);
    margin-bottom: 5px;
}

.filter-group input,
.filter-group select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid var(--card-border);
    border-radius: 6px;
    padding: 8px 12px;
    color: var(--text-primary);
    width: 100%;
}

#map {
    min-height: 600px;
}

.stat-card {
    background: rgba(40, 40, 40, 0.5);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 15px;
}

.stat-title {
    font-size: 16px;
    color: var(--text-secondary);
    margin-bottom: 10px;
}

.stat-value {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-subtitle {
    font-size: 13px;
    color: var(--text-secondary);
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 15px;
}

/* Info window styling */
.info-window {
    color: #333;
    padding: 8px;
    font-family: Arial, sans-serif;
}

.info-window h3 {
    margin: 0;
    font-size: 16px;
}

.info-window p {
    margin: 5px 0;
    font-size: 14px;
}

@media (max-width: 768px) {
    #map {
        height: 400px;
    }
    
    .filter-group {
        margin-bottom: 10px;
    }
}
</style>