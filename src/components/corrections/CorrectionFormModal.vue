<script setup>
import { onMounted, onUnmounted, reactive } from 'vue'

const emit = defineEmits(['close', 'submit'])

const handleKeydown = (event) => {
  if (event.key === 'Escape') {
    emit('close')
  }
}

const formData = reactive({
  title: '',
  description: '',
  page: '',
  pageUrl: '',
  status: 'new',
})

const submitForm = () => {
  emit('submit', { ...formData })
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

      <form class="correction-form" @submit.prevent="submitForm">
        <div class="form-field">
          <label for="correction-title"> Tytuł poprawki </label>

          <input
            id="correction-title"
            v-model.trim="formData.title"
            type="text"
            placeholder="Np. Zmienić zdjęcie w nagłówku"
            required
          />
        </div>

        <div class="form-field">
          <label for="correction-description"> Opis poprawki </label>

          <textarea
            id="correction-description"
            v-model.trim="formData.description"
            rows="2"
            placeholder="Opisz dokładnie, co należy poprawić..."
            required
          ></textarea>
        </div>

        <div class="form-field">
          <label for="correction-page"> Nazwa podstrony </label>

          <input
            id="correction-page"
            v-model.trim="formData.page"
            type="text"
            placeholder="Np. Strona główna"
            required
          />
        </div>

        <div class="form-field">
          <label for="correction-page-url"> Adres strony </label>

          <input
            id="correction-page-url"
            v-model.trim="formData.pageUrl"
            type="url"
            placeholder="https://example.com/"
            required
          />
        </div>

        <div class="correction-form__actions">
          <button class="button button--secondary" type="button" @click="emit('close')">
            Anuluj
          </button>

          <button class="button button--primary" type="submit">Dodaj poprawkę</button>
        </div>
      </form>
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

.correction-form {
  margin-top: 32px;
}

.form-field {
  display: grid;
  gap: 8px;
}

.form-field label {
  color: var(--color-heading);
  font-size: 14px;
  font-weight: 700;
}

.form-field input,
.form-field textarea {
  width: 100%;
  padding: 11px 14px;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  color: var(--color-heading);
  font: inherit;
  outline: none;
}

.form-field input::placeholder,
.form-field textarea::placeholder {
  color: var(--color-muted);
}

.form-field input:focus,
.form-field textarea::focus {
  border-color: var(--color-brand);
  box-shadow: 0 0 0 3px var(--color-brand-light);
}

.form-field + .form-field {
  margin-top: 20px;
}

.correction-form__actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 28px;
}

.button {
  min-height: 46px;
  padding: 11px 18px;
  border: 1px solid transparent;
  border-radius: 6px;
  font-weight: 700;
}

.button--primary {
  background-color: var(--color-brand);
  color: #ffffff;
}

.button--primary:hover {
  background-color: var(--color-brand-hover);
}

.button--secondary {
  border-color: var(--color-border);
  background-color: var(--color-surface);
  color: var(--color-text);
}

.button--secondary:hover {
  border-color: var(--color-muted);
}

@media (max-width: 479px) {
  .modal {
    padding: 12px;
  }

  .modal__content {
    padding: 24px 20px;
  }

  .correction-form__actions {
    flex-direction: column-reverse;
  }

  .correction-form__actions .button {
    width: 100%;
  }
}
</style>
