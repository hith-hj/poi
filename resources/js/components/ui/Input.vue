<template>
  <div :class="[width, 'mx-0']">

    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
    </label>

    <div class="flex flex-row items-center text-gray-800 bg-white rounded-full shadow-lg ">
        <div v-if="prepend" class="pl-1 flex items-center cursor-pointer text-gray-500">
            <slot name="prepend"/>
        </div>

        <input
            :id="id"
            :name="name"
            :type="type"
            :title="title"
            :disabled="disabled"
            :autofocus="autofocus"
            :aria-invalid="!!error"
            :placeholder="placeholder"
            :aria-describedby="hasDesc ? `${id}-desc` : null"
            v-model="localValue"
            :class="[
              'p-2 w-full text-sm',
              'focus:outline-none focus:ring-2 focus:ring-green-500',
            ]"
        />

        <div class="pr-1 flex items-center space-x-2 z-30 cursor-pointer ">
            <Icon
                v-if="localValue"
                @click="clearInput"
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
