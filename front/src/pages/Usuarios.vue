<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Usuarios"
        subtitle="Administracion de cuentas, estado y contrasenas."
        icon="sym_r_group"
      >
        <template #actions>
          <q-btn
            v-if="store.bool_registrar_usuarios"
            color="primary"
            icon="sym_r_add"
            label="Nuevo usuario"
            @click="regDialog"
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

      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-4">
          <q-card class="app-soft-card q-pa-lg">
            <div class="text-caption text-grey-6">Usuarios visibles</div>
            <div class="text-h4 text-weight-bold">{{ data.length }}</div>
            <div class="text-caption text-grey-7 q-mt-sm">Listado operativo del sistema.</div>
          </q-card>
        </div>
        <div class="col-12 col-md-4">
          <q-card class="app-soft-card q-pa-lg">
            <div class="text-caption text-grey-6">Activos</div>
            <div class="text-h4 text-weight-bold">{{ activeUsers }}</div>
            <div class="text-caption text-grey-7 q-mt-sm">Cuentas habilitadas.</div>
          </q-card>
        </div>
        <div class="col-12 col-md-4">
          <q-card class="app-soft-card q-pa-lg">
            <div class="text-caption text-grey-6">Bloqueados</div>
            <div class="text-h4 text-weight-bold">{{ blockedUsers }}</div>
            <div class="text-caption text-grey-7 q-mt-sm">Cuentas por revisar.</div>
          </q-card>
        </div>
      </div>

      <q-card class="app-soft-card app-table">
        <q-card-section class="q-pt-sm">
          <q-table
            dense
            :filter="filter"
            :rows="filteredUsers"
            :columns="columns"
            row-key="name"
            :rows-per-page-options="[20, 50, 100]"
            :loading="loading"
            flat
            bordered
          >
            <template #body-cell-estado="props">
              <q-td :props="props">
                <q-badge
                  :color="props.row.estado === 'ACTIVO' ? 'positive' : 'negative'"
                  class="cursor-pointer"
                  @click="editActivo(props.row)"
                >
                  {{ props.row.estado }}
                </q-badge>
              </q-td>
            </template>

            <template #body-cell-opcion="props">
              <q-td :props="props">
                <q-btn
                  v-if="store.bool_modificar_usuarios"
                  dense
                  round
                  flat
                  color="primary"
                  @click="editRow(props)"
                  icon="sym_r_edit"
                />
                <q-btn
                  v-if="store.bool_modificar_password"
                  dense
                  round
                  flat
                  color="secondary"
                  @click="cambiopass(props)"
                  icon="sym_r_vpn_key"
                />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <q-dialog v-model="alert" full-width>
      <q-card class="app-soft-card dialog-card">
        <q-card-section :class="dato.id == undefined ? 'bg-primary text-white' : 'bg-secondary text-white'">
          <div class="row items-center justify-between">
            <div>
              <div class="text-overline text-white-7">Gestion de usuario</div>
              <div class="text-h6">{{ dato.id == undefined ? 'Registro de nuevo usuario' : 'Modificar usuario' }}</div>
            </div>
            <q-btn flat round icon="sym_r_close" color="white" v-close-popup />
          </div>
        </q-card-section>

        <q-card-section class="q-pa-lg">
          <q-form @submit.prevent="onSubmit" @reset="onReset" class="q-gutter-md">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-6">
                <q-input outlined dense v-model="dato.cedula" label="Numero carnet" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" />
              </div>
              <div class="col-12 col-md-6">
                <q-input outlined dense v-model="dato.name" label="Cuenta" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" />
              </div>
              <div class="col-12">
                <q-input outlined dense v-model="dato.nombre" label="Nombre completo" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" />
              </div>
              <div class="col-12 col-md-6">
                <q-input outlined dense v-model="dato.celular" label="Celular" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" />
              </div>
              <div class="col-12 col-md-6">
                <q-input outlined dense v-model="dato.email" label="Correo" type="email" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" />
              </div>
              <div v-if="dato.id == undefined" class="col-12 col-md-6">
                <q-input
                  outlined
                  dense
                  v-model="dato.password"
                  label="Contraseña"
                  lazy-rules
                  :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']"
                  :type="typePassword ? 'password' : 'text'"
                >
                  <template #append>
                    <q-icon class="cursor-pointer" @click="typePassword = !typePassword" :name="typePassword ? 'sym_r_visibility' : 'sym_r_visibility_off'" />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-md-6">
                <q-input outlined dense v-model="dato.fecha_limite" type="date" label="Fecha limite" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" />
              </div>
              <div class="col-12 col-md-6">
                <q-select outlined dense v-model="rol" label="Rol" :options="roles" option-label="nombre" />
              </div>
            </div>
          </q-form>
        </q-card-section>

        <q-card-actions align="right" class="q-px-lg q-pb-lg">
          <q-btn label="Cancelar" color="negative" v-close-popup />
          <q-btn
            :label="dato.id == undefined ? 'Crear' : 'Modificar'"
            color="primary"
            icon="sym_r_save"
            @click="onSubmit"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialog_del">
      <q-card class="app-soft-card">
        <q-card-section class="row items-center q-gutter-md">
          <q-avatar icon="sym_r_delete_forever" color="negative" text-color="white" />
          <div>
            <div class="text-subtitle1 text-weight-bold">Eliminar registro</div>
            <div class="text-caption text-grey-7">Esta accion no se puede deshacer.</div>
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="primary" v-close-popup />
          <q-btn flat label="Eliminar" color="negative" @click="onDel" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'
import AppSectionHeader from 'components/AppSectionHeader.vue'
import { globalStore } from '../stores/globalStore'

export default {
  name: 'UserPage',
  components: {
    AppSectionHeader
  },
  data () {
    return {
      permisos: [],
      store: globalStore(),
      alert: false,
      dialog_mod: false,
      dialog_del: false,
      typePassword: true,
      fecha: moment().format('YYYY-MM-DD'),
      filter: '',
      dato: { fecha_limite: moment().add(11, 'months').format('YYYY-MM-DD') },
      model: '',
      dato2: {},
      options: [],
      props: [],
      modelpermiso: false,
      modelprofiles: false,
      filterU: [],
      columns: [
        { name: 'opcion', label: 'Acciones', field: 'action', sortable: false },
        { name: 'name', align: 'left', label: 'Cuenta', field: 'name', sortable: true },
        { name: 'nombre', align: 'left', label: 'Nombre', field: 'nombre', sortable: true },
        { name: 'fechalimite', align: 'left', label: 'Fecha lim', field: row => row.fecha_limite, format: val => `${moment(val).format('YYYY-MM-DD')}`, sortable: true },
        { name: 'estado', align: 'left', label: 'Estado', field: 'estado', sortable: true },
        { name: 'rol', align: 'left', label: 'Rol', field: row => row.rol.nombre, sortable: true }
      ],
      data: [],
      roles: [],
      rol: { nombre: '' }
    }
  },

  computed: {
    filteredUsers () {
      const term = this.filter.trim().toLowerCase()
      if (!term) {
        return this.data
      }

      return this.data.filter(user => {
        return [
          user.name,
          user.nombre,
          user.cedula,
          user.email,
          user.estado,
          user.rol?.nombre
        ].some(value => String(value || '').toLowerCase().includes(term))
      })
    },
    activeUsers () {
      return this.data.filter(user => user.estado === 'ACTIVO').length
    },
    blockedUsers () {
      return this.data.filter(user => user.estado !== 'ACTIVO').length
    }
  },

  mounted () {
    if (!this.store.bool_usuarios) {
      this.$router.replace({ path: '/home' })
      return
    }

    this.misdatos()
    this.getRoles()
  },

  methods: {
    editActivo (row) {
      if (!this.store.bool_activar_usuario) {
        return false
      }

      this.$api.post('cambioEstado', row)
        .then(() => {
          this.$q.notify({
            color: 'positive',
            textColor: 'white',
            icon: 'sym_r_check_circle',
            message: 'Estado actualizado correctamente'
          })
          this.misdatos()
        })
        .catch(err => {
          this.$q.notify({
            message: err.response.data.message,
            icon: 'sym_r_close',
            color: 'negative'
          })
        })
    },
    getRoles () {
      this.$api.get('rol').then((res) => {
        this.roles = res.data
      })
    },
    updatepermisos () {
      this.$api.put('updatepermisos/' + this.dato.id, { permisos: this.permisos }).then(() => {
        this.modelpermiso = false
        this.misdatos()
      }).catch(err => {
        this.$q.notify({
          message: err.response.data.message,
          icon: 'sym_r_close',
          color: 'negative'
        })
      })
    },
    regDialog () {
      this.dato = { fecha_limite: moment().add(12, 'months').format('YYYY-MM-DD') }
      this.rol = { nombre: '' }
      this.alert = true
    },
    misdatos () {
      this.$q.loading.show()
      this.$api.get('user').then((res) => {
        this.data = res.data
      }).finally(() => {
        this.$q.loading.hide()
      })
    },
    editRow (item) {
      this.dato = item.row
      this.rol = this.roles.find(role => Number(role.id) === Number(this.dato.rol_id || this.dato.rol?.id)) || this.dato.rol || { nombre: '' }
      if (this.dato.conjunto) {
        this.conjunto = this.dato.conjunto
      }
      this.alert = true
    },
    deleteRow (item) {
      this.dato = item.row
      this.dialog_del = true
    },
    onSubmit () {
      this.$q.loading.show()

      if (this.rol.id == undefined) {
        this.$q.notify({
          message: 'Por favor seleccione un rol',
          icon: 'sym_r_error',
          color: 'negative'
        })
        this.$q.loading.hide()
        return
      }

      this.dato.rol_id = this.rol.id

      if (this.dato.id == undefined) {
        this.$api.post('user', this.dato).then(() => {
          this.$q.notify({
            color: 'positive',
            textColor: 'white',
            icon: 'sym_r_check_circle',
            message: 'Creado correctamente'
          })
          this.dato = { fecha_limite: moment().add(12, 'months').format('YYYY-MM-DD') }
          this.rol = { nombre: '' }
          this.alert = false
          this.misdatos()
        }).catch(err => {
          this.$q.notify({
            message: err.response.data.message,
            icon: 'sym_r_close',
            color: 'negative'
          })
          this.$q.loading.hide()
        })
      } else {
        this.$api.put('user/' + this.dato.id, this.dato).then(() => {
          this.$q.notify({
            color: 'positive',
            textColor: 'white',
            icon: 'sym_r_check_circle',
            message: 'Modificado correctamente'
          })
          this.dato = { fecha_limite: moment().add(12, 'months').format('YYYY-MM-DD') }
          this.rol = { nombre: '' }
          this.alert = false
          this.misdatos()
        }).catch(err => {
          this.$q.notify({
            message: err.response.data.message,
            icon: 'sym_r_close',
            color: 'negative'
          })
          this.$q.loading.hide()
        })
      }
    },
    onDel () {
      this.$q.loading.show()
      this.$api.delete('user/' + this.dato.id)
        .then(() => {
          this.$q.notify({
            color: 'positive',
            textColor: 'white',
            icon: 'sym_r_check_circle',
            message: 'Eliminado correctamente'
          })
          this.dialog_del = false
          this.misdatos()
        }).catch(err => {
          this.$q.loading.hide()
          this.$q.notify({
            message: err.response.data.message,
            icon: 'sym_r_error',
            color: 'negative'
          })
        })
    },
    onReset () {
      this.dato.nombre = null
      this.dato.inicio = 0
      this.dato.fin = 0
    },
    cambiopass (i) {
      this.$q.dialog({
        title: 'Cambiar password',
        message: 'Ingresar nueva contraseña',
        prompt: {
          model: '',
          type: 'text',
          rules: [
            val => val && val.length >= 6 || 'La contraseña debe tener al menos 6 caracteres'
          ]
        },
        cancel: true,
        persistent: false
      }).onOk(data => {
        this.$q.loading.show()

        this.$api.post('updatePassword', { id: i.row.id, nuevopassword: data }).then(() => {
          this.$q.notify({
            color: 'positive',
            textColor: 'white',
            icon: 'sym_r_check_circle',
            message: 'Cambiado correctamente'
          })
        }).catch(err => {
          this.$q.notify({
            message: err.response.data.message,
            icon: 'sym_r_error',
            color: 'negative'
          })
        }).finally(() => {
          this.$q.loading.hide()
        })
      })
    }
  }
}
</script>
