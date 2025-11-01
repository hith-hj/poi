<template>
    <div class="absolute top-2 left-1/2 z-2 w-full -translate-x-1/2 transform px-1">
        <div class="flex flex-col items-start gap-2 md:flex-row md:items-center">
            <div class="w-full min-w-0 px-1 md:w-1/4">
                <div class="relative flex flex-col gap-2">
                    <div class="min-w-0 flex-1">
                        <Input
                            id="searchInput"
                            v-model:inputValue="search"
                            placeholder="Search location"
                            :prepend="true"
                        >
                            <template #prepend>
                                <Icon
                                    size="lg"
                                    icon="person"
                                    @click="openModal('profile')"
                                />
                            </template>

                            <template #append>
                                <Icon icon="search" size="sm"/>
                            </template>
                        </Input>
                    </div>
                    <ul
                        v-if="filteredCities.length > 0"
                        class="absolute top-13 z-10 w-full rounded-sm bg-white shadow"
                    >
                        <li
                            v-for="(city, index) in filteredCities"
                            @click="selectCity(city)"
                            :key="city.name + '-' + index"
                            class="cursor-pointer p-3 hover:bg-gray-100"
                        >
                            {{ ucfirst(city.name) }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="w-full min-w-0 px-1 md:w-3/4">
                <div class="flex items-center justify-evenly">
                    <div class="w-3/4">
                        <Slider>
                            <div
                                v-if="categories.length > 0"
                                class="flex max-w-screen min-w-0 gap-4 py-1 select-none"
                            >
                                <Button
                                    v-for="(category, index) in categories"
                                    :key="category + '-' + index"
                                    :data-cat="category"
                                    :text="category"
                                    @click="toggleCategory(category)"
                                    :class="{ 'font-semibold': isSelected(category) }"
                                >
                                    <span class="truncate">
                                        {{ ucfirst(category) }}
                                    </span>
                                </Button>
                            </div>
                        </Slider>
                    </div>

                    <div class="w-1/4">
                        <div class="flex items-center justify-end gap-2">
                            <Button
                                v-if="selection"
                                title="Clear selection"
                                variant="danger"
                                @click="clearSelection"
                            >
                                <Icon
                                    icon="arrow-clockwise"
                                    color="danger"
                                    size="sm"
                                />
                                <span class="truncate">
                                    {{ selectedCategories.length }}
                                </span>
                            </Button>

                            <Dropdown
                                align="center"
                                direction="vertical"
                            >
                                <template #icon>
                                    <Icon
                                        icon="sliders2"
                                        sizex="sm"
                                    />
                                </template>

                                <Dropdown
                                    align="side-left"
                                    direction="horizontal"
                                >
                                    <template #icon>
                                        <Icon
                                            icon="layers"
                                            sizex="sm"
                                        />
                                    </template>
                                    <template
                                        v-for="layer in layers"
                                        :key="layer.name"
                                    >
                                        <Button @click="setLayer(layer.name)">
                                            <p class="truncate px-2"
                                            :class="{'font-extrabold':layer.name== selectedLayer}">
                                                {{ ucfirst(layer.name) }}
                                            </p>
                                        </Button>
                                    </template>
                                </Dropdown>

                                <Dropdown
                                    align="side-left"
                                    direction="vertical"
                                >
                                    <template #icon>
                                        <Icon icon="geo" />
                                    </template>
                                    <div class="grid grid-cols-2 gap-1">
                                        <template
                                            v-for="city in cities"
                                            :key="city.name"
                                        >
                                            <Button @click="setCity(city)">
                                                <p class="truncate px-2"
                                                :class="{'font-extrabold':city.name== selectedCity.name}">
                                                    {{ ucfirst(city.name) }}
                                                </p>
                                            </Button>
                                        </template>
                                    </div>
                                </Dropdown>

                                <Button
                                    title="Fit in screen"
                                    @click="fitPoints"
                                >
                                    <Icon icon="fullscreen" />
                                </Button>

                                <Button
                                    title="Current location"
                                    @click="getCurrentLocation"
                                >
                                    <Icon icon="crosshair" />
                                </Button>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <Modal
        v-model="modals.profile"
        @update:modelValue="closeModal('profile')"
    >
        <template #title>User Profile</template>
        <p>This is the profile modal component.</p>
        <template #footer>
            <button
                @click="closeModal('profile')"
                class="rounded bg-blue-600 px-4 py-2 text-white"
            >
                Got it
            </button>
        </template>
    </Modal>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { PropType } from 'vue';
import { computed, reactive, ref, watch } from 'vue';
import Slider from '../components/Slider.vue';
import Button from '../components/ui/Button.vue';
import Dropdown from '../components/ui/Dropdown.vue';
import Icon from '../components/ui/Icon.vue';
import Input from '../components/ui/Input.vue';
import Modal from '../components/ui/Modal.vue';
import { ucfirst } from '../lib/utils.ts';
import { City as TCity } from '../types/index.d.ts';

const emit = defineEmits<{
    (e: 'categories', categories: Array<string>): void;
    (e: 'city', city: TCity): void;
    (e: 'clearSelection'): void;
}>();

const cmProps = defineProps({
    map: Object as PropType<LeafLet>,
    selectedLayer: {type:String,default:'light'},
    selectedCity: {type: Object as PropType<TCity>,},
    selectedCategories: {type: Array ,default: () => [],},
    selection: { type: Boolean, default: false },
});

const { props } = usePage();
const categories = ref(props.categories);
const cities = ref(props.cities);
const selectedLayer = ref(cmProps.selectedLayer);
const search = ref('');
const selected = ref([...cmProps.selectedCategories]);
const modals = reactive({profile: false,});

const layers = {
    light: { name: 'light', icon: '' },
    dark: { name: 'dark', icon: '' },
    normal: { name: 'normal', icon: '' },
    satellite: { name: 'satellite', icon: '' },
};

const filteredCities = computed(() =>
    search.value.length > 0 ? props.cities.filter((city) => city.name.toLowerCase().includes(search.value.toLowerCase())) : [],
);

const isSelected = (category: string) => {
    return selected.value.includes(category.toLowerCase());
};

function setCity(city: TCity) {
    emit('city', city);
    search.value = '';
}

function setLayer(layer: string) {
    if (!layer || !cmProps.map) return;
    selectedLayer.value = layer
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

function openModal(id: keyof typeof modals) {
    modals[id] = true;
}

function closeModal(id: keyof typeof modals) {
    modals[id] = false;
}

function toggleCategory(category: string) {
    const cat = category.toLowerCase();

    if (!isSelected(cat)) {
        selected.value.push(cat);
    } else {
        selected.value = selected.value.filter((c) => c !== cat);
    }

    emit('categories', selected.value);
}

function selectCity(city: TCity) {
    emit('city', city);
    search.value = '';
}

function clearSelection() {
    emit('clearSelection');
}

watch(search, (val) => {
    console.log(val);
});
</script>
