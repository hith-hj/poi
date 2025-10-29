<template>
  <ul
    :id="id"
    v-if="items.length && showDropdown"
    ref="dropdownRef"
    :style="dropdownStyles"
    class="dropdown-list absolute bg-white border border-gray-300 rounded-md shadow-lg z-50 max-h-60  no-scrollbar overflow-x-auto scroll-smooth"
  >
    <li
      v-for="item in items"
      :key="item"
      class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
      :class="{ 'bg-gray-200 font-semibold': item === selected }"
      @click="selectItem(item)"
      :title="item"
    >
      {{ ucfirst(item) }}
    </li>
  </ul>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onMounted } from 'vue'
import type { PropType, Ref } from 'vue'
import { ucfirst } from '../../lib/utils.ts'

const emit = defineEmits<{ (e: 'select', item: string): void }>()

const props = defineProps({
    id: String,
    items: { type: Array as PropType<string[]>, default: () => [] },
    elementRef: {
      type: Object as PropType<Ref<HTMLElement | null> | null>,
      default: null
    },
    showDropdown: { type: Boolean, default: false }
})

const dropdownStyles = ref<Record<string, string>>({})
const selected = ref<string>('')
const dropdownRef = ref<HTMLElement | null>(null)

function selectItem(item: string) {
  selected.value = item
  emit('select', item)
}

function positionDropdown() {
  const trigger = props.elementRef
  const dropdown = dropdownRef.value
  if (!trigger || !dropdown) return

  const rect = trigger.getBoundingClientRect()
  const padding = 8
  const width = Math.max(trigger.offsetWidth, 120)
  const height = Math.min(props.items.length * 40, 300)

  let top = rect.bottom + padding
  let left = rect.left

  if (top + height > window.innerHeight) {
    top = rect.top - height - padding
  }

  if (left + width > window.innerWidth) {
    left = window.innerWidth - width - padding
  }

  dropdownStyles.value = {
    position: 'fixed',
    top: `${Math.max(top, padding)}px`,
    left: `${Math.max(left, padding)}px`,
    width: `${width}px`
  }
}

watch(() => props.showDropdown, (visible) => {
  if (visible) {
    nextTick(() => {
      positionDropdown()
      if (props.items.length && !selected.value) {
        selected.value = props.items[0]
      }
    })
  }
})

onMounted(() => {
  if (props.showDropdown) {
    nextTick(() => {
      positionDropdown()
    })
  }
})
</script>
