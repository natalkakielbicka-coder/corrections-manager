<script setup>
import { ref } from 'vue'
import CorrectionCard from '../components/corrections/CorrectionCard.vue'
import EmptyCorrectionsState from '../components/corrections/EmptyCorrectionsState.vue'
import CorrectionFormModal from '../components/corrections/CorrectionFormModal.vue'
import { useCorrections } from '../composables/useCorrections'

const { sortedCorrections, addCorrection, addComment, updateCorrectionStatus } = useCorrections()

const isCorrectionFormOpen = ref(false)

const openCorrectionForm = () => {
  isCorrectionFormOpen.value = true
}

const closeCorrectionForm = () => {
  isCorrectionFormOpen.value = false
}

const handleCorrectionSubmit = (correctionData) => {
  addCorrection(correctionData)
  closeCorrectionForm()
}
</script>

<template>
  <main class="page">
    <section class="project-board">
      <header class="project-board__header">
        <div>
          <h1>Poprawki do strony</h1>

          <p class="project-board__name">Zielony Dom</p>

          <p class="project-board__description">
            Tu wspólnie zapisujemy i omawiamy poprawki do projektu.
          </p>
        </div>

        <button class="button" type="button" @click="openCorrectionForm">
          <span aria-hidden="true">+</span>
          Dodaj poprawkę
        </button>
      </header>

      <section class="corrections" v-if="sortedCorrections.length">
        <CorrectionCard
          v-for="(correction, index) in sortedCorrections"
          :key="correction.id"
          :correction="correction"
          :number="index + 1"
          @add-comment="addComment"
          @update-status="updateCorrectionStatus"
        />
      </section>

      <EmptyCorrectionsState v-else @add="openCorrectionForm" />
    </section>

    <CorrectionFormModal
      v-if="isCorrectionFormOpen"
      @close="closeCorrectionForm"
      @submit="handleCorrectionSubmit"
    />
  </main>
</template>

<style scoped>
.page {
  min-height: 100vh;
  padding: 48px 24px;
}

.project-board {
  width: min(100%, 1280px);
  min-height: calc(100vh - 96px);
  margin-inline: auto;
  padding: 48px;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  background-color: var(--color-surface);
  box-shadow: 0 16px 50px rgba(7, 25, 54, 0.06);
}

.project-board__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 32px;
  padding-bottom: 32px;
  border-bottom: 1px solid var(--color-border);
}

h1 {
  margin: 0;
  color: var(--color-heading);
  font-size: clamp(32px, 4vw, 44px);
  line-height: 1.1;
}

.project-board__name {
  margin: 8px 0 0;
  color: var(--color-heading);
  font-size: 20px;
  font-weight: 500;
}

.project-board__description {
  margin: 8px 0 0;
  color: var(--color-muted);
  line-height: 1.6;
}

.button {
  display: inline-flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: center;
  gap: 8px;
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
  margin-top: 24px;
}

@media (max-width: 767px) {
  .page {
    padding: 16px;
  }

  .project-board {
    min-height: calc(100vh - 32px);
    padding: 24px 20px;
  }

  .project-board__header {
    flex-direction: column;
  }

  .button {
    width: 100%;
  }
}
</style>
