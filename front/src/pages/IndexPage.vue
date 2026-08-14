<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg app-fade-in">
      <q-card class="app-hero">
        <q-card-section class="q-pa-xl">
          <div class="row q-col-gutter-xl items-center">
            <div class="col-12 col-lg-7">
              <q-chip square dense class="app-brand-chip q-mb-md">
                <q-icon name="sym_r_smart_toy" size="18px" class="q-mr-xs" />
                Operacion veterinaria inteligente
              </q-chip>

              <div class="text-h3 text-weight-bolder">
                Bienvenido, {{ store.user?.nombre || 'usuario' }}
              </div>
              <div class="text-body1 q-mt-md" style="max-width: 760px;">
                Controla registros, campañas y catálogos desde un panel limpio, rapido y responsive.
                La informacion clave esta siempre visible para tomar decisiones con menos clics.
              </div>

              <div class="row q-col-gutter-md q-mt-lg">
                <div class="col-12 col-sm-4">
                  <div class="app-soft-card q-pa-md">
                    <div class="text-caption text-white-7">Rol actual</div>
                    <div class="text-subtitle1 text-weight-bold">{{ store.rol?.nombre || 'Sin rol' }}</div>
                  </div>
                </div>
                <div class="col-12 col-sm-4">
                  <div class="app-soft-card q-pa-md">
                    <div class="text-caption text-white-7">Sesion</div>
                    <div class="text-subtitle1 text-weight-bold">Activa</div>
                  </div>
                </div>
                <div class="col-12 col-sm-4">
                  <div class="app-soft-card q-pa-md">
                    <div class="text-caption text-white-7">Cobertura</div>
                    <div class="text-subtitle1 text-weight-bold">{{ summaryCoverage }}%</div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-5">
              <q-card class="app-shell-card bg-white text-dark">
                <q-card-section class="q-pa-lg">
                  <div class="row items-center justify-between">
                    <div>
                      <div class="text-overline text-primary">Resumen rapido</div>
                      <div class="text-h5 text-weight-bold">Estado general</div>
                    </div>
                    <q-avatar rounded class="bg-primary text-white">
                      <q-icon name="sym_r_monitor_heart" />
                    </q-avatar>
                  </div>

                  <div class="row q-col-gutter-md q-mt-md">
                    <div class="col-6">
                      <div class="text-caption text-grey-6">Campañas activas</div>
                      <div class="text-h4 text-weight-bold">{{ metrics.activeCampaigns }}</div>
                    </div>
                    <div class="col-6">
                      <div class="text-caption text-grey-6">Mascotas</div>
                      <div class="text-h4 text-weight-bold">{{ metrics.pets }}</div>
                    </div>
                    <div class="col-6">
                      <div class="text-caption text-grey-6">Personas</div>
                      <div class="text-h4 text-weight-bold">{{ metrics.people }}</div>
                    </div>

                  </div>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </q-card-section>
      </q-card>

      <div class="row q-col-gutter-lg">
        <div class="col-12 col-md-6 col-xl-4">
          <AppMetricCard
            label="Personas"
            :value="metrics.people"
            subtitle="Registros disponibles"
            icon="sym_r_people"
            trend="+ base operativa"
            trend-color="primary"
            :progress="progressRatio(metrics.people, metrics.people)"
            hint="Consulta y seguimiento rapido"
          />
        </div>
        <div class="col-12 col-md-6 col-xl-4">
          <AppMetricCard
            label="Mascotas"
            :value="metrics.pets"
            subtitle="Historicos de atencion"
            icon="sym_r_pets"
            trend="Cobertura amplia"
            trend-color="secondary"
            :progress="progressRatio(metrics.pets, Math.max(metrics.people, metrics.pets, 1))"
            hint="Incluye registros vinculados"
          />
        </div>
        <div class="col-12 col-md-6 col-xl-4">
          <AppMetricCard
            label="Campañas activas"
            :value="metrics.activeCampaigns"
            subtitle="Actualmente en curso"
            icon="sym_r_event"
            trend="En ejecucion"
            trend-color="positive"
            :progress="progressRatio(metrics.activeCampaigns, Math.max(metrics.campaigns, 1))"
            hint="Bloqueadas si estan anuladas"
          />
        </div>

      </div>

      <div class="row q-col-gutter-lg">
        <div class="col-12 col-xl-8">
          <q-card class="app-soft-card app-calendar-card">
            <q-card-section class="row items-center justify-between q-gutter-sm">
              <div>
                <div class="text-subtitle1 text-weight-bold">Calendario de campañas</div>
                <div class="text-caption text-grey-7">
                  Marca fechas de inicio y cierre para visualizar la programacion.
                </div>
              </div>
              <q-badge color="primary" rounded>{{ campaignDates.length }} eventos</q-badge>
            </q-card-section>
            <q-separator />
            <q-card-section class="q-pa-lg">
              <div class="row q-col-gutter-lg">
                <div class="col-12 col-lg-7">
                  <q-date
                    v-model="selectedDate"
                    mask="YYYY-MM-DD"
                    minimal
                    bordered
                    flat
                    :default-year-month="calendarDefaultMonth"
                    :events="calendarEventDates"
                    event-color="secondary"
                    class="full-width"
                  />
                </div>
                <div class="col-12 col-lg-5">
                  <div class="text-subtitle2 text-weight-bold q-mb-sm">
                    Campañas programadas
                  </div>
                  <div v-if="dayCampaigns.length" class="column q-gutter-sm">
                    <q-card
                      v-for="campaign in dayCampaigns"
                      :key="campaign.id"
                      flat
                      bordered
                      class="q-pa-md"
                    >
                      <div class="row items-start justify-between q-gutter-sm">
                        <div>
                          <div class="text-weight-bold">{{ campaign.nombre }}</div>
                          <div class="text-caption text-grey-7">
                            {{ formatRange(campaign) }} | {{ campaign.lugar || 'Sin lugar' }}
                          </div>
                        </div>
                        <q-badge :color="statusColor(campaign)" rounded>
                          {{ statusLabel(campaign) }}
                        </q-badge>
                      </div>
                    </q-card>
                  </div>
                  <q-banner v-else rounded class="bg-blue-1 text-blue-10">
                    No hay campañas programadas para esta fecha.
                  </q-banner>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-xl-4">
          <q-card class="app-soft-card">
            <q-card-section class="row items-center justify-between">
              <div>
                <div class="text-subtitle1 text-weight-bold">Estado de campañas</div>
                <div class="text-caption text-grey-7">Distribucion actual del servicio.</div>
              </div>
              <q-icon name="sym_r_query_stats" color="primary" size="28px" />
            </q-card-section>
            <q-card-section class="q-pt-none">
              <div v-for="item in campaignBreakdown" :key="item.label" class="q-mb-md">
                <div class="row items-center justify-between q-mb-xs">
                  <span class="text-weight-medium">{{ item.label }}</span>
                  <span class="text-caption text-grey-7">{{ item.value }}</span>
                </div>
                <q-linear-progress
                  rounded
                  size="10px"
                  :value="item.ratio"
                  :color="item.color"
                />
              </div>
            </q-card-section>
          </q-card>

          <q-card class="app-soft-card q-mt-lg">
            <q-card-section>
              <div class="text-subtitle1 text-weight-bold">Acciones rapidas</div>
              <div class="text-caption text-grey-7">Atajos frecuentes para el equipo.</div>
            </q-card-section>
            <q-list separator>
              <q-item clickable v-ripple to="/registro-persona-mascota">
                <q-item-section avatar>
                  <q-avatar rounded class="bg-primary text-white">
                    <q-icon name="sym_r_add_circle" />
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>Registrar mascota</q-item-label>
                  <q-item-label caption>Alta y seguimiento de pacientes</q-item-label>
                </q-item-section>
              </q-item>

              <q-item clickable v-ripple to="/campanias">
                <q-item-section avatar>
                  <q-avatar rounded class="bg-secondary text-white">
                    <q-icon name="sym_r_event_available" />
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label>Gestionar campañas</q-item-label>
                  <q-item-label caption>Programacion y anulacion</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-card>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import moment from 'moment'
import AppMetricCard from 'components/AppMetricCard.vue'
import { globalStore } from 'src/stores/globalStore'
import { api } from 'boot/axios'

const store = globalStore()
const selectedDate = ref(moment().format('YYYY-MM-DD'))
const campaigns = ref([])
const metrics = ref({
  people: 0,
  pets: 0,
  campaigns: 0,
  activeCampaigns: 0,
  annulledCampaigns: 0,
})

const campaignDates = computed(() => {
  const dates = new Set()

  campaigns.value.forEach(campaign => {
    const start = moment(campaign.fec_ini)
    const end = moment(campaign.fec_fin)

    if (start.isValid()) {
      dates.add(start.format('YYYY-MM-DD'))
    }

    if (end.isValid()) {
      dates.add(end.format('YYYY-MM-DD'))
    }
  })

  return Array.from(dates)
})

const calendarEventDates = computed(() => campaignDates.value.map(date => (
  moment(date, 'YYYY-MM-DD').format('YYYY/MM/DD')
)))

const calendarDefaultMonth = computed(() => (
  moment(selectedDate.value, 'YYYY-MM-DD', true).isValid()
    ? moment(selectedDate.value, 'YYYY-MM-DD').format('YYYY/MM')
    : moment().format('YYYY/MM')
))

const dayCampaigns = computed(() => {
  const day = moment(selectedDate.value).format('YYYY-MM-DD')

  return campaigns.value.filter(campaign => {
    const start = moment(campaign.fec_ini)
    const end = moment(campaign.fec_fin)

    if (!start.isValid() || !end.isValid()) {
      return false
    }

    return day >= start.format('YYYY-MM-DD') && day <= end.format('YYYY-MM-DD')
  })
})

const campaignBreakdown = computed(() => {
  const total = Math.max(metrics.value.campaigns, 1)

  return [
    {
      label: 'Activas',
      value: metrics.value.activeCampaigns,
      ratio: metrics.value.activeCampaigns / total,
      color: 'positive'
    },
    {
      label: 'Anuladas',
      value: metrics.value.annulledCampaigns,
      ratio: metrics.value.annulledCampaigns / total,
      color: 'grey-7'
    },
    {
      label: 'Finalizadas',
      value: Math.max(metrics.value.campaigns - metrics.value.activeCampaigns - metrics.value.annulledCampaigns, 0),
      ratio: Math.max(metrics.value.campaigns - metrics.value.activeCampaigns - metrics.value.annulledCampaigns, 0) / total,
      color: 'warning'
    }
  ]
})

const summaryCoverage = computed(() => {
  const total = metrics.value.campaigns + metrics.value.people + metrics.value.pets
  return total ? Math.min(100, Math.round((metrics.value.activeCampaigns / total) * 100)) : 0
})

onMounted(async () => {
  await loadDashboardData()
})

async function loadDashboardData () {
  const requests = await Promise.allSettled([
    fetchList('persona'),
    fetchList('mascota'),
    fetchList('campania'),
    fetchList('especie'),
    fetchList('raza'),
    fetchList('categoria')
  ])

  const [people, pets, campaignsRes, especies, razas, categorias] = requests.map(result => (
    result.status === 'fulfilled' ? result.value : []
  ))

  campaigns.value = campaignsRes
  metrics.value = {
    people: people.length,
    pets: pets.length,
    campaigns: campaignsRes.length,
    activeCampaigns: campaignsRes.filter(isActiveCampaign).length,
    annulledCampaigns: campaignsRes.filter(campaign => String(campaign.estado || '').toUpperCase() === 'ANULADA').length,
  }
}

async function fetchList (endpoint) {
  const { data } = await api.get(endpoint)
  return Array.isArray(data) ? data : []
}

function isActiveCampaign (campaign) {
  const status = String(campaign.estado || '').toUpperCase()
  if (status === 'ANULADA') {
    return false
  }

  const end = moment(campaign.fec_fin)
  if (status !== 'ACTIVA') {
    return false
  }

  if (!end.isValid()) {
    return true
  }

  return moment().isSameOrBefore(end.endOf('day'))
}

function formatRange (campaign) {
  return `${formatDate(campaign.fec_ini)} - ${formatDate(campaign.fec_fin)}`
}

function formatDate (value) {
  return value ? moment(value).format('DD/MM/YYYY') : '-'
}

function statusLabel (campaign) {
  if (String(campaign.estado || '').toUpperCase() === 'ANULADA') {
    return 'Anulada'
  }

  if (!isActiveCampaign(campaign)) {
    return 'Finalizada'
  }

  return 'Activa'
}

function statusColor (campaign) {
  if (String(campaign.estado || '').toUpperCase() === 'ANULADA') {
    return 'grey-7'
  }

  if (!isActiveCampaign(campaign)) {
    return 'warning'
  }

  return 'positive'
}

function progressRatio (value, total) {
  if (!total) {
    return 0
  }

  return Math.min(1, Number(value) / Number(total))
}
</script>
