<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Roles y permisos"
        subtitle="Gestion de roles, perfiles y asignacion de permisos."
        icon="sym_r_assignment_ind"
      >
        <template #actions>
          <q-btn
            v-if="store.bool_registrar_rol"
            color="primary"
            icon="sym_r_add"
            label="Nuevo rol"
            @click="openCreate"
          />
          <q-input
            v-model="filter"
            outlined
            dense
            debounce="300"
            placeholder="Buscar..."
            style="min-width: 240px;"
          >
            <template #prepend>
              <q-icon name="sym_r_search" />
            </template>
          </q-input>
        </template>
      </AppSectionHeader>

      <q-card class="app-soft-card app-table">
        <q-card-section class="q-pt-sm">
          <q-table
            :rows="filteredRoles"
            :columns="columns"
            row-key="id"
            :loading="loading"
            flat
            bordered
            dense
          >
            <template #body-cell-permisos="props">
              <q-td :props="props">
                <div class="row q-gutter-xs">
                  <q-chip
                    v-for="permiso in props.row.permisos || []"
                    :key="permiso.id || permiso.nombre"
                    dense
                    square
                    color="blue-1"
                    text-color="blue-10"
                    class="q-ma-none"
                  >
                    {{ permiso.nombre }}
                  </q-chip>
                </div>
              </q-td>
            </template>

            <template #body-cell-op="props">
              <q-td :props="props">
                <q-btn
                  v-if="store.bool_modificar_rol"
                  flat
                  dense
                  round
                  icon="sym_r_edit"
                  color="primary"
                  @click="cargar(props.row)"
                />
                <q-btn
                  v-if="store.bool_modificar_permiso"
                  flat
                  dense
                  round
                  icon="sym_r_fact_check"
                  color="secondary"
                  @click="verpermiso(props.row)"
                />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <q-dialog v-model="dialogreg" persistent>
      <q-card class="app-soft-card" style="min-width: 360px; width: 100%; max-width: 520px;">
        <q-card-section class="bg-primary text-white row items-center justify-between">
          <div>
            <div class="text-h6">{{ rol.id === undefined ? 'Registrar rol' : 'Modificar rol' }}</div>
            <div class="text-caption text-white-7">Define el perfil y sus accesos.</div>
          </div>
          <q-btn flat round icon="sym_r_close" color="white" v-close-popup />
        </q-card-section>

        <q-form class="q-gutter-md" @submit.prevent="enviarRol">
          <q-card-section class="q-pa-lg">
            <q-input v-model="rol.nombre" outlined dense label="Nombre" :rules="[val => !!val || 'Ingresa un nombre']" />
          </q-card-section>

          <q-card-actions align="right" class="q-px-lg q-pb-lg">
            <q-btn flat label="Cancelar" color="negative" v-close-popup />
            <q-btn
              :label="rol.id === undefined ? 'Registrar' : 'Modificar'"
              color="primary"
              type="submit"
              :loading="loading"
            />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialogpermisos" persistent>
      <q-card class="app-soft-card" style="min-width: 420px; width: 100%; max-width: 760px;">
        <q-card-section class="bg-secondary text-white row items-center justify-between">
          <div>
            <div class="text-h6">{{ rol.nombre }} permisos</div>
            <div class="text-caption text-white-7">Activa o desactiva accesos del rol.</div>
          </div>
          <q-btn flat round icon="sym_r_close" color="white" v-close-popup />
        </q-card-section>

        <q-card-section class="q-pa-lg">
          <div class="column q-gutter-md">
            <q-card
              v-for="(permiso, pIndex) in permisos"
              :key="permiso.id"
              flat
              bordered
              class="q-pa-md"
            >
              <q-checkbox
                :label="permiso.nombre"
                v-model="permiso.estado"
                @update:model-value="togglePadre(pIndex, $event)"
              />

              <div v-if="permiso.estado" class="q-pl-xl q-mt-sm column q-gutter-xs">
                <q-checkbox
                  v-for="hijo in permiso.sub_permisos"
                  :key="hijo.id"
                  :label="hijo.nombre"
                  v-model="hijo.estado"
                />
              </div>
            </q-card>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="q-px-lg q-pb-lg">
          <q-btn flat label="Cancelar" color="negative" v-close-popup />
          <q-btn label="Modificar" color="primary" @click="enviarPermisos" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import AppSectionHeader from 'components/AppSectionHeader.vue'
import { globalStore } from '../stores/globalStore'

export default {
  name: 'RolesPage',
  components: {
    AppSectionHeader
  },
  data () {
    return {
      store: globalStore(),
      dialogreg: false,
      dialogpermisos: false,
      filter: '',
      loading: false,
      permisos: [],
      roles: [],
      dialog: false,
      rol: {},
      columns: [
        { name: 'op', label: 'Acciones', align: 'left', field: row => row.id },
        { name: 'nombre', label: 'Nombre', align: 'left', field: row => row.nombre, sortable: true },
        { name: 'permisos', label: 'Permisos', align: 'left', field: row => 'permisos' }
      ]
    }
  },
  computed: {
    filteredRoles () {
      const term = this.filter.trim().toLowerCase()
      if (!term) {
        return this.roles
      }

      return this.roles.filter(role => {
        return [
          role.nombre,
          ...(role.permisos || []).map(permiso => permiso.nombre)
        ].some(value => String(value || '').toLowerCase().includes(term))
      })
    }
  },
  created () {
    if (!this.store.bool_roles) {
      this.$router.push('/home')
      return
    }

    this.getRoles()
    this.getPermisos()
  },
  methods: {
    openCreate () {
      this.rol = {}
      this.dialogreg = true
    },
    togglePadre (pIndex, estado) {
      if (!estado) {
        this.permisos[pIndex].sub_permisos.forEach(hijo => {
          hijo.estado = false
        })
      }
    },
    enviarPermisos () {
      this.rol.permisos = this.permisos
      this.$api.post('updatepermisos', this.rol).then(() => {
        this.dialogpermisos = false
        this.getRoles()
      }).catch(err => {
        this.$q.notify({
          message: err.response.data.message,
          icon: 'sym_r_close',
          color: 'negative'
        })
      })
    },
    enviarRol () {
      this.loading = true
      let url = 'rol'
      let method = 'post'

      if (this.rol.id !== undefined) {
        url = `rol/${this.rol.id}`
        method = 'put'
      }

      this.$api[method](url, this.rol).then(res => {
        this.$q.notify({
          message: res.data.message,
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.dialogreg = false
        this.getRoles()
      }).catch(error => {
        this.$q.notify({
          message: error.response.data.message,
          color: 'negative',
          position: 'top',
          timeout: 2000
        })
      }).finally(() => {
        this.loading = false
      })
    },
    cargar (dato) {
      this.rol = dato
      this.dialogreg = true
    },
    verpermiso (dato) {
      this.rol = dato

      const assignedPerms = Array.isArray(this.rol.permisos) ? this.rol.permisos : []

      this.permisos.forEach(pe => {
        const padre = assignedPerms.find(r => r.pivot.permiso_id === pe.id)
        pe.estado = padre !== undefined

        pe.sub_permisos.forEach(sp => {
          const subp = assignedPerms.find(r => r.pivot.permiso_id === sp.id)
          sp.estado = subp !== undefined
        })
      })

      this.dialogpermisos = true
    },
    getPermisos () {
      this.loading = true
      this.$api.get('permiso').then(res => {
        res.data.forEach(r => {
          r.estado = false
          r.sub_permisos.forEach(sp => {
            sp.estado = false
          })
        })
        this.permisos = res.data
      }).catch(error => {
        this.$q.notify({
          message: error.response,
          color: 'negative',
          position: 'top',
          timeout: 2000
        })
      }).finally(() => {
        this.loading = false
      })
    },
    getRoles () {
      this.loading = true
      this.$api.get('rol').then(res => {
        this.roles = res.data
      }).catch(error => {
        this.$q.notify({
          message: error.response.data.message,
          color: 'negative',
          position: 'top',
          timeout: 2000
        })
      }).finally(() => {
        this.loading = false
      })
    }
  }
}
</script>
