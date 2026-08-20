<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Procesos"
        subtitle="Define el flujo de seguimiento de denuncias, el orden consecutivo y el color de estado."
        icon="sym_r_timeline"
      >
        <template #actions>
          <q-btn outline color="primary" icon="sym_r_refresh" label="Recargar" :loading="loading" @click="loadData" />
          <q-btn v-if="store.bool_registrar_procesos" color="primary" icon="sym_r_add" label="Nuevo proceso" @click="openCreate" />
        </template>
      </AppSectionHeader>

      <q-card class="app-soft-card app-table">
        <q-card-section class="q-pb-none">
          <q-input v-model="filter" outlined dense debounce="300" placeholder="Buscar proceso, orden o color..." />
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
            <template #body-cell-orden="props">
              <q-td :props="props">
                <q-badge color="grey-8" rounded>{{ props.row.orden }}</q-badge>
              </q-td>
            </template>

            <template #body-cell-color="props">
              <q-td :props="props">
                <q-badge :color="props.row.color || 'primary'" text-color="white" rounded>
                  {{ props.row.color || 'primary' }}
                </q-badge>
              </q-td>
            </template>

            <template #body-cell-actions="props">
              <q-td :props="props" class="text-right">
                <q-btn
                  v-if="store.bool_modificar_procesos"
                  flat
                  dense
                  icon="sym_r_edit"
                  color="primary"
                  @click="openEdit(props.row)"
                />
                <q-btn
                  v-if="store.bool_eliminar_procesos"
                  flat
                  dense
                  icon="sym_r_delete"
                  color="negative"
                  @click="confirmDelete(props.row)"
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
          <div class="text-h6">{{ form.id ? 'Modificar proceso' : 'Registrar proceso' }}</div>
        </q-card-section>

        <q-form @submit.prevent="save">
          <q-card-section class="q-gutter-md">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-select
                  v-model="form.orden"
                  :options="orderOptions"
                  option-label="label"
                  option-value="value"
                  emit-value
                  map-options
                  :label="$requiredLabel('Orden')"
                  outlined
                  dense
                  :disable="!orderOptions.length"
                >
                  <template #selected-item="scope">
                    <q-chip dense square color="grey-8" text-color="white">{{ scope.opt.label }}</q-chip>
                  </template>
                </q-select>
              </div>
              <div class="col-12 col-md-8">
                <q-select
                  v-model="form.color"
                  :options="colorOptions"
                  option-label="label"
                  option-value="value"
                  emit-value
                  map-options
                  :label="$requiredLabel('Color')"
                  outlined
                  dense
                >
                  <template #option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section avatar>
                        <q-badge :color="scope.opt.value" rounded />
                      </q-item-section>
                      <q-item-section>
                        <q-item-label>{{ scope.opt.label }}</q-item-label>
                        <q-item-label caption>{{ scope.opt.value }}</q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                  <template #selected-item="scope">
                    <q-chip dense square :color="scope.opt.value" text-color="white">{{ scope.opt.label }}</q-chip>
                  </template>
                </q-select>
              </div>
              <div class="col-12">
                <q-input v-model="form.descripcion" :label="$requiredLabel('Descripcion')" outlined dense maxlength="255" />
              </div>
            </div>
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

const COLOR_OPTIONS = [
  { label: 'Primario', value: 'primary' },
  { label: 'Secundario', value: 'secondary' },
  { label: 'Acento', value: 'accent' },
  { label: 'Positivo', value: 'positive' },
  { label: 'Negativo', value: 'negative' },
  { label: 'Advertencia', value: 'warning' },
  { label: 'Informacion', value: 'info' },
  { label: 'Oscuro', value: 'dark' },
  { label: 'Gris', value: 'grey-7' },
  { label: 'Azul', value: 'indigo' },
  { label: 'Verde agua', value: 'teal' },
  { label: 'Naranja', value: 'orange' },
  { label: 'Naranja oscuro', value: 'deep-orange' },
  { label: 'Morado', value: 'purple' },
  { label: 'Rosa', value: 'pink' }
]

const emptyForm = () => ({
  id: null,
  orden: 1,
  descripcion: '',
  color: 'primary'
})

export default {
  name: 'ProcesosPage',
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
        { name: 'orden', label: 'Orden', field: 'orden', align: 'left', sortable: true },
        { name: 'descripcion', label: 'Descripcion', field: 'descripcion', align: 'left', sortable: true },
        { name: 'color', label: 'Color', field: 'color', align: 'left' },
        { name: 'actions', label: 'Acciones', field: 'actions', align: 'right' }
      ]
    }
  },
  computed: {
    filteredRows () {
      const term = this.normalizeText(this.filter)
      if (!term) {
        return this.rows
      }

      return this.rows.filter(row => {
        return [
          row.orden,
          row.descripcion,
          row.color
        ].some(value => this.normalizeText(value).includes(term))
      })
    },
    colorOptions () {
      return COLOR_OPTIONS
    },
    orderOptions () {
      const total = this.rows.length + (this.form.id ? 0 : 1)
      const limit = Math.max(total, 1)

      return Array.from({ length: limit }, (_, index) => ({
        label: String(index + 1),
        value: index + 1
      }))
    }
  },
  created () {
    if (!this.store.isLoggedIn) {
      this.$router.push('/')
      return
    }

    if (!this.store.bool_procesos) {
      this.$router.push('/home')
      return
    }

    this.loadData()
  },
  methods: {
    async loadData () {
      if (!this.store.bool_procesos) {
        return
      }

      this.loading = true
      try {
        const { data } = await this.$api.get('proceso')
        this.rows = Array.isArray(data) ? data : []

        if (this.form.id) {
          const current = this.rows.find(row => Number(row.id) === Number(this.form.id))
          if (current) {
            this.form.orden = Number(current.orden) || 1
          }
        }
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar los procesos.')
        if (error?.response?.status === 403) {
          this.$router.push('/home')
        }
      } finally {
        this.loading = false
      }
    },
    openCreate () {
      if (!this.store.bool_registrar_procesos) {
        return
      }

      this.form = {
        ...emptyForm(),
        orden: this.rows.length + 1,
        color: 'primary'
      }
      this.dialog = true
    },
    openEdit (row) {
      if (!this.store.bool_modificar_procesos) {
        return
      }

      this.form = {
        id: row.id,
        orden: Number(row.orden) || 1,
        descripcion: row.descripcion || '',
        color: row.color || 'primary'
      }
      this.dialog = true
    },
    async save () {
      if (this.form.id ? !this.store.bool_modificar_procesos : !this.store.bool_registrar_procesos) {
        return
      }

      this.saving = true
      try {
        const payload = {
          orden: this.form.orden,
          descripcion: this.form.descripcion,
          color: this.form.color
        }

        const { data } = this.form.id
          ? await this.$api.put(`proceso/${this.form.id}`, payload)
          : await this.$api.post('proceso', payload)

        this.$q.notify({
          message: data.message || 'Proceso guardado.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.dialog = false
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo guardar el proceso.')
      } finally {
        this.saving = false
      }
    },
    confirmDelete (row) {
      if (!this.store.bool_eliminar_procesos) {
        return
      }

      this.$q.dialog({
        title: 'Eliminar proceso',
        message: `Desea eliminar "${row.descripcion}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => this.deleteRow(row))
    },
    async deleteRow (row) {
      if (!this.store.bool_eliminar_procesos) {
        return
      }

      try {
        const { data } = await this.$api.delete(`proceso/${row.id}`)
        this.$q.notify({
          message: data.message || 'Proceso eliminado.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        await this.loadData()
      } catch (error) {
        this.notifyError(error, error?.response?.data?.message || 'No se pudo eliminar el proceso.')
      }
    },
    normalizeText (value) {
      return String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toUpperCase()
        .trim()
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
