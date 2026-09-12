<script setup>
import { correctionStatuses } from '../../constants/correctionStatuses'
import CorrectionComments from '../comments/CorrectionComments.vue'

const props = defineProps({
  correction: {
    type: Object,
    required: true,
  },
  number: {
    type: Number,
    required: true,
  },
})
</script>

<template>
  <article class="correction-card">
    <div class="correction-card__content">
      <div class="correction-card__header">
        <h2 class="correction-card__title">
          <span>{{ number }}.</span>
          {{ correction.title }}
        </h2>

        <span
          class="correction-card__status"
          :class="correctionStatuses[correction.status].className"
        >
          {{ correctionStatuses[correction.status].label }}
        </span>
      </div>

      <p class="correction-card__description">
        {{ correction.description }}
      </p>

      <p class="correction-card__page">
        <span aria-hidden="true">🔗</span>
        {{ correction.page }}
      </p>

      <div class="correction-card__actions">
        <button type="button">Edytuj</button>

        <span aria-hidden="true">|</span>

        <button type="button">Usuń</button>
      </div>
    </div>

    <CorrectionComments :comments="correction.comments" />
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
  padding-right: 32px;
}

.correction-card__title {
  margin: 0;
  color: #111827;
  font-size: 20px;
}

.correction-card__description {
  margin: 12px 0 0;
  color: #6b7280;
  line-height: 1.6;
}

.correction-card__page {
  margin: 0 0 8px;
  color: var(--color-brand);
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.correction-card__page {
  margin: 0;
  color: var(--color-brand);
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
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
  align-items: center;
  gap: 8px;
  margin: 8px 0 0;
  color: var(--color-brand);
  font-size: 14px;
  font-weight: 600;
}

.correction-card__status {
  flex-shrink: 0;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
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
}
</style>
