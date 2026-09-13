import { computed, ref } from 'vue'

const currentUserName = ref('')

const hasCurrentUser = computed(() => {
  return currentUserName.value.length > 0
})

const setCurrentUser = (name) => {
  currentUserName.value = name.trim()
}

const clearCurrentUser = () => {
  currentUserName.value = ''
}

export function useCurrentUser() {
  return {
    currentUserName,
    hasCurrentUser,
    setCurrentUser,
    clearCurrentUser,
  }
}
