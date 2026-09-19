const getPagesApiUrl = () => {
  const appElement = document.querySelector('#corrections-manager-app')

  const pagesUrl = appElement?.dataset.pagesUrl ?? ''
  const currentPageId = appElement?.dataset.currentPageId ?? ''

  if (!pagesUrl) {
    return ''
  }

  const apiUrl = new URL(pagesUrl)

  if (currentPageId) {
    apiUrl.searchParams.set('exclude', currentPageId)
  }

  return apiUrl.toString()
}

export const fetchPages = async () => {
  const apiUrl = getPagesApiUrl()

  if (!apiUrl) {
    throw new Error('Nie znaleziono adresu API stron.')
  }

  const response = await fetch(apiUrl, {
    cache: 'no-store',
    headers: {
      Accept: 'application/json',
    },
  })

  if (!response.ok) {
    throw new Error('Nie udało się pobrać stron.')
  }

  return response.json()
}
