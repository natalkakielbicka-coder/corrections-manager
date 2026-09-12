<script setup>
import { reactive, computed } from 'vue'
import BaseModal from '../ui/BaseModal.vue'

const emit = defineEmits(['close', 'submit'])

const props = defineProps({
  correction: {
    type: Object,
    default: null,
  },
})

const isEditing = computed(() => {
  return Boolean(props.correction)
})

const formData = reactive({
  title: props.correction?.title ?? '',
  description: props.correction?.description ?? '',
  page: props.correction?.page ?? '',
  pageUrl: props.correction?.pageUrl ?? '',
  status: props.correction?.status ?? 'new',
})

const submitForm = () => {
  emit('submit', { ...formData })
}
</script>

<template>
  <BaseModal label="Dodaj poprawkę" @close="emit('close')">
    <template #header>
      <h2 class="modal-title">
        {{ isEditing ? 'Edytuj poprawkę' : 'Dodaj poprawkę' }}
      </h2>
    </template>

    <form id="correction-form" class="correction-form" @submit.prevent="submitForm">
      <div class="form-field">
        <label for="correction-title">Tytuł poprawki</label>

        <input
          id="correction-title"
          v-model.trim="formData.title"
          type="text"
          placeholder="Np. Zmienić zdjęcie w nagłówku"
          required
        />
      </div>

      <div class="form-field">
        <label for="correction-description">Opis poprawki</label>

        <textarea
          id="correction-description"
          v-model.trim="formData.description"
          rows="5"
          placeholder="Opisz dokładnie, co należy poprawić..."
          required
        ></textarea>
      </div>

      <div class="form-field">
        <label for="correction-page">Nazwa strony</label>

        <input
          id="correction-page"
          v-model.trim="formData.page"
          type="text"
          placeholder="Np. Strona główna"
          required
        />
      </div>

      <div class="form-field">
        <label for="correction-page-url">Adres strony</label>

        <input
          id="correction-page-url"
          v-model.trim="formData.pageUrl"
          type="url"
          placeholder="https://example.com/"
          required
        />
      </div>
    </form>

    <template #footer>
      <div class="correction-form__actions">
        <button class="button button--secondary" type="button" @click="emit('close')">
          Anuluj
        </button>

        <button class="button button--primary" type="submit" form="correction-form">
          {{ isEditing ? 'Zapisz zmiany' : 'Dodaj poprawkę' }}
        </button>
      </div>
    </template>
  </BaseModal>
</template>

<style scoped>
.modal-title {
  margin: 0;
  color: var(--color-heading);
  font-size: 28px;
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
  .correction-form__actions {
    flex-direction: column-reverse;
  }

  .correction-form__actions .button {
    width: 100%;
  }
}
</style>
