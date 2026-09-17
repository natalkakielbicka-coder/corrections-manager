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

  const response = await fetch(apiUrl, {
    method: 'POST',

    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      'X-WP-Nonce': nonce,
    },

    body: JSON.stringify({
      title: correctionData.title,
      description: correctionData.description,
      pageId: correctionData.pageId,
      author: correctionData.author,
      nonce,
    }),
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
      nonce,
    }),
  })

  if (!response.ok) {
    const errorData = await response.json().catch(() => null)

    throw new Error(errorData?.message ?? 'Nie udało się zapisać zmian.')
  }

  return response.json()
}
