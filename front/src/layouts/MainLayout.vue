<template>
  <q-layout view="hHh Lpr fFf" class="app-shell">
    <q-header class="app-shell__header">
      <q-toolbar class="q-py-sm q-px-md">
        <q-btn
          flat
          round
          dense
          class="lt-lg"
          icon="sym_r_menu"
          aria-label="Abrir menú"
          @click="drawerOpen = !drawerOpen"
        />

        <div class="row items-center q-gutter-sm no-wrap">
          <q-avatar rounded size="44px" class="bg-white text-primary">
            <img src="/img/zoonosis.jpg" alt="Logo del sistema" />
          </q-avatar>
          <div class="column">
            <div class="text-subtitle1 text-weight-bold">Sistema de Informacion Municipal de Canes</div>
            <div class="text-caption text-white-7">
              Registro de mascotas, campañas y control operativo
            </div>
          </div>
        </div>

        <q-space />

        <div class="gt-sm row items-center q-gutter-sm q-mr-md">
          <q-chip dense square class="app-brand-chip">
            <q-icon name="sym_r_badge" size="18px" class="q-mr-xs" />
            {{ store.rol?.nombre || 'Sin rol' }}
          </q-chip>
        </div>

        <q-btn
          flat
          round
          dense
          class="q-mr-sm"
          :icon="theme.isDark ? 'sym_r_light_mode' : 'sym_r_dark_mode'"
          :aria-label="theme.isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
          @click="theme.toggleTheme"
        />

        <q-btn
          flat
          round
          dense
          icon="sym_r_logout"
          aria-label="Cerrar sesión"
          @click="logout"
        />
      </q-toolbar>
    </q-header>

    <q-drawer
      v-model="drawerOpen"
      :breakpoint="1024"
      show-if-above
      bordered
      class="app-shell__drawer"
    >
      <div class="app-shell__drawer-hero">
        <div class="row items-center q-gutter-sm">
        </div>

        <div class="q-mt-md text-body2">
          <div class="text-weight-medium">{{ store.user?.nombre || 'Usuario' }}</div>
        </div>
      </div>

      <q-scroll-area class="fit">
        <div class="q-pa-md q-gutter-md">
          <q-list v-if="hasGeneralAccess" class="q-gutter-xs">
            <q-item-label header class="text-uppercase text-weight-bold text-grey-6 q-pb-xs">
              General
            </q-item-label>

            <q-item clickable v-ripple to="/home" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_home" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Inicio</q-item-label>
                <q-item-label caption>Resumen general del sistema</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_registro_persona_mascota" clickable v-ripple to="/registro-persona-mascota" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_health_and_safety" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Registro</q-item-label>
                <q-item-label caption>Personas y mascotas</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_busqueda" clickable v-ripple to="/buscar-persona" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_manage_search" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Búsqueda</q-item-label>
                <q-item-label caption>Consulta y vacunas</q-item-label>
              </q-item-section>
            </q-item>
            <q-item v-if="store.bool_registro_vacunas" clickable v-ripple to="/registro-vacunas" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_vaccines" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Registro vacunas</q-item-label>
                <q-item-label caption>Alta y listado por fechas</q-item-label>
              </q-item-section>
            </q-item>
            <q-item v-if="store.bool_reporte_registro_vacunas" clickable v-ripple to="/reporte-registro-vacunas" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_assessment" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Reporte vacunas</q-item-label>
                <q-item-label caption>Especie, lugar y menores</q-item-label>
              </q-item-section>
            </q-item>
            <q-item v-if="store.bool_denuncia" clickable v-ripple to="/denuncias" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_report" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Denuncias</q-item-label>
                <q-item-label caption>Registro y seguimiento</q-item-label>
              </q-item-section>
            </q-item>
            <q-item v-if="store.bool_reporte_denuncia" clickable v-ripple to="/reporte-denuncias" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_assessment" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Reporte denuncia</q-item-label>
                <q-item-label caption>Fechas, tipo y logs</q-item-label>
              </q-item-section>
            </q-item>
            <q-item v-if="store.bool_tipo_denuncia" clickable v-ripple to="/denuncia-tipos" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_view_list" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Tipos de denuncia</q-item-label>
                <q-item-label caption>Catalogo de denuncias</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_procesos" clickable v-ripple to="/procesos" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_timeline" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Procesos</q-item-label>
                <q-item-label caption>Flujo y colores de estado</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_health_centers" clickable v-ripple to="/health-centers" exact class="app-nav-item">
              <q-item-section avatar><q-icon name="sym_r_local_hospital" /></q-item-section>
              <q-item-section>
                <q-item-label>Centros de salud</q-item-label>
                <q-item-label caption>Catalogo sanitario</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_personals" clickable v-ripple to="/personals" exact class="app-nav-item">
              <q-item-section avatar><q-icon name="sym_r_badge" /></q-item-section>
              <q-item-section>
                <q-item-label>Personal</q-item-label>
                <q-item-label caption>Registro y mantenimiento</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>

          <q-list v-if="hasAdminAccess" class="q-gutter-xs">
            <q-item-label header class="text-uppercase text-weight-bold text-grey-6 q-pb-xs">
              Administración
            </q-item-label>

            <q-item v-if="store.bool_roles" clickable v-ripple to="/roles" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_assignment_ind" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Roles</q-item-label>
                <q-item-label caption>Permisos y perfiles</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_usuarios" clickable v-ripple to="/usuarios" exact class="app-nav-item">
              <q-item-section avatar>
                <q-icon name="sym_r_group" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Usuarios</q-item-label>
                <q-item-label caption>Accesos y cuentas</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>

          <q-list v-if="hasCatalogAccess" class="q-gutter-xs">
            <q-item-label header class="text-uppercase text-weight-bold text-grey-6 q-pb-xs">
              Catálogos
            </q-item-label>

            <q-item v-if="store.bool_especies" clickable v-ripple to="/especies" exact class="app-nav-item">
              <q-item-section avatar><q-icon name="sym_r_pets" /></q-item-section>
              <q-item-section>
                <q-item-label>Especies</q-item-label>
                <q-item-label caption>Esquema biológico</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_razas" clickable v-ripple to="/razas" exact class="app-nav-item">
              <q-item-section avatar><q-icon name="sym_r_favorite" /></q-item-section>
              <q-item-section>
                <q-item-label>Razas</q-item-label>
                <q-item-label caption>Clasificación de mascotas</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_categorias" clickable v-ripple to="/categorias" exact class="app-nav-item">
              <q-item-section avatar><q-icon name="sym_r_category" /></q-item-section>
              <q-item-section>
                <q-item-label>Categorías</q-item-label>
                <q-item-label caption>Segmentación interna</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_campania_tipos" clickable v-ripple to="/campania-tipos" exact class="app-nav-item">
              <q-item-section avatar><q-icon name="sym_r_campaign" /></q-item-section>
              <q-item-section>
                <q-item-label>Tipos de campaña</q-item-label>
                <q-item-label caption>Clasificación operativa</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_campanias" clickable v-ripple to="/campanias" exact class="app-nav-item">
              <q-item-section avatar><q-icon name="sym_r_event" /></q-item-section>
              <q-item-section>
                <q-item-label>Campañas</q-item-label>
                <q-item-label caption>Programación y control</q-item-label>
              </q-item-section>
            </q-item>

            <q-item v-if="store.bool_places" clickable v-ripple to="/places" exact class="app-nav-item">
              <q-item-section avatar><q-icon name="sym_r_location_on" /></q-item-section>
              <q-item-section>
                <q-item-label>Lugares</q-item-label>
                <q-item-label caption>Catálogo de ubicaciones</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </div>
      </q-scroll-area>
    </q-drawer>

    <q-page-container class="app-page-container">
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { computed, getCurrentInstance, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useQuasar } from 'quasar'
import { globalStore } from 'src/stores/globalStore'
import { useAppTheme } from 'src/composables/useAppTheme'

const $q = useQuasar()
const route = useRoute()
const router = useRouter()
const store = globalStore()
const theme = useAppTheme()
const drawerOpen = ref($q.screen.gt.md)
const instance = getCurrentInstance()
const proxy = instance?.proxy

const hasCatalogAccess = computed(() => (
  store.bool_especies ||
  store.bool_razas ||
  store.bool_categorias ||
  store.bool_campania_tipos ||
  store.bool_campanias ||
  store.bool_procesos ||
  store.bool_places ||
  store.bool_health_centers
))

const hasGeneralAccess = computed(() => (
  store.bool_registro_persona_mascota ||
  store.bool_busqueda ||
  store.bool_registro_vacunas ||
  store.bool_reporte_registro_vacunas ||
  store.bool_denuncia ||
  store.bool_reporte_denuncia ||
  store.bool_tipo_denuncia ||
  store.bool_personals
))

const hasAdminAccess = computed(() => store.bool_roles || store.bool_usuarios)

onMounted(() => {
  if (!store.isLoggedIn) {
    router.push('/')
  }
})

watch(() => route.fullPath, () => {
  if ($q.screen.lt.md) {
    drawerOpen.value = false
  }
})

function logout () {
  if ($q.screen.lt.md) {
    drawerOpen.value = false
  }

  proxy?.$logout?.()
}
</script>
