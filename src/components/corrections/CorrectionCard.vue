<script setup>
import { correctionStatuses } from '../../constants/correctionStatuses'
import CorrectionComments from '../comments/CorrectionComments.vue'

defineProps({
  correction: {
    type: Object,
    required: true,
  },
  number: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits(['add-comment', 'update-status', 'edit', 'delete'])
</script>

<template>
  <article class="correction-card">
    <div class="correction-card__content">
      <div class="correction-card__header">
        <h2 class="correction-card__title">
          <span>{{ number }}.</span>
          {{ correction.title }}
        </h2>
        <select
          class="correction-card__status"
          :class="correctionStatuses[correction.status].className"
          :value="correction.status"
          aria-label="Zmień status poprawki"
          @change="
            emit('update-status', {
              correctionId: correction.id,
              status: $event.target.value,
            })
          "
        >
          <option
            v-for="(statusData, statusValue) in correctionStatuses"
            :key="statusValue"
            :value="statusValue"
          >
            {{ statusData.label }}
          </option>
        </select>
      </div>

      <p class="correction-card__description">
        {{ correction.description }}
      </p>

      <a
        class="correction-card__page"
        :href="correction.pageUrl"
        target="_blank"
        rel="noopener noreferrer"
      >
        <span aria-hidden="true">🔗</span>
        {{ correction.page }}
      </a>

      <div class="correction-card__actions">
        <button type="button" @click="emit('edit', correction.id)">Edytuj</button>

        <span aria-hidden="true">|</span>

        <button type="button" @click="emit('delete', correction.id)">Usuń</button>
      </div>
    </div>

    <CorrectionComments
      :comments="correction.comments"
      :correction-id="correction.id"
      @add="emit('add-comment', $event)"
    />
  </article>
</template>

<style scoped>
.correction-card {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(320px, 0.9fr);
  padding-block: 24px;
  border-bottom: 1px solid var(--color-border);
}

.correction-card:last-child {
  border-bottom: 0;
}

.correction-card__content {
  min-width: 0;
  padding-right: 32px;
}

.correction-card__header {
  display: flex;
  align-items: center;
  gap: 16px;
}

.correction-card__title {
  margin: 0;
  color: var(--color-heading);
  font-size: 20px;
  line-height: 1.4;
}

.correction-card__description {
  margin: 8px 0 0;
  color: var(--color-text);
  line-height: 1.6;
}

.correction-card__page {
  display: flex;
  width: fit-content;
  align-items: center;
  gap: 8px;
  margin: 8px 0 0;
  color: var(--color-brand);
  font-size: 14px;
  font-weight: 600;
  text-decoration: none;
}

.correction-card__page:hover {
  color: var(--color-brand-hover);
}

.correction-card__status {
  flex-shrink: 0;
  padding: 6px 30px 6px 12px;
  border: 0;
  border-radius: 999px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2340516c' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  background-size: 12px;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  outline: none;
  appearance: none;
}

.status--new {
  background-color: #fee2e2;
  color: #b42318;
}

.status--in-progress {
  background-color: #fef3c7;
  color: #92400e;
}

.status--ready {
  background-color: #d1fae5;
  color: #067647;
}

.correction-card__actions {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-top: 12px;
  color: var(--color-muted);
}

.correction-card__actions button {
  padding: 0;
  border: 0;
  background: none;
  color: inherit;
  font-size: 14px;
}

.correction-card__actions button:hover {
  color: var(--color-brand);
}

.correction-card__actions .correction-card__delete:hover {
  color: #b42318;
}

.correction-card > .comments {
  min-width: 0;
  padding-left: 32px;
  border-left: 1px solid var(--color-border);
}

@media (max-width: 767px) {
  .correction-card__header {
    align-items: flex-start;
    flex-direction: column;
    gap: 10px;
  }

  .correction-card {
    grid-template-columns: 1fr;
  }

  .correction-card__content {
    padding-right: 0;
  }

  .correction-card > .comments {
    margin-top: 24px;
    padding-top: 24px;
    padding-left: 0;
    border-top: 1px solid var(--color-border);
    border-left: 0;
  }
}
</style>
