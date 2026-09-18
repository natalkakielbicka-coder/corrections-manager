<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
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
import { useToast } from '../composables/useToast'
import { correctionStatuses } from '../constants/correctionStatuses'
import UserEntryScreen from '../components/UserEntryScreen.vue'

const appElement = document.querySelector('#corrections-manager-app')

const siteName = appElement?.dataset.siteName || 'Strona internetowa'

const REFRESH_INTERVAL = 15_000

let correctionsRefreshIntervalId = null

const isSubmittingCorrectionForm = ref(false)
const isDeletingCorrection = ref(false)
const isDeletingComment = ref(false)
const updatingStatusCorrectionIds = ref([])
const addingCommentCorrectionIds = ref([])

const refreshCorrectionsIfPossible = () => {
  const hasOpenModal =
    isCorrectionFormOpen.value ||
    Boolean(correctionToDelete.value) ||
    Boolean(commentToDelete.value)

  if (document.visibilityState !== 'visible' || hasOpenModal) {
    return
  }

  refreshCorrections()
}

const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    refreshCorrectionsIfPossible()
  }
}

const {
  sortedCorrections,
  addCorrection,
  addComment,
  updateCorrectionStatus,
  deleteCorrection,
  updateCorrection,
  deleteComment,
  isLoading,
  loadCorrections,
  refreshCorrections,
} = useCorrections()

onMounted(() => {
  loadCorrections()
  loadPages()

  correctionsRefreshIntervalId = window.setInterval(refreshCorrectionsIfPossible, REFRESH_INTERVAL)

  document.addEventListener('visibilitychange', handleVisibilityChange)
})

onUnmounted(() => {
  if (correctionsRefreshIntervalId) {
    window.clearInterval(correctionsRefreshIntervalId)
  }

  document.removeEventListener('visibilitychange', handleVisibilityChange)
})

const { pages, loadPages } = usePages()

const { currentUserName, hasCurrentUser, setCurrentUser, clearCurrentUser, isWordPressUser } =
  useCurrentUser()

const { showToast } = useToast()

const selectedPageId = ref('all')

const showOnlyNew = ref(false)

const selectedStatusTab = ref('active')

const tabCounts = computed(() => {
  const correctionsForSelectedPage =
    selectedPageId.value === 'all'
      ? sortedCorrections.value
      : sortedCorrections.value.filter((correction) => {
          return correction.pageId === selectedPageId.value
        })

  const counts = {
    active: 0,
    review: 0,
    ready: 0,
  }

  correctionsForSelectedPage.forEach((correction) => {
    if (correction.status === 'inProgress') {
      counts.active += 1
    }

    if (correction.status === 'review') {
      counts.review += 1
    }

    if (correction.status === 'ready') {
      counts.ready += 1
    }
  })

  return counts
})

const statusFilteredCorrections = computed(() => {
  if (selectedStatusTab.value === 'active') {
    return sortedCorrections.value.filter((correction) => {
      return correction.status === 'inProgress'
    })
  }

  return sortedCorrections.value.filter((correction) => {
    return correction.status === selectedStatusTab.value
  })
})

const filterCorrectionsByPage = (corrections) => {
  if (selectedPageId.value === 'all') {
    return corrections
  }

  return corrections.filter((correction) => {
    return correction.pageId === selectedPageId.value
  })
}

const visibleCorrections = computed(() => {
  const pageFilteredCorrections = filterCorrectionsByPage(statusFilteredCorrections.value)

  if (!showOnlyNew.value || selectedStatusTab.value !== 'active') {
    return pageFilteredCorrections
  }

  return pageFilteredCorrections.filter((correction) => {
    return correction.isNew
  })
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

const handleCorrectionSubmit = async (correctionData) => {
  if (isSubmittingCorrectionForm.value) return

  isSubmittingCorrectionForm.value = true

  try {
    if (correctionToEdit.value) {
      await updateCorrection(correctionToEdit.value.id, correctionData)

      showToast('Zapisano zmiany w poprawce')
    } else {
      await addCorrection({
        ...correctionData,
        author: currentUserName.value,
      })

      showToast('Dodano nową poprawkę')
    }

    closeCorrectionForm()
  } catch (error) {
    console.error(error)

    showToast(error.message || 'Nie udało się zapisać poprawki', 'delete')
  } finally {
    isSubmittingCorrectionForm.value = false
  }
}

const handleAddComment = async ({ correctionId, content }) => {
  if (addingCommentCorrectionIds.value.includes(correctionId)) {
    return
  }

  addingCommentCorrectionIds.value.push(correctionId)

  try {
    await addComment({
      correctionId,
      content,
      author: currentUserName.value,
    })

    showToast('Dodano komentarz')
  } catch (error) {
    console.error(error)

    showToast(error.message || 'Nie udało się dodać komentarza', 'delete')
  } finally {
    addingCommentCorrectionIds.value = addingCommentCorrectionIds.value.filter((id) => {
      return id !== correctionId
    })
  }
}

const handleStatusUpdate = async ({ correctionId, status }) => {
  const statusData = correctionStatuses[status]

  if (!statusData || updatingStatusCorrectionIds.value.includes(correctionId)) {
    return
  }

  updatingStatusCorrectionIds.value.push(correctionId)

  try {
    await updateCorrectionStatus({
      correctionId,
      status,
    })

    selectedStatusTab.value = status === 'inProgress' ? 'active' : status

    showToast(`Przeniesiono poprawkę do „${statusData.label}”`)
  } catch (error) {
    console.error(error)

    showToast(error.message || 'Nie udało się zmienić statusu', 'delete')
  } finally {
    updatingStatusCorrectionIds.value = updatingStatusCorrectionIds.value.filter((id) => {
      return id !== correctionId
    })
  }
}

const openDeleteModal = (correctionId) => {
  correctionToDelete.value = sortedCorrections.value.find((correction) => {
    return correction.id === correctionId
  })
}

const closeDeleteModal = () => {
  correctionToDelete.value = null
}

const confirmDeleteCorrection = async () => {
  if (!correctionToDelete.value || isDeletingCorrection.value) {
    return
  }

  isDeletingCorrection.value = true

  try {
    await deleteCorrection(correctionToDelete.value.id)

    closeDeleteModal()
    showToast('Usunięto poprawkę', 'delete')
  } catch (error) {
    console.error(error)

    showToast(error.message || 'Nie udało się usunąć poprawki', 'delete')
  } finally {
    isDeletingCorrection.value = false
  }
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

const confirmDeleteComment = async () => {
  if (!commentToDelete.value || isDeletingComment.value) return

  isDeletingComment.value = true

  try {
    await deleteComment({
      correctionId: commentToDelete.value.correctionId,
      commentId: commentToDelete.value.commentId,
    })

    closeDeleteCommentModal()
    showToast('Usunięto komentarz', 'delete')
  } catch (error) {
    console.error(error)

    showToast(error.message || 'Nie udało się usunąć komentarza', 'delete')
  } finally {
    isDeletingComment.value = false
  }
}
</script>

<template>
  <UserEntryScreen v-if="!hasCurrentUser" @submit="setCurrentUser" />

  <main v-else class="page">
    <section class="project-board">
      <ProjectHeader
        :site-name="siteName"
        :current-user-name="currentUserName"
        :can-change-user="!isWordPressUser"
        @add-correction="openCorrectionForm"
        @change-user="clearCurrentUser"
      />

      <div v-if="isLoading" class="filter-empty-state">
        <p>Ładowanie poprawek...</p>
      </div>

      <div class="corrections-toolbar">
        <CorrectionsTabs
          :active-tab="selectedStatusTab"
          :counts="tabCounts"
          @change="selectedStatusTab = $event"
        />

        <div v-if="sortedCorrections.length" class="corrections-filters">
          <label v-if="selectedStatusTab === 'active'" class="corrections-filters__new">
            <input v-model="showOnlyNew" type="checkbox" />

            <span>Tylko nowe</span>
          </label>

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

        <p v-if="showOnlyNew && selectedStatusTab === 'active'">
          Nie ma nowych poprawek spełniających wybrane kryteria.
        </p>

        <p v-else>Brak poprawek dla wybranego statusu i strony.</p>

        <div v-if="showOnlyNew || selectedPageId !== 'all'" class="filter-empty-state__actions">
          <button
            v-if="showOnlyNew && selectedStatusTab === 'active'"
            type="button"
            @click="showOnlyNew = false"
          >
            Pokaż wszystkie aktywne
          </button>

          <button v-if="selectedPageId !== 'all'" type="button" @click="selectedPageId = 'all'">
            Pokaż wszystkie strony
          </button>
        </div>
      </div>

      <template v-if="sortedCorrections.length">
        <CorrectionsList
          v-if="visibleCorrections.length"
          :corrections="visibleCorrections"
          :show-comments="selectedStatusTab !== 'ready'"
          :updating-status-correction-ids="updatingStatusCorrectionIds"
          :adding-comment-correction-ids="addingCommentCorrectionIds"
          @add-comment="handleAddComment"
          @update-status="handleStatusUpdate"
          @edit="openEditModal"
          @delete="openDeleteModal"
          @delete-comment="openDeleteCommentModal"
        />
      </template>

      <EmptyCorrectionsState v-else-if="!isLoading" @add="openCorrectionForm" />
    </section>

    <CorrectionFormModal
      v-if="isCorrectionFormOpen"
      :correction="correctionToEdit"
      :is-submitting="isSubmittingCorrectionForm"
      @close="closeCorrectionForm"
      @submit="handleCorrectionSubmit"
    />

    <DeleteCorrectionModal
      v-if="correctionToDelete"
      :correction-title="correctionToDelete.title"
      :is-deleting="isDeletingCorrection"
      @close="closeDeleteModal"
      @confirm="confirmDeleteCorrection"
    />

    <DeleteCommentModal
      v-if="commentToDelete"
      :comment-content="commentToDelete.content"
      :is-deleting="isDeletingComment"
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

.corrections-filters .corrections-filters__new {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  gap: 7px;
  cursor: pointer;
}

.corrections-filters__new input {
  width: 16px;
  height: 16px;
  margin: 0;
  accent-color: var(--color-brand);
  cursor: pointer;
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

.filter-empty-state__actions {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 12px;
  margin-top: 24px;
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
