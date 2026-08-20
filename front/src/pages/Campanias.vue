<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Campanias"
        subtitle="Registro, modificacion y anulacion de campanias."
        icon="sym_r_event"
      >
        <template #actions>
          <q-btn outline color="primary" icon="sym_r_refresh" label="Recargar" :loading="loading" @click="loadData" />
          <q-btn
            v-if="store.bool_registrar_campanias"
            color="primary"
            icon="sym_r_add"
            label="Nueva campania"
            @click="openCreate"
          />
        </template>
      </AppSectionHeader>

      <q-card class="app-soft-card app-table">
        <q-card-section class="q-pb-none">
          <q-input v-model="filter" outlined dense debounce="300" placeholder="Buscar campania..." />
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
            wrap-cells
          >
            <template #body-cell-fechas="props">
              <q-td :props="props">
                {{ formatDate(props.row.fec_ini) }} - {{ formatDate(props.row.fec_fin) }}
              </q-td>
            </template>

            <template #body-cell-tipo="props">
              <q-td :props="props">
                {{ tipoNombre(props.row) }}
              </q-td>
            </template>

            <template #body-cell-estado="props">
              <q-td :props="props">
                <q-badge :color="badgeColor(props.row)" text-color="white">
                  {{ estadoEtiqueta(props.row) }}
                </q-badge>
              </q-td>
            </template>

            <template #body-cell-actions="props">
              <q-td :props="props" class="text-right">
                <q-btn
                  v-if="store.bool_modificar_campanias && !isLocked(props.row)"
                  flat
                  dense
                  icon="sym_r_edit"
                  color="primary"
                  @click="openEdit(props.row)"
                />
                <q-btn
                  v-if="store.bool_anular_campanias && canAnular(props.row)"
                  flat
                  dense
                  icon="sym_r_block"
                  color="negative"
                  @click="confirmAnular(props.row)"
                />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <q-dialog v-model="dialog" persistent>
      <q-card class="app-soft-card" style="min-width: 520px; width: 100%; max-width: 760px;">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">{{ form.id ? 'Modificar campania' : 'Registrar campania' }}</div>
        </q-card-section>

        <q-form @submit.prevent="save">
          <q-card-section class="q-gutter-md">
            <div class="row q-col-gutter-md">
              <div class="col-12">
                <q-input v-model="form.nombre" :label="$requiredLabel('Nombre')" outlined dense maxlength="255" :disable="lockedForm" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.fec_ini" :label="$requiredLabel('Fecha inicio')" type="date" outlined dense :disable="lockedForm" />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.fec_fin" :label="$requiredLabel('Fecha fin')" type="date" outlined dense :disable="lockedForm" />
              </div>
              <div class="col-12">
                <q-input v-model="form.lugar" :label="$requiredLabel('Lugar')" outlined dense maxlength="255" :disable="lockedForm" />
              </div>
              <div class="col-12">
                <q-select
                  v-model="form.campania_tipo_id"
                  :options="campaniaTipos"
                  option-label="nombre"
                  option-value="id"
                  emit-value
                  map-options
                  :label="$requiredLabel('Tipo de campania')"
                  outlined
                  dense
                  :disable="lockedForm"
                />
              </div>
              <div class="col-12">
                <q-input v-model="form.descripcion" label="Descripcion" outlined dense type="textarea" :disable="lockedForm" />
              </div>
            </div>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancelar" color="negative" v-close-popup />
            <q-btn v-if="!lockedForm" :label="form.id ? 'Actualizar' : 'Guardar'" color="primary" type="submit" :loading="saving" />
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
  fec_ini: '',
  fec_fin: '',
  lugar: '',
  campania_tipo_id: null,
  descripcion: '',
  estado: 'ACTIVA'
})

export default {
  name: 'CampaniasPage',
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
      campaniaTipos: [],
      dialog: false,
      form: emptyForm(),
      columns: [
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left', sortable: true },
        { name: 'fechas', label: 'Fechas', field: 'fechas', align: 'left' },
        { name: 'tipo', label: 'Tipo', field: 'tipo', align: 'left' },
        { name: 'lugar', label: 'Lugar', field: 'lugar', align: 'left', sortable: true },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left' },
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
          row.nombre,
          row.lugar,
          this.tipoNombre(row),
          row.estado
        ].some(value => String(value || '').toLowerCase().includes(term))
      })
    },
    lockedForm () {
      return this.form.id ? this.isAnulada(this.form) : false
    }
  },
  created () {
    if (!this.store.isLoggedIn) {
      this.$router.push('/')
      return
    }

    if (!this.store.bool_campanias) {
      this.$router.push('/home')
      return
    }

    this.loadInitialData()
  },
  methods: {
    async loadInitialData () {
      await Promise.all([this.loadTipos(), this.loadData()])
    },
    async loadTipos () {
      if (!this.store.bool_campanias) {
        return
      }

      try {
        const { data } = await this.$api.get('campania-tipo')
        this.campaniaTipos = Array.isArray(data) ? data : []
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar los tipos de campania.')
      }
    },
    async loadData () {
      if (!this.store.bool_campanias) {
        return
      }

      this.loading = true
      try {
        const { data } = await this.$api.get('campania')
        this.rows = Array.isArray(data) ? data : []
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar las campanias.')
        if (error?.response?.status === 403) {
          this.$router.push('/home')
        }
      } finally {
        this.loading = false
      }
    },
    openCreate () {
      if (!this.store.bool_registrar_campanias) {
        return
      }

      this.form = emptyForm()
      this.dialog = true
    },
    openEdit (row) {
      if (!this.store.bool_modificar_campanias || this.isLocked(row)) {
        return
      }

      this.form = {
        id: row.id,
        nombre: row.nombre || '',
        fec_ini: this.formatDate(row.fec_ini),
        fec_fin: this.formatDate(row.fec_fin),
        lugar: row.lugar || '',
        campania_tipo_id: row.campania_tipo_id || null,
        descripcion: row.descripcion || '',
        estado: row.estado || 'ACTIVA'
      }
      this.dialog = true
    },
    async save () {
      if (this.form.id ? !this.store.bool_modificar_campanias : !this.store.bool_registrar_campanias) {
        return
      }

      this.saving = true
      try {
        const payload = {
          nombre: this.form.nombre,
          fec_ini: this.form.fec_ini,
          fec_fin: this.form.fec_fin,
          lugar: this.form.lugar,
          campania_tipo_id: this.form.campania_tipo_id,
          descripcion: this.form.descripcion,
          estado: this.form.estado
        }

        const { data } = this.form.id
          ? await this.$api.put(`campania/${this.form.id}`, payload)
          : await this.$api.post('campania', payload)

        this.$q.notify({
          message: data.message || 'Guardado correctamente.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.dialog = false
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo guardar la campania.')
      } finally {
        this.saving = false
      }
    },
    confirmAnular (row) {
      if (!this.store.bool_anular_campanias || !this.canAnular(row)) {
        return
      }

      this.$q.dialog({
        title: 'Anular campania',
        message: `Desea anular "${row.nombre}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => this.anular(row))
    },
    async anular (row) {
      try {
        const { data } = await this.$api.put(`campania/${row.id}/anular`)
        this.$q.notify({
          message: data.message || 'Campania anulada.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo anular la campania.')
      }
    },
    formatDate (value) {
      if (!value) {
        return ''
      }

      return String(value).slice(0, 10)
    },
    tipoNombre (row) {
      return row?.campaniaTipo?.nombre || row?.campania_tipo?.nombre || 'Sin tipo'
    },
    estadoEtiqueta (row) {
      if (this.isAnulada(row)) {
        return 'ANULADA'
      }

      if (this.isExpired(row)) {
        return 'FINALIZADA'
      }

      return (row.estado || 'ACTIVA').toUpperCase()
    },
    badgeColor (row) {
      if (this.isAnulada(row)) {
        return 'grey-7'
      }

      if (this.isExpired(row)) {
        return 'orange'
      }

      return 'positive'
    },
    isExpired (row) {
      if (!row?.fec_fin) {
        return false
      }

      const end = new Date(`${this.formatDate(row.fec_fin)}T23:59:59`)
      return new Date() > end
    },
    isAnulada (row) {
      return String(row?.estado || '').toUpperCase() === 'ANULADA'
    },
    isLocked (row) {
      return this.isExpired(row) || this.isAnulada(row)
    },
    canAnular (row) {
      return !this.isLocked(row) && String(row?.estado || '').toUpperCase() === 'ACTIVA'
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
