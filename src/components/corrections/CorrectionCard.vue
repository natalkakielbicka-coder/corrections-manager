<script setup>
import { correctionStatuses } from '../../constants/correctionStatuses'

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
    <div class="correction-card__meta">
      <p class="correction-card__page">
        {{ correction.page }}
      </p>

      <span
        class="correction-card__status"
        :class="correctionStatuses[correction.status].className"
      >
        {{ correctionStatuses[correction.status].label }}
      </span>
    </div>

    <h2 class="correction-card__title">
      <span>{{ number }}.</span>
      {{ correction.title }}
    </h2>

    <p class="correction-card__description">
      {{ correction.description }}
    </p>
  </article>
</template>

<style scoped>
.correction-card {
  padding: 24px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background-color: #ffffff;
  margin-bottom: 20px;
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

.correction-card__meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 12px;
}

.correction-card__page {
  margin: 0;
  color: var(--color-brand);
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
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
</style>
