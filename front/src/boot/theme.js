import { boot } from 'quasar/wrappers'
import { Dark } from 'quasar'

const STORAGE_KEY = 'mascota-theme-mode'

function resolveInitialMode () {
  if (typeof window === 'undefined') {
    return false
  }

  const savedMode = localStorage.getItem(STORAGE_KEY)

  if (savedMode === 'dark') {
    return true
  }

  if (savedMode === 'light') {
    return false
  }

  return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
}

export default boot(() => {
  Dark.set(resolveInitialMode())
})

export { STORAGE_KEY }
