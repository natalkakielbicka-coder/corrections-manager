import { ref } from 'vue'
import { initialPages } from '../data/pages'

export const usePages = () => {
  const pages = ref([...initialPages])

  const getPageById = (pageId) => {
    return pages.value.find((page) => {
      return page.id === pageId
    })
  }

  return {
    pages,
    getPageById,
  }
}
