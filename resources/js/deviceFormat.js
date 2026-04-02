const DEVICE_TYPE_LABELS = {
  phone: 'Telefons',
  laptop: 'Portatīvais dators',
  tablet: 'Planšete',
  desktop_pc: 'Stacionārais dators',
  pc_component: 'Datora komponente',
  other: 'Cits',
}

export function deviceTypeLabel(type) {
  return DEVICE_TYPE_LABELS[type] || type || 'Ierīce'
}

export function formatDevice(device) {
  if (!device) return '—'

  const title = [device.brand, device.model].filter(Boolean).join(' ').trim()

  if (device.type === 'desktop_pc') {
    return title ? `Stacionārais dators: ${title}` : 'Stacionārais dators'
  }

  if (device.type === 'pc_component') {
    const component = device.component_type || 'Komponente'
    return title ? `${component}: ${title}` : component
  }

  if (device.type === 'other') {
    return title ? `${title} (Cits)` : 'Cits'
  }

  return title ? `${title} (${deviceTypeLabel(device.type)})` : `Ierīce #${device.id}`
}
