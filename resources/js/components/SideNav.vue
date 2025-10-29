<template>
    <div class="absolute top-40 right-4 z-10 flex flex-col items-center gap-2 md:top-24">
        <Button title="Profile" @click="openModal('profile')">
            <Icon icon="person" />
        </Button>

        <Button v-if="1==2" title="Toggle layers" :dropdown="['normal', 'satellite', 'hybrid', 'terrain']"
            @select="setLayer">
            <Icon icon="layers"/>
        </Button>

        <Button title="categories" :dropdown="categories" @select="setCategory">
            <Icon icon="tags" />
        </Button>

        <Button title="Fit in screen" @click="fitPoints">
            <Icon icon="fullscreen" />
        </Button>

        <Button title="Current location" @click="getCurrentLocation">
            <Icon icon="crosshair" />
        </Button>

        <Button title="Settings" @click="openModal('more')">
            <Icon icon="three-dots-vertical" />
        </Button>

        <Button v-if="selection" title="Clear selection" variant="danger" @click="clearSelection">
            <Icon icon="arrow-clockwise" color="danger" />
        </Button>
    </div>

    <Modal v-model="modals.more" @update:modelValue="closeModal('more')">
        <template #title>More</template>
        <p>This is a reusable modal component.</p>
        <template #footer>
            <button @click="closeModal('more')" class="rounded bg-blue-600 px-4 py-2 text-white">Got it</button>
        </template>
    </Modal>

    <Modal v-model="modals.profile" @update:modelValue="closeModal('profile')">
        <template #title>More</template>
        <p>This is a reusable modal component.</p>
        <template #footer>
            <button @click="closeModal('profile')" class="rounded bg-blue-600 px-4 py-2 text-white">Got it</button>
        </template>
    </Modal>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

import { reactive, ref } from 'vue';
import Button from '../components/ui/Button.vue';
import Icon from '../components/ui/Icon.vue';
import Modal from '../components/ui/Modal.vue';

const cmProps = defineProps<{
    map: InstanceType<typeof LeafLet>;
    category: (category: string) => void;
    selection: boolean;
}>();
const { props } = usePage();
const categories = ref(props.categories);
const emit = defineEmits<{ (e: 'clearSelection'): void; (e: 'category'): void }>();
const modals = reactive({
    more: false,
    profile: false,
});

function setCategory(category: string) {
    if (!category) return;
    cmProps.category(category);
}

function setLayer(layer: string){
  if(!layer || !cmProps.map) return;
  cmProps.map.setLayer(layer)
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
