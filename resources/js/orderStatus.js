export const ORDER_STATUSES = [
  { value: 'new', label: 'Jauns' },
  { value: 'confirmed', label: 'Apstiprināts' },
  { value: 'diagnostics', label: 'Diagnostika' },
  { value: 'in_progress', label: 'Remontā' },
  { value: 'waiting_parts', label: 'Gaida detaļas' },
  { value: 'ready', label: 'Gatavs saņemšanai' },
  { value: 'done', label: 'Pabeigts' },
  { value: 'cancelled', label: 'Atcelts' },
]

export function statusLabel(status) {
  return ORDER_STATUSES.find(item => item.value === status)?.label || status || '—'
}

export const PAYMENT_STATUSES = [
  { value: 'pending', label: 'Gaida apmaksu' },
  { value: 'paid', label: 'Apmaksāts' },
  { value: 'cancelled', label: 'Atcelts' },
]

export function paymentStatusLabel(status) {
  return PAYMENT_STATUSES.find(item => item.value === status)?.label || status || '—'
}
