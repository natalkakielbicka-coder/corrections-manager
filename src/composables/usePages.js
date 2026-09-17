import { ref } from 'vue'
import { fetchPages } from '../api/pagesApi'

const pages = ref([])
const isLoadingPages = ref(false)

const loadPages = async () => {
  isLoadingPages.value = true

  try {
    pages.value = await fetchPages()
  } catch (error) {
    console.error(error)
  } finally {
    isLoadingPages.value = false
  }
}

const getPageById = (pageId) => {
  return pages.value.find((page) => {
    return page.id === pageId
  })
}

export const usePages = () => {
  return {
    pages,
    isLoadingPages,
    loadPages,
    getPageById,
  }
}
