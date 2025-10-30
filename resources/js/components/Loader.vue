<template>
    <div :class="containerClass">
        <div :class="innerClass">
            <div class="flex items-center justify-center gap-3">
                <template
                    v-for="index in dotCount"
                    :key="index"
                >
                    <div
                        class="dot"
                        :style="getDotStyle(index - 1)"
                    ></div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    bg: { type: String, default: 'bg-red-700' },
    size: { type: String, default: '10px' },
    dotColor: { type: String, default: 'white' },
    dotCount: { type: Number, default: 5 },
    fullscreen: { type: Boolean, default: true },
});

const getDotStyle = (index) => {
    const middle = Math.floor(props.dotCount / 2);
    const delay = Math.abs(index - middle) * 0.2;
    return {
        width: props.size,
        height: props.size,
        backgroundColor: props.dotColor,
        animationDelay: `${delay}s`,
    };
};

const containerClass = computed(() => (props.fullscreen ? ['fixed inset-0 z-50', props.bg] : ['w-full', props.bg]));

const innerClass = computed(() => (props.fullscreen ? 'absolute top-1/2 w-full -translate-y-1/2' : 'flex justify-center'));
</script>

<style scoped>
.dot {
    border-radius: 50%;
    opacity: 0;
    transform: scale(0.5);
    animation: pulse 1.5s infinite ease-in-out;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 0.2;
        transform: scale(0.5);
    }
    50% {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
