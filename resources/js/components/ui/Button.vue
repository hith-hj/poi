<template>
  <div class="inline-block">
    <button
        ref="buttonRef"
        :title="text"
        :type="type"
        :id="id"
        :class="buttonClasses"
        :aria-label="text"
        @click="toggleDropdown"
        v-bind="$attrs"
    >
        <Icon v-if="icon" :icon="icon" :size="size" />
        <span v-if="text" class="truncate">{{ text }}</span>
        <slot />
    </button>

    <ul
      v-if="showDropdown && dropdown.length"
      :style="dropdownStyles"
      class="bg-white border border-gray-300 rounded-md shadow-lg"
    >
        <li
            v-for="(item, index) in dropdown"
            :key="index"
            class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
            :class="{ 'bg-gray-200 font-semibold': item === selectedItem }"
            @click="handleSelect(item)"
        >
            <Icon v-if="icon" :icon="icon" />
            {{ ucfirst(item) }}
        </li>
    </ul>
  </div>
</template>


<script setup lang='ts'>
    import { ref, computed, onMounted, nextTick} from 'vue'
    import Icon from './Icon.vue'

    const emit = defineEmits<{
      (e: 'select',layer: string): void;
    }>();

    const props = defineProps({
      id: { type: String, default: () => `btn_id_${Math.floor(Math.random() * 100000)}` },
      type : {type: String, default: 'button'},
      text: { type: String, default: null },
      icon: { type: String, default: null },
      size: { type: String, default: 'md' },
      variant: { type: String, default: 'default' },
      dropdown: { type: Array as () => string[], default: () => [] }
    })

    const buttonRef = ref(null)
    const selectedItem = ref(null)
    const dropdownStyles = ref({})
    const showDropdown = ref(false)
    const buttonClasses = computed(() => {
        const base = 'inline-flex items-center justify-center rounded-full transition-shadow shadow-sm border border-transparent cursor-pointer p-2 focus:outline-none focus:ring-2 focus:bg-white';
        const sizes = {
            sm: 'text-sm h-6',
            md: 'text-sm h-8',
            lg: 'text-base h-10',
        }
        const variants = {
            default: 'bg-white text-black ring-1 ring-gray-300 hover:ring-gray-500',
            success: 'bg-green-300 text-white hover:bg-green-500',
            danger: 'bg-red-300 text-white hover:bg-red-500',
        }

        let classes = `${base} ${sizes[props.size] || sizes.md} ${variants[props.variant] || variants.default}`;
        if (props.icon && props.text) classes += ' h-[30px] w-fit gap-2'
        if (props.icon && !props.text) classes += ' h-[35px] w-[35px]'
        return classes.trim()
    })

    function toggleDropdown() {
        showDropdown.value = !showDropdown.value
        if (showDropdown.value) nextTick(positionDropdown)
    }

    function positionDropdown() {
        const button = buttonRef.value
        if (!button) return

        const rect = button.getBoundingClientRect()
        const dropdownWidth = 100
        const dropdownHeight = props.dropdown.length * 40

        let top = rect.bottom + 8
        let left = rect.left

        // Prevent overflow bottom
        if (top + dropdownHeight > window.innerHeight) {
            top = window.innerHeight - dropdownHeight - 8
        }

        // Prevent overflow right
        if (left + dropdownWidth > window.innerWidth) {
            left = window.innerWidth - dropdownWidth - 8
        }

        dropdownStyles.value = {
        position: 'fixed',
        top: `${top}px`,
        left: `${left}px`,
        width: `${dropdownWidth}px`,
        zIndex: 9999,
        }
    }

    function handleSelect(item: string) {
        selectedItem.value = item
        showDropdown.value = false
        emit('select', item)
    }

    function ucfirst(str) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    }

    onMounted(()=>{
        if(props.dropdown.length > 0){
            handleSelect(props.dropdown[0])
        }
    })
</script>
