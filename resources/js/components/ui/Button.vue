<template>
    <div class="relative inline-block">
        <button
            ref="buttonRef"
            :id="id"
            :type="type"
            :title="text"
            :aria-label="text"
            :class="buttonClasses"
            @click="handleClick">
            <slot />
        </button>

        <List
            v-if="dropdown.length"
            :id="id"
            :items="dropdown"
            :elementRef="buttonRef"
            :showDropdown="showDropdown"
            @select="handleSelect"/>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'
import List from './List.vue'

const emit = defineEmits<{
  (e: 'click', event: MouseEvent): void
  (e: 'select', item: string): void
}>()

const props = defineProps({
  id: { type: String, default: () => `btn_${Math.random().toString(36).slice(2, 8)}` },
  type: { type: String, default: 'button' },
  text: { type: String, default: '' },
  icon: { type: String, default: null },
  size: { type: String, default: 'md' },
  variant: { type: String, default: 'default' },
  dropdown: { type: Array as () => string[], default: () => [] }
})

const buttonRef = ref<HTMLElement | null>(null)
const showDropdown = ref(false)

const buttonClasses = computed(() => {
  const base = 'p-2 rounded-full shadow cursor-pointer bg-white'
  const sizes = { sm: 'text-sm', md: 'text-md', lg: 'text-lg' }
  const variants = {
    default: 'text-black ring-1 ring-gray-200 hover:ring-gray-600',
    success: 'text-green-600 ring-2 ring-green-300 hover:ring-green-500',
    danger: 'text-red-600 ring-2 ring-red-300 hover:ring-red-500'
  }
  return `${base} ${sizes[props.size] || sizes.md} ${variants[props.variant] || variants.default}`
})

function handleClick(event: MouseEvent) {
    emit('click', event)
    if(props.dropdown.length > 0){
        showDropdown.value = !showDropdown.value
    }
}

function handleSelect(item: string) {
    showDropdown.value = false
    emit('select', item)
}

function handleClickOutside(event: MouseEvent) {
    const button = buttonRef.value
    const dropdown = document.querySelector(`#${props.id}`)
    if (
        button &&
        !button.contains(event.target as Node) &&
        dropdown &&
        !dropdown.contains(event.target as Node)
      ) {
        showDropdown.value = false
    }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
