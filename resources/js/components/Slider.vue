<template>
    <div
        class="relative flex min-w-full items-center"
        @mouseenter="pauseAutoSlide"
        @mouseleave="resumeAutoSlide"
    >
        <Icon
            icon="chevron-left"
            v-if="showButtons && showLeftButton"
            @click="scrollBy(-scrollAmount)"
            class="absolute top-1/2 left-0 -translate-y-1/2 rounded-l-full bg-gray-50 shadow-lg"
        />

        <div
            ref="slider"
            class="no-scrollbar max-w-full min-w-full overflow-x-auto scroll-smooth whitespace-nowrap"
            @pointerdown="onPointerDown"
            @pointerup="onPointerUp"
            @pointercancel="onPointerUp"
            @pointermove="onPointerMove"
            @scroll="updateButtonVisibility"
            @wheel="handleWheel"
        >
            <div class="flex w-full gap-1 select-none">
                <slot />
            </div>
        </div>

        <Icon
            icon="chevron-right"
            v-if="showButtons && showRightButton"
            @click="scrollBy(scrollAmount)"
            class="absolute top-1/2 right-0 -translate-y-1/2 rounded-r-full bg-gray-50 shadow-lg"
        />
    </div>
</template>

<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref } from 'vue';
import Icon from '../components/ui/Icon.vue';

const props = defineProps({
    items: {
        type: Array,
        default: () => [],
    },
    autoSlide: {
        type: Boolean,
        default: false,
    },
    slideInterval: {
        type: Number,
        default: 3000,
    },
});

const slider = ref<HTMLElement | null>(null);
const dragging = ref(false);
const startX = ref(0);
const startScrollLeft = ref(0);
const showButtons = ref(true);
const showLeftButton = ref(false);
const showRightButton = ref(false);
let scrollAmount = 250;
let resizeObserver: ResizeObserver | null = null;
let autoSlideTimer: ReturnType<typeof setInterval> | null = null;
let isHovered = false;

const isMobile = () => window.innerWidth < 768;

const updateButtonVisibility = () => {
    const el = slider.value;
    if (!el) return;

    const scrollable = el.scrollWidth > el.clientWidth;
    showButtons.value = !isMobile() && scrollable;

    if (!scrollable || props.autoSlide) {
        showLeftButton.value = false;
        showRightButton.value = false;
        return;
    }

    const left = el.scrollLeft;
    const sw = el.scrollWidth - 10;
    showLeftButton.value = left > 0;
    showRightButton.value = left + el.clientWidth < sw;
};

const onPointerDown = (e: PointerEvent) => {
    const el = slider.value;
    if (!el) return;

    dragging.value = true;
    e.target?.setPointerCapture?.(e.pointerId);
    startX.value = e.pageX - el.offsetLeft;
    startScrollLeft.value = el.scrollLeft;
};

const onPointerUp = (e: PointerEvent) => {
    dragging.value = false;
    e.target?.releasePointerCapture?.(e.pointerId);
};

const onPointerMove = (e: PointerEvent) => {
    if (!dragging.value) return;

    e.preventDefault();
    const el = slider.value;
    if (!el) return;

    const x = e.pageX - el.offsetLeft;
    const walk = (x - startX.value) * 2;
    el.scrollLeft = startScrollLeft.value - walk;
};

const scrollBy = (amount: number) => {
    const el = slider.value;
    if (!el) return;

    el.scrollBy({ left: amount, behavior: 'smooth' });
};

const handleWheel = (event: WheelEvent) => {
    if (event.deltaY < 0) {
        scrollBy(scrollAmount);
    } else if (event.deltaY > 0) {
        scrollBy(-scrollAmount);
    }
};

const startAutoSlide = () => {
    stopAutoSlide();
    autoSlideTimer = setInterval(() => {
        if (!slider.value || isHovered) return;
        const el = slider.value;
        const maxScrollLeft = el.scrollWidth - el.clientWidth;

        if (el.scrollLeft + scrollAmount >= maxScrollLeft - 5) {
            el.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            scrollBy(scrollAmount);
        }
    }, props.slideInterval);
};

const stopAutoSlide = () => {
    if (autoSlideTimer) {
        clearInterval(autoSlideTimer);
        autoSlideTimer = null;
    }
};

const pauseAutoSlide = () => {
    isHovered = true;
};

const resumeAutoSlide = () => {
    isHovered = false;
};

onMounted(async () => {
    await nextTick();
    updateButtonVisibility();
    window.addEventListener('resize', updateButtonVisibility, { passive: true });

    if (window.ResizeObserver && slider.value) {
        resizeObserver = new ResizeObserver(updateButtonVisibility);
        resizeObserver.observe(slider.value);
        if (slider.value.firstElementChild) {
            resizeObserver.observe(slider.value.firstElementChild);
        }
    }

    if (slider.value?.firstElementChild) {
        scrollAmount = slider.value?.firstElementChild?.clientWidth;
        // slider.value?.firstElementChild?.children.length ;
    }

    if (props.autoSlide) {
        startAutoSlide();
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', updateButtonVisibility);
    resizeObserver?.disconnect();
    resizeObserver = null;
    stopAutoSlide();
});
</script>
