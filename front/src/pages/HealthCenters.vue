<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Centros de salud"
        subtitle="Registro y modificacion del catalogo de centros de salud."
        icon="sym_r_local_hospital"
      >
        <template #actions>
          <q-btn outline color="primary" icon="sym_r_refresh" label="Recargar" :loading="loading" @click="loadData" />
          <q-btn v-if="store.bool_registrar_health_centers" color="primary" icon="sym_r_add" label="Nuevo centro" @click="openCreate" />
        </template>
      </AppSectionHeader>

      <q-card class="app-soft-card app-table">
        <q-card-section class="q-pb-none">
          <q-input v-model="filter" outlined dense debounce="300" placeholder="Buscar centro de salud..." />
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
              <q-td :props="props">
                <q-btn v-if="store.bool_modificar_health_centers" flat dense icon="sym_r_edit" color="primary" @click="openEdit(props.row)" />
                <q-btn v-if="store.bool_eliminar_health_centers" flat dense icon="sym_r_delete" color="negative" @click="confirmDelete(props.row)" />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <q-dialog v-model="dialog" persistent>
      <q-card class="app-soft-card" style="min-width: 420px; width: 100%; max-width: 560px;">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">{{ form.id ? 'Modificar centro de salud' : 'Registrar centro de salud' }}</div>
        </q-card-section>

        <q-form @submit.prevent="save">
          <q-card-section class="q-gutter-md">
            <q-input v-model="form.nombre" :label="$requiredLabel('Nombre')" outlined dense maxlength="255" />
            <q-input v-model="form.direccion" label="Direccion" outlined dense maxlength="255" />
            <q-input v-model="form.telefono" label="Telefono" outlined dense maxlength="255" />
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancelar" color="negative" v-close-popup />
            <q-btn :label="form.id ? 'Actualizar' : 'Guardar'" color="primary" type="submit" :loading="saving" />
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
  nombre: '',
  direccion: '',
  telefono: ''
})

export default {
  name: 'HealthCentersPage',
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
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left', sortable: true },
        { name: 'direccion', label: 'Direccion', field: 'direccion', align: 'left', sortable: true },
        { name: 'telefono', label: 'Telefono', field: 'telefono', align: 'left', sortable: true },
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

      return this.rows.filter(row => [
        row.nombre,
        row.direccion,
        row.telefono
      ].some(value => String(value || '').toLowerCase().includes(term)))
    }
  },
  created () {
    if (!this.store.isLoggedIn) {
      this.$router.push('/')
      return
    }

    if (!this.store.bool_health_centers) {
      this.$router.push('/home')
      return
    }

    this.loadData()
  },
  methods: {
    async loadData () {
      this.loading = true
      try {
        const { data } = await this.$api.get('health-center')
        this.rows = Array.isArray(data) ? data : []
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar los centros de salud.')
      } finally {
        this.loading = false
      }
    },
    openCreate () {
      this.form = emptyForm()
      this.dialog = true
    },
    openEdit (row) {
      this.form = {
        id: row.id,
        nombre: row.nombre || '',
        direccion: row.direccion || '',
        telefono: row.telefono || ''
      }
      this.dialog = true
    },
    async save () {
      this.saving = true
      try {
        const payload = {
          nombre: this.form.nombre,
          direccion: this.form.direccion || null,
          telefono: this.form.telefono || null
        }

        const { data } = this.form.id
          ? await this.$api.put(`health-center/${this.form.id}`, payload)
          : await this.$api.post('health-center', payload)

        this.$q.notify({
          message: data.message || 'Guardado correctamente.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.dialog = false
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo guardar el centro de salud.')
      } finally {
        this.saving = false
      }
    },
    confirmDelete (row) {
      this.$q.dialog({
        title: 'Eliminar centro de salud',
        message: `Desea eliminar "${row.nombre}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => this.remove(row))
    },
    async remove (row) {
      try {
        const { data } = await this.$api.delete(`health-center/${row.id}`)
        this.$q.notify({
          message: data.message || 'Centro de salud eliminado.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo eliminar el centro de salud.')
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
