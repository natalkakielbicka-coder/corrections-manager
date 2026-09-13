<script setup>
import { nextTick, onMounted, onUnmounted, ref } from 'vue'

defineProps({
  label: {
    type: String,
    required: true,
  },
})

const emit = defineEmits(['close'])

const modalRef = ref(null)
let previouslyFocusedElement = null

const handleKeydown = (event) => {
  if (event.key === 'Escape') {
    emit('close')
  }
}

let previousBodyOverflow = ''

onMounted(() => {
  previouslyFocusedElement = document.activeElement

  nextTick(() => {
    modalRef.value?.focus()
  })

  previousBodyOverflow = document.body.style.overflow
  document.body.style.overflow = 'hidden'

  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.body.style.overflow = previousBodyOverflow

  document.removeEventListener('keydown', handleKeydown)

  previouslyFocusedElement?.focus()
})
</script>

<template>
  <div
    class="modal"
    role="dialog"
    ref="modalRef"
    tabindex="-1"
    aria-modal="true"
    :aria-label="label"
    @click.self="emit('close')"
  >
    <div class="modal__content">
      <header class="modal__header">
        <slot name="header" />

        <button class="modal__close" type="button" aria-label="Zamknij okno" @click="emit('close')">
          ×
        </button>
      </header>

      <div class="modal__body">
        <slot />
      </div>

      <footer v-if="$slots.footer" class="modal__footer">
        <slot name="footer" />
      </footer>
    </div>
  </div>
</template>

<style scoped>
.modal {
  position: fixed;
  inset: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background-color: rgba(7, 25, 54, 0.55);
}

.modal__content {
  width: min(100%, 640px);
  max-height: calc(100vh - 48px);
  overflow-y: auto;
  padding: 32px;
  border-radius: 10px;
  background-color: var(--color-surface);
  box-shadow: 0 24px 70px rgba(7, 25, 54, 0.2);
}

.modal__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
}

.modal__close {
  display: flex;
  width: 40px;
  height: 40px;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 0;
  background: none;
  color: var(--color-muted);
  font-size: 32px;
  line-height: 1;
}

.modal__close:hover {
  color: var(--color-heading);
}

.modal__body {
  margin-top: 28px;
}

.modal__footer {
  margin-top: 28px;
}

@media (max-width: 479px) {
  .modal {
    padding: 12px;
  }

  .modal__content {
    padding: 24px 20px;
  }
}
</style>
