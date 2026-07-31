<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Tipos de campania"
        subtitle="Registro y modificacion del catalogo de tipos de campania."
        icon="sym_r_campaign"
      >
        <template #actions>
          <q-btn outline color="primary" icon="sym_r_refresh" label="Recargar" :loading="loading" @click="loadData" />
          <q-btn v-if="store.bool_registrar_campania_tipos" color="primary" icon="sym_r_add" label="Nuevo tipo" @click="openCreate" />
        </template>
      </AppSectionHeader>

      <q-card class="app-soft-card app-table">
        <q-card-section class="q-pb-none">
          <q-input v-model="filter" outlined dense debounce="300" placeholder="Buscar tipo..." />
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
                <q-btn v-if="store.bool_modificar_campania_tipos" flat dense icon="sym_r_edit" color="primary" @click="openEdit(props.row)" />
                <q-btn v-if="store.bool_eliminar_campania_tipos" flat dense icon="sym_r_delete" color="negative" @click="confirmDelete(props.row)" />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <q-dialog v-model="dialog" persistent>
      <q-card class="app-soft-card" style="min-width: 420px; width: 100%; max-width: 520px;">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">{{ form.id ? 'Modificar tipo de campania' : 'Registrar tipo de campania' }}</div>
        </q-card-section>

        <q-form @submit.prevent="save">
          <q-card-section class="q-gutter-md">
            <q-input v-model="form.nombre" label="Nombre" outlined dense maxlength="255" />
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
  nombre: ''
})

export default {
  name: 'CampaniaTiposPage',
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

      return this.rows.filter(row => String(row.nombre || '').toLowerCase().includes(term))
    }
  },
  created () {
    if (!this.store.bool_campania_tipos) {
      this.$router.push('/home')
      return
    }

    this.loadData()
  },
  methods: {
    async loadData () {
      this.loading = true
      try {
        const { data } = await this.$api.get('campania-tipo')
        this.rows = Array.isArray(data) ? data : []
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar los tipos de campania.')
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
        nombre: row.nombre || ''
      }
      this.dialog = true
    },
    async save () {
      this.saving = true
      try {
        const payload = { nombre: this.form.nombre }

        const { data } = this.form.id
          ? await this.$api.put(`campania-tipo/${this.form.id}`, payload)
          : await this.$api.post('campania-tipo', payload)

        this.$q.notify({
          message: data.message || 'Guardado correctamente.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.dialog = false
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo guardar el tipo de campania.')
      } finally {
        this.saving = false
      }
    },
    confirmDelete (row) {
      this.$q.dialog({
        title: 'Eliminar tipo de campania',
        message: `Desea eliminar "${row.nombre}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => this.remove(row))
    },
    async remove (row) {
      try {
        const { data } = await this.$api.delete(`campania-tipo/${row.id}`)
        this.$q.notify({
          message: data.message || 'Tipo de campania eliminado.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo eliminar el tipo de campania.')
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
