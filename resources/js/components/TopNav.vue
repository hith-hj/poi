<template>
    <div class="absolute top-5 left-1/2 z-20 w-full -translate-x-1/2 transform px-2">
        <div class="flex flex-col items-start gap-3 md:flex-row md:items-center md:gap-4">
            <div class="w-full min-w-0 px-1 md:w-1/2">
                <div class="flex items-center gap-2 py-2">
                    <div class="min-w-0 flex-1">
                        <Input id="searchInput" v-model="search" placeholder="Search location">
                            <template #append>
                                <Icon icon="search" />
                            </template>
                        </Input>
                    </div>
                </div>
            </div>

            <div class="w-full min-w-0 px-1 md:w-1/2">
                <Slider>
                    <div v-if="cities.length > 0" class="flex max-w-screen min-w-0 gap-3 p-1 select-none">
                        <Button
                            v-for="(city, index) in cities"
                            :key="city + '-' + index"
                            :data-cat="city"
                            :text="city"
                            class="flex-shrink-0 rounded-full text-sm font-semibold"
                            @click="emit('city',city)"
                        />
                    </div>
                </Slider>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import Button from '../components/ui/Button.vue';
import Input from '../components/ui/Input.vue';
import Slider from '../components/Slider.vue';
import Icon from '../components/ui/Icon.vue';
import List from '../components/ui/List.vue';

const emit = defineEmits<{
      (e: 'city',city: string): void;
    }>();
const { props } = usePage();
const cities = ref(props.cities);
const search = ref('');
</script>
