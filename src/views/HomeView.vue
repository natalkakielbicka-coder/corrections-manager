<script setup>
import { ref, computed } from 'vue'
import ProjectHeader from '../components/project/ProjectHeader.vue'
import CorrectionsList from '../components/corrections/CorrectionsList.vue'
import EmptyCorrectionsState from '../components/corrections/EmptyCorrectionsState.vue'
import CorrectionFormModal from '../components/corrections/CorrectionFormModal.vue'
import DeleteCorrectionModal from '../components/corrections/DeleteCorrectionModal.vue'
import DeleteCommentModal from '../components/comments/DeleteCommentModal.vue'
import { useCorrections } from '../composables/useCorrections'
import { usePages } from '../composables/usePages'
import { useCurrentUser } from '../composables/useCurrentUser'
import UserEntryScreen from '../components/UserEntryScreen.vue'

const {
  sortedCorrections,
  addCorrection,
  addComment,
  updateCorrectionStatus,
  deleteCorrection,
  updateCorrection,
  activeCorrections,
  completedCorrections,
  statusCounts,
  deleteComment,
} = useCorrections()

const { pages } = usePages()

const { currentUserName, hasCurrentUser, setCurrentUser } = useCurrentUser()

const selectedPageId = ref('all')

const filterCorrectionsByPage = (corrections) => {
  if (selectedPageId.value === 'all') {
    return corrections
  }

  return corrections.filter((correction) => {
    return correction.pageId === selectedPageId.value
  })
}

const filteredActiveCorrections = computed(() => {
  return filterCorrectionsByPage(activeCorrections.value)
})

const filteredCompletedCorrections = computed(() => {
  return filterCorrectionsByPage(completedCorrections.value)
})

const hasFilteredCorrections = computed(() => {
  return filteredActiveCorrections.value.length > 0 || filteredCompletedCorrections.value.length > 0
})

const isCorrectionFormOpen = ref(false)

const correctionToEdit = ref(null)
const commentToDelete = ref(null)

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
    addCorrection({
      ...correctionData,
      author: currentUserName.value,
    })
  }

  closeCorrectionForm()
}

const handleAddComment = ({ correctionId, content }) => {
  addComment({
    correctionId,
    content,
    author: currentUserName.value,
  })
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

const openDeleteCommentModal = ({ correctionId, commentId }) => {
  const correction = sortedCorrections.value.find((correctionItem) => {
    return correctionItem.id === correctionId
  })

  if (!correction) return

  const comment = correction.comments.find((commentItem) => {
    return commentItem.id === commentId
  })

  if (!comment) return

  commentToDelete.value = {
    correctionId,
    commentId,
    content: comment.content,
  }
}

const closeDeleteCommentModal = () => {
  commentToDelete.value = null
}

const confirmDeleteComment = () => {
  if (!commentToDelete.value) return

  deleteComment({
    correctionId: commentToDelete.value.correctionId,
    commentId: commentToDelete.value.commentId,
  })

  closeDeleteCommentModal()
}
</script>

<template>
  <UserEntryScreen v-if="!hasCurrentUser" @submit="setCurrentUser" />
  <main v-else class="page">
    <section class="project-board">
      <ProjectHeader :status-counts="statusCounts" @add-correction="openCorrectionForm" />

      <div v-if="sortedCorrections.length" class="corrections-filters">
        <label for="page-filter"> Pokaż poprawki dla strony </label>

        <select id="page-filter" v-model="selectedPageId">
          <option value="all">Wszystkie strony</option>

          <option v-for="page in pages" :key="page.id" :value="page.id">
            {{ page.title }}
          </option>
        </select>
      </div>

      <div v-if="sortedCorrections.length && !hasFilteredCorrections" class="filter-empty-state">
        <h2>Brak poprawek</h2>

        <p>Dla wybranej strony nie dodano jeszcze żadnych poprawek.</p>

        <button type="button" @click="selectedPageId = 'all'">Pokaż wszystkie strony</button>
      </div>

      <template v-if="sortedCorrections.length">
        <section v-if="filteredActiveCorrections.length" class="corrections-section">
          <div class="active-section__header">
            <h2 class="corrections-section__title">
              <span class="active-section__dot" aria-hidden="true"></span>
              Aktywne
            </h2>

            <span class="active-section__count">
              {{ filteredActiveCorrections.length }}
            </span>
          </div>

          <CorrectionsList
            :corrections="filteredActiveCorrections"
            @add-comment="handleAddComment"
            @update-status="updateCorrectionStatus"
            @edit="openEditModal"
            @delete="openDeleteModal"
            @delete-comment="openDeleteCommentModal"
          />
        </section>

        <p v-else-if="hasFilteredCorrections" class="no-active-corrections">
          Brak aktywnych poprawek dla wybranej strony.
        </p>

        <section
          v-if="filteredCompletedCorrections.length"
          class="corrections-section corrections-section--completed"
        >
          <div class="completed-section__header">
            <h2 class="corrections-section__title">
              <span aria-hidden="true">✓</span>
              Gotowe
            </h2>

            <span class="completed-section__count">
              {{ filteredCompletedCorrections.length }}
            </span>
          </div>

          <CorrectionsList
            :corrections="filteredCompletedCorrections"
            @add-comment="handleAddComment"
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

    <DeleteCommentModal
      v-if="commentToDelete"
      :comment-content="commentToDelete.content"
      @close="closeDeleteCommentModal"
      @confirm="confirmDeleteComment"
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

.corrections-filters {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.corrections-filters label {
  color: var(--color-text);
  font-size: 14px;
  font-weight: 600;
}

.corrections-filters select {
  min-width: 220px;
  min-height: 42px;
  padding: 8px 36px 8px 12px;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  background-color: var(--color-surface);
  color: var(--color-heading);
  font-size: 14px;
  cursor: pointer;
  outline: none;
}

.corrections-filters select:focus {
  border-color: var(--color-brand);
  box-shadow: 0 0 0 3px var(--color-brand-light);
}

.filter-empty-state {
  padding: 64px 24px;
  text-align: center;
}

.filter-empty-state h2 {
  margin: 0;
  color: var(--color-heading);
  font-size: 26px;
}

.filter-empty-state p {
  margin: 12px 0 0;
  color: var(--color-muted);
}

.filter-empty-state button {
  min-height: 44px;
  margin-top: 24px;
  padding: 10px 18px;
  border: 1px solid var(--color-brand);
  border-radius: 6px;
  background-color: var(--color-surface);
  color: var(--color-brand);
  font-weight: 700;
}

.filter-empty-state button:hover {
  background-color: var(--color-brand-light);
}

@media (max-width: 991px) {
  .page {
    padding: 24px;
  }

  .project-board {
    min-height: calc(100vh - 48px);
    padding: 32px;
  }
}

@media (max-width: 767px) {
  .page {
    padding: 16px;
  }

  .project-board {
    min-height: calc(100vh - 32px);
    padding: 24px 20px;
  }

  .corrections-section--completed {
    margin-top: 28px;
    padding: 18px;
  }

  h1 {
    font-size: clamp(24px, 8vw, 32px);
  }

  .corrections-section__title {
    font-size: 18px;
  }

  .active-section__header {
    padding: 12px 14px;
  }

  .corrections-filters {
    align-items: stretch;
    flex-direction: column;
  }

  .corrections-filters select {
    width: 100%;
  }
}
</style>
