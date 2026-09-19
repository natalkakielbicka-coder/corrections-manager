export const handleApiError = async (response, fallbackMessage) => {
  const errorData = await response.json().catch(() => null)

  if (response.status === 403 && errorData?.code === 'corrections_manager_invalid_nonce') {
    throw new Error('Sesja wygasła. Odśwież stronę i spróbuj ponownie.')
  }

  if (response.status === 401 || response.status === 403) {
    throw new Error(
      'Dostęp do REST API został zablokowany. Sprawdź wtyczkę bezpieczeństwa, firewall lub konfigurację serwera.',
    )
  }

  if (response.status === 404) {
    throw new Error(
      errorData?.message ||
        'Nie znaleziono endpointu REST API. Sprawdź bezpośrednie odnośniki i konfigurację REST API.',
    )
  }

  if (response.status === 409) {
    throw new Error(errorData?.message || 'Dane zostały w międzyczasie zmienione przez inną osobę.')
  }

  if (response.status === 429) {
    throw new Error('Serwer ograniczył liczbę żądań. Spróbuj ponownie za chwilę.')
  }

  if (response.status >= 500) {
    throw new Error(errorData?.message || 'Wystąpił błąd serwera podczas komunikacji z REST API.')
  }

  throw new Error(errorData?.message ?? fallbackMessage)
}

export const apiFetch = async (url, options = {}) => {
  try {
    return await fetch(url, options)
  } catch (error) {
    if (error instanceof TypeError) {
      throw new Error(
        'Nie udało się połączyć z REST API. Sprawdź połączenie, firewall lub konfigurację WordPressa.',
      )
    }

    throw error
  }
}
