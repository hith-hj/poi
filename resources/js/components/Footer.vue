<template>
    <div class="absolute bottom-5 left-1/2 z-20 w-full -translate-x-1/2 transform px-2 ">
        <div class="flex flex-col items-center justify-between text-center md:flex-row ">
            <div class="w-full min-w-0 px-1 hidden md:block">
                <Slider>
                	<template v-for="(n, index) in 10" :key="index">
	                	<div class="min-w-1/2 bg-white p-2 rounded-full">post {{index}}</div>
	                </template>
                </Slider>
            </div>

            <div class="w-full min-w-0 px-1">
                <div class="flex gap-2 backdrop-blur-[1px] rounded-full py-2">
                    <div class="flex-1 items-center">
                        <h1 class="text-xl text-blue-500 font-extrabold tracking-widest hover:tracking-normal">POI</h1>
                    </div>
                </div>
            </div>

            <div class="w-full min-w-0 px-1">
                <Slider>
                	<template v-for="(n, index) in 10" :key="index">
	                	<div class="min-w-1/2 bg-white p-2 rounded-full px-4">add {{index}}</div>
	                </template>
                </Slider>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
	import Slider from './Slider.vue';
</script>
