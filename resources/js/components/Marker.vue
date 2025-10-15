<!-- <template>
  <div v-if="map" />
</template>

<script setup lang="ts">
  import { onMounted, onBeforeUnmount, ref, watch } from 'vue';
  import L, { Marker } from 'leaflet';

  const props = defineProps<{
    position: [number, number];
    color?: string;
    size?: number;
    draggable?: boolean;
    map: L.Map | null;
  }>();

  const emit = defineEmits<{
    (e: 'dragend', latlng: [number, number]): void;
  }>();

  const markerRef = ref<Marker | null>(null);

  function createDivIcon(color = 'blue', size = 32): L.DivIcon {
    const style = `
      width: ${size+100}px;
      height: ${size+100}px;
      color: ${color};
      transition: transform 0.3s ease;
    `;
    // const html = `<div class="bi bi-geo-fill animated-marker" style="${style}"></div>`;
    const html = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" fill="${color}" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
    <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
  </svg>`;

    return L.divIcon({
      html,
      className: '', // prevent default Leaflet styles
      iconSize: [size, size],
      iconAnchor: [size / 2, size],
      popupAnchor: [0, -size],
    });
  }

  function addMarker() {
    if (!props.map || markerRef.value) return;

    const icon = createDivIcon(props.color, props.size);
    const marker = L.marker(props.position, {
      icon,
      draggable: props.draggable ?? false,
    });

    marker.addTo(props.map);

    if (props.draggable) {
      marker.on('dragend', () => {
        const pos = marker.getLatLng();
        emit('dragend', [pos.lat, pos.lng]);
      });
    }

    markerRef.value = marker;
  }

  function removeMarker() {
    if (markerRef.value && props.map) {
      props.map.removeLayer(markerRef.value);
      markerRef.value = null;
    }
  }

  onMounted(() => {
    addMarker();
  });

  onBeforeUnmount(() => {
    removeMarker();
  });

  watch(() => props.position, (newPos) => {
    if (markerRef.value) {
      markerRef.value.setLatLng(newPos);
    }
  });

  watch(() => props.color, () => {
    if (markerRef.value) {
      markerRef.value.setIcon(createDivIcon(props.color, props.size));
    }
  });

  watch(() => props.size, () => {
    if (markerRef.value) {
      markerRef.value.setIcon(createDivIcon(props.color, props.size));
    }
  });
</script>

<style scoped>
  .animated-marker:hover {
    transform: scale(5) rotate(10deg);
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
  }
</style> -->

<template>
    <div v-if="map" />
</template>

<script setup lang="ts">
import L, { Marker } from 'leaflet';
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    position: [number, number];
    color?: string;
    size?: number;
    draggable?: boolean;
    map: L.Map | null;
}>();

const emit = defineEmits<{
    (e: 'dragend', latlng: [number, number]): void;
}>();

const markerRef = ref<Marker | null>(null);

// Re-defining for this single-purpose component
function createDivIcon(color = '#0070c0', size = 32): L.DivIcon {
    const html = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" fill="${color}" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
  <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
</svg>`;

    return L.divIcon({
        html,
        className: '',
        iconSize: [size, size],
        iconAnchor: [size / 2, size],
        popupAnchor: [0, -size],
    });
}

function addMarker() {
    if (!props.map || markerRef.value) return;

    const icon = createDivIcon(props.color, props.size);
    const marker = L.marker(props.position, {
        icon,
        draggable: props.draggable ?? false,
    });

    marker.addTo(props.map);

    if (props.draggable) {
        marker.on('dragend', () => {
            const pos = marker.getLatLng();
            emit('dragend', [pos.lat, pos.lng]);
        });
    }

    markerRef.value = marker;
}

function removeMarker() {
    if (markerRef.value && props.map) {
        props.map.removeLayer(markerRef.value);
        markerRef.value = null;
    }
}

onMounted(() => {
    addMarker();
});

onBeforeUnmount(() => {
    removeMarker();
});

watch(
    () => props.position,
    (newPos) => {
        if (markerRef.value) {
            markerRef.value.setLatLng(newPos);
        }
    },
);

watch([() => props.color, () => props.size], () => {
    if (markerRef.value) {
        markerRef.value.setIcon(createDivIcon(props.color, props.size));
    }
});

// Expose a method for external control if needed
defineExpose({
    getLatLng: () => markerRef.value?.getLatLng(),
});
</script>

<style scoped>
/* NOTE: The animated-marker class should ideally be a global style injected by Leaflet */
/* .animated-marker:hover {
  transform: scale(5) rotate(10deg);
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
} */
</style>
