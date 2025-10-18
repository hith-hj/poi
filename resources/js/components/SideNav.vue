<template>
    <div class="absolute top-40 right-4 z-20 flex flex-col items-center gap-3 md:top-24">
        <Button id="profile" title="Profile" @click="openModal('profile')">
          <Icon icon="person"/>
        </Button>
        <Button id="settings" title="Settings" @click="openModal('more')">
          <Icon icon="three-dots-vertical"/>
        </Button>

        <Button id="toggleLayers" title="Toggle layers" :dropdown="['normal', 'satellite', 'hybrid', 'terrain']"
            @select="cmProps.setLayer">
         <Icon icon="layers"/>
       </Button>

       <Button id="categories" title="categories" :dropdown="categories"
            @select="selectedCategory">
         <Icon icon="filter"/>
       </Button>

        <Button id="fitPoints" title="Fit in screen" @click="cmProps.fitPoints()">
          <Icon icon="fullscreen"/>
        </Button>

        <Button id="currentLocation" title="Current location" @click="cmProps.currentLocation()">
          <Icon icon="crosshair"/>
        </Button>

        <Button id="clearSelection" title="Clear selection" variant="danger" @click="emit('clearSelection')">
         <Icon icon="arrow-clockwise"/>
       </Button>
    </div>

    <Modal v-model="modals.more" @update:modelValue="closeModal('more')">
        <template #title>More</template>
        <p>This is a reusable modal component.</p>
        <template #footer>
          <button @click="closeModal('more')" class="rounded bg-blue-600 px-4 py-2 text-white">Got it</button>
        </template>
    </Modal>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

import { reactive, ref } from 'vue';
import Button from '../components/ui/Button.vue';
import Modal from '../components/ui/Modal.vue';
import Icon from '../components/ui/Icon.vue';

const cmProps = defineProps<{
  fitPoints: () => void;
  currentLocation: () => void;
  setLayer: (layer: string) => void;
}>();


const emit = defineEmits<{(e: 'clearSelection'): void;}>();

const modals = reactive({
  more: false,
  profile: false,
});

const { props } = usePage();
const categories = ref(props.categories);

function openModal(id: keyof typeof modals) {
  modals[id] = true;
}

function closeModal(id: keyof typeof modals) {
  modals[id] = false;
}

function selectedCategory(cat){
  console.log(cat)
}
</script>
