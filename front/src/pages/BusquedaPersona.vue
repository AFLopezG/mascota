<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row q-col-gutter-md">
      <div class="col-12 col-lg-4">
        <q-card class="shadow-2">
          <q-card-section class="bg-secondary text-white">
            <div class="text-h6">Buscar persona</div>
            <div class="text-caption">Filtra por CINIT, nombre o apellidos.</div>
          </q-card-section>

          <q-card-section class="q-gutter-md">
            <q-input v-model="filtros.cinit" label="CINIT" outlined dense />
            <q-input v-model="filtros.nombre" label="Nombre" outlined dense />
            <q-input v-model="filtros.paterno" label="Paterno" outlined dense />
            <q-input v-model="filtros.materno" label="Materno" outlined dense />
            <div class="row justify-end q-gutter-sm">
              <q-btn outline color="primary" label="Limpiar" @click="limpiarFiltro" />
              <q-btn color="primary" icon="search" label="Buscar" :loading="buscando" @click="buscar" />
            </div>
          </q-card-section>
        </q-card>

        <q-card class="shadow-2 q-mt-md">
          <q-card-section>
            <q-table
              :rows="personas"
              :columns="columns"
              row-key="id"
              dense
              flat
              :loading="buscando"
              :pagination="{ rowsPerPage: 8 }"
              @row-click="seleccionarPersona"
            />
          </q-card-section>
        </q-card>
      </div>

      <div class="col-12 col-lg-8">
        <q-card class="shadow-2">
          <q-card-section class="bg-primary text-white">
            <div class="text-h6">Detalle de persona</div>
            <div class="text-caption">
              {{ personaSeleccionada ? personaNombre(personaSeleccionada) : 'Seleccione un resultado para ver mascotas y vacunas.' }}
            </div>
          </q-card-section>

          <q-card-section v-if="personaSeleccionada">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-input :model-value="personaSeleccionada.cinit" label="CINIT" dense outlined readonly />
              </div>
              <div class="col-12 col-md-4">
                <q-input :model-value="personaSeleccionada.complemento" label="Complemento" dense outlined readonly />
              </div>
              <div class="col-12 col-md-4">
                <q-input :model-value="personaNombre(personaSeleccionada)" label="Nombre completo" dense outlined readonly />
              </div>
              <div class="col-12 col-md-8">
                <q-input :model-value="personaSeleccionada.direccion" label="Direccion" dense outlined readonly />
              </div>
              <div class="col-12 col-md-4">
                <q-input :model-value="personaSeleccionada.telefono" label="Telefono" dense outlined readonly />
              </div>
              <div class="col-12 col-md-4">
                <q-input :model-value="personaSeleccionada.correo" label="Correo" dense outlined readonly />
              </div>
              <div class="col-12 col-md-4">
                <q-input :model-value="personaSeleccionada.zona" label="Zona" dense outlined readonly />
              </div>
              <div class="col-12 col-md-4">
                <q-input :model-value="personaSeleccionada.distrito" label="Distrito" dense outlined readonly />
              </div>
              <div class="col-12 col-md-4">
                <q-input :model-value="personaSeleccionada.fecha" label="Fecha" dense outlined readonly />
              </div>
            </div>

            <q-separator class="q-my-md" />

            <div class="text-subtitle1 q-mb-sm">Mascotas</div>
            <q-list bordered separator>
              <q-expansion-item
                v-for="mascota in personaSeleccionada.mascotas || []"
                :key="mascota.id"
                :label="`${mascota.nombre} - ${mascota.codigo || 'SIN CODIGO'}`"
                :caption="`${mascota.especie || mascota.raza?.especie?.nombre || '-'} | ${mascota.raza?.nombre || '-'} | ${mascota.categoria?.nombre || '-'} | ${mascota.estado || '-'}`"
                expand-separator
              >
                <q-card flat bordered class="q-ma-sm">
                  <q-card-section>
                    <div class="row q-col-gutter-md">
                      <div class="col-12 col-md-3">
                        <q-input :model-value="mascota.especie || mascota.raza?.especie?.nombre" label="Especie" dense outlined readonly />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input :model-value="mascota.raza?.nombre" label="Raza" dense outlined readonly />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input :model-value="mascota.color_principal" label="Color principal" dense outlined readonly />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input :model-value="mascota.estado" label="Estado" dense outlined readonly />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input :model-value="mascota.categoria?.nombre" label="Categoria" dense outlined readonly />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input :model-value="mascota.color_secundario" label="Color secundario" dense outlined readonly />
                      </div>
                    </div>

                    <div class="row justify-end q-mt-md">
                      <q-btn color="positive" icon="vaccines" label="Registrar vacuna" @click="abrirVacuna(mascota)" />
                    </div>

                    <q-table
                      class="q-mt-md"
                      :rows="mascota.vacunas || []"
                      :columns="vacunaColumns"
                      row-key="id"
                      dense
                      flat
                      title="Vacunas registradas"
                      :pagination="{ rowsPerPage: 5 }"
                    />
                  </q-card-section>
                </q-card>
              </q-expansion-item>
            </q-list>
          </q-card-section>

          <q-card-section v-else class="text-grey-7">
            No hay una persona seleccionada.
          </q-card-section>
        </q-card>
      </div>
    </div>

    <q-dialog v-model="showVacuna" persistent full-width>
      <q-card>
        <q-card-section class="bg-positive text-white">
          <div class="text-h6">Registrar vacuna</div>
          <div class="text-caption">{{ mascotaVacuna ? mascotaVacuna.nombre : '' }}</div>
        </q-card-section>

        <q-card-section>
          <q-form class="q-gutter-md" @submit.prevent="guardarVacuna">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-input v-model="vacunaForm.fecha" type="date" label="Fecha" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="vacunaForm.tipo" label="Tipo" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="vacunaForm.lugar" label="Lugar" outlined dense />
              </div>
              <div class="col-12">
                <q-input v-model="vacunaForm.observacion" type="textarea" autogrow label="Observacion" outlined dense />
              </div>
            </div>

            <div class="row justify-end q-gutter-sm">
              <q-btn flat label="Cancelar" color="negative" v-close-popup />
              <q-btn color="positive" type="submit" :loading="guardandoVacuna" label="Guardar vacuna" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'

export default {
  name: 'BusquedaPersona',
  data () {
    return {
      buscando: false,
      guardandoVacuna: false,
      personas: [],
      personaSeleccionada: null,
      showVacuna: false,
      mascotaVacuna: null,
      filtros: {
        cinit: '',
        nombre: '',
        paterno: '',
        materno: ''
      },
      vacunaForm: {
        mascota_id: null,
        fecha: moment().format('YYYY-MM-DD'),
        tipo: '',
        lugar: '',
        observacion: ''
      },
      columns: [
        { name: 'cinit', label: 'CINIT', field: 'cinit', align: 'left', sortable: true },
        { name: 'nombre', label: 'NOMBRE', field: row => `${row.nombre || ''} ${row.paterno || ''}`.trim(), align: 'left' },
        { name: 'telefono', label: 'TELEFONO', field: 'telefono', align: 'left' },
        { name: 'mascotas', label: 'MASCOTAS', field: row => (row.mascotas || []).length, align: 'center' }
      ],
      vacunaColumns: [
        { name: 'fecha', label: 'FECHA', field: 'fecha', align: 'left' },
        { name: 'tipo', label: 'TIPO', field: 'tipo', align: 'left' },
        { name: 'lugar', label: 'LUGAR', field: 'lugar', align: 'left' },
        { name: 'observacion', label: 'OBSERVACION', field: 'observacion', align: 'left' }
      ]
    }
  },
  methods: {
    async buscar () {
      try {
        this.buscando = true
        const { data } = await this.$api.get('persona', {
          params: this.filtros
        })
        this.personas = data || []
        this.personaSeleccionada = this.personas[0] || null
      } catch (error) {
        this.$q.notify({
          color: 'negative',
          message: error?.response?.data?.message || 'No se pudo buscar la persona'
        })
      } finally {
        this.buscando = false
      }
    },
    limpiarFiltro () {
      this.filtros = {
        cinit: '',
        nombre: '',
        paterno: '',
        materno: ''
      }
      this.personas = []
      this.personaSeleccionada = null
    },
    seleccionarPersona (_evt, row) {
      this.personaSeleccionada = row
    },
    personaNombre (persona) {
      return [persona?.nombre, persona?.paterno, persona?.materno].filter(Boolean).join(' ')
    },
    abrirVacuna (mascota) {
      this.mascotaVacuna = mascota
      this.vacunaForm = {
        mascota_id: mascota.id,
        fecha: moment().format('YYYY-MM-DD'),
        tipo: '',
        lugar: '',
        observacion: ''
      }
      this.showVacuna = true
    },
    async guardarVacuna () {
      try {
        this.guardandoVacuna = true
        await this.$api.post('vacuna', this.vacunaForm)
        this.$q.notify({
          color: 'positive',
          message: 'Vacuna registrada'
        })
        this.showVacuna = false
        await this.buscar()
      } catch (error) {
        this.$q.notify({
          color: 'negative',
          message: error?.response?.data?.message || 'No se pudo registrar la vacuna'
        })
      } finally {
        this.guardandoVacuna = false
      }
    }
  }
}
</script>
