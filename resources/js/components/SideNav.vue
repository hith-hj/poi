<template>
    <div class="absolute top-35 right-4 z-10 flex flex-col items-center gap-2 md:top-20">
        <Button
            v-if="1 == 2"
            title="Toggle layers"
            :dropdown="['normal', 'satellite', 'hybrid', 'terrain']"
            @select="setLayer"
        >
            <Icon icon="layers" />
        </Button>

        <Button title="cities" ref="buttonComp">
            <template #default>
                <Icon icon="map" />
            </template>

            <template #dropdown="{ buttonRef, showDropdown, id }">
                <List
                    v-if="cities && cities.length"
                    :id="id"
                    :items="cities"
                    :elementRef="buttonRef"
                    :showDropdown="showDropdown"
                    @select="setCity"/>
            </template>
        </Button>

        <Button
            title="Fit in screen"
            @click="fitPoints">
            <Icon icon="fullscreen" />
        </Button>

        <Button
            title="Current location"
            @click="getCurrentLocation">
            <Icon icon="crosshair" />
        </Button>

        <Button
            title="Settings"
            @click="openModal('more')">
            <Icon icon="three-dots-vertical" />
        </Button>

        <Button
            v-if="selection"
            title="Clear selection"
            variant="danger"
            @click="clearSelection">
            <Icon
                icon="arrow-clockwise"
                color="danger"
            />
        </Button>
    </div>

    <Modal
        v-model="modals.more"
        @update:modelValue="closeModal('more')"
    >
        <template #title>More</template>
        <p>This is a reusable modal component.</p>
        <template #footer>
            <button
                @click="closeModal('more')"
                class="rounded bg-blue-600 px-4 py-2 text-white"
            >
                Got it
            </button>
        </template>
    </Modal>

</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

import { reactive, ref } from 'vue';
import Button from '../components/ui/Button.vue';
import Icon from '../components/ui/Icon.vue';
import List from '../components/ui/List.vue';
import Modal from '../components/ui/Modal.vue';
import {City as TCity} from '../types/index.d.ts';

const cmProps = defineProps({
    map: Object as LeafLet,
    city: Object as TCity,
    selection: {type:Boolean,default:false},
    selectedCity: {
        type: Object as PropType<TCity>,
        // default: () => ({ name: 'damascus', coords: [33.5138, 36.2765] }),
    },
});
const { props } = usePage();
const cities = ref(props.cities);
const emit = defineEmits<{ (e: 'clearSelection'): void; (e: 'city'): void }>();
const modals = reactive({
    more: false,
    profile: false,
});

function setCity(city: TCity) {
    emit('city', city);
}

function setLayer(layer: string) {
    if (!layer || !cmProps.map) return;
    cmProps.map.setLayer(layer);
}

function fitPoints() {
    if (!cmProps.map) return;
    cmProps.map.fitPoints();
}

function getCurrentLocation() {
    if (!cmProps.map) return;
    cmProps.map.currentLocation();
}

function clearSelection() {
    emit('clearSelection');
}

function openModal(id: keyof typeof modals) {
    modals[id] = true;
}

function closeModal(id: keyof typeof modals) {
    modals[id] = false;
}
</script>
