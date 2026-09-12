import { ref, computed } from 'vue'
import { initialCorrections } from '../data/corrections'

export const useCorrections = () => {
  const corrections = ref([...initialCorrections])

  const sortedCorrections = computed(() => {
    const correctionsCopy = [...corrections.value]

    return correctionsCopy.sort((firstCorrection, secondCorrection) => {
      const firstDate = new Date(firstCorrection.createdAt)
      const secondDate = new Date(secondCorrection.createdAt)

      return secondDate - firstDate
    })
  })

  const addCorrection = (correctionData) => {
    const newCorrection = {
      ...correctionData,
      id: Date.now(),
      createdAt: new Date().toISOString(),
      comments: [],
    }

    corrections.value.push(newCorrection)
  }

  const addComment = ({ correctionId, content }) => {
    const correction = corrections.value.find((correctionItem) => {
      return correctionItem.id === correctionId
    })

    if (!correction) return

    correction.comments.push({
      id: Date.now(),
      author: 'Klient',
      content,
      createdAt: new Date().toISOString(),
    })
  }

  const updateCorrectionStatus = ({ correctionId, status }) => {
    const correction = corrections.value.find((correctionItem) => {
      return correctionItem.id === correctionId
    })

    if (!correction) return

    correction.status = status
  }

  return {
    corrections,
    sortedCorrections,
    addCorrection,
    addComment,
    updateCorrectionStatus,
  }
}
