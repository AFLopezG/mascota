<template>
  <q-layout view="lHh Lpr lFf">
    <q-header elevated>
      <q-toolbar>
        <q-btn flat dense round icon="menu" aria-label="Menu" @click="toggleLeftDrawer" />

        <q-toolbar-title>
          <div class="row">
            <div class="" style="margin: 0; padding: 0; vertical-align: middle;"><img src="img/logo.png" style="height: 60px; width: 60px; margin: 0; padding: 0;" /></div>
            <div style="font-size: 18px; font-style: italic; font-family: Verdana, Geneva, Tahoma, sans-serif; vertical-align: middle;">SISTEMA REGISTRO MASCOTAS <br><span style="font-size: 12px;">{{ store.rol.nombre }}</span>
            </div>
          </div>
        </q-toolbar-title>

        <div>{{ store.user.nombre }}
          <q-btn flat dense round icon="logout" aria-label="Logout" @click="logout" />
        </div>
      </q-toolbar>
    </q-header>

    <q-drawer v-model="leftDrawerOpen" show-if-above bordered :width="250">
      <q-list bordered class="rounded-borders">
        <q-item-label header class="text-center text-bold bg-red-14 text-white">
          Opciones
        </q-item-label>

        <q-item clickable dense to="/home" exact active-class="bg-primary text-white">
          <q-item-section avatar><q-icon name="home" /></q-item-section>
          <q-item-section><q-item-label>Principal</q-item-label><q-item-label caption class="text-grey-2"></q-item-label></q-item-section>
        </q-item>

        <q-expansion-item active-class="bg-primary text-white" dense exact expand-separator icon="assignment_ind" label="Roles" to="/roles" expand-icon="null" v-if="store.bool_roles" />
        <q-expansion-item active-class="bg-primary text-white" dense exact expand-separator icon="people" label="Usuarios" to="/usuarios" expand-icon="null" v-if="store.bool_usuarios" />

        <q-item clickable dense to="/registro-persona-mascota" exact active-class="bg-primary text-white">
          <q-item-section avatar><q-icon name="pets" /></q-item-section>
          <q-item-section>
            <q-item-label>Registro</q-item-label>
            <q-item-label caption class="text-grey-2"></q-item-label>
          </q-item-section>
        </q-item>

        <q-item clickable dense to="/buscar-persona" exact active-class="bg-primary text-white">
          <q-item-section avatar><q-icon name="manage_search" /></q-item-section>
          <q-item-section>
            <q-item-label>Busqueda</q-item-label>
            <q-item-label caption class="text-grey-2"></q-item-label>
          </q-item-section>
        </q-item>

        <q-expansion-item
          active-class="bg-primary text-white"
          dense
          expand-separator
          icon="category"
          label="Catalogos"
          expand-icon="null"
        >
          <q-item clickable dense to="/especies" exact active-class="bg-primary text-white">
            <q-item-section avatar><q-icon name="pets" /></q-item-section>
            <q-item-section><q-item-label>Especies</q-item-label></q-item-section>
          </q-item>
          <q-item clickable dense to="/razas" exact active-class="bg-primary text-white">
            <q-item-section avatar><q-icon name="tag" /></q-item-section>
            <q-item-section><q-item-label>Razas</q-item-label></q-item-section>
          </q-item>
          <q-item clickable dense to="/categorias" exact active-class="bg-primary text-white">
            <q-item-section avatar><q-icon name="view_list" /></q-item-section>
            <q-item-section><q-item-label>Categorias</q-item-label></q-item-section>
          </q-item>
          <q-item clickable dense to="/campania-tipos" exact active-class="bg-primary text-white">
            <q-item-section avatar><q-icon name="campaign" /></q-item-section>
            <q-item-section><q-item-label>CampaniaTipo</q-item-label></q-item-section>
          </q-item>
        </q-expansion-item>
      </q-list>
    </q-drawer>

    <q-page-container>
      <router-view />
    </q-page-container>
  </q-layout>
</template>

<script>
import { defineComponent, ref } from 'vue'
import { globalStore } from 'src/stores/globalStore'

export default defineComponent({
  name: 'MainLayout',
  data () {
    return {
      leftDrawerOpen: ref(false),
      store: globalStore(),
      valid: false
    }
  },
  created () {
    if (!this.store.isLoggedIn) {
      this.$router.push('/')
    }
  },
  methods: {
    logout () {
      this.$logout()
    },
    toggleLeftDrawer () {
      this.leftDrawerOpen = !this.leftDrawerOpen
    }
  }
})
</script>
