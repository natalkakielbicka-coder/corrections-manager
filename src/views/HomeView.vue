<script setup>
import CorrectionCard from '../components/corrections/CorrectionCard.vue'
import { corrections } from '../data/corrections'
</script>

<template>
  <main class="page">
    <div class="page__header">
      <div>
        <p class="page__eyebrow">Lista zgłoszeń</p>
        <h1>Poprawki</h1>
        <p class="page__description">
          Zarządzaj poprawkami zgłoszonymi do realizowanych projektów.
        </p>
      </div>

      <button class="button" type="button">Dodaj poprawkę</button>
    </div>

    <section class="corrections">
      <CorrectionCard
        v-for="correction in corrections"
        :key="correction.id"
        :correction="correction"
      />
    </section>
  </main>
</template>

<style scoped>
.page {
  width: min(100% - 32px, 1280px);
  margin-inline: auto;
  padding-block: 48px;
}

.page__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 32px;
}

.page__eyebrow {
  margin: 0 0 8px;
  color: var(--color-brand);
  font-size: 14px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

h1 {
  margin: 0;
  color: #111827;
  font-size: clamp(36px, 5vw, 56px);
  line-height: 1;
}

.page__description {
  max-width: 600px;
  margin: 16px 0 0;
  color: #6b7280;
  line-height: 1.6;
}

.button {
  flex-shrink: 0;
  min-height: 48px;
  padding: 12px 20px;
  border: 0;
  border-radius: 8px;
  background-color: var(--color-brand);
  color: #ffffff;
  font-weight: 700;
}

.button:hover {
  background-color: var(--color-brand-hover);
}

.corrections {
  display: grid;
  gap: 16px;
  margin-top: 40px;
}

@media (max-width: 767px) {
  .page {
    padding-block: 32px;
  }

  .page__header {
    flex-direction: column;
  }

  .button {
    width: 100%;
  }
}
</style>
