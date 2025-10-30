<template>
  <ul
    :id="id"
    v-if="visible && items && items.length"
    ref="dropdownRef"
    :style="dropdownStyles"
    class="dropdown-list absolute bg-white border border-gray-300 rounded-md shadow-lg z-50 max-h-60 no-scrollbar overflow-x-auto scroll-smooth"
  >
    <li
      v-for="item in items"
      :key="getKey(item)"
      class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
      :class="{ 'bg-gray-200 font-semibold': isSelected(item) }"
      @click="selectItem(item)"
      :title="getDisplay(item)"
    >
      {{ getDisplay(item) }}
    </li>
  </ul>
</template>

<script setup lang="ts">
import { ref, watch, nextTick, onMounted, computed, unref } from 'vue'
import type { PropType, Ref } from 'vue'
import { ucfirst } from '../../lib/utils.ts'

type Item = string | { name?: string; id?: string | number; [k: string]: any }

const emit = defineEmits<{ (e: 'select', item: string): void }>()

const props = defineProps({
  id: String,
  items: { type: Array as PropType<Item[]>, default: () => [] },
  elementRef: {
    type: Object as PropType<Ref<HTMLElement | null> | null>,
    default: null
  },
  showDropdown: { type: [Boolean, Object] as PropType<boolean | Ref<boolean>>, default: false }
})

const dropdownStyles = ref<Record<string, string>>({})
const selected = ref<Item | null>(null)
const dropdownRef = ref<HTMLElement | null>(null)

const visible = computed(() => !!unref(props.showDropdown))

function getDisplay(item: Item): string {
  if (typeof item === 'string') return ucfirst(item)
  if (item && typeof item === 'object' && typeof item.name === 'string') return ucfirst(item.name)
  return String(item ?? '')
}

function getKey(item: Item): string {
  if (typeof item === 'string') return item
  if (item && typeof item === 'object') return (item.id ?? item.name ?? JSON.stringify(item)).toString()
  return String(item)
}

function isSelected(item: Item) {
  if (!selected.value) return false
  if (typeof item === 'string' && typeof selected.value === 'string') return item === selected.value
  if (typeof item === 'object' && typeof selected.value === 'object') {
    return (item.name && (selected.value as any).name) ? item.name === (selected.value as any).name : JSON.stringify(item) === JSON.stringify(selected.value)
  }
  return false
}

function selectItem(item: Item) {
    selected.value = item
    emit('select', item)
}

function positionDropdown() {
  const triggerRef = props.elementRef as Ref<HTMLElement | null> | null
  const trigger = triggerRef ? unref(triggerRef) : null
  const dropdown = dropdownRef.value
  if (!trigger || !dropdown) return

  const rect = trigger.getBoundingClientRect()
  const padding = 8
  const width = Math.max(trigger.offsetWidth, 120)
  const height = Math.min((props.items?.length ?? 0) * 40, 300)

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

watch(() => unref(props.showDropdown), (visibleNow) => {
  if (visibleNow) {
    nextTick(() => {
      positionDropdown()
      if (props.items && props.items.length && !selected.value) {
        selected.value = props.items[0]
      }
    })
  }
})

onMounted(() => {
  if (unref(props.showDropdown)) {
    nextTick(() => {
      positionDropdown()
    })
  }
})
</script>
