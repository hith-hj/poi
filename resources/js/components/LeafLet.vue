<template>
    <div class="leaflet-wrapper">
        <div ref="mapDiv" id="map" class="leaflet-container relative z-10" />
    </div>
</template>

<script setup lang="ts">
import L from 'leaflet';
import { debounce } from 'lodash-es';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import syriaGeoJson from '../lib/sy.json';

// 1. Define Props and Emits
const props = defineProps<{
    view: [number, number];
    zoom: number;
    points: Array<{
        id: number;
        coords: [number, number];
        color: string;
        size: number;
        // Add other POI properties you need to pass back on click
        name: string;
    }>;
}>();

// 2. Reactive Refs and Map State
const mapDiv = ref<HTMLElement | null>(null);
const mapRef = ref<L.Map | null>(null);

let currentLayer: L.TileLayer | L.LayerGroup | null = null;
let markersLayer: L.LayerGroup | null = null; // Layer to hold all POI markers
let calls = ref(0);

const layers = {
    normal: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }),
    satellite: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenTopoMap' }),
    terrain: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri',
    }),
    hybrid: L.layerGroup([
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles © Esri',
        }),
        L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Labels © Esri',
        }),
    ]),
};

// 3. Core Map Logic
function createDivIcon(color = '#0070c0', size = 32, index = 0): L.DivIcon {
    // Add index
    const html = `
    <div
      class="animated-marker"
      style="--marker-color: ${color}; --marker-size: ${size}px; --animation-delay: ${index * 50}ms;"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" fill="currentColor" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
      </svg>
    </div>
  `;

    return L.divIcon({
        html,
        className: 'poi-marker-container',
        iconSize: [size, size],
        iconAnchor: [size / 2, size],
        popupAnchor: [0, -size],
    });
}

const emit = defineEmits<{
    (e: 'mapMoved', state: { center: [number, number]; zoom: number }): void;
    (e: 'markerClicked', point: any): void;
}>();

const debouncedEmitMapState = debounce(() => {
    if (!mapRef.value) return;
    if (calls.value < 3) {
        calls.value += 1;
        return;
    }
    calls.value = 0;
    const center = mapRef.value.getCenter();
    const zoom = mapRef.value.getZoom();
    const bounds = mapRef.value.getBounds();
    emit('mapMoved', {
        center: [center.lat, center.lng],
        zoom,
        bbox: [bounds.getSouthWest().lat, bounds.getSouthWest().lng, bounds.getNorthEast().lat, bounds.getNorthEast().lng],
    });
}, 500);

function initMap() {
    if (!mapDiv.value) return;

    const map = L.map(mapDiv.value, { zoomControl: false }).setView(props.view, props.zoom);
    // L.control.zoom({ position: 'bottomright' }).addTo(map);
    mapRef.value = map;
    markersLayer = L.layerGroup().addTo(map); // Initialize the markers layer
    currentLayer = layers.normal;
    currentLayer.addTo(map);

    // Set up event listeners with the debounced function
    map.on('moveend', debouncedEmitMapState);
    map.on('zoomend', debouncedEmitMapState);

    setBoundry();
}

function destroyMap() {
    if (!mapRef.value) return;
    mapRef.value.off();
    mapRef.value.remove();
    mapRef.value = null;
}

// 4. Methods exposed to the parent (Map.vue)
function fitPoints() {
    if (!mapRef.value || !markersLayer || markersLayer.getLayers().length === 0) return;

    const bounds = markersLayer.getBounds();
    if (bounds.isValid()) {
        mapRef.value.fitBounds(bounds, { padding: [50, 50] });
    }
}

function setLayer(layerName: keyof typeof layers) {
    if (!mapRef.value || !currentLayer) return;

    // Remove the old layer
    mapRef.value.removeLayer(currentLayer);

    // Add the new layer
    currentLayer = layers[layerName];
    currentLayer.addTo(mapRef.value);

    // Ensure other layers (like boundary and markers) are on top
    markersLayer?.bringToFront();
}

function currentLocation() {
    mapRef.value?.locate({ setView: true, maxZoom: 16 });
}

function setView(latlng: [number, number], zm?: number) {
    if (!mapRef.value) return;
    mapRef.value.setView(latlng, zm ?? mapRef.value.getZoom());
}

function setBoundry() {
    if (!mapRef.value) return;
    const highlightStyle = {
        color: '#006400',
        weight: 1,
        fillColor: '#006400',
        fillOpacity: 0.0,
    };

    const geoLayer = L.geoJSON(syriaGeoJson as any, {
        // Ensure syriaGeoJson is properly typed
        style: highlightStyle,
        onEachFeature: (feature, layer) => {
            layer.on({
                // mouseover: () => layer.setStyle({ fillOpacity: 0.0 }),
                // mouseout: () => layer.setStyle({ fillOpacity: 0.0 }),
                click: () => mapRef.value?.fitBounds(layer.getBounds()),
            });
        },
    }).addTo(mapRef.value);

    mapRef.value.fitBounds(geoLayer.getBounds());
    mapRef.value.setMaxBounds(geoLayer.getBounds());
}

// 5. Lifecycle and Watchers
onMounted(async () => {
    await nextTick();
    initMap();
    window.addEventListener('resize', () => mapRef.value?.invalidateSize());
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', () => mapRef.value?.invalidateSize());
    destroyMap();
});

watch(
    () => props.view,
    (v) => mapRef.value?.setView(v, mapRef.value.getZoom()),
);
watch(
    () => props.zoom,
    (z) => mapRef.value?.setZoom(z),
);

// CRITICAL WATCHER: Handles all POI markers efficiently in one go
watch(
    () => props.points,
    (newPoints) => {
        if (!mapRef.value || !markersLayer) return;

        if (markersLayer.getLayers().length > 200) {
            markersLayer.clearLayers();
        }

        newPoints.forEach((point, index) => {
            const marker = L.marker(point.coords, {
                icon: createDivIcon(point.color, point.size, index),
            });
            // Attach click handler to emit event to the Vue parent
            marker.on('click', () => {
                emit('markerClicked', point); // Pass the full point object back
            });

            markersLayer?.addLayer(marker);
        });
    },
    { deep: true, immediate: true },
);

// 6. Expose public methods
defineExpose({
    getMap: () => mapRef.value,
    setView,
    destroyMap,
    fitPoints,
    setLayer,
    currentLocation,
    // NOTE: addMarker is now internal, managed by the points watcher
});
</script>

<style scoped>
.leaflet-container {
    width: 100%;
    height: 100vh; /* Set a default height */
}
.leaflet-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
}
</style>
