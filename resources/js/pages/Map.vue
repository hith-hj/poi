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
        <LeafLet ref="map" :view="view" :zoom="zoom" :points="points" @mapMoved="fetchPoints" />
    </Main>
</template>
<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import LeafLet from '../components/LeafLet.vue';
import SideNav from '../components/SideNav.vue';
import TopNav from '../components/TopNav.vue';
import Main from './Main.vue';

// 1. Type the ref to access exposed methods
const map = ref<InstanceType<typeof LeafLet> | null>(null);
const { props } = usePage();
const view = ref<[number, number]>(props.view);
const points = ref<any[]>(Array.isArray(props.points) ? props.points : []);
const zoom = ref<number>(props.zoom);

function alert(){
    alert('hfdgd')
}

function clearSelection() {
    selectedPoint.value = null;
    console.log('Selection cleared.');
}

type FetchPointsArgs = {
  center?: [number, number] | null;
  zoom?: number | null;
  bbox?: [number, number, number, number] | null;
};

async function fetchPoints(args: FetchPointsArgs = {}): Promise<void> {
  const { center, zoom, bbox } = args;

  try {
    const params: string[] = [];

    if (typeof zoom === 'number') params.push(`zoom=${encodeURIComponent(zoom)}`);
    if (Array.isArray(center) && center.length === 2) {
      params.push(`center=[${encodeURIComponent(center[0])},${encodeURIComponent(center[1])}]`);
    }
    if (Array.isArray(bbox) && bbox.length === 4) {
      params.push(`bbox=${bbox.map(n => encodeURIComponent(n)).join(',')}`);
    }

    const query = params.length ? `?${params.join('&')}` : '';
    const url = `/points${query}`;

    const response = await fetch(url);
    if (!response.ok) throw new Error(`Network response was not ok: ${response.status}`);
    const newPointsData = await response.json();
    points.value = newPointsData?.points ?? [];
  } catch (error) {
    console.error('Error fetching points:', error);
  }
}

onMounted(()=>{
    fetchPoints({center:props.view,zoom:zoom})
});
</script>
