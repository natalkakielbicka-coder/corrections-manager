import { ref, computed } from 'vue'
import {
  fetchCorrections,
  createCorrection,
  updateCorrectionRequest,
  updateCorrectionStatusRequest,
  deleteCorrectionRequest,
} from '../api/correctionsApi'
import { correctionStatuses } from '../constants/correctionStatuses'

export const useCorrections = () => {
  const corrections = ref([])
  const isLoading = ref(false)

  const loadCorrections = async () => {
    isLoading.value = true

    try {
      corrections.value = await fetchCorrections()
    } catch (error) {
      console.error(error)
    } finally {
      isLoading.value = false
    }
  }

  const refreshCorrections = async () => {
    try {
      corrections.value = await fetchCorrections()
    } catch (error) {
      console.error(error)
    }
  }

  const sortedCorrections = computed(() => {
    const correctionsCopy = [...corrections.value]

    return correctionsCopy.sort((firstCorrection, secondCorrection) => {
      const firstDate = new Date(firstCorrection.createdAt)
      const secondDate = new Date(secondCorrection.createdAt)

      return secondDate - firstDate
    })
  })

  const addCorrection = async (correctionData) => {
    const newCorrection = await createCorrection(correctionData)

    corrections.value.push(newCorrection)

    return newCorrection
  }

  const updateCorrection = async (correctionId, correctionData) => {
    const correction = corrections.value.find((correctionItem) => {
      return correctionItem.id === correctionId
    })

    if (!correction) {
      return
    }

    const updatedCorrection = await updateCorrectionRequest(correctionId, {
      ...correctionData,
      expectedUpdatedAt: correction.updatedAt,
    })

    Object.assign(correction, updatedCorrection)

    return updatedCorrection
  }

  const addComment = ({ correctionId, content, author }) => {
    const correction = corrections.value.find((correctionItem) => {
      return correctionItem.id === correctionId
    })

    if (!correction) return

    correction.comments.push({
      id: Date.now(),
      author,
      content,
      createdAt: new Date().toISOString(),
    })
  }

  const deleteComment = ({ correctionId, commentId }) => {
    const correction = corrections.value.find((correctionItem) => {
      return correctionItem.id === correctionId
    })

    if (!correction) return

    correction.comments = correction.comments.filter((comment) => {
      return comment.id !== commentId
    })
  }

  const allowedStatuses = Object.keys(correctionStatuses)

  const updateCorrectionStatus = async ({ correctionId, status }) => {
    if (!allowedStatuses.includes(status)) {
      return
    }

    const updatedCorrection = await updateCorrectionStatusRequest(correctionId, status)

    const correction = corrections.value.find((correctionItem) => {
      return correctionItem.id === correctionId
    })

    if (!correction) {
      return
    }

    Object.assign(correction, updatedCorrection)

    return updatedCorrection
  }

  const deleteCorrection = async (correctionId) => {
    await deleteCorrectionRequest(correctionId)

    corrections.value = corrections.value.filter((correction) => {
      return correction.id !== correctionId
    })
  }

  return {
    corrections,
    sortedCorrections,
    addCorrection,
    addComment,
    deleteComment,
    updateCorrectionStatus,
    deleteCorrection,
    updateCorrection,
    isLoading,
    loadCorrections,
    refreshCorrections,
  }
}
