import { ref } from 'vue'

const toast = ref(null)

let timeoutId = null

const showToast = (message, type = 'success') => {
  toast.value = {
    message,
    type,
  }

  clearTimeout(timeoutId)

  timeoutId = setTimeout(() => {
    toast.value = null
  }, 3000)
}

const hideToast = () => {
  toast.value = null
  clearTimeout(timeoutId)
}

export const useToast = () => {
  return {
    toast,
    showToast,
    hideToast,
  }
}
