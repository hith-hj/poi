<!-- <template>
  <Head>
    <title>Map</title>
  </Head>
  <TopNav />
  <SideNav
    v-if="map"
    :fit-points="map.fitPoints"
    :current-location="map.currentLocation"
    :set-layer="map.setLayer"
    @clear-selection="clearSelection"
  />
  <Main>
    <LeafLet
      ref="map"
      :view="view"
      :zoom="zoom"
      :points="points"
      @mapMoved="handleMapMoved"
      @markerClicked="handleMarkerClicked"
    />
  </Main>
</template>
-->
<template>
    <Head>
        <title>Map</title>
    </Head>
    <TopNav />
    <SideNav
        v-if="map"
        :fit-points="map.fitPoints"
        :current-location="map.currentLocation"
        :set-layer="map.setLayer"
        @clear-selection="clearSelection"
    />
    <Main>
        <LeafLet ref="map" :view="view" :zoom="zoom" :points="points" @mapMoved="handleMapMoved" @markerClicked="handleMarkerClicked" />
    </Main>

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
import { Head, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import LeafLet from '../components/LeafLet.vue';
import SideNav from '../components/SideNav.vue';
import TopNav from '../components/TopNav.vue';
import Modal from '../components/ui/Modal.vue';
import Main from './Main.vue';

// 1. Type the ref to access exposed methods
const map = ref<InstanceType<typeof LeafLet> | null>(null);

// 2. Initial state from Inertia props
const { props } = usePage();
const view = ref<[number, number]>(props.view);
const points = ref<any[]>(Array.isArray(props.points) ? props.points : []); // Initial points data
const zoom = ref<number>(props.zoom);
const showPointModal = ref(false);
const selectedPoint = ref<any>(null);

// 3. Handlers for Map Events (from LeafLet child component)
function handleMapMoved(state: { center: [number, number]; zoom: number; bbox: [number, number, number, number] }) {
    fetchPoints(state);
}

//3. Marker Click Handler
function handleMarkerClicked(point: any) {
    // 1. Store the data
    selectedPoint.value = point;

    // 2. Open the modal
    showPointModal.value = true;

    // Optional: Center map on the clicked point
    map.value?.setView(point.coords, map.value.getMap()?.getZoom());
}

// 4. Handlers for SideNav Events (via props on SideNav)
function clearSelection() {
    selectedPoint.value = null;
    // Additional logic to clear other UI elements
    console.log('Selection cleared.');
}

// 5. Data Fetching Logic (updated to directly set the reactive 'points' prop)

async function fetchPoints({ center, zoom, bbox }: { center: [number, number]; zoom: number; bbox: [number, number, number, number] }) {
    try {
        // Pass the BBox to the backend (SW_lat, SW_lng, NE_lat, NE_lng)
        const url = `/points?zoom=${zoom}&center=[${center[0]},${center[1]}]&bbox=${bbox.join(',')}`;
        const response = await fetch(url);

        // ... existing error checking ...

        const newPointsData = await response.json();

        // CRITICAL: The backend must be configured to return *only* the points
        // that fall within the requested BBox and zoom level.
        points.value = newPointsData.points || [];
    } catch (error) {
        console.error('Error fetching points:', error);
    }
}
</script>
