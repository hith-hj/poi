<template>
    <div class="absolute h-full w-screen">
        <div
            ref="mapDiv"
            class="relative z-1 h-full w-full"
        />
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
                <button
                    @click="showPointModal = false"
                    class="rounded bg-blue-600 px-4 py-2 text-white"
                >
                    Close
                </button>
            </template>
        </Modal>
    </div>
</template>

<script setup lang="ts">
import L from 'leaflet';
import { debounce } from 'lodash-es';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Modal from './ui/Modal.vue';

const props = defineProps<{
    view: [number, number];
    zoom: number;
    points: Array<{
        id: number;
        coords: [number, number];
        color: string;
        size: number;
        name: string;
    }>;
}>();

const emit = defineEmits<{
    (e: 'mapMoved', state: { center: [number, number]; zoom: number; bbox: number[] }): void;
}>();

const layers = {
    normal: () =>
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap',
        }),
    light: () =>
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CartoDB',
        }),
    dark: () =>
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap &copy; CartoDB',
        }),
    satellite: () =>
        L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenTopoMap',
        }),
    terrain: () =>
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles © Esri',
        }),
};

const mapDiv = ref<HTMLElement | null>(null);
const mapRef = ref<L.Map | null>(null);
const currentLayer = ref<L.Layer | null>(null);
const markersLayer = ref<L.LayerGroup | null>(null);
const showPointModal = ref(false);
const selectedPoint = ref(null);
const calls = ref(0);

function initMap() {
    if (!mapDiv.value) return;

    const map = L.map(mapDiv.value, {
        zoomControl: false,
        minZoom: 10,
        maxZoom: 20,
    }).setView(props.view, props.zoom);

    mapRef.value = map;
    markersLayer.value = L.layerGroup().addTo(map);

    currentLayer.value = layers.light();
    map.addLayer(currentLayer.value);

    map.on('moveend', debouncedEmitMapState);
    map.on('zoomend', debouncedEmitMapState);

    setTimeout(() => {
        if (map._loaded) setBoundary();
    }, 100);
}

function setLayer(layerName: keyof typeof layers) {
    if (!mapRef.value || !mapRef.value._loaded) return;

    const map = mapRef.value;
    const newLayer = layers[layerName]?.();
    if (!newLayer) return;

    const fadeDuration = 500;
    const oldLayer = currentLayer.value;

    currentLayer.value = newLayer;
    map.addLayer(newLayer);

    const newContainer = (newLayer as any)._container;
    if (newContainer) {
        newContainer.style.opacity = '0';
        newContainer.style.transition = `opacity ${fadeDuration}ms`;
        setTimeout(() => {
            newContainer.style.opacity = '1';
        }, 50);
    }

    if (oldLayer && map.hasLayer(oldLayer)) {
        const oldContainer = (oldLayer as any)._container;
        if (oldContainer) {
            oldContainer.style.transition = `opacity ${fadeDuration}ms`;
            oldContainer.style.opacity = '0';
        }

        setTimeout(() => {
            map.removeLayer(oldLayer);
        }, fadeDuration);
    }

    setTimeout(() => {
        if (map._loaded) setBoundary();
    }, fadeDuration + 100);
}

function setBoundary() {
    if (!mapRef.value || !mapRef.value._loaded) return;

    // const geoLayer = L.geoJSON(syriaGeoJson, {
    //   style: {
    //     color: '#006400',
    //     weight: 1,
    //     fillColor: '#006400',
    //     fillOpacity: 0.0,
    //   },
    // }).addTo(mapRef.value);
    // mapRef.value.setMaxBounds(geoLayer.getBounds());

    const syriaBounds = L.latLngBounds(L.latLng(32.0, 35.5), L.latLng(37.5, 42.0));

    mapRef.value.setMaxBounds(syriaBounds);
}

function handleResize() {
    if (mapRef.value && mapRef.value._loaded) {
        mapRef.value.invalidateSize();
    }
}

function destroyMap() {
    if (mapRef.value) {
        mapRef.value.off();
        mapRef.value.remove();
        mapRef.value = null;
    }
}

function createIcon(color = '#0070c0', size = 32, index = 0): L.DivIcon {
    const html = `
        <div class="animated-marker" style="--marker-color: ${color}; --marker-size: ${size}px; --animation-delay: ${index * 50}ms;">
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

function currentLocation() {
    if (!mapRef.value) return;
    mapRef.value?.locate({ setView: true, maxZoom: 16 });
}

function fitPoints() {
    if (!mapRef.value || !markersLayer.value) return;

    const bounds = buildBounds(markersLayer.value);
    if (bounds && bounds.isValid()) {
        mapRef.value.fitBounds(bounds, { padding: [5, 5], zoom: 20 });
    }
}

function buildBounds(layer) {
    const latlngs: L.LatLng[] = [];
    const layers = layer.getLayers?.() || [];
    for (const l of layers) {
        if (l.getLatLng) {
            latlngs.push(l.getLatLng());
        } else if (l.getBounds) {
            const b = l.getBounds();
            if (b.isValid()) {
                latlngs.push(b.getSouthWest(), b.getNorthEast());
            }
        }
    }
    return latlngs.length ? L.latLngBounds(latlngs) : null;
}

function setView(latlng: [number, number], zm?: number) {
    if (!mapRef.value) return;
    mapRef.value.setView(latlng, zm ?? mapRef.value.getZoom());
}

function getZoom() {
    return mapRef.value?.getZoom();
}

function openModal(point) {
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
    window.addEventListener('resize', () => handleResize);
    mapRef.value.on('dblclick', clickHandler);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', () => handleResize);
    destroyMap();
});

watch(
    () => props.points,
    (newPoint) => {
        if (!mapRef.value || !markersLayer.value) return;

        if (markersLayer.value.getLayers().length > 200) {
            markersLayer.value.clearLayers();
        }

        newPoint.forEach((point, index) => {
            const marker = L.marker(point.coords, {
                icon: createIcon(point.color, point.size, index),
            });
            marker.on('click', () => {
                openModal(point);
            });

            markersLayer?.value?.addLayer(marker);
        });
    },
    { deep: true, immediate: true },
);

defineExpose({
    getMap: () => mapRef.value,
    setView,
    destroyMap,
    fitPoints,
    setLayer,
    currentLocation,
    getZoom,
    openModal,
});
</script>
