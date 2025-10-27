<template>
    <div class="relative h-screen w-screen overflow-hidden bg-slate-100 antialiased">
        <Transition name="fade">
            <Loader v-if="first" />
        </Transition>
        <main>
            <slot />
            <Footer />
        </main>
    </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import Footer from '../components/Footer.vue';
import Loader from '../components/Loader.vue';

const first = ref(true);

setTimeout(() => {
    first.value = false;
}, 3000);

onMounted(async () => {
    try {
        // const fp = await FingerprintJS.load();
        // const user = await fp.get();
        // console.log(user.visitorId);
    } catch (err) {
        console.error('Error during map initialization:', err);
    }
});
</script>
