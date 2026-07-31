import { computed } from 'vue'
import { Dark } from 'quasar'
import { STORAGE_KEY } from 'boot/theme'

export function useAppTheme () {
  const isDark = computed(() => Dark.isActive)

  function setTheme (value) {
    Dark.set(!!value)
    if (typeof window !== 'undefined') {
      localStorage.setItem(STORAGE_KEY, value ? 'dark' : 'light')
    }
  }

  function toggleTheme () {
    setTheme(!Dark.isActive)
  }

  return {
    isDark,
    setTheme,
    toggleTheme
  }
}
