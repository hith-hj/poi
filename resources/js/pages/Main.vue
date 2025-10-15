<template>
    <div class="relative h-screen w-screen overflow-hidden bg-slate-100 antialiased">
        <main>
            <slot />
            <Footer/>
        </main>
    </div>

</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import Footer from '../components/Footer.vue';
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
