<script setup>
import { reactive, computed, ref } from 'vue'
import BaseModal from '../ui/BaseModal.vue'
import { usePages } from '../../composables/usePages'

const emit = defineEmits(['close', 'submit'])

const props = defineProps({
  correction: {
    type: Object,
    default: null,
  },
  isSubmitting: {
    type: Boolean,
    default: false,
  },
  editingByOther: {
    type: String,
    default: '',
  },
})

const { pages, getPageById } = usePages()

const isEditing = computed(() => {
  return Boolean(props.correction)
})

const formData = reactive({
  title: props.correction?.title ?? '',
  description: props.correction?.description ?? '',
  pageId: props.correction?.pageId ?? '',
  status: props.correction?.status ?? 'inProgress',
  imageUrl: props.correction?.imageUrl ?? '',
})

const imageInput = ref(null)

const imageFile = ref(null)

const imageError = ref('')

const allowedImageTypes = ['image/jpeg', 'image/png', 'image/webp']

const maxImageSize = 5 * 1024 * 1024

const removeImage = () => {
  formData.imageUrl = ''
  imageFile.value = null
  imageError.value = ''

  if (imageInput.value) {
    imageInput.value.value = ''
  }
}

const handleImageChange = (event) => {
  const file = event.target.files[0]

  imageError.value = ''

  if (!file) return

  if (!allowedImageTypes.includes(file.type)) {
    imageError.value = 'Wybierz plik JPG, PNG lub WebP.'
    event.target.value = ''
    return
  }

  if (file.size > maxImageSize) {
    imageError.value = 'Obraz nie może być większy niż 5 MB.'
    event.target.value = ''
    return
  }

  imageFile.value = file

  const reader = new FileReader()

  reader.addEventListener('load', () => {
    formData.imageUrl = reader.result
  })

  reader.addEventListener('error', () => {
    imageFile.value = null
    imageError.value = 'Nie udało się odczytać obrazu.'
  })

  reader.readAsDataURL(file)
}

const submitForm = () => {
  if (props.isSubmitting) return

  const selectedPage = getPageById(formData.pageId)

  if (!selectedPage) return

  const removeImage = isEditing.value && Boolean(props.correction?.imageUrl) && !formData.imageUrl

  emit('submit', {
    ...formData,
    imageFile: imageFile.value,
    removeImage,
  })
}
</script>

<template>
  <BaseModal
    :label="isEditing ? 'Edytuj poprawkę' : 'Dodaj poprawkę'"
    :prevent-close="isSubmitting"
    @close="emit('close')"
  >
    <template #header>
      <h2 class="modal-title">
        {{ isEditing ? 'Edytuj poprawkę' : 'Dodaj poprawkę' }}
      </h2>
    </template>

    <div v-if="isEditing && editingByOther" class="editing-warning" role="status">
      <strong>{{ editingByOther }}</strong>
      również edytuje tę poprawkę.

      <span>
        Możesz kontynuować, ale jeśli druga osoba zapisze wcześniej, Twoja wersja nie nadpisze jej
        zmian.
      </span>
    </div>

    <form id="correction-form" class="correction-form" @submit.prevent="submitForm">
      <fieldset class="correction-form__fieldset" :disabled="isSubmitting">
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
          <label for="correction-page"> Strona </label>

          <select id="correction-page" v-model.number="formData.pageId" required>
            <option disabled value="">Wybierz stronę</option>

            <option v-for="page in pages" :key="page.id" :value="page.id">
              {{ page.title }}
            </option>
          </select>
        </div>

        <div class="form-field">
          <label for="correction-image"> Zdjęcie lub zrzut ekranu </label>

          <input
            ref="imageInput"
            id="correction-image"
            type="file"
            accept="image/png, image/jpeg, image/webp"
            @change="handleImageChange"
          />

          <p class="form-field__hint">Dozwolone formaty: JPG, PNG i WebP.</p>

          <p v-if="imageError" class="form-field__error" role="alert">
            {{ imageError }}
          </p>

          <div v-if="formData.imageUrl" class="image-preview">
            <img :src="formData.imageUrl" alt="Podgląd załączonego obrazu" />

            <button type="button" @click="removeImage">Usuń zdjęcie</button>
          </div>
        </div>
      </fieldset>
    </form>

    <template #footer>
      <div class="correction-form__actions">
        <button
          class="button button--secondary"
          type="button"
          :disabled="isSubmitting"
          @click="emit('close')"
        >
          Anuluj
        </button>

        <button
          class="button button--primary"
          type="submit"
          form="correction-form"
          :disabled="isSubmitting"
        >
          {{ isSubmitting ? 'Zapisywanie...' : isEditing ? 'Zapisz zmiany' : 'Dodaj poprawkę' }}
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
.form-field textarea,
.form-field select {
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
.form-field textarea:focus,
.form-field select:focus {
  border-color: var(--color-brand);
  box-shadow: 0 0 0 3px var(--color-brand-light);
}

.form-field select {
  min-height: 48px;
  background-color: var(--color-surface);
  cursor: pointer;
}

.form-field + .form-field {
  margin-top: 20px;
}

.form-field input[type='file'] {
  padding: 10px;
  background-color: var(--color-surface);
  cursor: pointer;
}

.form-field__hint {
  margin: 0;
  color: var(--color-muted);
  font-size: 13px;
}

.image-preview {
  position: relative;
  overflow: hidden;
  margin-top: 8px;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  background-color: var(--color-background);
}

.image-preview img {
  width: 100%;
  max-height: 280px;
  object-fit: contain;
}

.image-preview button {
  position: absolute;
  top: 12px;
  right: 12px;
  padding: 8px 12px;
  border: 0;
  border-radius: 6px;
  background-color: rgba(180, 35, 24, 0.92);
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
}

.image-preview button:hover {
  background-color: #8f1c13;
}

.correction-form__actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 28px;
}

.correction-form__fieldset {
  min-width: 0;
  margin: 0;
  padding: 0;
  border: 0;
}

.correction-form__fieldset:disabled {
  opacity: 0.7;
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

.editing-warning {
  margin-bottom: 20px;
  padding: 12px 14px;
  border: 1px solid #fbbf24;
  border-radius: 6px;
  background-color: #fffbeb;
  color: #92400e;
  font-size: 13px;
  line-height: 1.5;
}

.editing-warning strong {
  font-weight: 800;
}

.editing-warning span {
  display: block;
  margin-top: 2px;
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
