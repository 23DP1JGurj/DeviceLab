const SUGGESTION_TYPES = ['phone', 'tablet', 'laptop']

function normalizeType(type) {
  return SUGGESTION_TYPES.includes(type) ? type : ''
}

export async function fetchDeviceBrands(type = 'phone', search = '') {
  const normalizedType = normalizeType(type)

  if (!normalizedType) return []

  const params = new URLSearchParams()
  const normalizedSearch = String(search || '').trim()

  params.set('type', normalizedType)

  if (normalizedSearch) {
    params.set('search', normalizedSearch)
  }

  const response = await fetch(`/api/device-brands?${params.toString()}`, {
    headers: { Accept: 'application/json' },
    credentials: 'same-origin',
  })

  if (!response.ok) return []

  return await response.json()
}

export async function fetchDeviceModels(brand, search = '') {
  return fetchDeviceModelsByType('phone', brand, search)
}

export async function fetchDeviceModelsByType(type = 'phone', brand, search = '') {
  const normalizedType = normalizeType(type)
  const normalizedBrand = String(brand || '').trim()

  if (!normalizedType || !normalizedBrand) return []

  const params = new URLSearchParams({ type: normalizedType, brand: normalizedBrand })
  const normalizedSearch = String(search || '').trim()

  if (normalizedSearch) {
    params.set('search', normalizedSearch)
  }

  const response = await fetch(`/api/device-models?${params.toString()}`, {
    headers: { Accept: 'application/json' },
    credentials: 'same-origin',
  })

  if (!response.ok) return []

  return await response.json()
}
