<template>
    <span :class="iconSize" v-bind="$attrs">
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
            default: 'sm'
        },
        color: {
            type: String,
            default: 'default'
        }
    });

    const iconClasse = computed(() => {
        const colors = {
            default: 'text-black focus:text-gray-800',
            success: 'text-green-500 focus:text-green-800',
            danger: 'text-red-500 focus:text-red-800'
        };

        return [
            `bi bi-${props.icon} `,
            colors[props.color],
        ];
    });

    const iconSize = computed(() => {
        const sizes = {
            sm: 'text-xl',
            md: 'text-2xl',
            lg: 'text-3xl',
            xl: 'text-4xl'
        };

        return [
            sizes[props.size],
        ];
    })
</script>

