<template>
    <div class="absolute top-5 left-1/2 z-10 w-full -translate-x-1/2 transform px-2">
        <div class="flex flex-col items-start gap-3 md:flex-row md:items-center md:gap-4">
            <div class="w-full min-w-0 px-1 md:w-1/2">
                <div class="relative flex flex-col gap-2 py-2">
                    <div class="min-w-0 flex-1">
                        <Input
                            id="searchInput"
                            v-model:inputValue="search"
                            placeholder="Search location"
                        >
                            <template #append>
                                <Icon icon="search" />
                            </template>
                        </Input>
                    </div>
                    <ul
                        v-if="filteredCities.length > 0"
                        class="absolute top-15 z-10 w-full rounded-sm bg-white shadow"
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

            <div class="w-full min-w-0 px-1 md:w-1/2">
                <Slider>
                    <div
                        v-if="cities.length > 0"
                        class="flex max-w-screen min-w-0 gap-3 p-1 select-none"
                    >
                        <Button
                            v-for="(city, index) in cities"
                            :key="city.name + '-' + index"
                            :data-cat="city.name"
                            :text="city.name"
                            @click="selectCity(city)"
                            :class="{ 'font-semibold': prop.selectedCity.name === city.name }"
                        >
                            <span class="truncate">
                                {{ ucfirst(city.name) }}
                            </span>
                        </Button>
                    </div>
                </Slider>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { PropType, computed, ref } from 'vue';
import Slider from '../components/Slider.vue';
import Button from '../components/ui/Button.vue';
import Icon from '../components/ui/Icon.vue';
import Input from '../components/ui/Input.vue';
import { ucfirst } from '../lib/utils.ts';
import { TCity } from '../types/index.d.js';

const emit = defineEmits<{ (e: 'city', city: TCity): void }>();
const { props } = usePage();
const cities = ref(props.cities);
const search = ref('');

const prop = defineProps({
    selectedCity: {
        type: Object as PropType<TCity>,
        // default: () => ({ name: 'damascus', coords: [33.5138, 36.2765] }),
    },
});

const filteredCities = computed(() =>
    search.value.length > 0 ? cities.value.filter((city) => city.name.toLowerCase().includes(search.value.toLowerCase())) : [],
);

// watch(search, (val) => {
//   console.log('Search :', val);
// });

function selectCity(city) {
    emit('city', city);
}
</script>
