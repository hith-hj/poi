<template>
    <div class="absolute bottom-5 left-1/2 z-20 w-full -translate-x-1/2 transform px-2">
        <div class="flex flex-col items-center justify-between gap-2 text-center md:flex-row">
            <div class="w-full min-w-0 hidden md:block" id="posts">
                <Slider>
                    <template v-for="(n, index) in 10" :key="index">
                        <div class="min-w-1/2 rounded-full bg-white p-2">post {{ n }} {{ index }}</div>
                    </template>
                </Slider>
            </div>

            <div class="w-full min-w-0" id="spinner">
                <div class="flex flex-col items-top backdrop-blur-lg py-1 rounded-full">
                    <h1 class="text-xl font-extrabold tracking-widest text-red-500">
                        POI
                    </h1>
                    <Transition name="fade">
                        <Loader
                            v-if="isLoading"
                            size="3px"
                            dotColor="red"
                            :dotCount="11"
                            bg="bg-transparent"
                            :fullscreen="false"
                            />
                    </Transition>
                </div>
            </div>

            <div class="w-full min-w-0" id="ads">
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
import { ref } from 'vue';

import Slider from './Slider.vue';
import Loader from './Loader.vue';
import { emitter } from '../lib/emitter.ts';

const isLoading = ref(false);
emitter.on('loading',(value)=>{
    isLoading.value = value;
})

</script>
