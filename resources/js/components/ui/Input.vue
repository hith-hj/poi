<template>
  <div :class="[width, 'mx-0']">
    <!-- Label -->
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
    </label>

    <div class="relative">
      <!-- Icon -->
      <div
        v-if="icon"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10 text-gray-500"
      >
        <Icon :name="icon" class="h-5 w-5" />
      </div>

      <!-- Input -->
      <input
        :id="id"
        :name="name"
        :type="type"
        :title="title"
        v-model="inputValue"
        :placeholder="placeholder"
        :autofocus="autofocus"
        :disabled="disabled"
        :aria-describedby="hasDesc ? `${id}-desc` : null"
        :aria-invalid="!!error"
        :class="[
          'py-2 block w-full text-sm text-gray-900 bg-white rounded-full transition-shadow shadow-sm',
          'border border-transparent focus:outline-none focus:ring-2 focus:ring-green-500',
          icon ? 'pl-11' : 'pl-3'
        ]"
      />

      <!-- Append slot & Clear button -->
      <div class="absolute inset-y-0 right-0 pr-2 flex items-center space-x-2 z-20">
        <Icon
          v-if="inputValue"
          type="button"
          aria-label="Clear"
          @click="clearInput"
          class="px-2"
          icon="x"
        />
        <slot name="append" />
      </div>
    </div>

    <p
      v-if="error"
      :id="`${id}-desc`"
      class="mt-1 font-semibold text-sm text-red-500"
    >
      {{ error }}
    </p>
    <p
      v-else-if="helper"
      :id="`${id}-desc`"
      class="mt-1 font-semibold text-sm text-gray-500"
    >
      {{ helper }}
    </p>
  </div>
</template>

<script setup>
  import { ref, computed, watch } from 'vue'
  import Button from './Button.vue'
  import Icon from './Icon.vue'

  // Props
  const props = defineProps({
    id: {
      type: String,
      default: () => 'field_id_' + Math.floor(Math.random() * 100000),
    },
    name: String,
    type: {
      type: String,
      default: 'text',
    },
    value: {
      type: [String, Number],
      default: '',
    },
    placeholder: {
      type: String,
      default: '',
    },
    label: String,
    icon: String,
    helper: String,
    error: String,
    width: {
      type: String,
      default: 'w-full',
    },
    autofocus: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    title: String,
  })

  // Emits
  const emit = defineEmits(['update:value'])

  // Reactive input value
  const inputValue = ref(props.value)

  // Watch for external changes
  watch(() => props.value, (val) => {
    inputValue.value = val
  })

  // Emit changes
  watch(inputValue, (val) => {
    emit('update:value', val)
  })

  // Clear input
  function clearInput() {
    inputValue.value = ''
  }

  const hasDesc = computed(() => !!props.error || !!props.helper)
</script>
