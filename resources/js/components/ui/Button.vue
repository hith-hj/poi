<template>
  <div class="inline-block">
    <button
      ref="buttonRef"
      :title="text"
      :type="type"
      :id="id"
      :class="buttonClasses"
      :aria-label="text"
      @click="onButtonClick"
      v-bind="forwardAttrs"
    >
      <slot />
    </button>

    <List
      v-if="dropdown.length > 0"
      :items="dropdown"
      :elementRef="buttonRef"
      :showDropdown="showDropdown"
      @select="onSelect"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, nextTick, onMounted, toRefs } from 'vue'
import List from './List.vue'

const emit = defineEmits<{ (e: 'select', layer: string): void; (e: 'click', eObj: MouseEvent): void }>();

const props = defineProps({
  id: { type: String, default: () => `btn_id_${Math.floor(Math.random() * 100000)}` },
  type: { type: String, default: 'button' },
  text: { type: String, default: null },
  icon: { type: String, default: null },
  size: { type: String, default: 'md' },
  variant: { type: String, default: 'default' },
  dropdown: { type: Array as () => string[], default: () => [] }
})

const forwardAttrs = {} as Record<string, unknown>

const buttonRef = ref<HTMLElement | null>(null)
const selectedItem = ref<string | null>(null)
const dropdownStyles = ref<Record<string, string>>({})
const showDropdown = ref(false)

const buttonClasses = computed(() => {
  const base = 'p-2 rounded-full shadow cursor-pointer bg-white'
  const sizes: Record<string, string> = { sm: 'text-sm', md: 'text-md', lg: 'text-lg' }
  const variants: Record<string, string> = {
    default: 'text-black ring-1 ring-gray-200 hover:ring-gray-600',
    success: 'text-green-300 ring-2 ring-green-300 hover:ring-green-500',
    danger: 'text-red-300 ring-2 ring-red-300 hover:ring-red-500'
  }

  return `${base} ${sizes[props.size] || sizes.md} ${variants[props.variant] || variants.default}`.trim()
})

function onButtonClick(e: MouseEvent) {
  // Emit a single 'click' from this component so parent can listen with @click="..."
  emit('click', e)
  // Toggle dropdown as a component-internal action
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) nextTick(positionDropdown)
}

function positionDropdown() {
  const button = buttonRef.value
  if (!button) return

  const rect = button.getBoundingClientRect()
  const dropdownWidth = rect.width + 10
  const dropdownHeight = props.dropdown.length * 40

  let top = rect.bottom + 8
  let left = rect.left

  if (top + dropdownHeight > window.innerHeight) {
    top = window.innerHeight - dropdownHeight - 8
  }

  if (left + dropdownWidth > window.innerWidth) {
    left = window.innerWidth - dropdownWidth - 8
  }

  dropdownStyles.value = {
    position: 'fixed',
    top: `${top}px`,
    left: `${left}px`,
    width: `${dropdownWidth}px`,
    zIndex: '9999'
  }
}

function onSelect(item: string) {
  selectedItem.value = item
  // Close dropdown but do not emit a synthetic click here
  showDropdown.value = false
  emit('select', item)
}

onMounted(() => {
  if (props.dropdown.length > 0 && showDropdown.value) {
    onSelect(props.dropdown[0])
  }
})
</script>

