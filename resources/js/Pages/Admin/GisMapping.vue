<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import { Loader } from '@googlemaps/js-api-loader';

const props = defineProps({
    alumniData: Array,
    companiesData: Array,
    employmentData: Array,
    filters: Object
});

// Reactive state
const map = ref(null);
const markers = ref([]);
const heatmap = ref(null);
const infoWindow = ref(null);
const selectedCompany = ref(null);
const selectedAlumni = ref(null);
const activeTab = ref('alumni');
const selectedIndustry = ref(props.filters.industry || '');
const selectedCountry = ref(props.filters.country || '');
const searchRadius = ref(props.filters.radius || 50);
const searchQuery = ref(props.filters.search || '');
const networkConnections = ref([]);

// Computed properties
const topCountry = computed(() => {
    const countries = {};
    props.alumniData.forEach(alum => {
        countries[alum.country] = (countries[alum.country] || 0) + 1;
    });
    const sorted = Object.entries(countries).sort((a,b) => b[1] - a[1]);
    return sorted.length > 0 ? { name: sorted[0][0], count: sorted[0][1] } : { name: 'N/A', count: 0 };
});

const industries = computed(() => {
    const industries = {};
    props.companiesData.forEach(company => {
        if (company.industry) {
            industries[company.industry] = (industries[company.industry] || 0) + 1;
        }
    });
    return industries;
});

const countries = computed(() => {
    const countries = {};
    props.alumniData.forEach(alum => {
        countries[alum.country] = (countries[alum.country] || 0) + 1;
    });
    return countries;
});

// Initialize the map
onMounted(() => {
    initMap();
});

// Watch for filter changes
watch([activeTab, selectedIndustry, selectedCountry, searchRadius, searchQuery], () => {
    updateMap();
});

const initMap = async () => {
    const loader = new Loader({
        apiKey: import.meta.env.VITE_GOOGLE_MAPS_API_KEY,
        version: "weekly",
        libraries: ["visualization", "geometry"]
    });

    await loader.load();
    
    // Create map centered on first alumni or default to world view
    const center = props.alumniData.length > 0 ? 
        { lat: parseFloat(props.alumniData[0].lat), lng: parseFloat(props.alumniData[0].lng) } : 
        { lat: 20, lng: 0 };
    
    map.value = new google.maps.Map(document.getElementById("map"), {
        center,
        zoom: 3,
        styles: getMapStyle()
    });

    infoWindow.value = new google.maps.InfoWindow();
    updateMap();
};

const updateMap = () => {
    // Clear existing markers, heatmap, and connections
    clearMap();

    // Filter data based on selections
    const filteredAlumni = filterAlumni();
    const filteredCompanies = filterCompanies();

    // Add visualization based on active tab
    switch(activeTab.value) {
        case 'alumni':
            renderAlumniDistribution(filteredAlumni);
            break;
        case 'companies':
            renderCompanyDistribution(filteredCompanies);
            break;
        case 'heatmap':
            renderEmploymentHeatmap(filteredCompanies);
            break;
        case 'network':
            if (searchQuery.value) {
                renderProximityNetwork(filteredAlumni);
            } else {
                renderAlumniDistribution(filteredAlumni);
            }
            break;
    }
};

const clearMap = () => {
    // Clear all markers
    markers.value.forEach(marker => {
        marker.setMap(null);
        if (marker.clickListeners) {
            google.maps.event.removeListener(marker.clickListeners);
        }
    });
    markers.value = [];
    
    // Clear heatmap if exists
    if (heatmap.value) {
        heatmap.value.setMap(null);
        heatmap.value = null;
    }
    
    // Clear network connections
    networkConnections.value.forEach(line => line.setMap(null));
    networkConnections.value = [];
    
    // Close any open info windows
    if (infoWindow.value) {
        infoWindow.value.close();
    }
    
    // Reset selections
    selectedCompany.value = null;
    selectedAlumni.value = null;
};

const filterAlumni = () => {
    let filtered = [...props.alumniData];
    if (selectedCountry.value) {
        filtered = filtered.filter(a => a.country === selectedCountry.value);
    }
    return filtered;
};

const filterCompanies = () => {
    let filtered = [...props.companiesData];
    if (selectedIndustry.value) {
        filtered = filtered.filter(c => c.industry === selectedIndustry.value);
    }
    if (selectedCountry.value) {
        filtered = filtered.filter(c => c.country === selectedCountry.value);
    }
    return filtered;
};



const renderAlumniDistribution = (alumni) => {
    alumni.forEach(alum => {
        const marker = new google.maps.Marker({
            position: { lat: parseFloat(alum.lat), lng: parseFloat(alum.lng) },
            map: map.value,
            icon: {
                url: alum.photo || '/images/default-avatar.png',
                scaledSize: new google.maps.Size(40, 40),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(20, 20)
            }
        });

        marker.addListener('click', () => {
            selectedAlumni.value = alum;
            infoWindow.value.setContent(generateAlumniInfoWindow(alum));
            infoWindow.value.open(map.value, marker);
        });

        markers.value.push(marker);
    });
};

const renderCompanyDistribution = (companies) => {
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
                scale: 6 + Math.log(company.employee_count || 1) * 2
            }
        });

        marker.addListener('click', () => {
            selectedCompany.value = company;
            infoWindow.value.setContent(generateCompanyInfoWindow(company));
            infoWindow.value.open(map.value, marker);
        });

        markers.value.push(marker);
    });
};

const renderEmploymentHeatmap = (companies) => {
    const heatmapData = companies.map(company => {
        return {
            location: new google.maps.LatLng(company.lat, company.lng),
            weight: company.employee_count || 1
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

const renderProximityNetwork = (alumni) => {
    const searchedAlumni = alumni.find(alum => 
        alum.name.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
        alum.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    );

    if (!searchedAlumni) return;

    // Center map on searched alumni
    map.value.setCenter({ lat: parseFloat(searchedAlumni.lat), lng: parseFloat(searchedAlumni.lng) });
    map.value.setZoom(10);

    // Add main marker
    const mainMarker = new google.maps.Marker({
        position: { lat: parseFloat(searchedAlumni.lat), lng: parseFloat(searchedAlumni.lng) },
        map: map.value,
        icon: {
            url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
        }
    });
    markers.value.push(mainMarker);

    // Add search radius circle
    const circle = new google.maps.Circle({
        strokeColor: "#FF0000",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#FF0000",
        fillOpacity: 0.2,
        map: map.value,
        center: { lat: parseFloat(searchedAlumni.lat), lng: parseFloat(searchedAlumni.lng) },
        radius: searchRadius.value * 1000
    });

    // Find nearby alumni
    const nearbyAlumni = alumni.filter(alum => {
        if (alum.id === searchedAlumni.id) return false;
        
        const distance = calculateDistance(
            searchedAlumni.lat, searchedAlumni.lng,
            alum.lat, alum.lng
        );
        
        return distance <= searchRadius.value;
    });

    // Add markers for nearby alumni
    nearbyAlumni.forEach(alum => {
        const marker = new google.maps.Marker({
            position: { lat: parseFloat(alum.lat), lng: parseFloat(alum.lng) },
            map: map.value,
            icon: {
                url: alum.photo || '/images/default-avatar.png',
                scaledSize: new google.maps.Size(30, 30)
            }
        });

        // Create connection line
        const line = new google.maps.Polyline({
            path: [
                { lat: parseFloat(searchedAlumni.lat), lng: parseFloat(searchedAlumni.lng) },
                { lat: parseFloat(alum.lat), lng: parseFloat(alum.lng) }
            ],
            geodesic: true,
            strokeColor: "#FF0000",
            strokeOpacity: 0.5,
            strokeWeight: 1,
            map: map.value
        });

        markers.value.push(marker);
        networkConnections.value.push(line);
    });
};

// Helper functions
const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371; // Earth radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
};

const getMapStyle = () => [
    {
        "featureType": "administrative",
        "elementType": "labels.text.fill",
        "stylers": [{ "color": "#444444" }]
    },
    {
        "featureType": "landscape",
        "elementType": "all",
        "stylers": [{ "color": "#f2f2f2" }]
    },
    {
        "featureType": "poi",
        "elementType": "all",
        "stylers": [{ "visibility": "off" }]
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
        "stylers": [{ "visibility": "simplified" }]
    },
    {
        "featureType": "road.arterial",
        "elementType": "labels.icon",
        "stylers": [{ "visibility": "off" }]
    },
    {
        "featureType": "transit",
        "elementType": "all",
        "stylers": [{ "visibility": "off" }]
    },
    {
        "featureType": "water",
        "elementType": "all",
        "stylers": [
            { "color": "#46bcec" },
            { "visibility": "on" }
        ]
    }
];

const generateAlumniInfoWindow = (alum) => `
    <div class="info-window">
        <div class="flex items-center gap-3 mb-2">
            <img src="${alum.photo || '/images/default-avatar.png'}" 
                 class="w-10 h-10 rounded-full">
            <div>
                <h3 class="font-bold">${alum.name}</h3>
                <p class="text-sm">${alum.city}, ${alum.country}</p>
            </div>
        </div>
        <p class="text-sm"><a href="mailto:${alum.email}" class="text-blue-500">${alum.email}</a></p>
        ${alum.current_company ? `
            <div class="mt-2">
                <p class="text-sm"><strong>Current Company:</strong> ${alum.current_company.name}</p>
                <p class="text-sm"><strong>Position:</strong> ${alum.current_job_title}</p>
            </div>
        ` : ''}
        <div class="mt-3">
            <a href="/profile/${alum.id}" class="text-blue-500 text-sm">View Full Profile</a>
        </div>
    </div>
`;

const generateCompanyInfoWindow = (company) => `
    <div class="info-window" style="
        max-width: 300px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: 8px;
        padding: 12px;
        color: var(--text-primary);
    ">
        <div class="info-header" style="
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        ">
            <i class="fas fa-building" style="color: var(--primary);"></i>
            <h3 style="margin: 0; font-size: 16px; font-weight: 600;">${company.name}</h3>
        </div>
        
        <div class="info-details" style="margin-bottom: 12px;">
            <p style="margin: 4px 0; font-size: 14px;">
                <strong style="color: var(--primary);">Industry:</strong> ${company.industry || 'N/A'}
            </p>
            <p style="margin: 4px 0; font-size: 14px;">
                <strong style="color: var(--primary);">Location:</strong> 
                ${company.city || ''}${company.city && company.country ? ', ' : ''}${company.country || ''}
            </p>
            <p style="margin: 4px 0; font-size: 14px;">
                <strong style="color: var(--primary);">Alumni Employees:</strong> ${company.employee_count || 0}
            </p>
        </div>
        
        ${company.employee_count > 0 ? `
            <div class="user-profiles" style="
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin: 12px 0;
            ">
                ${company.employees.slice(0, 5).map(user => `
                    <div class="profile-container" style="
                        position: relative;
                        width: 40px;
                        height: 40px;
                    ">
                        <img src="${user.photo || '/images/default-avatar.png'}" 
                             alt="${user.name}"
                             title="${user.name}"
                             style="
                                width: 100%;
                                height: 100%;
                                border-radius: 50%;
                                object-fit: cover;
                                border: 2px solid var(--primary);
                                cursor: pointer;
                             ">
                    </div>
                `).join('')}
            </div>
            
            ${company.employee_count > 5 ? `
                <p style="
                    font-size: 12px;
                    color: var(--text-secondary);
                    margin: 4px 0;
                ">
                    +${company.employee_count - 5} more alumni
                </p>
            ` : ''}
            
            <div class="info-footer" style="
                margin-top: 12px;
                padding-top: 8px;
                border-top: 1px solid var(--card-border);
            ">
                <a href="/company/${company.id}/employees" style="
                    font-size: 14px;
                    color: var(--primary);
                    text-decoration: none;
                    display: inline-block;
                    padding: 4px 8px;
                    border-radius: 4px;
                    background: var(--primary-light);
                ">
                    View all alumni employees
                </a>
            </div>
        ` : ''}
    </div>
`;

</script>

<template>
    <AdminLayout title="Alumni Geographic Analytics">
        <Head title="GIS Mapping" />
        
        <div class="main-content">
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
                                <option v-for="(count, country) in countries" :value="country">
                                    {{ country }} ({{ count }})
                                </option>
                            </select>
                        </div>

                        <div class="filter-group" v-if="activeTab === 'companies' || activeTab === 'heatmap'">
                            <label>Industry</label>
                            <select v-model="selectedIndustry" class="w-full">
                                <option value="">All Industries</option>
                                <option v-for="(count, industry) in industries" :value="industry">
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

                <!-- Company Info Window -->
                <GMapInfoWindow 
                    v-if="selectedCompany" 
                    class="info-window"
                    :closeclick="true"
                    @closeclick="selectedCompany = null"
                >
                    <div class="info-content">
                        <div class="info-header">
                            <i class="fas fa-building"></i>
                            <h3>{{ selectedCompany.name }}</h3>
                        </div>
                        <p class="info-industry">{{ selectedCompany.industry || 'N/A' }}</p>
                        <p class="info-employees">Alumni Employees ({{ selectedCompany.employee_count || 0 }}):</p>
                        
                        <div class="user-profiles" v-if="selectedCompany.employees && selectedCompany.employees.length > 0">
                            <template v-for="(user, userIndex) in selectedCompany.employees.slice(0, 5)" :key="user.id">
                                <Link 
                                    :href="`/profile/${user.id}`"
                                    class="user-profile"
                                    :style="{
                                        'z-index': 5 - userIndex,
                                        'margin-left': userIndex > 0 ? '-12px' : '0'
                                    }"
                                >
                                    <img 
                                        :src="user.photo || '/images/default-avatar.png'" 
                                        alt="Profile" 
                                        class="profile-image"
                                    />
                                    <span class="profile-tooltip">{{ user.name }}</span>
                                </Link>
                            </template>
                        </div>
                        
                        <div class="info-footer" v-if="selectedCompany.employees && selectedCompany.employees.length > 0">
                            <template v-if="selectedCompany.employees.length > 5">
                                <p class="more-users">
                                    +{{ selectedCompany.employees.length - 5 }} more
                                </p>
                            </template>
                            
                            <Link 
                                :href="`/company/${selectedCompany.id}/employees`"
                                class="view-all"
                            >
                                View all alumni employees
                            </Link>
                        </div>
                    </div>
                </GMapInfoWindow>
            </div>

            <!-- Analytics Summary -->
            <div class="data-section">
                <h2 class="section-title">Geographic Analytics</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="stat-card">
                        <h3 class="stat-title">Total Alumni Locations</h3>
                        <div class="stat-value">{{ alumniData.length }}</div>
                        <div class="stat-subtitle">across {{ Object.keys(countries).length }} countries</div>
                    </div>

                    <div class="stat-card">
                        <h3 class="stat-title">Companies</h3>
                        <div class="stat-value">{{ companiesData.length }}</div>
                        <div class="stat-subtitle">in {{ Object.keys(industries).length }} industries</div>
                    </div>

                    <div class="stat-card">
                        <h3 class="stat-title">Top Country</h3>
                        <div class="stat-value">{{ topCountry.name }}</div>
                        <div class="stat-subtitle">with {{ topCountry.count }} alumni</div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
.info-window {
    max-width: 300px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.info-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.info-header i {
    color: #4a5568;
    font-size: 18px;
}

.info-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.info-industry, .info-location, .info-employees {
    margin: 4px 0;
    font-size: 14px;
    color: #4a5568;
}

.user-profiles {
    display: flex;
    margin: 10px 0;
}

.user-profile {
    position: relative;
    display: inline-block;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: 2px solid white;
    transition: transform 0.2s;
}

.user-profile:hover {
    transform: scale(1.2);
    z-index: 10 !important;
}

.profile-image {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}

.profile-tooltip {
    visibility: hidden;
    width: auto;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 4px;
    padding: 4px 8px;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 0.3s;
    white-space: nowrap;
    font-size: 12px;
}

.user-profile:hover .profile-tooltip {
    visibility: visible;
    opacity: 1;
}

.more-users {
    font-size: 12px;
    color: #718096;
    margin: 4px 0;
}

.info-footer {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #e2e8f0;
}

.view-all {
    font-size: 14px;
    color: #4299e1;
    text-decoration: none;
}

.view-all:hover {
    text-decoration: underline;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-title {
    font-size: 14px;
    color: #718096;
    margin: 0 0 8px 0;
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
    margin: 0;
    color: #2d3748;
}

.stat-subtitle {
    font-size: 12px;
    color: #718096;
    margin: 4px 0 0 0;
}

.filter-group {
    margin-bottom: 12px;
}

.filter-group label {
    display: block;
    margin-bottom: 4px;
    font-size: 14px;
    font-weight: 500;
    color: #4a5568;
}

.filter-group select, .filter-group input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 14px;
}

.data-section {
    margin-bottom: 24px;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 16px 0;
    color: #2d3748;
}
</style>