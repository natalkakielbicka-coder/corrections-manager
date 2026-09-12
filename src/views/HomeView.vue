<script setup>
import { ref } from 'vue'
import CorrectionsList from '../components/corrections/CorrectionsList.vue'
import EmptyCorrectionsState from '../components/corrections/EmptyCorrectionsState.vue'
import CorrectionFormModal from '../components/corrections/CorrectionFormModal.vue'
import DeleteCorrectionModal from '../components/corrections/DeleteCorrectionModal.vue'
import { useCorrections } from '../composables/useCorrections'

const {
  sortedCorrections,
  addCorrection,
  addComment,
  updateCorrectionStatus,
  deleteCorrection,
  updateCorrection,
  activeCorrections,
  completedCorrections,
} = useCorrections()

const isCorrectionFormOpen = ref(false)

const correctionToEdit = ref(null)

const openCorrectionForm = () => {
  correctionToEdit.value = null
  isCorrectionFormOpen.value = true
}

const correctionToDelete = ref(null)

const openEditModal = (correctionId) => {
  correctionToEdit.value = sortedCorrections.value.find((correction) => {
    return correction.id === correctionId
  })

  if (!correctionToEdit.value) return

  isCorrectionFormOpen.value = true
}

const closeCorrectionForm = () => {
  isCorrectionFormOpen.value = false
  correctionToEdit.value = null
}

const handleCorrectionSubmit = (correctionData) => {
  if (correctionToEdit.value) {
    updateCorrection(correctionToEdit.value.id, correctionData)
  } else {
    addCorrection(correctionData)
  }

  closeCorrectionForm()
}

const openDeleteModal = (correctionId) => {
  correctionToDelete.value = sortedCorrections.value.find((correction) => {
    return correction.id === correctionId
  })
}

const closeDeleteModal = () => {
  correctionToDelete.value = null
}

const confirmDeleteCorrection = () => {
  if (!correctionToDelete.value) return

  deleteCorrection(correctionToDelete.value.id)
  closeDeleteModal()
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

      <template v-if="sortedCorrections.length">
        <section v-if="activeCorrections.length" class="corrections-section">
          <div class="active-section__header">
            <h2 class="corrections-section__title">
              <span class="active-section__dot" aria-hidden="true"></span>
              Aktywne
            </h2>

            <span class="active-section__count">
              {{ activeCorrections.length }}
            </span>
          </div>

          <CorrectionsList
            :corrections="activeCorrections"
            @add-comment="addComment"
            @update-status="updateCorrectionStatus"
            @edit="openEditModal"
            @delete="openDeleteModal"
          />
        </section>

        <p v-else class="no-active-corrections">Wszystkie poprawki zostały wykonane.</p>

        <section
          v-if="completedCorrections.length"
          class="corrections-section corrections-section--completed"
        >
          <div class="completed-section__header">
            <h2 class="corrections-section__title">
              <span aria-hidden="true">✓</span>
              Gotowe
            </h2>

            <span class="completed-section__count">
              {{ completedCorrections.length }}
            </span>
          </div>

          <CorrectionsList
            :corrections="completedCorrections"
            @add-comment="addComment"
            :show-comments="false"
            @update-status="updateCorrectionStatus"
            @edit="openEditModal"
            @delete="openDeleteModal"
          />
        </section>
      </template>

      <EmptyCorrectionsState v-else @add="openCorrectionForm" />
    </section>

    <CorrectionFormModal
      v-if="isCorrectionFormOpen"
      :correction="correctionToEdit"
      @close="closeCorrectionForm"
      @submit="handleCorrectionSubmit"
    />

    <DeleteCorrectionModal
      v-if="correctionToDelete"
      :correction-title="correctionToDelete.title"
      @close="closeDeleteModal"
      @confirm="confirmDeleteCorrection"
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

.corrections-section {
  margin-top: 32px;
}

.corrections-section__title {
  margin: 0 0 8px;
  color: var(--color-heading);
  font-size: 20px;
}

.corrections-section--completed {
  margin-top: 40px;
  padding: 24px 28px;
  border: 1px solid #b7e4cd;
  border-radius: 10px;
  background-color: #f0faf5;
}

.no-active-corrections {
  margin: 48px 0;
  color: var(--color-muted);
  text-align: center;
}

.completed-section__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 12px;
  padding-bottom: 16px;
  border-bottom: 1px solid #b7e4cd;
}

.completed-section__header .corrections-section__title {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 0;
  color: #067647;
}

.completed-section__count {
  display: flex;
  min-width: 30px;
  height: 30px;
  align-items: center;
  justify-content: center;
  padding-inline: 8px;
  border-radius: 999px;
  background-color: #d1fae5;
  color: #067647;
  font-size: 13px;
  font-weight: 800;
}

.active-section__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 12px;
  padding: 14px 18px;
  border-left: 4px solid var(--color-brand);
  border-radius: 8px;
  background-color: var(--color-brand-light);
}

.active-section__header .corrections-section__title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0;
  color: var(--color-heading);
}

.active-section__dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background-color: var(--color-brand);
}

.active-section__count {
  display: flex;
  min-width: 30px;
  height: 30px;
  align-items: center;
  justify-content: center;
  padding-inline: 8px;
  border-radius: 999px;
  background-color: var(--color-surface);
  color: var(--color-brand);
  font-size: 13px;
  font-weight: 800;
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

  .corrections-section--completed {
    padding: 20px;
  }
}
</style>
