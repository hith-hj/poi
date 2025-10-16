<template>
	<transition name="fade">
	    <div v-if="modelValue"
	      class="fixed inset-0 z-50 flex items-center justify-center backdrop-blur-[4px]"
	      @click.self="close"
	    >
			<div
				class="w-full max-w-lg rounded-lg bg-white p-6 shadow-lg"
				role="dialog"
				aria-modal="true"
			>
			<header class="mb-4 flex justify-between items-center">
			  <h2 class="text-lg font-semibold">
			    <slot name="title">Modal Title</slot>
			  </h2>
			  <button @click="close" aria-label="Close modal" class="text-gray-500 hover:text-black">
			    ✕
			  </button>
			</header>

			<div class="mb-4">
			  <slot>
			    Modal content goes here.
			  </slot>
			</div>

			<footer class="flex justify-end gap-2">
			  <slot name="footer">
			    <button @click="close" class="rounded bg-gray-200 px-4 py-2 hover:bg-gray-300">
			      Close
			    </button>
			  </slot>
			</footer>
			</div>
		</div>
	</transition>
</template>

<script setup lang="ts">
	import { defineEmits, defineProps } from 'vue';

	const props = defineProps<{
	  modelValue: boolean;
	}>();

	const emit = defineEmits(['update:modelValue']);

	const close = () => {
	  emit('update:modelValue', false);
	};
</script>
