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
    const errorData = await response.json().catch(() => null)

    throw new Error(errorData?.message ?? 'Nie udało się zapisać poprawki.')
  }

  return response.json()
}

export const updateCorrectionRequest = async (correctionId, correctionData) => {
  const { apiUrl, nonce } = getCorrectionsApiConfig()

  if (!apiUrl || !nonce) {
    throw new Error('Brakuje konfiguracji REST API.')
  }

  const response = await fetch(`${apiUrl}/${correctionId}`, {
    method: 'PATCH',

    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
    },

    body: JSON.stringify({
      title: correctionData.title,
      description: correctionData.description,
      pageId: correctionData.pageId,
      expectedUpdatedAt: correctionData.expectedUpdatedAt,
      nonce,
    }),
  })

  if (!response.ok) {
    const errorData = await response.json().catch(() => null)

    throw new Error(errorData?.message ?? 'Nie udało się zapisać zmian.')
  }

  return response.json()
}

export const updateCorrectionStatusRequest = async (correctionId, status) => {
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
      nonce,
    }),
  })

  if (!response.ok) {
    const errorData = await response.json().catch(() => null)

    throw new Error(errorData?.message ?? 'Nie udało się zmienić statusu.')
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
    const errorData = await response.json().catch(() => null)

    throw new Error(errorData?.message ?? 'Nie udało się usunąć poprawki.')
  }

  return response.json()
}
