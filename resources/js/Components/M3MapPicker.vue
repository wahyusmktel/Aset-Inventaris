<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import L from 'leaflet';

const props = defineProps({
  latitude: {
    type: [String, Number],
    default: '-6.175392', // Default Jakarta
  },
  longitude: {
    type: [String, Number],
    default: '106.827153',
  },
  address: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:latitude', 'update:longitude', 'update:address', 'location-selected']);

const mapContainer = ref(null);
let map = null;
let marker = null;

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const isReverseGeocoding = ref(false);

// Leaflet default icon fix
const customIcon = L.icon({
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
  iconSize: [25, 41],
  iconAnchor: [12, 41],
  popupAnchor: [1, -34],
  shadowSize: [41, 41],
});

const initMap = () => {
  if (!mapContainer.value) return;

  const lat = parseFloat(props.latitude) || -6.175392;
  const lng = parseFloat(props.longitude) || 106.827153;

  map = L.map(mapContainer.value, {
    center: [lat, lng],
    zoom: 15,
    zoomControl: true,
  });

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map);

  marker = L.marker([lat, lng], {
    icon: customIcon,
    draggable: true,
  }).addTo(map);

  // Marker drag event
  marker.on('dragend', async (e) => {
    const position = marker.getLatLng();
    updateLocation(position.lat, position.lng, true);
  });

  // Map click event
  map.on('click', (e) => {
    marker.setLatLng(e.latlng);
    updateLocation(e.latlng.lat, e.latlng.lng, true);
  });

  // Trigger resize fix
  setTimeout(() => {
    map.invalidateSize();
  }, 300);
};

const updateLocation = async (lat, lng, shouldReverseGeocode = false) => {
  const formattedLat = parseFloat(lat).toFixed(6);
  const formattedLng = parseFloat(lng).toFixed(6);

  emit('update:latitude', formattedLat);
  emit('update:longitude', formattedLng);

  if (shouldReverseGeocode) {
    await reverseGeocode(formattedLat, formattedLng);
  }
};

// OpenStreetMap Nominatim Reverse Geocoding
const reverseGeocode = async (lat, lng) => {
  isReverseGeocoding.value = true;
  try {
    const res = await fetch(
      `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}&accept-language=id`,
      {
        headers: {
          'Accept': 'application/json',
          'User-Agent': 'TemplateUI-School-Manager/1.0',
        },
      }
    );
    if (res.ok) {
      const data = await res.json();
      if (data && data.display_name) {
        emit('update:address', data.display_name);
        emit('location-selected', {
          lat,
          lng,
          address: data.display_name,
        });
      }
    }
  } catch (err) {
    console.warn('Reverse geocoding error:', err);
  } finally {
    isReverseGeocoding.value = false;
  }
};

// OpenStreetMap Nominatim Location Search
let searchTimeout = null;
const handleSearchInput = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  if (!searchQuery.value || searchQuery.value.trim().length < 3) {
    searchResults.value = [];
    return;
  }

  searchTimeout = setTimeout(async () => {
    isSearching.value = true;
    try {
      const res = await fetch(
        `https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(
          searchQuery.value
        )}&countrycodes=id&limit=5&accept-language=id`,
        {
          headers: {
            'Accept': 'application/json',
            'User-Agent': 'TemplateUI-School-Manager/1.0',
          },
        }
      );
      if (res.ok) {
        searchResults.value = await res.json();
      }
    } catch (e) {
      console.warn('Search geocoding error:', e);
    } finally {
      isSearching.value = false;
    }
  }, 400);
};

const selectSearchResult = (item) => {
  const lat = parseFloat(item.lat);
  const lng = parseFloat(item.lon);

  if (map && marker) {
    map.setView([lat, lng], 16);
    marker.setLatLng([lat, lng]);
  }

  emit('update:latitude', lat.toFixed(6));
  emit('update:longitude', lng.toFixed(6));
  emit('update:address', item.display_name);
  emit('location-selected', {
    lat: lat.toFixed(6),
    lng: lng.toFixed(6),
    address: item.display_name,
  });

  searchQuery.value = '';
  searchResults.value = [];
};

const useCurrentLocation = () => {
  if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        if (map && marker) {
          map.setView([lat, lng], 16);
          marker.setLatLng([lat, lng]);
        }
        updateLocation(lat, lng, true);
      },
      (err) => {
        alert('Tidak dapat mendeteksi lokasi saat ini.');
      }
    );
  }
};

watch(
  () => [props.latitude, props.longitude],
  ([newLat, newLng]) => {
    if (map && marker && newLat && newLng) {
      const lat = parseFloat(newLat);
      const lng = parseFloat(newLng);
      if (!isNaN(lat) && !isNaN(lng)) {
        const currentPos = marker.getLatLng();
        if (currentPos.lat !== lat || currentPos.lng !== lng) {
          marker.setLatLng([lat, lng]);
          map.setView([lat, lng], map.getZoom());
        }
      }
    }
  }
);

onMounted(() => {
  nextTick(() => {
    initMap();
  });
});

onUnmounted(() => {
  if (map) {
    map.remove();
  }
});
</script>

<template>
  <div class="space-y-3 w-full">
    <!-- Search Bar & Current GPS Button -->
    <div class="flex items-center gap-2">
      <!-- Search Input -->
      <div class="relative flex-1">
        <span class="material-symbols-outlined text-[20px] text-surface-on-variant absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
          search
        </span>
        <input
          type="text"
          v-model="searchQuery"
          @input="handleSearchInput"
          placeholder="Cari lokasi sekolah / jalan di OpenStreetMap..."
          class="w-full h-11 pl-10 pr-10 rounded-m3-md border border-outline focus:border-primary focus:ring-0 bg-surface-container-lowest text-xs text-surface-foreground placeholder:text-surface-on-variant"
        />
        <span v-if="isSearching" class="absolute right-3.5 top-1/2 -translate-y-1/2">
          <svg class="animate-spin h-4 w-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
        </span>

        <!-- Search Auto-Suggest Dropdown Results -->
        <div
          v-if="searchResults.length > 0"
          class="absolute top-12 left-0 right-0 z-[1000] bg-surface-container-lowest border border-outline-variant rounded-m3-md shadow-m3-elevation-3 max-h-52 overflow-y-auto divide-y divide-outline-variant/30"
        >
          <button
            v-for="item in searchResults"
            :key="item.place_id"
            type="button"
            @click="selectSearchResult(item)"
            class="w-full text-left p-3 hover:bg-primary-container/40 transition-colors flex items-start gap-2.5 text-xs text-surface-foreground cursor-pointer"
          >
            <span class="material-symbols-outlined text-[18px] text-primary shrink-0 mt-0.5">location_on</span>
            <span class="line-clamp-2 leading-relaxed">{{ item.display_name }}</span>
          </button>
        </div>
      </div>

      <!-- Current Location GPS Button -->
      <button
        type="button"
        @click="useCurrentLocation"
        class="h-11 px-3.5 rounded-m3-md border border-outline hover:bg-primary-container hover:border-primary text-xs font-semibold text-surface-foreground flex items-center gap-1.5 transition-colors cursor-pointer shrink-0"
        title="Gunakan Lokasi GPS Saya"
      >
        <span class="material-symbols-outlined text-[18px] text-primary">my_location</span>
        <span class="hidden sm:inline">GPS Saya</span>
      </button>
    </div>

    <!-- Leaflet Map Canvas Container -->
    <div class="relative w-full h-64 sm:h-72 rounded-m3-md overflow-hidden border border-outline shadow-inner">
      <div ref="mapContainer" class="w-full h-full z-0"></div>

      <!-- Instruction Overlay Badge -->
      <div class="absolute bottom-2.5 left-2.5 z-[500] bg-surface-container-lowest/90 backdrop-blur-md px-3 py-1.5 rounded-full border border-outline-variant/50 shadow-sm flex items-center gap-1.5 text-[11px] text-surface-on-variant select-none pointer-events-none">
        <span class="material-symbols-outlined text-[15px] text-primary">touch_app</span>
        <span>Klik atau geser pin merah untuk memilih titik lokasi</span>
      </div>

      <!-- Loading Reverse Geocode Overlay -->
      <div
        v-if="isReverseGeocoding"
        class="absolute top-2.5 right-2.5 z-[500] bg-primary-container text-primary-on-container px-3 py-1 rounded-full text-[11px] font-bold shadow-md flex items-center gap-1.5"
      >
        <svg class="animate-spin h-3.5 w-3.5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Mengambil alamat...</span>
      </div>
    </div>

    <!-- Coordinates Display -->
    <div class="flex items-center justify-between text-[11px] text-surface-on-variant bg-surface-container-low px-3 py-2 rounded-m3-xs border border-outline-variant/40">
      <div class="flex items-center gap-2">
        <span class="font-bold text-surface-foreground">Titik Koordinat:</span>
        <span class="font-mono bg-white px-2 py-0.5 rounded border border-outline-variant/50 text-primary font-bold">
          {{ latitude || '0.000000' }}, {{ longitude || '0.000000' }}
        </span>
      </div>
      <span class="text-[10px] text-emerald-700 font-semibold flex items-center gap-1">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        <span>OpenStreetMap Connected</span>
      </span>
    </div>
  </div>
</template>
