<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Busqueda de persona"
        subtitle="Busca por cedula, selecciona la persona y gestiona mascotas y vacunas."
        icon="sym_r_manage_search"
      />

      <div class="row q-col-gutter-lg">
        <div class="col-12 col-lg-4">
          <q-card class="app-soft-card">
            <q-card-section class="bg-secondary text-white">
              <div class="text-h6">Buscar por cedula</div>
              <div class="text-caption text-white-7">
                Escribe la cedula y carga hasta 20 coincidencias.
              </div>
            </q-card-section>

            <q-card-section class="q-gutter-md">
              <q-select
                v-model="personaSeleccionadaId"
                :options="personaOptions"
                option-label="label"
                option-value="id"
                emit-value
                map-options
                label="Cedula o nombre"
                outlined
                dense
                clearable
                use-input
                fill-input
                hide-selected
                input-debounce="300"
                :loading="buscando"
                hint="Escribe cédula, nombre paterno o materno para buscar."
                @filter="filtrarPersonas"
                @update:model-value="cargarPersonaSeleccionada"
              />

              <q-banner v-if="!personaOptions.length && !buscando" rounded class="bg-blue-1 text-blue-10">
                Escribe para buscar personas.
              </q-banner>

              <div class="row justify-end">
                <q-btn outline color="primary" label="Limpiar" @click="limpiar" />
              </div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-lg-8">
          <q-card class="app-soft-card">
            <q-card-section class="bg-primary text-white">
              <div class="text-h6">Detalle de persona</div>
              <div class="text-caption text-white-7">
                {{ personaSeleccionada ? personaEtiqueta(personaSeleccionada) : 'Seleccione una persona para ver mascotas y vacunas.' }}
              </div>
            </q-card-section>

            <q-card-section v-if="personaSeleccionada" class="q-gutter-lg">
              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-4">
                  <q-input :model-value="personaSeleccionada.cinit" label="Cedula" dense outlined readonly />
                </div>
                <div class="col-12 col-md-4">
                  <q-input :model-value="personaSeleccionada.complemento" label="Complemento" dense outlined readonly />
                </div>
                <div class="col-12 col-md-4">
                  <q-input :model-value="personaEtiqueta(personaSeleccionada)" label="Nombre completo" dense outlined readonly />
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

              <q-separator />

              <div class="row items-center justify-between q-gutter-sm">
                <div>
                  <div class="text-subtitle1 text-weight-bold">Mascotas</div>
                  <div class="text-caption text-grey-7">Selecciona una mascota para registrar una vacuna.</div>
                </div>
                <q-badge color="primary" rounded>{{ mascotasPersona.length }} mascotas</q-badge>
              </div>

              <q-banner v-if="!mascotasPersona.length" rounded class="bg-blue-1 text-blue-10">
                Esta persona no tiene mascotas registradas.
              </q-banner>

              <q-list v-else bordered separator class="rounded-borders">
                <q-expansion-item
                  v-for="mascota in mascotasPersona"
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
                        <q-btn color="primary" icon="sym_r_vaccines" label="Registrar vacuna" @click="abrirVacuna(mascota)" />
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
                        no-data-label="Sin vacunas registradas"
                      />
                    </q-card-section>
                  </q-card>
                </q-expansion-item>
              </q-list>
            </q-card-section>

            <q-card-section v-else class="text-grey-7">
              Escribe una cedula, ejecuta la busqueda y selecciona una persona para continuar.
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <q-dialog v-model="showVacuna" persistent full-width>
      <q-card class="app-soft-card">
        <q-card-section class="bg-positive text-white">
          <div class="text-h6">Registrar vacuna</div>
          <div class="text-caption text-white-7">{{ mascotaVacuna ? mascotaVacuna.nombre : '' }}</div>
        </q-card-section>

        <q-card-section>
          <q-form class="q-gutter-md" @submit.prevent="guardarVacuna">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-input v-model="vacunaForm.fecha" type="date" label="Fecha" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="vacunaForm.fecha_prox" type="date" label="Fecha prox" outlined dense hint="Opcional" />
              </div>
              <div class="col-12 col-md-4">
                <q-select
                  v-model="vacunaForm.campania_id"
                  :options="campaniaOptions"
                  option-label="label"
                  option-value="value"
                  emit-value
                  map-options
                  label="Campania de vacunacion"
                  outlined
                  dense
                  clearable
                  :rules="[val => !!val || 'Seleccione una campania vigente']"
                />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="vacunaForm.tipo" label="Tipo" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="vacunaForm.lugar" label="Lugar" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="vacunaForm.num_lote" label="Numero de lote" outlined dense hint="Opcional" />
              </div>
              <div class="col-12">
                <q-input v-model="vacunaForm.observacion" type="textarea" autogrow label="Observacion" outlined dense />
              </div>
            </div>

            <div class="row justify-end q-gutter-sm">
              <q-btn flat label="Cancelar" color="negative" v-close-popup />
              <q-btn color="primary" type="submit" :loading="guardandoVacuna" label="Guardar vacuna" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import moment from 'moment'
import AppSectionHeader from 'components/AppSectionHeader.vue'

export default {
  name: 'BusquedaPersona',
  components: {
    AppSectionHeader
  },
  data () {
    return {
      buscando: false,
      guardandoVacuna: false,
      personaOptions: [],
      campaniaOptions: [],
      personaSeleccionadaId: null,
      personaSeleccionada: null,
      showVacuna: false,
      mascotaVacuna: null,
      searchSeq: 0,
      vacunaForm: {
        mascota_id: null,
        fecha: moment().format('YYYY-MM-DD'),
        fecha_prox: '',
        tipo: '',
        lugar: '',
        num_lote: '',
        campania_id: null,
        observacion: ''
      },
      vacunaColumns: [
        { name: 'fecha', label: 'FECHA', field: 'fecha', align: 'left' },
        { name: 'fecha_prox', label: 'FECHA PROX', field: 'fecha_prox', align: 'left' },
        { name: 'tipo', label: 'TIPO', field: 'tipo', align: 'left' },
        { name: 'lugar', label: 'LUGAR', field: 'lugar', align: 'left' },
        { name: 'num_lote', label: 'LOTE', field: 'num_lote', align: 'left' },
        { name: 'campania', label: 'CAMPANIA', field: row => row.campania?.nombre || '', align: 'left' },
        { name: 'observacion', label: 'OBSERVACION', field: 'observacion', align: 'left' }
      ]
    }
  },
  computed: {
    mascotasPersona () {
      return Array.isArray(this.personaSeleccionada?.mascotas) ? this.personaSeleccionada.mascotas : []
    }
  },
  methods: {
    async cargarCampaniasVigentes () {
      try {
        const { data } = await this.$api.get('campania', {
          params: {
            vigentes: 1
          }
        })

        const campanias = Array.isArray(data) ? data : []
        this.campaniaOptions = campanias.map(campania => ({
          label: `${campania.nombre}${campania.fec_ini ? ` (${moment(campania.fec_ini).format('DD/MM/YYYY')})` : ''}`,
          value: campania.id
        }))
      } catch (error) {
        this.campaniaOptions = []
        this.$q.notify({
          color: 'negative',
          message: error?.response?.data?.message || 'No se pudieron cargar las campanas vigentes'
        })
      }
    },
    async filtrarPersonas (val, update, abort) {
      const term = String(val || '').trim()

      if (!term) {
        update(() => {
          this.personaOptions = []
        })
        return
      }

      const currentSeq = ++this.searchSeq

      try {
        this.buscando = true
        const { data } = await this.$api.get('persona', {
          params: {
            q: term,
            limit: 20
          }
        })

        if (currentSeq !== this.searchSeq) {
          abort()
          return
        }

        const personas = Array.isArray(data) ? data : []
        update(() => {
          this.personaOptions = personas.map(persona => ({
            ...persona,
            label: this.personaEtiqueta(persona)
          }))
        })
      } catch (error) {
        if (currentSeq === this.searchSeq) {
          update(() => {
            this.personaOptions = []
          })
          this.$q.notify({
            color: 'negative',
            message: error?.response?.data?.message || 'No se pudo buscar la persona'
          })
        } else {
          abort()
        }
      } finally {
        if (currentSeq === this.searchSeq) {
          this.buscando = false
        }
      }
    },
    limpiar () {
      this.limpiarResultados()
    },
    limpiarResultados () {
      this.personaOptions = []
      this.personaSeleccionadaId = null
      this.personaSeleccionada = null
      this.showVacuna = false
      this.mascotaVacuna = null
      this.searchSeq += 1
    },
    async cargarPersonaSeleccionada (personaId) {
      if (!personaId) {
        this.personaSeleccionada = null
        return
      }

      try {
        this.buscando = true
        const { data } = await this.$api.get(`persona/${personaId}`)
        this.personaSeleccionada = data?.data || null
      } catch (error) {
        this.personaSeleccionada = null
        this.$q.notify({
          color: 'negative',
          message: error?.response?.data?.message || 'No se pudo cargar la persona seleccionada'
        })
      } finally {
        this.buscando = false
      }
    },
    personaEtiqueta (persona) {
      const nombreCompleto = [persona?.nombre, persona?.paterno, persona?.materno]
        .filter(Boolean)
        .join(' ')

      return `${persona?.cinit || ''} - ${nombreCompleto || 'Sin nombre'}`
    },
    abrirVacuna (mascota) {
      this.mascotaVacuna = mascota
      this.vacunaForm = {
        mascota_id: mascota.id,
        fecha: moment().format('YYYY-MM-DD'),
        fecha_prox: '',
        tipo: '',
        lugar: '',
        num_lote: '',
        campania_id: null,
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

        if (this.personaSeleccionadaId) {
          await this.cargarPersonaSeleccionada(this.personaSeleccionadaId)
        }
      } catch (error) {
        this.$q.notify({
          color: 'negative',
          message: error?.response?.data?.message || 'No se pudo registrar la vacuna'
        })
      } finally {
        this.guardandoVacuna = false
      }
    }
  },
  async mounted () {
    await this.cargarCampaniasVigentes()
  }
}
</script>
