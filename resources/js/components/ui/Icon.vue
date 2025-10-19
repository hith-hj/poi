<template>
    <span class="flex items-center justify-center" :class="iconSize" v-bind="$attrs">
        <i :class="iconClasse"></i>
    </span>
</template>

<script setup lang="ts">
    import { computed } from 'vue';

    const props = defineProps({
        icon: {
            type: String,
            default: 'check'
        },
        size: {
            type: String,
            default: 'md'
        },
        color: {
            type: String,
            default: 'default'
        }
    });

    const iconClasse = computed(() => {
        const colors = {
            default: 'text-black focus:text-gray-800',
            success: 'text-green-500 focus:text-green-500',
            danger: 'text-red-500 focus:text-red-500'
        };

        return [
            `bi bi-${props.icon} `,
            colors[props.color],
        ];
    });

    const iconSize = computed(() => {
        const sizes = {
            sm: 'h-6 w-6 text-xl',
            md: 'h-8 w-8 text-2xl',
            lg: 'h-10 w-10 text-3xl',
            xl: 'h-12 w-12 text-4xl',
        };

        return [
            sizes[props.size],
        ];
    })
</script>

