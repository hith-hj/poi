<template>
  <ul
    v-if="items.length && showDropdown"
    :style="dropdownStyles"
    class="absolute bg-white border border-gray-300 rounded-md shadow-lg z-50 no-scrollbar overflow-x-auto scroll-smooth"
  >
    <li
      v-for="(item, index) in items"
      :key="item"
      class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
      :class="{ 'bg-gray-200 font-semibold': item === selected }"
      @click="selectItem(item)"
    >
      {{ capitalize(item) }}
    </li>
  </ul>
</template>

<script setup lang="ts">
    import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
    import type { PropType, Ref } from 'vue'

    const emit = defineEmits<{ (e: 'select', item: string): void }>()

    const props = defineProps({
      items: { type: Array as PropType<string[]>, default: () => [] },
      elementRef: { type: Object as PropType<Ref<HTMLButtonElement | null>>, default: () => null },
      showDropdown: { type: Boolean, default: false }
    })

    const dropdownStyles = ref<Record<string, string>>({})
    const selected = ref<string>('')

    function selectItem(item: string) {
      selected.value = item
      emit('select', item)
    }

    function capitalize(str: string) {
      return str.charAt(0).toUpperCase() + str.slice(1)
    }

    function dropdownPosition() {
      const button = props.elementRef
      if (!button) return
      const rect = button.getBoundingClientRect()
      const dropdownWidth = 120
      const dropdownHeight = props.items.length * 40
      const padding = 10
      let top = rect.bottom + padding
      let left = rect.left - rect.width

      if (top + dropdownHeight > window.innerHeight) {
        const aboveTop = rect.top - dropdownHeight - padding
        top = aboveTop > padding ? aboveTop : window.innerHeight - dropdownHeight - padding
      }

      if (left + dropdownWidth > window.innerWidth) {
        left = window.innerWidth - dropdownWidth - padding
      }

      top = Math.max(top, padding)
      left = Math.max(left, padding)

      dropdownStyles.value = {
        position: 'fixed',
        top: `${top}px`,
        left: `${left}px`,
        width: `${dropdownWidth}px`,
        'max-height': '200px',
        overflow:'auto'
      }
    }

    onMounted(() => {
        nextTick(() => {
            dropdownPosition();
            if (props.items.length > 0 && !selected.value) {
                selected.value = props.items[0]
            }
        })
    })

    watch(() => props.showDropdown, (visible) => {
      if (visible) {
        nextTick(() => {
          dropdownPosition()
          if (props.items.length > 0 && !selected.value) {
              selected.value = props.items[0]
          }
        })
      }
    })
</script>

