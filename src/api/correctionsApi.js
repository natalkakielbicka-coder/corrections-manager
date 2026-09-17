const getCorrectionsApiUrl = () => {
  const appElement = document.querySelector('#corrections-manager-app')

  return appElement?.dataset.restUrl ?? ''
}

export const fetchCorrections = async () => {
  const apiUrl = getCorrectionsApiUrl()

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
