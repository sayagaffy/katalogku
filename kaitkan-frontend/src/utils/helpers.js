/**
 * Format WhatsApp number to 62xxx format
 * @param {string} whatsapp - WhatsApp number in any format
 * @returns {string} - Formatted WhatsApp number (62xxx)
 */
export function formatWhatsappNumber(whatsapp) {
  // Remove all non-digit characters
  let cleaned = whatsapp.replace(/\D/g, '')

  // Convert 08xxx to 628xxx
  if (cleaned.startsWith('08')) {
    cleaned = '62' + cleaned.substring(1)
  }

  // Ensure it starts with 62
  if (!cleaned.startsWith('62')) {
    cleaned = '62' + cleaned
  }

  return cleaned
}

/**
 * Format price to Indonesian Rupiah
 * @param {number} price - Price value
 * @returns {string} - Formatted price (Rp 100.000)
 */
export function formatPrice(price) {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(price)
}

/**
 * Format date to Indonesian locale
 * @param {string|Date} date - Date to format
 * @param {Object} options - Intl.DateTimeFormat options
 * @returns {string} - Formatted date
 */
export function formatDate(date, options = {}) {
  const defaultOptions = {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    ...options,
  }
  return new Intl.DateTimeFormat('id-ID', defaultOptions).format(new Date(date))
}

/**
 * Format datetime to relative time (e.g., "2 jam yang lalu")
 * @param {string|Date} date - Date to format
 * @returns {string} - Relative time string
 */
export function formatRelativeTime(date) {
  const now = new Date()
  const past = new Date(date)
  const diffInSeconds = Math.floor((now - past) / 1000)

  if (diffInSeconds < 60) {
    return 'Baru saja'
  }

  const diffInMinutes = Math.floor(diffInSeconds / 60)
  if (diffInMinutes < 60) {
    return `${diffInMinutes} menit yang lalu`
  }

  const diffInHours = Math.floor(diffInMinutes / 60)
  if (diffInHours < 24) {
    return `${diffInHours} jam yang lalu`
  }

  const diffInDays = Math.floor(diffInHours / 24)
  if (diffInDays < 7) {
    return `${diffInDays} hari yang lalu`
  }

  const diffInWeeks = Math.floor(diffInDays / 7)
  if (diffInWeeks < 4) {
    return `${diffInWeeks} minggu yang lalu`
  }

  return formatDate(date)
}

/**
 * Truncate text to specified length
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Maximum length
 * @returns {string} - Truncated text
 */
export function truncate(text, maxLength = 100) {
  if (!text) return ''
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

/**
 * Generate slug from text
 * @param {string} text - Text to convert to slug
 * @returns {string} - URL-friendly slug
 */
export function slugify(text) {
  return text
    .toString()
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-') // Replace spaces with -
    .replace(/[^\w\-]+/g, '') // Remove non-word chars
    .replace(/\-\-+/g, '-') // Replace multiple - with single -
}

/**
 * Debounce function
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in milliseconds
 * @returns {Function} - Debounced function
 */
export function debounce(func, wait = 300) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

/**
 * Copy text to clipboard
 * @param {string} text - Text to copy
 * @returns {Promise<boolean>} - Success status
 */
export async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text)
    return true
  } catch (err) {
    console.error('Failed to copy:', err)
    return false
  }
}

/**
 * Generate catalog URL
 * @param {string} username - Catalog username
 * @returns {string} - Full catalog URL
 */
export function generateCatalogUrl(username) {
  const baseUrl = window.location.origin
  return `${baseUrl}/c/${username}`
}

/**
 * Generate WhatsApp chat URL
 * @param {string} phone - WhatsApp number (62xxx format)
 * @param {string} message - Pre-filled message (optional)
 * @returns {string} - WhatsApp API URL
 */
export function generateWhatsAppUrl(phone, message = '') {
  const formattedPhone = formatWhatsappNumber(phone)
  const encodedMessage = encodeURIComponent(message)
  return `https://wa.me/${formattedPhone}${message ? `?text=${encodedMessage}` : ''}`
}
