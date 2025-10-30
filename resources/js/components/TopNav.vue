<template>
    <div class="absolute top-5 left-1/2 z-10 w-full -translate-x-1/2 transform px-2">
        <div class="flex flex-col items-start gap-2 md:flex-row md:items-center">
            <div class="w-full min-w-0 px-1 md:w-1/2 ">
                <div class="relative flex flex-col gap-2">
                    <div class="min-w-0 flex-1">
                        <Input
                            id="searchInput"
                            v-model:inputValue="search"
                            placeholder="Search location"
                            :prepend="true">
                            <template #prepend>
                                <Icon icon="person" @click="profile = true"/>
                            </template>

                            <template #append>
                                <Icon icon="search" />
                            </template>
                        </Input>
                    </div>
                    <ul v-if="filteredCities.length > 0"
                        class="absolute top-13 z-10 w-full rounded-sm bg-white shadow">
                        <li v-for="(city, index) in filteredCities"
                            @click="selectCity(city)"
                            :key="city.name + '-' + index"
                            class="cursor-pointer p-3 hover:bg-gray-100">
                            {{ ucfirst(city.name) }}
                        </li>
                    </ul>
                </div>
            </div>

            <div class="w-full min-w-0 px-1 md:w-1/2">
                <Slider>
                    <div
                        v-if="categories.length > 0"
                        class="flex max-w-screen min-w-0 gap-3 select-none py-1">
                        <Button
                            v-for="(category, index) in categories"
                            :key="category + '-' + index"
                            :data-cat="category"
                            :text="category"
                            @click="toggleCategory(category)"
                            :class="{ 'font-semibold': isSelected(category) }">
                            <span class="truncate">
                                {{ ucfirst(category) }}
                            </span>
                        </Button>
                    </div>
                </Slider>
            </div>
        </div>
    </div>
    <Modal
        v-model="profile"
        @update:modelValue="profile = false"
    >
        <template #title>More</template>
        <p>This is a reusable modal component.</p>
        <template #footer>
            <button
                @click="profile = false"
                class="rounded bg-blue-600 px-4 py-2 text-white"
            >
                Got it
            </button>
        </template>
    </Modal>
</template>

<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ucfirst } from '../lib/utils.ts';
import { computed, ref, reactive, watch } from 'vue';
import Button from '../components/ui/Button.vue';
import Input from '../components/ui/Input.vue';
import Modal from '../components/ui/Modal.vue';
import Slider from '../components/Slider.vue';
import Icon from '../components/ui/Icon.vue';

const emit = defineEmits<{
    (e: 'categories' ,categories: Array):void;
    (e: 'city' ,city: String):void;
}>();
const { props } = usePage();
const categories = ref(props.categories);
const search = ref('');
const prop = defineProps({
    selectedCategories: {
        type: Array,
        default: ()=>[]
    },
});

let profile = ref<Boolean>(false)

const filteredCities = computed(() =>
    search.value.length > 0 ?
    props.cities.filter((city) => city.name.toLowerCase().includes(search.value.toLowerCase())) : [],
);

const isSelected = (category) => {
    return prop.selectedCategories.includes(category.toLowerCase())
}

function toggleCategory(category) {
    if(!isSelected(category)){
        prop.selectedCategories.push(category.toLowerCase())
    }else{
        prop.selectedCategories.pop(category.toLowerCase())
    }
    emit('categories',prop.selectedCategories);
}

function selectCity(city) {
    emit('city', city);
}

watch(search, (val) => {});
</script>
