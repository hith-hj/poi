<template>
    <div class="leaflet-wrapper">
        <div ref="mapDiv" id="map" class="leaflet-container relative z-10" />
    </div>
    <Modal v-model="showPointModal">
        <template #title>{{ selectedPoint?.name || 'Point of Interest' }}</template>

        <div v-if="selectedPoint">
            <p><strong>Coordinates:</strong> {{ selectedPoint.coords.join(', ') }}</p>
            <p>This is a detailed description of the point and its heritage.</p>
        </div>
        <div v-else>
            <p>Loading point details...</p>
        </div>

        <template #footer>
            <button @click="showPointModal = false" class="rounded bg-blue-600 px-4 py-2 text-white">Close</button>
        </template>
    </Modal>
</template>

<script setup lang="ts">
import L from 'leaflet';
import { debounce } from 'lodash-es';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import syriaGeoJson from '../lib/sy.json';
import Modal from './ui/Modal.vue';


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

const emit = defineEmits<{
    (e: 'mapMoved', state: { center: [number, number]; zoom: number }): void;
}>();

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

let currentLayer: L.TileLayer | L.LayerGroup | null = null;
let markersLayer: L.LayerGroup | null = null;
const mapDiv = ref<HTMLElement | null>(null);
const mapRef = ref<L.Map | null>(null);
const showPointModal = ref(false);
const selectedPoint = ref(null);
let calls = ref(0);

function initMap() {
    if (!mapDiv.value) return;

    const map = L.map(mapDiv.value, { zoomControl: false }).setView(props.view, props.zoom);
    // L.control.zoom({ position: 'bottomright' }).addTo(map);
    mapRef.value = map;
    markersLayer = L.layerGroup().addTo(map);
    currentLayer = layers.normal;
    currentLayer.addTo(map);
    map.on('moveend', debouncedEmitMapState);
    map.on('zoomend', debouncedEmitMapState);

    setBoundary();
}

function currentLocation() {
    mapRef.value?.locate({ setView: true, maxZoom: 16 });
}

function createIcon(color = '#0070c0', size = 32, index = 0): L.DivIcon {
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

function fitPoints() {
  if (!mapRef?.value) return;

  const layer = (markersLayer && ('value' in markersLayer ? markersLayer.value : markersLayer)) as any;
  if (!layer) return;

  let bounds: L.LatLngBounds | null = null;
  if (typeof layer.getBounds === 'function') {
    try {
      bounds = layer.getBounds();
    } catch {
      bounds = null;
    }
  }

  if (!bounds || !bounds.isValid || !bounds.isValid()) {
    bounds = buildBounds(layer)
  }

  if (bounds && bounds.isValid && bounds.isValid()) {
    mapRef.value.fitBounds(bounds, { padding: [5, 5],zoom:20 });
  }
}

function buildBounds(layer){
    const latlngs: L.LatLng[] = [];
    const layers = typeof layer.getLayers === 'function' ? layer.getLayers() : [];
    for (const l of layers) {
      if (l && typeof l.getLatLng === 'function') {
        const ll = l.getLatLng();
        if (ll && typeof ll.lat === 'number' && typeof ll.lng === 'number') latlngs.push(ll);
      } else if (l && l.getBounds && typeof l.getBounds === 'function') {
        const b = l.getBounds();
        if (b && b.isValid && b.isValid()) {
          latlngs.push(b.getSouthWest(), b.getNorthEast());
        }
      }
    }
    if (latlngs.length) return L.latLngBounds(latlngs);
}

function setLayer(layerName: keyof typeof layers) {
    if (!mapRef?.value) return;
    const map = mapRef.value;
    const newLayer = layers[layerName];
    if (!newLayer) {
        console.warn(`Layer "${String(layerName)}" not found`);
        return;
    }

    if (currentLayer && map.hasLayer(currentLayer)) {
        try {
            map.removeLayer(currentLayer);
        } catch (err) {
            console.warn('Failed to remove current layer', err);
        }
    }

    try {
        const layerToAdd = typeof newLayer === 'function' ? newLayer() : newLayer;
        currentLayer = layerToAdd as any;
        map.addLayer(currentLayer);
    } catch (err) {
        console.error('Failed to add new layer', err);
        return;
    }
    setBoundary();
}

function setView(latlng: [number, number], zm?: number) {
    if (!mapRef.value) return;
    mapRef.value.setView(latlng, zm ?? mapRef.value.getZoom());
}

function setBoundary() {
    if (!mapRef.value) return;
    const highlightStyle = {
        color: '#006400',
        weight: 1,
        fillColor: '#006400',
        fillOpacity: 0.0,
    };

    const geoLayer = L.geoJSON(syriaGeoJson, {
        style: highlightStyle,
        onEachFeature: (feature, layer) => {
            console.log(layer.getBounds())
            layer.on({
                // mouseover: () => layer.setStyle({ fillOpacity: 0.0 }),
                // mouseout: () => layer.setStyle({ fillOpacity: 0.04 }),
                click: () => setView(midpointSimple(
                    layer.getBounds()._southWest,
                    layer.getBounds()._northEast
                ),getZoom()),
            });
        },
    }).addTo(mapRef.value);

    // fitBounds(geoLayer.getBounds());
    setMaxBounds(geoLayer.getBounds());
}

function midpointSimple(a, b) {
  return {
    lat: (a.lat + b.lat) / 2,
    lng: (a.lng + b.lng) / 2,
  };
}

function setMaxBounds(bounds){
    if (!mapRef.value) return;
    mapRef.value.setMaxBounds(bounds);
}

function fitBounds(bounds){
    if (!mapRef.value) return;
    mapRef.value.fitBounds(bounds);
}

function getZoom(){
    if (!mapRef.value) return;
    return mapRef.value?.getZoom()
}

function destroyMap() {
    if (!mapRef.value) return;
    mapRef.value.off();
    mapRef.value.remove();
    mapRef.value = null;
}

function openModal(point){
    selectedPoint.value = point;
    showPointModal.value = true;
}

const debouncedEmitMapState = debounce(() => {
    if (!mapRef.value) return;
    if (calls.value < 1) {
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

const clickHandler = (e: L.LeafletMouseEvent) => {
    const map = mapRef.value;
    if (!map || !e?.latlng) return;
    const { lat, lng } = e.latlng;
    const clickedCoords = { lat: +lat.toFixed(6), lng: +lng.toFixed(6) };

    L.popup({ closeButton: true, autoClose: true })
      .setLatLng(e.latlng)
      .setContent(`Lat: ${clickedCoords.lat}, Lng: ${clickedCoords.lng}`)
      .openOn(mapRef.value as L.Map);
};

onMounted(async () => {
    await nextTick();
    initMap();
    window.addEventListener('resize', () => mapRef.value?.invalidateSize());
    mapRef.value.on('dblclick', clickHandler);
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
watch(
    () => props.points,
    (newPoints) => {
        if (!mapRef.value || !markersLayer) return;

        if (markersLayer.getLayers().length > 200) {
            markersLayer.clearLayers();
        }

        newPoints.forEach((point, index) => {
            const marker = L.marker(point.coords, {
                icon: createIcon(point.color, point.size, index),
            });
            // Attach click handler to emit event to the Vue parent
            marker.on('click', () => {
                openModal(point);
                // setView(point.coords, mapRef.getZoom());
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


