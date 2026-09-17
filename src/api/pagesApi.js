const getPagesApiUrl = () => {
  const appElement = document.querySelector('#corrections-manager-app')

  return appElement?.dataset.pagesUrl ?? ''
}

export const fetchPages = async () => {
  const apiUrl = getPagesApiUrl()

  if (!apiUrl) {
    throw new Error('Nie znaleziono adresu API stron.')
  }

  const response = await fetch(apiUrl, {
    headers: {
      Accept: 'application/json',
    },
  })

  if (!response.ok) {
    throw new Error('Nie udało się pobrać stron.')
  }

  return response.json()
}
