<script setup>
import { onMounted, onUnmounted } from 'vue'

const emit = defineEmits(['close'])

const handleKeydown = (event) => {
  if (event.key === 'Escape') {
    emit('close')
  }
}

let previousBodyOverflow = ''

onMounted(() => {
  previousBodyOverflow = document.body.style.overflow
  document.body.style.overflow = 'hidden'

  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.body.style.overflow = previousBodyOverflow

  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <div
    class="modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="correction-form-title"
    @click.self="emit('close')"
  >
    <div class="modal__content">
      <header class="modal__header">
        <h2 id="correction-form-title">Dodaj poprawkę</h2>

        <button
          class="modal__close"
          type="button"
          aria-label="Zamknij formularz"
          @click="emit('close')"
        >
          ×
        </button>
      </header>

      <p class="modal__placeholder">Tutaj dodamy formularz poprawki.</p>
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

.modal__header h2 {
  margin: 0;
  color: var(--color-heading);
  font-size: 28px;
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

.modal__placeholder {
  margin: 32px 0 0;
  color: var(--color-muted);
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
