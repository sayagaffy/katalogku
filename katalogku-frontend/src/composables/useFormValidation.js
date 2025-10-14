import { ref, computed } from 'vue'

export function useFormValidation() {
  const errors = ref({})

  const hasErrors = computed(() => Object.keys(errors.value).length > 0)

  function validateWhatsapp(whatsapp) {
    const regex = /^(08|628|\+628)[0-9]{8,12}$/
    if (!whatsapp) {
      return 'Nomor WhatsApp harus diisi'
    }
    if (!regex.test(whatsapp)) {
      return 'Format nomor WhatsApp tidak valid. Contoh: 081234567890'
    }
    return null
  }

  function validateOTP(otp) {
    if (!otp) {
      return 'Kode OTP harus diisi'
    }
    if (!/^\d{6}$/.test(otp)) {
      return 'Kode OTP harus 6 digit angka'
    }
    return null
  }

  function validateName(name) {
    if (!name) {
      return 'Nama harus diisi'
    }
    if (name.length < 3) {
      return 'Nama minimal 3 karakter'
    }
    if (name.length > 100) {
      return 'Nama maksimal 100 karakter'
    }
    return null
  }

  function validatePassword(password) {
    if (!password) {
      return 'Password harus diisi'
    }
    if (password.length < 8) {
      return 'Password minimal 8 karakter'
    }
    return null
  }

  function validatePasswordConfirmation(password, confirmation) {
    if (!confirmation) {
      return 'Konfirmasi password harus diisi'
    }
    if (password !== confirmation) {
      return 'Konfirmasi password tidak cocok'
    }
    return null
  }

  function validateProductName(name) {
    if (!name) {
      return 'Nama produk harus diisi'
    }
    if (name.length > 200) {
      return 'Nama produk maksimal 200 karakter'
    }
    return null
  }

  function validatePrice(price) {
    if (!price && price !== 0) {
      return 'Harga harus diisi'
    }
    if (isNaN(price) || price < 0) {
      return 'Harga harus berupa angka positif'
    }
    return null
  }

  function validateImage(file) {
    if (!file) {
      return 'Gambar harus diisi'
    }
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
    if (!allowedTypes.includes(file.type)) {
      return 'Format gambar harus JPG, PNG, atau WebP'
    }
    const maxSize = 10 * 1024 * 1024 // 10MB
    if (file.size > maxSize) {
      return 'Ukuran gambar maksimal 10MB'
    }
    return null
  }

  function setError(field, message) {
    errors.value[field] = message
  }

  function clearError(field) {
    delete errors.value[field]
  }

  function clearAllErrors() {
    errors.value = {}
  }

  function getError(field) {
    return errors.value[field] || null
  }

  return {
    errors,
    hasErrors,
    validateWhatsapp,
    validateOTP,
    validateName,
    validatePassword,
    validatePasswordConfirmation,
    validateProductName,
    validatePrice,
    validateImage,
    setError,
    clearError,
    clearAllErrors,
    getError,
  }
}
