<template>
  <div :class="[width, 'mx-0']">
    <!-- Label -->
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
    </label>

    <div class="relative">
      <!-- prepend -->
      <div v-if="prepend"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10 text-gray-500">
        <slot name="prepend"/>
      </div>

      <!-- Input -->
      <input
        :id="id"
        :name="name"
        :type="type"
        :title="title"
        v-model="localValue"
        :placeholder="placeholder"
        :autofocus="autofocus"
        :disabled="disabled"
        :aria-describedby="hasDesc ? `${id}-desc` : null"
        :aria-invalid="!!error"
        :class="[
          'py-2 block w-full text-sm text-gray-900 bg-white rounded-full transition-shadow shadow-sm',
          'border border-transparent focus:outline-none focus:ring-2 focus:ring-green-500',
          prepend ? 'pl-10' : 'pl-3',
        ]"
      />

      <!-- Append slot & Clear button -->
      <div class="absolute inset-y-0 right-0 pr-2 flex items-center space-x-2 z-20">
        <Icon
          v-if="localValue"
          type="button"
          aria-label="Clear"
          @click="clearInput"
          class="px-2"
          icon="x"
        />
        <slot name="append" />
      </div>
    </div>

    <p v-if="error"
      :id="`${id}-desc`"
      class="mt-1 font-semibold text-sm text-red-500">
      {{ error }}
    </p>
    <p v-else-if="helper"
      :id="`${id}-desc`"
      class="mt-1 font-semibold text-sm text-gray-500">
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
    inputValue: {
      type: [String, Number],
      default: '',
    },
    placeholder: {
      type: String,
      default: '',
    },
    label: String,
    prepend: {type:Boolean,default:false},
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

  const emit = defineEmits(['update:inputValue'])

  const localValue = ref(props.inputValue)

  watch(() => props.inputValue, (val) => {
    localValue.value = val
  })

  watch(localValue, (val) => {
    emit('update:inputValue', val)
  })

  function clearInput() {
    localValue.value = ''
  }

  const hasDesc = computed(() => !!props.error || !!props.helper)
</script>
