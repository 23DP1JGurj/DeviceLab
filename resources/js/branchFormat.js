export function formatBranchShort(branch) {
  if (!branch) return 'Filiāle'

  const rawName = String(branch.name || '').trim()
  const rawAddress = String(branch.address || '').trim()
  const name = rawName
    .replace(/^DeviceLab\s*[—-]\s*/i, '')
    .replace(/^DeviceLab\s+/i, '')
    .trim()

  if (name) return name

  if (rawAddress) {
    return rawAddress.split(',')[0]?.trim() || rawAddress
  }

  return 'Filiāle'
}

export function uniqueBranches(branches = []) {
  const seenIds = new Set()
  const seenLabels = new Set()
  const seenAddresses = new Set()

  return branches.filter((branch) => {
    const id = Number(branch?.id)
    const address = String(branch?.address || '').trim().toLowerCase()
    const labelKey = `${String(branch?.name || '').trim().toLowerCase()}|${address}`

    if (id && seenIds.has(id)) return false
    if (address && seenAddresses.has(address)) return false
    if (labelKey !== '|' && seenLabels.has(labelKey)) return false

    if (id) seenIds.add(id)
    if (address) seenAddresses.add(address)
    if (labelKey !== '|') seenLabels.add(labelKey)

    return true
  })
}
