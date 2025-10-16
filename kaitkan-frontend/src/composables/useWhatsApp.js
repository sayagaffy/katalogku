import { formatPrice, formatWhatsappNumber, generateWhatsAppUrl } from '@/utils/helpers'
import { productService } from '@/services/product.service'

// Simple WhatsApp helper composable for consistent links and tracking
export function useWhatsApp() {
  /**
   * Build an order message for a product
   * @param {Object} product - { name, price }
   * @param {string} storeName - Optional catalog/store name
   */
  function buildOrderMessage(product, storeName = 'Kak') {
    const priceText = typeof product?.price === 'number' ? formatPrice(product.price) : ''
    const name = product?.name || 'produk'
    const prefix = storeName ? `Halo ${storeName}, ` : 'Halo, '
    return `${prefix}mau order ${name}${priceText ? ' - ' + priceText : ''}`
  }

  /**
   * Generate WhatsApp link for ordering a product
   * @param {Object} product - Product object
   * @param {string} catalogWhatsApp - WhatsApp number (any format)
   * @param {string} storeName - Optional catalog/store name for greeting
   * @returns {string}
   */
  function generateOrderLink(product, catalogWhatsApp, storeName = '') {
    if (!catalogWhatsApp) return ''
    const phone = formatWhatsappNumber(catalogWhatsApp)
    const message = buildOrderMessage(product, storeName)
    return generateWhatsAppUrl(phone, message)
  }

  /**
   * Track a product click via API (analytics)
   * @param {number} productId
   */
  async function trackClick(productId) {
    try {
      if (!productId) return
      await productService.trackClick(productId)
    } catch (e) {
      // Swallow errors to not block UX
      console.warn('trackClick failed:', e?.message || e)
    }
  }

  return { buildOrderMessage, generateOrderLink, trackClick }
}

