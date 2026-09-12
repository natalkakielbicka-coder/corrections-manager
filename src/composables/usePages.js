import { ref } from 'vue'
import { initialPages } from '../data/pages'

const pages = ref([...initialPages])

const getPageById = (pageId) => {
  return pages.value.find((page) => page.id === pageId)
}

export const usePages = () => {
  return {
    pages,
    getPageById,
  }
}
