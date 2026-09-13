<script setup>
import { ref, computed } from 'vue'
import ProjectHeader from '../components/project/ProjectHeader.vue'
import CorrectionsTabs from '../components/corrections/CorrectionsTabs.vue'
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
  statusCounts,
  deleteComment,
} = useCorrections()

const { pages } = usePages()

const { currentUserName, hasCurrentUser, setCurrentUser, clearCurrentUser } = useCurrentUser()

const selectedPageId = ref('all')

const selectedStatusTab = ref('active')

const tabCounts = computed(() => {
  return {
    active: statusCounts.value.new + statusCounts.value.inProgress,
    review: statusCounts.value.review,
    ready: statusCounts.value.ready,
  }
})

const statusFilteredCorrections = computed(() => {
  if (selectedStatusTab.value === 'active') {
    return sortedCorrections.value.filter((correction) => {
      return correction.status === 'new' || correction.status === 'inProgress'
    })
  }

  return sortedCorrections.value.filter((correction) => {
    return correction.status === selectedStatusTab.value
  })
})

const visibleCorrections = computed(() => {
  return filterCorrectionsByPage(statusFilteredCorrections.value)
})

const filterCorrectionsByPage = (corrections) => {
  if (selectedPageId.value === 'all') {
    return corrections
  }

  return corrections.filter((correction) => {
    return correction.pageId === selectedPageId.value
  })
}

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
      <ProjectHeader
        :current-user-name="currentUserName"
        @add-correction="openCorrectionForm"
        @change-user="clearCurrentUser"
      />

      <div class="corrections-toolbar">
        <CorrectionsTabs
          :active-tab="selectedStatusTab"
          :counts="tabCounts"
          @change="selectedStatusTab = $event"
        />

        <div v-if="sortedCorrections.length" class="corrections-filters">
          <label for="page-filter"> Pokaż poprawki dla strony </label>

          <select id="page-filter" v-model="selectedPageId">
            <option value="all">Wszystkie strony</option>

            <option v-for="page in pages" :key="page.id" :value="page.id">
              {{ page.title }}
            </option>
          </select>
        </div>
      </div>

      <div v-if="sortedCorrections.length && !visibleCorrections.length" class="filter-empty-state">
        <h2>Brak poprawek</h2>

        <p>Brak poprawek dla wybranego statusu i strony.</p>

        <button v-if="selectedPageId !== 'all'" type="button" @click="selectedPageId = 'all'">
          Pokaż wszystkie strony
        </button>
      </div>

      <template v-if="sortedCorrections.length">
        <CorrectionsList
          v-if="visibleCorrections.length"
          :corrections="visibleCorrections"
          :show-comments="selectedStatusTab !== 'ready'"
          @add-comment="handleAddComment"
          @update-status="updateCorrectionStatus"
          @edit="openEditModal"
          @delete="openDeleteModal"
          @delete-comment="openDeleteCommentModal"
        />
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

.corrections-filters {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  gap: 12px;
  margin-bottom: 0;
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

.corrections-toolbar {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-bottom: 24px;
  border-bottom: 1px solid var(--color-border);
}

.corrections-toolbar > :first-child {
  min-width: 0;
  flex: 1;
}

@media (max-width: 991px) {
  .page {
    padding: 24px;
  }

  .project-board {
    min-height: calc(100vh - 48px);
    padding: 32px;
  }

  .corrections-toolbar {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .corrections-filters {
    justify-content: space-between;
    padding-bottom: 8px;
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

  h1 {
    font-size: clamp(24px, 8vw, 32px);
  }

  .corrections-filters {
    align-items: stretch;
    flex-direction: column;
    gap: 8px;
    width: 100%;
    margin-bottom: 0;
  }

  .corrections-filters select {
    width: 100%;
  }

  .corrections-filters label {
    font-size: 13px;
  }
}
</style>
