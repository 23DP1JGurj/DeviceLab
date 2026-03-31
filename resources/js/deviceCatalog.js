export async function fetchDeviceBrands(search = '') {
  const params = new URLSearchParams()
  const normalizedSearch = String(search || '').trim()

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
  const normalizedBrand = String(brand || '').trim()

  if (!normalizedBrand) return []

  const params = new URLSearchParams({ brand: normalizedBrand })
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
