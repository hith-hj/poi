<template>
    <Head>
        <title>Map</title>
    </Head>
    <TopNav @city="city"
        :selectedCity="selectedCity" />
    <SideNav v-if="map"
        :map="map"
        :category="category"
        :selection="selection"
        @clear-selection="clearSelection" />
    <Main>
        <LeafLet ref="map"
        :view="props.view"
        :zoom="zoom"
        :points="points"
        @mapMoved="fetchPoints" />
    </Main>
</template>
<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted, ref, reactive } from 'vue';
import LeafLet from '../components/LeafLet.vue';
import SideNav from '../components/SideNav.vue';
import TopNav from '../components/TopNav.vue';
import { emitter } from '../lib/emitter.ts';
import { City as TCity } from '../types/index.d.js';

import Main from './Main.vue';

const map = ref<InstanceType<typeof LeafLet> | null>(null);
const { props } = usePage();
let view = ref<[number, number]>(props.view);
let points = ref<any[]>([]);
let zoom = ref<number>(props.zoom);
let selection = ref<Boolean>(false);
let selectedCity = ref<TCity>({
  name: props.city,
  coords: props.view,
});
let selectedCategory = ref<String|null>(null);

function city(city: TCity) {
    selectedCity.value = city;
    map.value.setView(city.coords, 18);
}

function category(category) {
    selection.value = true;
    console.log(category);
}

function clearSelection() {
    selection.value = false;
    selectedCategory = null;
    console.log('clearing');
}

type FetchPointsArgs = {
    center?: [number, number] | null;
    zoom?: number | null;
    bbox?: [number, number, number, number] | null;
    category?: string | null;
    city?: string | null;
};

async function fetchPoints(args: FetchPointsArgs = {}): Promise<void> {
    const { center, zoom, bbox } = args;

    try {
        const params: string[] = [];
        emitter.emit('loading',true)
        if (typeof zoom === 'number') params.push(`zoom=${encodeURIComponent(zoom)}`);
        if (Array.isArray(center) && center.length === 2) {
            params.push(`center=[${encodeURIComponent(center[0])},${encodeURIComponent(center[1])}]`);
        }
        if (Array.isArray(bbox) && bbox.length === 4) {
            params.push(`bbox=${bbox.map((n) => encodeURIComponent(n)).join(',')}`);
        }

        const query = params.length ? `?${params.join('&')}` : '';
        const url = `/points${query}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error(`Network response was not ok: ${response.status}`);
        const newPointsData = await response.json();
        points.value = newPointsData?.points ?? [];
    } catch (error) {
        console.error('Error fetching points:', error);
    }finally{
        emitter.emit('loading',false)
    }
}

onMounted(() => {
    fetchPoints({ center: props.view, zoom: zoom });
});
</script>
