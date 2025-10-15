<!-- <template>
    <div class="absolute top-40 right-4 z-20 flex flex-col items-center gap-3 md:top-24">
        <Button id="profile" title="Profile" icon="person" @click="openModal('options')"/>
        <Button id="settings" title="Settings" icon="three-dots-vertical" @click="showMoreModal = true"/>
        <Button id="filters" title="Filters" icon="filter" />
        <Button id="toggleLayers" title="Toggle layers" icon="layers"
        :dropdown="['normal', 'satellite', 'hyperd', 'terrain']" />
        <Button id="fitPoints" title="Fit in screen" icon="fullscreen" @click="emit('fitPoints')" />
        <Button id="currentLocation" title="Current location" icon="crosshair" @click="emit('currentLocation')" />
        <Button id="clearSelection" title="Clear selection" icon="arrow-clockwise" variant="danger" @click="emit('clearSelection')" />
    </div>

    <Modal v-model="showMoreModal">
      <template #title>More</template>
      <p>This is a reusable modal component.</p>
      <template #footer>
        <button @click="showMoreModal = false" class="rounded bg-blue-600 px-4 py-2 text-white">
          Got it
        </button>
      </template>
    </Modal>
</template>

<script setup lang="ts">
    import { onMounted, ref } from 'vue';
    import Button from '../components/ui/Button.vue';
    import Icon from '../components/ui/Icon.vue';
    import Modal from '../components/ui/Modal.vue';
    import { emitter } from '../lib/emitter';
    import * as _ from '../lib/helpers';

    let showMoreModal = ref(false)

    function emit(event: string, option: null) {
        emitter.emit(event, option);
    }

    function setLayer(layer: string) {
        emit('layerChange', layer);
    }

    const openModal = (id: string) => _.openModal(id);
    onMounted(() => {
        emitter.on('select', setLayer);
    });
</script>
 -->
<template>
    <div class="absolute top-40 right-4 z-20 flex flex-col items-center gap-3 md:top-24">
        <Button id="profile" title="Profile" icon="person" @click="openModal('options')" />
        <Button id="settings" title="Settings" icon="three-dots-vertical" @click="showMoreModal = true" />
        <Button id="filters" title="Filters" icon="filter" />

        <Button
            id="toggleLayers"
            title="Toggle layers"
            icon="layers"
            :dropdown="['normal', 'satellite', 'hybrid', 'terrain']"
            @select="props.setLayer"
        />

        <Button id="fitPoints" title="Fit in screen" icon="fullscreen" @click="props.fitPoints()" />
        <Button id="currentLocation" title="Current location" icon="crosshair" @click="props.currentLocation()" />

        <Button id="clearSelection" title="Clear selection" icon="arrow-clockwise" variant="danger" @click="emit('clearSelection')" />
    </div>

    <Modal v-model="showMoreModal">
        <template #title>More</template>
        <p>This is a reusable modal component.</p>
        <template #footer>
            <button @click="showMoreModal = false" class="rounded bg-blue-600 px-4 py-2 text-white">Got it</button>
        </template>
    </Modal>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import Button from '../components/ui/Button.vue';
import Modal from '../components/ui/Modal.vue';
import * as _ from '../lib/helpers'; // Assuming this utility is still needed

// 1. Define props for the map control functions passed from Map.vue
const props = defineProps<{
    fitPoints: () => void;
    currentLocation: () => void;
    setLayer: (layer: string) => void;
}>();

// 2. Define emits for parent actions
const emit = defineEmits<{
    (e: 'clearSelection'): void;
}>();

const showMoreModal = ref(false);

const openModal = (id: string) => _.openModal(id);

// NOTE: The global emitter is entirely removed from this component for map logic.
// The layer change is now handled directly by calling props.setLayer.
</script>
