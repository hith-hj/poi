<template>
  <div :id="id" class="relative inline-block text-left" ref="dropdownRef">
    <Button
      @click="toggleDropdown"
      title="map settings"
    >
      <div class="flex justify-evenly gap-1">
        <slot name="icon"/>
        <p v-if="buttonText">
          {{ buttonText }}
        </p>
      </div>
    </Button>

    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-50"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-50"
    >
      <div
        v-show="isOpen"
        class="absolute w-auto min-w-max rounded-md"
        :class="menuPositionClasses"
        role="menu"
        aria-orientation="vertical"
        tabindex="-1"
      >
        <div
          class="flex gap-2"
          :class="listLayoutClasses"
          role="none"
        >
          <slot></slot>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Button from './Button.vue'

const props = defineProps({
  id: { type: String, default: () => `dropdown_${Math.random().toString(36).slice(2, 8)}` },
  buttonText: {
    type: String,
    default: '',
  },
  direction: {
    type: String,
    default: 'vertical',
    validator: (value) => ['vertical', 'horizontal'].includes(value),
  },
  align: {
    type: String,
    default: 'side-left',
    validator: (value) => [
      'top-left',
      'top-right',
      'bottom-left',
      'bottom-right',
      'side-left',
      'side-right',
      'center']
      .includes(value),
  }
});

const isOpen = ref(false);
const dropdownRef = ref(null);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const listLayoutClasses = computed(() => {
  return props.direction === 'vertical' ? 'flex-col' : 'flex-row space-x-2';
});

const menuPositionClasses = computed(() => {
  switch (props.align) {
    case 'top-right':
      return 'origin-bottom-right bottom-full right-0 mb-2';
    case 'top-left':
      return 'origin-bottom-left bottom-full left-0 mb-2';
    case 'bottom-right':
      return 'origin-top-right right-0 mt-2';
    case 'bottom-left':
      return 'origin-top-left left-0 mt-2';
    case 'side-left':
      return 'origin-right top-1/2 -translate-y-1/2 right-full mr-2';
    case 'side-right':
      return 'origin-left top-1/2 -translate-y-1/2 left-full ml-2';
    case 'center':
      return 'origin-top -translate-x-1/2 left-1/2 mt-2';
    default:
      return 'origin-top-left left-0 mt-2';
  }
});

const handleClickOutside = (event) => {
  if (isOpen.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
