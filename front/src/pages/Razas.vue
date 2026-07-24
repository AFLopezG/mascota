<template>
  <q-page class="q-pa-md bg-grey-1">
    <q-card class="shadow-3">
      <q-card-section class="bg-primary text-white row items-center justify-between">
        <div>
          <div class="text-h6">Razas</div>
          <div class="text-caption">Alta, edicion y baja del catalogo de razas.</div>
        </div>
        <div class="row q-gutter-sm">
          <q-btn outline color="white" icon="refresh" label="Recargar" :loading="loading" @click="loadData" />
          <q-btn color="positive" icon="add" label="Nueva raza" @click="openCreate" />
        </div>
      </q-card-section>

      <q-card-section>
        <q-input v-model="filter" outlined dense debounce="300" placeholder="Buscar raza..." class="q-mb-md" />

        <q-table
          :rows="filteredRows"
          :columns="columns"
          row-key="id"
          :loading="loading"
          flat
          bordered
          dense
        >
          <template #body-cell-especie="props">
            <q-td :props="props">
              {{ props.row.especie?.nombre || '-' }}
            </q-td>
          </template>
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
      <q-card style="min-width: 460px; width: 100%; max-width: 620px;">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">{{ form.id ? 'Modificar raza' : 'Registrar raza' }}</div>
        </q-card-section>

        <q-form @submit.prevent="save">
          <q-card-section class="q-gutter-md">
            <q-input v-model="form.nombre" label="Nombre" outlined dense maxlength="255" />
            <q-select
              v-model="form.especie_id"
              :options="especieOptions"
              label="Especie"
              outlined
              dense
              emit-value
              map-options
            />
            <q-input v-model="form.descrpcion" type="textarea" autogrow label="Descripcion" outlined dense />
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
  nombre: '',
  descrpcion: '',
  especie_id: null
})

export default {
  name: 'RazasPage',
  data () {
    return {
      loading: false,
      saving: false,
      filter: '',
      rows: [],
      especies: [],
      dialog: false,
      form: emptyForm(),
      columns: [
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left', sortable: true },
        { name: 'especie', label: 'Especie', field: row => row.especie?.nombre || '-', align: 'left' },
        { name: 'descrpcion', label: 'Descripcion', field: 'descrpcion', align: 'left' },
        { name: 'actions', label: 'Acciones', field: 'actions', align: 'right' }
      ]
    }
  },
  computed: {
    especieOptions () {
      return this.especies.map(especie => ({
        label: `${especie.nombre} (${especie.codigo})`,
        value: especie.id
      }))
    },
    filteredRows () {
      const term = this.filter.trim().toLowerCase()
      if (!term) {
        return this.rows
      }

      return this.rows.filter(row => {
        return [
          row.nombre,
          row.descrpcion,
          row.especie?.nombre,
          row.especie?.codigo
        ].some(value => String(value || '').toLowerCase().includes(term))
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
        const [razasRes, especiesRes] = await Promise.all([
          this.$api.get('raza'),
          this.$api.get('especie')
        ])

        this.rows = Array.isArray(razasRes.data) ? razasRes.data : []
        this.especies = Array.isArray(especiesRes.data) ? especiesRes.data : []
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar las razas.')
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
        descrpcion: row.descrpcion || '',
        especie_id: row.especie_id || row.especie?.id || null
      }
      this.dialog = true
    },
    async save () {
      this.saving = true
      try {
        const payload = {
          nombre: this.form.nombre,
          descrpcion: this.form.descrpcion,
          especie_id: this.form.especie_id
        }

        const { data } = this.form.id
          ? await this.$api.put(`raza/${this.form.id}`, payload)
          : await this.$api.post('raza', payload)

        this.$q.notify({
          message: data.message || 'Guardado correctamente.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.dialog = false
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo guardar la raza.')
      } finally {
        this.saving = false
      }
    },
    confirmDelete (row) {
      this.$q.dialog({
        title: 'Eliminar raza',
        message: `Desea eliminar "${row.nombre}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => this.remove(row))
    },
    async remove (row) {
      try {
        const { data } = await this.$api.delete(`raza/${row.id}`)
        this.$q.notify({
          message: data.message || 'Raza eliminada.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo eliminar la raza.')
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
