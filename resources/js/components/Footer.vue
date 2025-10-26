<template>
    <div class="absolute bottom-5 left-1/2 z-20 w-full -translate-x-1/2 transform px-2">
        <div class="flex flex-col items-center justify-between gap-2 text-center md:flex-row">
            <div class="w-1/3" id="ads">
                <Slider>
                    <template v-for="(n, index) in 1" :key="index">
                        <div class="min-w-1/2 rounded-full bg-white p-2">post {{ n }} {{ index }}</div>
                    </template>
                </Slider>
            </div>

            <div class="w-1/3" id="spinner">
                <div class="flex gap-2 rounded-full py-2 backdrop-blur-[5px]">
                    <div class="flex-1 items-center">
                        <h1 class="text-xl font-extrabold tracking-widest text-blue-500 hover:text-red-500">POI</h1>
                    </div>
                </div>
            </div>

            <div class="w-1/3" id="ads">
                <Slider>
                    <template v-for="(n, index) in 10" :key="index">
                        <div class="min-w-1/2 rounded-full bg-white p-2">ad {{ n }} {{ index }}</div>
                    </template>
                </Slider>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Slider from './Slider.vue';
</script>
