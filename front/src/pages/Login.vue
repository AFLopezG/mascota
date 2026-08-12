<template>
  <q-layout view="hHh lpr fff" class="login-shell">
    <q-page-container>
      <q-page class="login-page">
        <div class="login-bg" />

        <div class="row items-center justify-center login-grid q-col-gutter-xl">
          

          <div class="col-12 col-md-10 col-lg-5 q-pa-lg">
            <q-card class="app-login-card">
              <q-card-section class="q-pa-xl">
                <div class="row justify-between items-start q-gutter-md">
                  <div>
                    <div class="text-overline text-primary">Acceso al sistema</div>
                    <div class="text-h4 text-weight-bold">Iniciar sesion</div>
                    <div class="text-body2 text-grey-7 q-mt-sm">
                      Ingresa con tu cuenta autorizada para continuar.
                    </div>
                  </div>

                  <q-btn
                    flat
                    round
                    :icon="theme.isDark ? 'sym_r_light_mode' : 'sym_r_dark_mode'"
                    :aria-label="theme.isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                    @click="theme.toggleTheme"
                  />
                </div>

                <q-form class="q-gutter-md q-mt-lg" @submit.prevent="login">
                  <q-input
                    v-model="cuenta"
                    label="Cuenta"
                    outlined
                    dense
                    autocomplete="username"
                  >
                    <template #prepend>
                      <q-icon name="sym_r_person" />
                    </template>
                  </q-input>

                  <q-input
                    v-model="password"
                    :type="showPassword ? 'text' : 'password'"
                    label="Password"
                    outlined
                    dense
                    autocomplete="current-password"
                  >
                    <template #prepend>
                      <q-icon name="sym_r_lock" />
                    </template>
                    <template #append>
                      <q-btn
                        flat
                        round
                        dense
                        :icon="showPassword ? 'sym_r_visibility_off' : 'sym_r_visibility'"
                        @click="showPassword = !showPassword"
                      />
                    </template>
                  </q-input>



                  <q-btn
                    class="full-width"
                    size="lg"
                    color="primary"
                    type="submit"
                    :loading="loading"
                    label="Ingresar"
                    icon="sym_r_login"
                  />
                </q-form>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
defineOptions({
  name: 'LoginPage'
})

import { getCurrentInstance, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { api } from 'boot/axios'
import { globalStore } from 'src/stores/globalStore'
import { useAppTheme } from 'src/composables/useAppTheme'

const $q = useQuasar()
const router = useRouter()
const store = globalStore()
const theme = useAppTheme()
const instance = getCurrentInstance()
const proxy = instance?.proxy

const cuenta = ref(localStorage.getItem('mascota-login-account') || '')
const password = ref('')
const remember = ref(true)
const showPassword = ref(false)
const loading = ref(false)

onMounted(() => {
  if (store.isLoggedIn) {
    router.push('/home')
  }
})

async function login () {
  loading.value = true

  try {
    const { data } = await api.post('login', {
      cuenta: cuenta.value,
      password: password.value
    })

    $q.notify({
      message: 'Bienvenido',
      color: 'positive',
      icon: 'sym_r_check_circle',
      position: 'top'
    })

    if (remember.value) {
      localStorage.setItem('mascota-login-account', cuenta.value)
    } else {
      localStorage.removeItem('mascota-login-account')
    }

    proxy?.$login?.(data)
  } catch (error) {
    $q.notify({
      message: error?.response?.data?.message || 'Error al iniciar sesion',
      color: 'negative',
      position: 'top',
      timeout: 2500
    })
  } finally {
    loading.value = false
  }
}
</script>
