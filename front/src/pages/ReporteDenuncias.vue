<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Reporte de denuncias"
        subtitle="Consulta denuncias por rango de fechas y tipo, con log actual e historial completo."
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
              <q-input v-model="filters.fecha_desde" type="date" label="Fecha desde" outlined dense />
            </div>
            <div class="col-12 col-md-3">
              <q-input v-model="filters.fecha_hasta" type="date" label="Fecha hasta" outlined dense />
            </div>
            <div class="col-12 col-md-4">
              <q-select
                v-model="filters.denuncia_tipo_id"
                :options="denunciaTipoOptions"
                option-label="label"
                option-value="value"
                emit-value
                map-options
                label="Tipo de denuncia"
                outlined
                dense
                clearable
              />
            </div>
            <div class="col-12 col-md-2">
              <div class="row q-gutter-sm justify-end">
                <q-btn outline color="grey-8" label="Limpiar" @click="resetFilters" />
                <q-btn color="primary" label="Filtrar" :loading="loading" @click="loadReport" />
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <div class="row q-col-gutter-lg">
        <div class="col-12 col-xl-7">
          <q-card class="app-soft-card app-table">
            <q-card-section class="q-pb-none">
              <div class="row items-center justify-between q-gutter-sm">
                <div>
                  <div class="text-subtitle1 text-weight-bold">Denuncias encontradas</div>
                  <div class="text-caption text-grey-7">
                    {{ rows.length }} registro(s) en el rango seleccionado.
                  </div>
                </div>
                <q-badge color="primary" rounded>
                  {{ totalLogs }} log(s)
                </q-badge>
              </div>
            </q-card-section>

            <q-card-section class="q-pt-sm">
              <q-table
                :rows="rows"
                :columns="columns"
                row-key="id"
                :loading="loading"
                flat
                bordered
                dense
                wrap-cells
                no-data-label="No hay denuncias para los filtros aplicados"
                @row-click="selectDenuncia"
              >
                <template #body-cell-fecha="props">
                  <q-td :props="props">
                    {{ formatDateTime(props.row.fec_denuncia) }}
                  </q-td>
                </template>

                <template #body-cell-persona="props">
                  <q-td :props="props">
                    {{ personaNombre(props.row.persona) }}
                  </q-td>
                </template>

                <template #body-cell-mascota="props">
                  <q-td :props="props">
                    {{ mascotaNombre(props.row.mascota) }}
                  </q-td>
                </template>

                <template #body-cell-tipos="props">
                  <q-td :props="props">
                    <div class="row q-col-gutter-xs">
                      <q-badge
                        v-for="tipo in props.row.tipos || []"
                        :key="tipo.id"
                        color="secondary"
                        rounded
                      >
                        {{ tipo.nombre }}
                      </q-badge>
                    </div>
                  </q-td>
                </template>

                <template #body-cell-estado="props">
                  <q-td :props="props">
                    <q-badge :color="denunciaColor(props.row)" text-color="white" rounded>
                      {{ props.row.estado || 'SIN ESTADO' }}
                    </q-badge>
                  </q-td>
                </template>

                <template #body-cell-log_actual="props">
                  <q-td :props="props">
                    <div class="column">
                      <span class="text-weight-medium">
                        {{ props.row.current_log?.proceso?.descripcion || props.row.current_log?.actividad || '-' }}
                      </span>
                      <span class="text-caption text-grey-7">
                        {{ formatDateTime(props.row.current_log?.fechaHora) }}
                      </span>
                    </div>
                  </q-td>
                </template>

                <template #body-cell-historial="props">
                  <q-td :props="props">
                    <q-badge color="accent" rounded>
                      {{ Array.isArray(props.row.logs) ? props.row.logs.length : 0 }}
                    </q-badge>
                  </q-td>
                </template>

                <template #body-cell-actions="props">
                  <q-td :props="props" class="text-right">
                    <q-btn flat dense icon="sym_r_visibility" color="primary" @click.stop="selectDenuncia(null, props.row)" />
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-xl-5">
          <q-card class="app-soft-card" v-if="selectedDenuncia">
            <q-card-section class="bg-primary text-white">
              <div class="row items-center justify-between">
                <div>
                  <div class="text-h6">Detalle del reporte</div>
                  <div class="text-caption text-white-7">
                    Denuncia #{{ selectedDenuncia.numero }} - {{ personaNombre(selectedDenuncia.persona) }}
                  </div>
                </div>
                <q-badge :color="denunciaColor(selectedDenuncia)" text-color="white" rounded>
                  {{ selectedDenuncia.estado || 'SIN ESTADO' }}
                </q-badge>
              </div>
            </q-card-section>

            <q-card-section class="q-gutter-md">
              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-4">
                  <q-input :model-value="selectedDenuncia.numero" label="Numero" dense outlined readonly />
                </div>
                <div class="col-12 col-md-8">
                  <q-input :model-value="formatDateTime(selectedDenuncia.fec_denuncia)" label="Fecha registro" dense outlined readonly />
                </div>
                <div class="col-12">
                  <q-input :model-value="personaNombre(selectedDenuncia.persona)" label="Persona" dense outlined readonly />
                </div>
                <div class="col-12">
                  <q-input :model-value="mascotaNombre(selectedDenuncia.mascota)" label="Mascota" dense outlined readonly />
                </div>
                <div class="col-12">
                  <q-input :model-value="tiposTexto(selectedDenuncia.tipos)" label="Tipos" dense outlined readonly />
                </div>
                <div class="col-12">
                  <q-input :model-value="selectedDenuncia.direccion" label="Direccion" dense outlined readonly />
                </div>
                <div class="col-12">
                  <q-input :model-value="selectedDenuncia.descripcion" label="Descripcion" dense outlined type="textarea" readonly />
                </div>
              </div>

              <q-separator />

              <div>
                <div class="text-subtitle1 text-weight-bold">Log actual</div>
                <div class="text-caption text-grey-7">Ultimo movimiento registrado para esta denuncia.</div>
              </div>

              <q-card flat bordered>
                <q-card-section class="q-gutter-sm">
                  <div class="row q-col-gutter-md">
                    <div class="col-12 col-md-6">
                      <q-input :model-value="selectedCurrentLogProceso" label="Proceso" dense outlined readonly />
                    </div>
                    <div class="col-12 col-md-6">
                      <q-input :model-value="formatDateTime(selectedCurrentLog?.fechaHora)" label="Fecha" dense outlined readonly />
                    </div>
                    <div class="col-12">
                      <q-input :model-value="selectedCurrentLog?.actividad || '-'" label="Actividad" dense outlined readonly />
                    </div>
                    <div class="col-12">
                      <q-input :model-value="selectedCurrentLog?.resultado || '-'" label="Resultado" dense outlined readonly />
                    </div>
                    <div class="col-12">
                      <q-input :model-value="selectedCurrentLog?.obser || '-'" label="Observacion" dense outlined type="textarea" readonly />
                    </div>
                    <div class="col-12">
                      <q-input :model-value="selectedCurrentLog?.user?.nombre || '-'" label="Usuario" dense outlined readonly />
                    </div>
                  </div>
                </q-card-section>
              </q-card>

              <q-separator />

              <div>
                <div class="text-subtitle1 text-weight-bold">Historial de logs</div>
                <div class="text-caption text-grey-7">Secuencia completa asociada a la denuncia seleccionada.</div>
              </div>

              <q-table
                :rows="selectedDenuncia.logs || []"
                :columns="logColumns"
                row-key="id"
                flat
                bordered
                dense
                wrap-cells
                :pagination="{ rowsPerPage: 5 }"
                no-data-label="Sin historial de logs"
              >
                <template #body-cell-fecha="props">
                  <q-td :props="props">
                    {{ formatDateTime(props.row.fechaHora) }}
                  </q-td>
                </template>

                <template #body-cell-proceso="props">
                  <q-td :props="props">
                    {{ props.row.proceso?.descripcion || '-' }}
                  </q-td>
                </template>

                <template #body-cell-usuario="props">
                  <q-td :props="props">
                    {{ props.row.user?.nombre || '-' }}
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
          </q-card>

          <q-card v-else class="app-soft-card">
            <q-card-section>
              <div class="text-subtitle1 text-weight-bold">Detalle</div>
              <div class="text-caption text-grey-7">Selecciona una denuncia para ver su log actual e historial.</div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script>
import moment from 'moment'
import AppSectionHeader from 'components/AppSectionHeader.vue'
import { globalStore } from 'src/stores/globalStore'

export default {
  name: 'ReporteDenunciasPage',
  components: {
    AppSectionHeader
  },
  data () {
    const hoy = moment()

    return {
      store: globalStore(),
      loading: false,
      rows: [],
      denunciaTipos: [],
      selectedDenuncia: null,
      filters: {
        fecha_desde: hoy.clone().startOf('month').format('YYYY-MM-DD'),
        fecha_hasta: hoy.format('YYYY-MM-DD'),
        denuncia_tipo_id: null
      },
      columns: [
        { name: 'numero', label: 'Nro', field: 'numero', align: 'left', sortable: true },
        { name: 'fecha', label: 'Fecha', field: 'fec_denuncia', align: 'left', sortable: true },
        { name: 'persona', label: 'Persona', field: 'persona', align: 'left' },
        { name: 'mascota', label: 'Mascota', field: 'mascota', align: 'left' },
        { name: 'tipos', label: 'Tipos', field: 'tipos', align: 'left' },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left' },
        { name: 'log_actual', label: 'Log actual', field: 'current_log', align: 'left' },
        { name: 'historial', label: 'Historial', field: 'logs', align: 'center' },
        { name: 'actions', label: 'Acciones', field: 'actions', align: 'right' }
      ],
      logColumns: [
        { name: 'fecha', label: 'Fecha', field: 'fechaHora', align: 'left' },
        { name: 'proceso', label: 'Proceso', field: 'proceso', align: 'left' },
        { name: 'actividad', label: 'Actividad', field: 'actividad', align: 'left' },
        { name: 'resultado', label: 'Resultado', field: 'resultado', align: 'left' },
        { name: 'usuario', label: 'Usuario', field: 'user', align: 'left' }
      ]
    }
  },
  computed: {
    denunciaTipoOptions () {
      return [
        { label: 'Todos', value: null },
        ...this.denunciaTipos.map(tipo => ({
          ...tipo,
          label: tipo.nombre,
          value: tipo.id
        }))
      ]
    },
    totalLogs () {
      return this.rows.reduce((total, row) => {
        return total + (Array.isArray(row.logs) ? row.logs.length : 0)
      }, 0)
    },
    selectedCurrentLog () {
      return this.selectedDenuncia?.current_log || (Array.isArray(this.selectedDenuncia?.logs) ? this.selectedDenuncia.logs[0] : null)
    },
    selectedCurrentLogProceso () {
      return this.selectedCurrentLog?.proceso?.descripcion || this.selectedCurrentLog?.actividad || '-'
    }
  },
  created () {
    if (!this.store.isLoggedIn) {
      this.$router.push('/')
      return
    }

    if (!this.store.bool_reporte_denuncia) {
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
        fecha_hasta: hoy.format('YYYY-MM-DD'),
        denuncia_tipo_id: null
      }

      this.loadReport()
    },
    async loadReport () {
      this.loading = true
      try {
        const [reportRes, tiposRes] = await Promise.all([
          this.$api.get('denuncia/reporte', { params: this.filters }),
          this.$api.get('denuncia-tipo')
        ])

        this.denunciaTipos = Array.isArray(tiposRes.data) ? tiposRes.data : []
        this.rows = Array.isArray(reportRes.data?.data) ? reportRes.data.data : []

        if (this.rows.length) {
          const currentId = this.selectedDenuncia?.id
          this.selectedDenuncia = this.rows.find(row => Number(row.id) === Number(currentId)) || this.rows[0]
        } else {
          this.selectedDenuncia = null
        }
      } catch (error) {
        this.notifyError(error, 'No se pudo cargar el reporte de denuncias.')
      } finally {
        this.loading = false
      }
    },
    selectDenuncia (_evt, row) {
      this.selectedDenuncia = row
    },
    personaNombre (persona) {
      if (!persona) {
        return '-'
      }

      return [persona.nombre, persona.paterno, persona.materno].filter(Boolean).join(' ') || '-'
    },
    mascotaNombre (mascota) {
      if (!mascota) {
        return '-'
      }

      return `${mascota.nombre || '-'}${mascota.codigo ? ` (${mascota.codigo})` : ''}`
    },
    tiposTexto (tipos) {
      return Array.isArray(tipos) ? tipos.map(tipo => tipo.nombre).join(', ') : '-'
    },
    estadoColor (estado) {
      const value = this.normalizeText(estado)
      if (value === 'FINALIZADA') {
        return 'positive'
      }
      if (value === 'EN REVISION') {
        return 'warning'
      }
      if (value === 'EN SEGUIMIENTO') {
        return 'secondary'
      }
      return 'primary'
    },
    denunciaColor (row) {
      return row?.current_log?.proceso?.color || this.estadoColor(row?.estado)
    },
    formatDateTime (value) {
      return value ? moment(value).format('DD/MM/YYYY HH:mm') : '-'
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
