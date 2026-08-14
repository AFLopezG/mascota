<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Reporte de vacunas"
        subtitle="Consulta registros por rango de fechas y revisa los totales por especie, lugar, centro de salud y condicion de menor."
        icon="sym_r_assessment"
      >
        <template #actions>
          <q-btn outline color="primary" icon="sym_r_refresh" label="Actualizar" :loading="loading" @click="loadReport" />
          <q-btn color="primary" icon="sym_r_search" label="Consultar" :loading="loading" @click="loadReport" />
        </template>
      </AppSectionHeader>

      <q-card class="app-soft-card">
        <q-card-section>
          <div class="row q-col-gutter-md items-end">
            <div class="col-12 col-md-3">
              <q-input v-model="filters.fecha_desde" type="date" :label="$requiredLabel('Fecha desde')" outlined dense />
            </div>
            <div class="col-12 col-md-3">
              <q-input v-model="filters.fecha_hasta" type="date" :label="$requiredLabel('Fecha hasta')" outlined dense />
            </div>
            <div class="col-12 col-md-6">
              <div class="row q-gutter-sm justify-end">
                <q-btn outline color="grey-8" label="Limpiar" @click="resetFilters" />
                <q-btn color="primary" label="Filtrar" :loading="loading" @click="loadReport" />
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <div class="row q-col-gutter-md">
        <div class="col-12 col-md-3">
          <q-card class="app-soft-card q-pa-lg">
            <div class="text-caption text-grey-6">Registros</div>
            <div class="text-h4 text-weight-bold">{{ summary.total }}</div>
            <div class="text-caption text-grey-7 q-mt-sm">Total de vacunas en el rango.</div>
          </q-card>
        </div>
        <div class="col-12 col-md-3">
          <q-card class="app-soft-card q-pa-lg">
            <div class="text-caption text-grey-6">Especies</div>
            <div class="text-h4 text-weight-bold">{{ summarySpeciesRows.length }}</div>
            <div class="text-caption text-grey-7 q-mt-sm">Categorias distintas encontradas.</div>
          </q-card>
        </div>
        <div class="col-12 col-md-3">
          <q-card class="app-soft-card q-pa-lg">
            <div class="text-caption text-grey-6">Lugares</div>
            <div class="text-h4 text-weight-bold">{{ summaryPlaceRows.length }}</div>
            <div class="text-caption text-grey-7 q-mt-sm">Puntos de vacunacion distintos.</div>
          </q-card>
        </div>
        <div class="col-12 col-md-3">
          <q-card class="app-soft-card q-pa-lg">
            <div class="text-caption text-grey-6">Centros de salud</div>
            <div class="text-h4 text-weight-bold">{{ summaryHealthCenterRows.length }}</div>
            <div class="text-caption text-grey-7 q-mt-sm">Centros distintos en el rango.</div>
          </q-card>
        </div>
        <div class="col-12 col-md-3">
          <q-card class="app-soft-card q-pa-lg">
            <div class="text-caption text-grey-6">Menores</div>
            <div class="text-h4 text-weight-bold">{{ minorsCount }}</div>
            <div class="text-caption text-grey-7 q-mt-sm">Registros marcados como menor de 1 ano.</div>
          </q-card>
        </div>
      </div>

      <div class="row q-col-gutter-lg">
        <div class="col-12 col-xl-3">
          <q-card class="app-soft-card app-table">
            <q-card-section class="q-pb-none">
              <div class="text-subtitle1 text-weight-bold">Por especie</div>
            </q-card-section>
            <q-card-section class="q-pt-sm">
              <q-table
                :rows="summarySpeciesRows"
                :columns="speciesColumns"
                row-key="nombre"
                dense
                flat
                bordered
                hide-pagination
                no-data-label="Sin datos por especie"
              />
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-xl-3">
          <q-card class="app-soft-card app-table">
            <q-card-section class="q-pb-none">
              <div class="text-subtitle1 text-weight-bold">Por lugar</div>
            </q-card-section>
            <q-card-section class="q-pt-sm">
              <q-table
                :rows="summaryPlaceRows"
                :columns="placeColumns"
                row-key="nombre"
                dense
                flat
                bordered
                hide-pagination
                no-data-label="Sin datos por lugar"
              />
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-xl-3">
          <q-card class="app-soft-card app-table">
            <q-card-section class="q-pb-none">
              <div class="text-subtitle1 text-weight-bold">Por centro de salud</div>
            </q-card-section>
            <q-card-section class="q-pt-sm">
              <q-table
                :rows="summaryHealthCenterRows"
                :columns="healthCenterColumns"
                row-key="nombre"
                dense
                flat
                bordered
                hide-pagination
                no-data-label="Sin datos por centro de salud"
              />
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-xl-3">
          <q-card class="app-soft-card app-table">
            <q-card-section class="q-pb-none">
              <div class="text-subtitle1 text-weight-bold">Menor de 1 ano</div>
            </q-card-section>
            <q-card-section class="q-pt-sm">
              <q-table
                :rows="summaryMenorRows"
                :columns="menorColumns"
                row-key="valor"
                dense
                flat
                bordered
                hide-pagination
                no-data-label="Sin datos por condicion"
              />
            </q-card-section>
          </q-card>
        </div>
      </div>

      <q-card class="app-soft-card app-table">
        <q-card-section class="q-pb-none">
          <div class="row items-center justify-between q-gutter-sm">
            <div>
              <div class="text-subtitle1 text-weight-bold">Detalle de registros</div>
              <div class="text-caption text-grey-7">
                {{ rows.length }} registro(s) en el rango seleccionado.
              </div>
            </div>
          </div>
        </q-card-section>

        <q-card-section class="q-pt-sm">
          <q-table
            :rows="rows"
            :columns="detailColumns"
            row-key="id"
            dense
            flat
            bordered
            :loading="loading"
            wrap-cells
            no-data-label="No hay registros de vacunas para los filtros aplicados"
          >
            <template #body-cell-fecha_vacuna="props">
              <q-td :props="props">
                {{ formatDateTime(props.row.fecha_vacuna) }}
              </q-td>
            </template>

            <template #body-cell-especie="props">
              <q-td :props="props">
                {{ props.row.especie?.nombre || props.row.especie || 'SIN ESPECIE' }}
              </q-td>
            </template>

            <template #body-cell-place="props">
              <q-td :props="props">
                {{ props.row.place?.nombre || 'SIN LUGAR' }}
              </q-td>
            </template>

            <template #body-cell-health_center="props">
              <q-td :props="props">
                {{ props.row.healthCenter?.nombre || 'SIN CENTRO DE SALUD' }}
              </q-td>
            </template>

            <template #body-cell-menor="props">
              <q-td :props="props">
                <q-badge :color="props.row.menor ? 'negative' : 'positive'" rounded>
                  {{ props.row.menor ? 'SI' : 'NO' }}
                </q-badge>
              </q-td>
            </template>
          </q-table>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<script>
import moment from 'moment'
import AppSectionHeader from 'components/AppSectionHeader.vue'
import { globalStore } from 'src/stores/globalStore'

export default {
  name: 'ReporteRegistroVacunasPage',
  components: {
    AppSectionHeader
  },
  data () {
    const hoy = moment()

    return {
      store: globalStore(),
      loading: false,
      rows: [],
      summary: {
        total: 0,
        especies: [],
        places: [],
        health_centers: [],
        menor: []
      },
      filters: {
        fecha_desde: hoy.clone().startOf('month').format('YYYY-MM-DD'),
        fecha_hasta: hoy.format('YYYY-MM-DD')
      },
      speciesColumns: [
        { name: 'nombre', label: 'Especie', field: 'nombre', align: 'left' },
        { name: 'cantidad', label: 'Cantidad', field: 'cantidad', align: 'right' }
      ],
      placeColumns: [
        { name: 'nombre', label: 'Lugar', field: 'nombre', align: 'left' },
        { name: 'cantidad', label: 'Cantidad', field: 'cantidad', align: 'right' }
      ],
      healthCenterColumns: [
        { name: 'nombre', label: 'Centro de salud', field: 'nombre', align: 'left' },
        { name: 'cantidad', label: 'Cantidad', field: 'cantidad', align: 'right' }
      ],
      menorColumns: [
        { name: 'valor', label: 'Menor', field: 'valor', align: 'left' },
        { name: 'cantidad', label: 'Cantidad', field: 'cantidad', align: 'right' }
      ],
      detailColumns: [
        { name: 'fecha_vacuna', label: 'Fecha', field: 'fecha_vacuna', align: 'left', sortable: true },
        { name: 'cedula', label: 'Cedula', field: 'cedula', align: 'left' },
        { name: 'nombre_mascota', label: 'Mascota', field: 'nombre_mascota', align: 'left' },
        { name: 'especie', label: 'Especie', field: row => row.especie?.nombre || row.especie || 'SIN ESPECIE', align: 'left' },
        { name: 'place', label: 'Lugar', field: row => row.place?.nombre || 'SIN LUGAR', align: 'left' },
        { name: 'health_center', label: 'Centro de salud', field: row => row.healthCenter?.nombre || 'SIN CENTRO DE SALUD', align: 'left' },
        { name: 'menor', label: 'Menor', field: 'menor', align: 'center' }
      ]
    }
  },
  computed: {
    summarySpeciesRows () {
      return Array.isArray(this.summary.especies) ? this.summary.especies : []
    },
    summaryPlaceRows () {
      return Array.isArray(this.summary.places) ? this.summary.places : []
    },
    summaryHealthCenterRows () {
      return Array.isArray(this.summary.health_centers) ? this.summary.health_centers : []
    },
    summaryMenorRows () {
      return Array.isArray(this.summary.menor)
        ? this.summary.menor.map(item => ({
            ...item,
            valor: item.valor === 'SI' ? 'Menor de 1 ano' : 'Mayor de 1 ano'
          }))
        : []
    },
    minorsCount () {
      return this.summaryMenorRows.reduce((total, row) => {
        return row.valor === 'Menor de 1 ano' ? total + Number(row.cantidad || 0) : total
      }, 0)
    }
  },
  created () {
    if (!this.store.isLoggedIn) {
      this.$router.push('/')
      return
    }

    if (!this.store.bool_registro_vacunas) {
      this.$router.push('/home')
      return
    }

    this.loadReport()
  },
  methods: {
    resetFilters () {
      const hoy = moment()
      this.filters = {
        fecha_desde: hoy.clone().startOf('month').format('YYYY-MM-DD'),
        fecha_hasta: hoy.format('YYYY-MM-DD')
      }

      this.loadReport()
    },
    async loadReport () {
      this.loading = true
      try {
        const { data } = await this.$api.get('registro-vacuna/reporte', {
          params: this.filters
        })

        this.rows = Array.isArray(data?.data) ? data.data : []
        this.summary = {
          total: Number(data?.summary?.total || 0),
          especies: Array.isArray(data?.summary?.especies) ? data.summary.especies : [],
          places: Array.isArray(data?.summary?.places) ? data.summary.places : [],
          health_centers: Array.isArray(data?.summary?.health_centers) ? data.summary.health_centers : [],
          menor: Array.isArray(data?.summary?.menor) ? data.summary.menor : []
        }
      } catch (error) {
        this.notifyError(error, 'No se pudo cargar el reporte de vacunas.')
      } finally {
        this.loading = false
      }
    },
    formatDateTime (value) {
      return value ? moment(value).format('DD/MM/YYYY HH:mm') : '-'
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
