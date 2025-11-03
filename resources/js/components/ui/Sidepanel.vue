<!--
usage
<Sidepanel v-model="show" side="left" maxWidthClass="max-w-sm">
        <template #trigger>
          <button @click="show = true" class="px-4 py-2 bg-green-600 text-white rounded">
              Open Panel
          </button>
        </template>

        <template #title>My Panel</template>

        <p>
          Panel content goes here. Backdrop click, Escape, or swipe down on mobile will close it.
        </p>
    </Sidepanel>

 -->
<template>
  <div>
    <slot name="trigger">
      <button @click="open" class="px-4 py-2 bg-blue-600 text-white rounded">Open Panel</button>
    </slot>

    <teleport to="body">
      <transition
        :enter-from-class="enterFrom"
        :enter-to-class="enterTo"
        :enter-active-class="enterActive"
        :leave-from-class="leaveFrom"
        :leave-to-class="leaveTo"
        :leave-active-class="leaveActive"
      >
        <div v-if="visible" class="fixed inset-0 z-50">
          <!-- Backdrop -->
          <div
            class="absolute inset-0 bg-black/40 transition-opacity duration-300"
            :class="visible ? 'opacity-100' : 'opacity-0'"
            @click="onBackdropClick"
            aria-hidden="true"
          />

          <!-- Panel -->
          <div
            ref="panelEl"
            :class="panelClasses"
            :style="panelStyle"
            role="dialog"
            aria-modal="true"
            :aria-label="ariaLabel"
            @click.stop
            @touchstart.passive="onTouchStart"
            @touchmove.passive="onTouchMove"
            @touchend.passive="onTouchEnd"
          >
            <div class="flex items-center justify-between px-4 py-2 border-b">
              <div class="text-lg font-medium"><slot name="title">Panel</slot></div>
              <button @click="close" aria-label="Close" class="p-1 rounded hover:bg-slate-100">
                <slot name="close">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12" /></svg>
                </slot>
              </button>
            </div>

            <div class="p-4 overflow-auto max-h-[80vh]">
              <slot />
            </div>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onBeforeUnmount, nextTick } from 'vue';

export default {
  name: 'SlidingPanel',
  props: {
    modelValue: { type: Boolean, default: false },
    side: { type: String, default: 'left', validator: v => ['left', 'right'].includes(v) },
    // Accept both "max-w-lg" or "md:max-w-lg"
    maxWidthClass: { type: String, default: 'max-w-md' },
    closeOnBackdrop: { type: Boolean, default: true },
    ariaLabel: { type: String, default: 'Sliding panel' }
  },
  emits: ['update:modelValue', 'open', 'close'],
  setup(props, { emit, expose }) {
    const visible = ref(props.modelValue);
    const panelEl = ref(null);
    const panelStyle = ref(null);
    let previouslyFocused = null;

    // normalize v-model
    watch(() => props.modelValue, v => (visible.value = v));
    watch(visible, v => emit('update:modelValue', v));

    // normalize passed maxWidth so it's applied at md+ (if user passed without md: prefix)
    const normalizedMaxWidth = computed(() => {
      return props.maxWidthClass.startsWith('md:') ? props.maxWidthClass : `md:${props.maxWidthClass}`;
    });

    const open = async () => {
      if (!visible.value) {
        previouslyFocused = document.activeElement;
        visible.value = true;
        emit('open');
        await nextTick();
        trapFocus();
      }
    };

    const close = () => {
      if (visible.value) {
        visible.value = false;
        emit('close');
        restoreFocus();
      }
    };

    const onBackdropClick = () => {
      if (!props.closeOnBackdrop) return;
      close();
    };

    // keyboard handling
    const onKeyDown = (e) => {
      if (!visible.value) return;
      if (e.key === 'Escape') { e.preventDefault(); close(); return; }
      if (e.key === 'Tab') maintainFocus(e);
    };

    onMounted(() => document.addEventListener('keydown', onKeyDown));
    onBeforeUnmount(() => document.removeEventListener('keydown', onKeyDown));

    // focus trap
    const getFocusable = (container) => {
      if (!container) return [];
      const selectors = [
        'a[href]',
        'area[href]',
        'input:not([disabled]):not([type="hidden"])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        'button:not([disabled])',
        'iframe',
        '[contenteditable]',
        '[tabindex]:not([tabindex^="-"])'
      ];
      return Array.from(container.querySelectorAll(selectors.join(','))).filter(el => !!(el.offsetWidth || el.offsetHeight || el.getClientRects().length));
    };

    const trapFocus = () => {
      const el = panelEl.value;
      if (!el) return;
      const focusables = getFocusable(el);
      if (focusables.length) focusables[0].focus();
      else { el.setAttribute('tabindex', '-1'); el.focus(); }
    };

    const maintainFocus = (e) => {
      const el = panelEl.value;
      if (!el) return;
      const focusables = getFocusable(el);
      if (!focusables.length) { e.preventDefault(); return; }
      const first = focusables[0], last = focusables[focusables.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    };

    const restoreFocus = () => {
      nextTick(() => { if (previouslyFocused && typeof previouslyFocused.focus === 'function') previouslyFocused.focus(); previouslyFocused = null; });
    };

    // transform origin so translate looks correct
    const originClass = computed(() => {
      return props.side === 'right' ? 'origin-bottom md:origin-right' : 'origin-bottom md:origin-left';
    });

    // transition classes — ensure vertical translate is cancelled on md+ with md:translate-y-0
    const isRight = computed(() => props.side === 'right');
    const enterActive = 'transform transition duration-300 ease-in-out';
    const leaveActive = 'transform transition duration-300 ease-in-out';

    const enterFrom = computed(() => {
      // mobile: start below; ensure md cancels vertical offset and starts horizontally off-screen
      return `translate-y-full md:translate-y-0 ${isRight.value ? 'md:translate-x-full' : 'md:-translate-x-full'}`;
    });
    const enterTo = 'translate-y-0 md:translate-x-0';
    const leaveFrom = 'translate-y-0 md:translate-x-0';
    const leaveTo = computed(() => {
      return `translate-y-full md:translate-y-0 ${isRight.value ? 'md:translate-x-full' : 'md:-translate-x-full'}`;
    });

    // panel classes — anchored to one side on md+, ensure opposite side is auto and md cancels translate-y
    const panelClasses = computed(() => {
      const base = [
        'fixed',
        'z-50',
        'bg-white',
        'shadow-xl',
        'flex',
        'flex-col',
        'transition-transform',
        'duration-300',
        'ease-in-out',
        'outline-none',
        originClass.value
      ];

      // mobile bottom sheet
      const small = ['left-0', 'right-0', 'bottom-0', 'w-full', 'h-1/2', 'flex', 'translate-y-full'];

      // md+ anchored and sized (use normalized width). ensure md:translate-y-0 cancels vertical offset
      const mdSide = props.side === 'right'
        ? ['md:top-0', 'md:right-0', 'md:left-auto', normalizedMaxWidth.value, 'md:h-full', 'md:translate-y-0']
        : ['md:top-0', 'md:left-0', 'md:right-auto', normalizedMaxWidth.value, 'md:h-full', 'md:translate-y-0'];

      return [...base, ...small, ...mdSide].join(' ');
    });

    // swipe handling
    let touchStartX = 0, touchStartY = 0, touchCurrentX = 0, touchCurrentY = 0, isSwiping = false;

    const onTouchStart = (e) => {
      if (!e.touches || !e.touches.length) return;
      touchStartX = e.touches[0].clientX; touchStartY = e.touches[0].clientY;
      touchCurrentX = touchStartX; touchCurrentY = touchStartY; isSwiping = true;
    };

    const onTouchMove = (e) => {
      if (!isSwiping || !e.touches || !e.touches.length) return;
      touchCurrentX = e.touches[0].clientX; touchCurrentY = e.touches[0].clientY;
      const panel = panelEl.value;
      if (!panel) return;
      const dx = touchCurrentX - touchStartX; const dy = touchCurrentY - touchStartY;
      const isMobile = window.matchMedia('(max-width: 767px)').matches;

      // apply a lightweight transform during drag; cleared on end
      if (isMobile) {
        const translateY = Math.max(0, dy);
        panelStyle.value = { transform: `translateY(${translateY}px)` };
      } else {
        if ((isRight.value && dx > 0) || (!isRight.value && dx < 0)) {
          panelStyle.value = { transform: `translateX(${dx}px)` };
        }
      }
    };

    const onTouchEnd = () => {
      if (!isSwiping) return;
      isSwiping = false;
      const dx = touchCurrentX - touchStartX; const dy = touchCurrentY - touchStartY;
      const absDx = Math.abs(dx), absDy = Math.abs(dy);
      const isMobile = window.matchMedia('(max-width: 767px)').matches;

      // clear interactive style (let CSS transitions handle final state)
      panelStyle.value = null;

      const threshold = 80;
      if (isMobile) {
        if (absDy > absDx && dy > threshold) close();
      } else {
        if (absDx > absDy && absDx > threshold) {
          if ((isRight.value && dx > 0) || (!isRight.value && dx < 0)) close();
        }
      }

      touchStartX = touchStartY = touchCurrentX = touchCurrentY = 0;
    };

    expose({ open, close });

    onBeforeUnmount(() => restoreFocus());

    return {
      visible,
      panelEl,
      panelStyle,
      open,
      close,
      onBackdropClick,
      onTouchStart,
      onTouchMove,
      onTouchEnd,
      panelClasses,
      enterFrom,
      enterTo,
      enterActive,
      leaveFrom,
      leaveTo,
      leaveActive,
      ariaLabel: props.ariaLabel
    };
  }
};
</script>
