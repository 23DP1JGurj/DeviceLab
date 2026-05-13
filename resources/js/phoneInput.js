export function sanitizePhoneInput(value) {
  const raw = String(value ?? '')
  const hasLeadingPlus = raw.startsWith('+')
  const digits = raw.replace(/\D/g, '')

  return hasLeadingPlus ? `+${digits}` : digits
}

export function isValidPhoneInput(value) {
  const phone = String(value ?? '').trim()

  return phone === '' || /^\+?\d+$/.test(phone)
}
