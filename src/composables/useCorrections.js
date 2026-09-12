import { ref, computed } from 'vue'
import { initialCorrections } from '../data/corrections'

export const useCorrections = () => {
  const corrections = ref(structuredClone(initialCorrections))

  const sortedCorrections = computed(() => {
    const correctionsCopy = [...corrections.value]

    return correctionsCopy.sort((firstCorrection, secondCorrection) => {
      const firstDate = new Date(firstCorrection.createdAt)
      const secondDate = new Date(secondCorrection.createdAt)

      return secondDate - firstDate
    })
  })

  const getNextCorrectionNumber = () => {
    const correctionNumbers = corrections.value.map((correction) => {
      return correction.number
    })

    return Math.max(0, ...correctionNumbers) + 1
  }

  const addCorrection = (correctionData) => {
    const newCorrection = {
      ...correctionData,
      id: Date.now(),
      number: getNextCorrectionNumber(),
      createdAt: new Date().toISOString(),
      comments: [],
    }

    corrections.value.push(newCorrection)
  }

  const updateCorrection = (correctionId, correctionData) => {
    const correction = corrections.value.find((correctionItem) => {
      return correctionItem.id === correctionId
    })

    if (!correction) return

    Object.assign(correction, correctionData)
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

  const updateCorrectionStatus = ({ correctionId, status }) => {
    const correction = corrections.value.find((correctionItem) => {
      return correctionItem.id === correctionId
    })

    if (!correction) return

    correction.status = status
  }

  const deleteCorrection = (correctionId) => {
    corrections.value = corrections.value.filter((correction) => {
      return correction.id !== correctionId
    })
  }

  const activeCorrections = computed(() => {
    return sortedCorrections.value.filter((correction) => {
      return correction.status !== 'ready'
    })
  })

  const completedCorrections = computed(() => {
    return sortedCorrections.value.filter((correction) => {
      return correction.status === 'ready'
    })
  })

  const statusCounts = computed(() => {
    return corrections.value.reduce(
      (counts, correction) => {
        counts[correction.status] += 1

        return counts
      },
      {
        new: 0,
        inProgress: 0,
        ready: 0,
      },
    )
  })

  return {
    corrections,
    sortedCorrections,
    addCorrection,
    addComment,
    deleteComment,
    updateCorrectionStatus,
    deleteCorrection,
    updateCorrection,
    activeCorrections,
    completedCorrections,
    statusCounts,
  }
}
