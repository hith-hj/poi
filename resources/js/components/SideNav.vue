<template>
    <div class="absolute top-40 right-4 z-20 flex flex-col items-center gap-3 md:top-24">
        <Button id="profile" title="Profile" icon="person" @click="openModal('profile')" />
        <Button id="settings" title="Settings" icon="three-dots-vertical" @click="openModal('more')" />

        <Button
          id="toggleLayers"
          title="Toggle layers"
          icon="layers"
          :dropdown="['normal', 'satellite', 'hybrid', 'terrain']"
          @select="props.setLayer"
        />

        <Button id="fitPoints" title="Fit in screen" icon="fullscreen" @click="props.fitPoints()" />

        <Button id="currentLocation" title="Current location" icon="crosshair" @click="props.currentLocation()" />

        <Button
          id="clearSelection"
          title="Clear selection"
          icon="arrow-clockwise"
          variant="danger"
          @click="emit('clearSelection')"
        />
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
import { reactive } from 'vue';
import Button from '../components/ui/Button.vue';
import Modal from '../components/ui/Modal.vue';

const props = defineProps<{
  fitPoints: () => void;
  currentLocation: () => void;
  setLayer: (layer: string) => void;
}>();

const emit = defineEmits<{
  (e: 'clearSelection'): void;
}>();

const modals = reactive({
  more: false,
  profile: false,
});

function openModal(id: keyof typeof modals) {
  modals[id] = true;
}

function closeModal(id: keyof typeof modals) {
  modals[id] = false;
}

</script>
