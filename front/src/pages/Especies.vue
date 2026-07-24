<template>
  <q-page class="q-pa-md bg-grey-1">
    <q-card class="shadow-3">
      <q-card-section class="bg-primary text-white row items-center justify-between">
        <div>
          <div class="text-h6">Especies</div>
          <div class="text-caption">Registro y modificacion del catalogo de especies.</div>
        </div>
        <div class="row q-gutter-sm">
          <q-btn outline color="white" icon="refresh" label="Recargar" :loading="loading" @click="loadData" />
          <q-btn color="positive" icon="add" label="Nueva especie" @click="openCreate" />
        </div>
      </q-card-section>

      <q-card-section>
        <q-input v-model="filter" outlined dense debounce="300" placeholder="Buscar especie..." class="q-mb-md" />

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
              <q-btn flat dense icon="edit" color="primary" @click="openEdit(props.row)" />
              <q-btn flat dense icon="delete" color="negative" @click="confirmDelete(props.row)" />
            </q-td>
          </template>
        </q-table>
      </q-card-section>
    </q-card>

    <q-dialog v-model="dialog" persistent>
      <q-card style="min-width: 420px; width: 100%; max-width: 520px;">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">{{ form.id ? 'Modificar especie' : 'Registrar especie' }}</div>
        </q-card-section>

        <q-form @submit.prevent="save">
          <q-card-section class="q-gutter-md">
            <q-input v-model="form.codigo" label="Codigo" outlined dense maxlength="50" />
            <q-input v-model="form.nombre" label="Nombre" outlined dense maxlength="255" />
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancelar" color="negative" v-close-popup />
            <q-btn :label="form.id ? 'Actualizar' : 'Guardar'" color="positive" type="submit" :loading="saving" />
          </q-card-actions>
        </q-form>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
const emptyForm = () => ({
  id: null,
  codigo: '',
  nombre: ''
})

export default {
  name: 'EspeciesPage',
  data () {
    return {
      loading: false,
      saving: false,
      filter: '',
      rows: [],
      dialog: false,
      form: emptyForm(),
      columns: [
        { name: 'codigo', label: 'Codigo', field: 'codigo', align: 'left', sortable: true },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left', sortable: true },
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
        return [row.codigo, row.nombre].some(value => String(value || '').toLowerCase().includes(term))
      })
    }
  },
  created () {
    this.loadData()
  },
  methods: {
    async loadData () {
      this.loading = true
      try {
        const { data } = await this.$api.get('especie')
        this.rows = Array.isArray(data) ? data : []
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar las especies.')
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
        codigo: row.codigo || '',
        nombre: row.nombre || ''
      }
      this.dialog = true
    },
    async save () {
      this.saving = true
      try {
        const payload = {
          codigo: this.form.codigo,
          nombre: this.form.nombre
        }

        const { data } = this.form.id
          ? await this.$api.put(`especie/${this.form.id}`, payload)
          : await this.$api.post('especie', payload)

        this.$q.notify({
          message: data.message || 'Guardado correctamente.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.dialog = false
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo guardar la especie.')
      } finally {
        this.saving = false
      }
    },
    confirmDelete (row) {
      this.$q.dialog({
        title: 'Eliminar especie',
        message: `Desea eliminar "${row.nombre}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => this.remove(row))
    },
    async remove (row) {
      try {
        const { data } = await this.$api.delete(`especie/${row.id}`)
        this.$q.notify({
          message: data.message || 'Especie eliminada.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo eliminar la especie.')
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
