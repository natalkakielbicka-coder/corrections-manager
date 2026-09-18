import { computed, ref } from 'vue'

const storageKey = 'corrections-manager-current-user'

const getWordPressUserName = () => {
  const appElement = document.querySelector('#corrections-manager-app')

  return appElement?.dataset.currentUserName?.trim() ?? ''
}

const getStoredUserName = () => {
  try {
    return localStorage.getItem(storageKey)?.trim() ?? ''
  } catch (error) {
    console.error(error)
    return ''
  }
}

const wordpressUserName = getWordPressUserName()

const isWordPressUser = computed(() => {
  return wordpressUserName.length > 0
})

const currentUserName = ref(wordpressUserName || getStoredUserName())

const hasCurrentUser = computed(() => {
  return currentUserName.value.length > 0
})

const setCurrentUser = (name) => {
  const trimmedName = name.trim()

  currentUserName.value = trimmedName

  if (wordpressUserName || !trimmedName) {
    return
  }

  try {
    localStorage.setItem(storageKey, trimmedName)
  } catch (error) {
    console.error(error)
  }
}

const clearCurrentUser = () => {
  if (wordpressUserName) {
    return
  }

  currentUserName.value = ''

  try {
    localStorage.removeItem(storageKey)
  } catch (error) {
    console.error(error)
  }
}

export function useCurrentUser() {
  return {
    currentUserName,
    hasCurrentUser,
    setCurrentUser,
    clearCurrentUser,
    isWordPressUser,
  }
}
