const handleApiError = async (response, fallbackMessage) => {
  if (response.status === 403) {
    throw new Error('Sesja wygasła. Odśwież stronę i spróbuj ponownie.')
  }

  const errorData = await response.json().catch(() => null)

  throw new Error(errorData?.message ?? fallbackMessage)
}

const getCorrectionsApiConfig = () => {
  const appElement = document.querySelector('#corrections-manager-app')

  return {
    apiUrl: appElement?.dataset.correctionsUrl ?? '',
    nonce: appElement?.dataset.restNonce ?? '',
  }
}

export const fetchCorrections = async () => {
  const { apiUrl } = getCorrectionsApiConfig()

  if (!apiUrl) {
    throw new Error('Nie znaleziono adresu REST API.')
  }

  const response = await fetch(apiUrl, {
    headers: {
      Accept: 'application/json',
    },
  })

  if (!response.ok) {
    throw new Error('Nie udało się pobrać poprawek.')
  }

  return response.json()
}

export const createCorrection = async (correctionData) => {
  const { apiUrl, nonce } = getCorrectionsApiConfig()

  if (!apiUrl || !nonce) {
    throw new Error('Brakuje konfiguracji REST API.')
  }

  const formData = new FormData()

  formData.append('title', correctionData.title)
  formData.append('description', correctionData.description)
  formData.append('pageId', String(correctionData.pageId))
  formData.append('author', correctionData.author)
  formData.append('nonce', nonce)

  if (correctionData.imageFile) {
    formData.append('image', correctionData.imageFile)
  }

  const response = await fetch(apiUrl, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'X-WP-Nonce': nonce,
    },
    body: formData,
  })

  if (!response.ok) {
    await handleApiError(response, 'Nie udało się zapisać poprawki.')
  }

  return response.json()
}

export const updateCorrectionRequest = async (correctionId, correctionData) => {
  const { apiUrl, nonce } = getCorrectionsApiConfig()

  if (!apiUrl || !nonce) {
    throw new Error('Brakuje konfiguracji REST API.')
  }

  const formData = new FormData()

  formData.append('title', correctionData.title)
  formData.append('description', correctionData.description)
  formData.append('pageId', String(correctionData.pageId))
  formData.append('expectedVersion', String(correctionData.expectedVersion))
  formData.append('removeImage', correctionData.removeImage ? '1' : '0')
  formData.append('nonce', nonce)

  if (correctionData.imageFile) {
    formData.append('image', correctionData.imageFile)
  }

  const response = await fetch(`${apiUrl}/${correctionId}`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'X-WP-Nonce': nonce,
      'X-HTTP-Method-Override': 'PATCH',
    },
    body: formData,
  })

  if (!response.ok) {
    await handleApiError(response, 'Nie udało się zapisać zmian.')
  }

  return response.json()
}

export const updateCorrectionStatusRequest = async (correctionId, status, expectedVersion) => {
  const { apiUrl, nonce } = getCorrectionsApiConfig()

  if (!apiUrl || !nonce) {
    throw new Error('Brakuje konfiguracji REST API.')
  }

  const response = await fetch(`${apiUrl}/${correctionId}/status`, {
    method: 'PATCH',

    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
    },

    body: JSON.stringify({
      status,
      expectedVersion,
      nonce,
    }),
  })

  if (!response.ok) {
    await handleApiError(response, 'Nie udało się zmienić statusu.')
  }

  return response.json()
}

export const deleteCorrectionRequest = async (correctionId) => {
  const { apiUrl, nonce } = getCorrectionsApiConfig()

  if (!apiUrl || !nonce) {
    throw new Error('Brakuje konfiguracji REST API.')
  }

  const response = await fetch(`${apiUrl}/${correctionId}`, {
    method: 'DELETE',

    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
    },

    body: JSON.stringify({
      nonce,
    }),
  })

  if (!response.ok) {
    await handleApiError(response, 'Nie udało się usunąć poprawki.')
  }

  return response.json()
}

export const addCommentRequest = async (correctionId, { content, author }) => {
  const { apiUrl, nonce } = getCorrectionsApiConfig()

  if (!apiUrl || !nonce) {
    throw new Error('Brakuje konfiguracji REST API.')
  }

  const response = await fetch(`${apiUrl}/${correctionId}/comments`, {
    method: 'POST',

    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
    },

    body: JSON.stringify({
      content,
      author,
      nonce,
    }),
  })

  if (!response.ok) {
    await handleApiError(response, 'Nie udało się dodać komentarza.')
  }

  return response.json()
}

export const deleteCommentRequest = async (correctionId, commentId) => {
  const { apiUrl, nonce } = getCorrectionsApiConfig()

  if (!apiUrl || !nonce) {
    throw new Error('Brakuje konfiguracji REST API.')
  }

  const response = await fetch(`${apiUrl}/${correctionId}/comments/${commentId}`, {
    method: 'DELETE',

    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
    },

    body: JSON.stringify({
      nonce,
    }),
  })

  if (!response.ok) {
    await handleApiError(response, 'Nie udało się usunąć komentarza.')
  }

  return response.json()
}
