<script setup>
import BaseModal from '../ui/BaseModal.vue'

defineProps({
  commentContent: {
    type: String,
    required: true,
  },
  isDeleting: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close', 'confirm'])
</script>

<template>
  <BaseModal
    label="Potwierdź usunięcie komentarza"
    :prevent-close="isDeleting"
    @close="emit('close')"
  >
    <template #header>
      <h2 class="modal-title">Usunąć komentarz?</h2>
    </template>

    <div class="delete-confirmation">
      <p class="delete-confirmation__comment">„{{ commentContent }}”</p>

      <p class="delete-confirmation__warning">Tej operacji nie będzie można cofnąć.</p>
    </div>

    <template #footer>
      <div class="delete-confirmation__actions">
        <button
          class="button button--secondary"
          type="button"
          :disabled="isDeleting"
          @click="emit('close')"
        >
          Anuluj
        </button>

        <button
          class="button button--danger"
          type="button"
          :disabled="isDeleting"
          @click="emit('confirm')"
        >
          {{ isDeleting ? 'Usuwanie...' : 'Usuń komentarz' }}
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

.delete-confirmation__comment {
  margin: 0;
  color: var(--color-text);
  font-style: italic;
  line-height: 1.6;
}

.delete-confirmation__warning {
  margin: 12px 0 0;
  color: #b42318;
  font-size: 14px;
}

.delete-confirmation__actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}

.button {
  min-height: 46px;
  padding: 11px 18px;
  border: 1px solid transparent;
  border-radius: 6px;
  font-weight: 700;
}

.button--secondary {
  border-color: var(--color-border);
  background-color: var(--color-surface);
  color: var(--color-text);
}

.button--secondary:hover {
  border-color: var(--color-muted);
}

.button--danger {
  background-color: #b42318;
  color: #ffffff;
}

.button--danger:hover {
  background-color: #8f1c13;
}

@media (max-width: 479px) {
  .delete-confirmation__actions {
    flex-direction: column-reverse;
  }

  .delete-confirmation__actions .button {
    width: 100%;
  }
}
</style>
