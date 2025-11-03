<template>
    <Head>
        <title>Map</title>
    </Head>
    <MapNav
        :map="map"
        :selection="selection"
        :selectedCity="selectedCity"
        :selectedCategories="selectedCategories"
        selectedLayer="light"
        @city="city"
        @categories="categories"
        @clear-selection="clearSelection"
    />

    <Main>
        <LeafLet
            ref="map"
            :view="view"
            :zoom="zoom"
            :points="points"
            @mapMoved="fetchPoints"
        />
    </Main>
</template>
<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import LeafLet from '../components/LeafLet.vue';
import MapNav from '../components/MapNav.vue';
import { emitter } from '../lib/emitter.ts';
import { City as TCity } from '../types/index.d.js';

import Main from './Main.vue';

const map = ref<InstanceType<typeof LeafLet> | null>(null);
const { props } = usePage();
const view = ref<[number, number]>(props.view);
const points = ref<any[]>([]);
const zoom = ref<number>(props.zoom);
const selection = ref<boolean>(false);
const selectedCity = ref<TCity>({
    name: props.city,
    coords: props.view,
});
const selectedCategories = ref<Array>([]);
// const show = ref(false);

function city(city: TCity) {
    selectedCity.value = city;
    map.value.setView(city.coords, 18);
    fetchPoints();
}

function categories(categories) {
    selectedCategories.value = categories;
    selection.value = selectedCategories.value.length > 0;
    fetchPoints();
}

function clearSelection() {
    selection.value = false;
    selectedCategories.value.length = 0;
    fetchPoints();
}

type FetchPointsArgs = {
    center?: [number, number] | null;
    zoom?: number | null;
    bbox?: [number, number, number, number] | null;
    categories?: [] | null;
    city?: string | null;
};

async function fetchPoints(args: FetchPointsArgs = {}): Promise<void> {
    const { center, zoom, bbox } = args;

    try {
        const params: string[] = [];
        emitter.emit('loading', true);
        if (typeof zoom === 'number') params.push(`zoom=${encodeURIComponent(zoom)}`);
        if (Array.isArray(center) && center.length === 2) {
            params.push(`center=[${encodeURIComponent(center[0])},${encodeURIComponent(center[1])}]`);
        }
        if (Array.isArray(bbox) && bbox.length === 4) {
            params.push(`bbox=${bbox.map((n) => encodeURIComponent(n)).join(',')}`);
        }
        if (Array.isArray(selectedCategories.value) && selectedCategories.value.length > 0) {
            params.push(`categories=[${selectedCategories.value.map((c) => encodeURIComponent(c)).join(',')}]`);
        }

        const query = params.length ? `?${params.join('&')}` : '';
        const url = `/points${query}`;
        console.log(url);
        const response = await fetch(url);
        if (!response.ok) throw new Error(`Network response was not ok: ${response.status}`);
        const newPointsData = await response.json();
        points.value = newPointsData?.points ?? [];
    } catch (error) {
        console.error('Error fetching points:', error);
    } finally {
        emitter.emit('loading', false);
    }
}

onMounted(() => {
    fetchPoints({ center: props.view, zoom: zoom });
});
</script>
