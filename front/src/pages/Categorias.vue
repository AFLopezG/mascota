<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Categorias"
        subtitle="Registro y modificacion del catalogo de categorias."
        icon="sym_r_category"
      >
        <template #actions>
          <q-btn outline color="primary" icon="sym_r_refresh" label="Recargar" :loading="loading" @click="loadData" />
          <q-btn v-if="store.bool_registrar_categorias" color="primary" icon="sym_r_add" label="Nueva categoria" @click="openCreate" />
        </template>
      </AppSectionHeader>

      <q-card class="app-soft-card app-table">
        <q-card-section class="q-pb-none">
          <q-input v-model="filter" outlined dense debounce="300" placeholder="Buscar categoria..." />
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
                <q-btn v-if="store.bool_modificar_categorias" flat dense icon="sym_r_edit" color="primary" @click="openEdit(props.row)" />
                <q-btn v-if="store.bool_eliminar_categorias" flat dense icon="sym_r_delete" color="negative" @click="confirmDelete(props.row)" />
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>

    <q-dialog v-model="dialog" persistent>
      <q-card class="app-soft-card" style="min-width: 420px; width: 100%; max-width: 520px;">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">{{ form.id ? 'Modificar categoria' : 'Registrar categoria' }}</div>
        </q-card-section>

        <q-form @submit.prevent="save">
          <q-card-section class="q-gutter-md">
            <q-input v-model="form.nombre" :label="$requiredLabel('Nombre')" outlined dense maxlength="255" />
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
  name: 'CategoriasPage',
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
    if (!this.store.bool_categorias) {
      this.$router.push('/home')
      return
    }

    this.loadData()
  },
  methods: {
    async loadData () {
      this.loading = true
      try {
        const { data } = await this.$api.get('categoria')
        this.rows = Array.isArray(data) ? data : []
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar las categorias.')
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
          ? await this.$api.put(`categoria/${this.form.id}`, payload)
          : await this.$api.post('categoria', payload)

        this.$q.notify({
          message: data.message || 'Guardado correctamente.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.dialog = false
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo guardar la categoria.')
      } finally {
        this.saving = false
      }
    },
    confirmDelete (row) {
      this.$q.dialog({
        title: 'Eliminar categoria',
        message: `Desea eliminar "${row.nombre}"?`,
        cancel: true,
        persistent: true
      }).onOk(() => this.remove(row))
    },
    async remove (row) {
      try {
        const { data } = await this.$api.delete(`categoria/${row.id}`)
        this.$q.notify({
          message: data.message || 'Categoria eliminada.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo eliminar la categoria.')
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
