import { ref, watch, onMounted } from 'vue'

const THEME_KEY = 'katalogku_theme'
const isDark = ref(false)

export function useTheme() {
  // Initialize theme from localStorage or system preference
  const initializeTheme = () => {
    const savedTheme = localStorage.getItem(THEME_KEY)

    if (savedTheme) {
      isDark.value = savedTheme === 'dark'
    } else {
      // Check system preference
      isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
    }

    applyTheme()
  }

  // Apply theme to document
  const applyTheme = () => {
    if (isDark.value) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  }

  // Toggle theme
  const toggleTheme = () => {
    isDark.value = !isDark.value
    localStorage.setItem(THEME_KEY, isDark.value ? 'dark' : 'light')
    applyTheme()
  }

  // Set specific theme
  const setTheme = (theme) => {
    isDark.value = theme === 'dark'
    localStorage.setItem(THEME_KEY, theme)
    applyTheme()
  }

  // Watch for changes
  watch(isDark, () => {
    applyTheme()
  })

  return {
    isDark,
    toggleTheme,
    setTheme,
    initializeTheme,
  }
}
