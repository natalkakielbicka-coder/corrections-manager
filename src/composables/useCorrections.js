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

  return {
    corrections,
    sortedCorrections,
  }
}
