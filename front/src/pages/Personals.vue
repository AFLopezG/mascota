<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Personal"
        subtitle="Registro y mantenimiento de personal disponible para los logs de denuncias."
        icon="sym_r_badge"
      >
        <template #actions>
          <q-btn outline color="primary" icon="sym_r_refresh" label="Recargar" :loading="loading" @click="loadData" />
          <q-btn v-if="store.bool_registrar_personals" color="primary" icon="sym_r_add" label="Nuevo personal" @click="openCreate" />
        </template>
      </AppSectionHeader>

      <q-card class="app-soft-card app-table">
        <q-card-section class="q-pb-none">
          <q-input v-model="filter" outlined dense debounce="300" placeholder="Buscar por cédula, nombre o celular..." />
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <q-table
            :rows="filteredRows"
            :columns="columns"
            row-key="id"
            :loading="loading"
            flat
            bordered
            dense
          >
            <template #body-cell-actions="props">
              <q-td :props="props" class="text-right">
                <q-btn v-if="store.bool_modificar_personals" flat dense icon="sym_r_edit" color="primary" @click="openEdit(props.row)" />
                <q-btn v-if="store.bool_eliminar_personals" flat dense icon="sym_r_delete" color="negative" @click="confirmDelete(props.row)" />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <q-dialog v-model="dialog" persistent>
      <q-card class="app-soft-card" style="min-width: 420px; width: 100%; max-width: 540px;">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">{{ form.id ? 'Modificar personal' : 'Registrar personal' }}</div>
        </q-card-section>

        <q-form @submit.prevent="save">
          <q-card-section class="q-gutter-md">
            <q-input v-model="form.cedula" :label="$requiredLabel('Cédula')" outlined dense maxlength="255" />
            <q-input v-model="form.nombre" :label="$requiredLabel('Nombre')" outlined dense maxlength="255" />
            <q-input v-model="form.celular" label="Celular" outlined dense maxlength="255" hint="Opcional" />
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancelar" color="negative" v-close-popup />
            <q-btn v-if="store.bool_registrar_personals || store.bool_modificar_personals" :label="form.id ? 'Actualizar' : 'Guardar'" color="primary" type="submit" :loading="saving" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import AppSectionHeader from 'components/AppSectionHeader.vue'
import { globalStore } from 'src/stores/globalStore'

const emptyForm = () => ({
  id: null,
  cedula: '',
  nombre: '',
  celular: ''
})

export default {
  name: 'PersonalsPage',
  components: {
    AppSectionHeader
  },
  data () {
    return {
      store: globalStore(),
      loading: false,
      saving: false,
      filter: '',
      rows: [],
      dialog: false,
      form: emptyForm(),
      columns: [
        { name: 'cedula', label: 'Cédula', field: 'cedula', align: 'left', sortable: true },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left', sortable: true },
        { name: 'celular', label: 'Celular', field: 'celular', align: 'left' },
        { name: 'actions', label: 'Acciones', field: 'actions', align: 'right' }
      ]
    }
  },
  computed: {
    filteredRows () {
      const term = this.filter.trim().toLowerCase()

      if (!term) {
        return this.rows
      }

      return this.rows.filter(row => {
        return [
          row.cedula,
          row.nombre,
          row.celular
        ].some(value => String(value || '').toLowerCase().includes(term))
      })
    }
  },
  created () {
    if (!this.store.isLoggedIn) {
      this.$router.push('/')
      return
    }

    if (!this.store.bool_personals) {
      this.$router.push('/home')
      return
    }

    this.loadData()
  },
  methods: {
    async loadData () {
      this.loading = true
      try {
        const { data } = await this.$api.get('personal')
        this.rows = Array.isArray(data) ? data : []
      } catch (error) {
        this.notifyError(error, 'No se pudo cargar el personal.')
      } finally {
        this.loading = false
      }
    },
    openCreate () {
      if (!this.store.bool_registrar_personals) {
        return
      }

      this.form = emptyForm()
      this.dialog = true
    },
    openEdit (row) {
      if (!this.store.bool_modificar_personals) {
        return
      }

      this.form = {
        id: row.id,
        cedula: row.cedula || '',
        nombre: row.nombre || '',
        celular: row.celular || ''
      }
      this.dialog = true
    },
    async save () {
      if (this.form.id ? !this.store.bool_modificar_personals : !this.store.bool_registrar_personals) {
        return
      }

      this.saving = true
      try {
        const payload = {
          cedula: this.form.cedula,
          nombre: this.form.nombre,
          celular: this.form.celular
        }

        const { data } = this.form.id
          ? await this.$api.put(`personal/${this.form.id}`, payload)
          : await this.$api.post('personal', payload)

        this.$q.notify({
          message: data.message || 'Guardado correctamente.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })

        this.dialog = false
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo guardar el personal.')
      } finally {
        this.saving = false
      }
    },
    confirmDelete (row) {
      if (!this.store.bool_eliminar_personals) {
        return
      }

      this.$q.dialog({
        title: 'Eliminar personal',
        message: `Desea eliminar "${row.nombre}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => this.remove(row))
    },
    async remove (row) {
      try {
        const { data } = await this.$api.delete(`personal/${row.id}`)
        this.$q.notify({
          message: data.message || 'Personal eliminado.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })

        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo eliminar el personal.')
      }
    },
    notifyError (error, fallback) {
      this.$q.notify({
        message: error?.response?.data?.message || fallback,
        color: 'negative',
        position: 'top',
        timeout: 2500
      })
    }
  }
}
</script>
