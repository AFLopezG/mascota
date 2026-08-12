<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg">
      <AppSectionHeader
        title="Denuncias"
        subtitle="Registra denuncias, avanza su proceso y deja trazabilidad en logs."
        icon="sym_r_report"
      >
        <template #actions>
          <q-btn outline color="primary" icon="sym_r_refresh" label="Recargar" :loading="loading" @click="loadData" />
          <q-btn color="primary" icon="sym_r_add" label="Nueva denuncia" @click="openCreate" />
        </template>
      </AppSectionHeader>

      <div class="row q-col-gutter-lg">
        <div class="col-12 col-xl-7">
          <q-card class="app-soft-card app-table">
            <q-card-section class="q-pb-none">
              <q-input v-model="filter" outlined dense debounce="300" placeholder="Buscar denuncia, persona, mascota o estado..." />
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
                    <q-badge :color="estadoColor(props.row.estado)" rounded>
                      {{ props.row.estado || 'SIN ESTADO' }}
                    </q-badge>
                  </q-td>
                </template>

                <template #body-cell-actions="props">
                  <q-td :props="props" class="text-right">
                    <q-btn flat dense icon="sym_r_list_alt" color="primary" @click.stop="selectDenuncia(null, props.row)" />
                    <q-btn
                      flat
                      dense
                      icon="sym_r_add_task"
                      color="positive"
                      :disable="!canAdvance(props.row)"
                      @click.stop="openLogDialog(props.row)"
                    />
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
                  <div class="text-h6">Detalle de denuncia</div>
                  <div class="text-caption text-white-7">
                    {{ selectedDenuncia.numero }} - {{ personaNombre(selectedDenuncia.persona) }}
                  </div>
                </div>
                <q-badge color="white" text-color="primary" rounded>
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
                  <q-input :model-value="formatDateTime(selectedDenuncia.fec_denuncia)" label="Fecha" dense outlined readonly />
                </div>
                <div class="col-12">
                  <q-input :model-value="personaNombre(selectedDenuncia.persona)" label="Persona" dense outlined readonly />
                </div>
                <div class="col-12">
                  <q-input :model-value="mascotaNombre(selectedDenuncia.mascota)" label="Mascota" dense outlined readonly />
                </div>
                <div class="col-12 col-md-6">
                  <q-input
                    :model-value="selectedDenuncia.mascota?.raza?.especie?.nombre || selectedDenuncia.raza?.especie?.nombre || '-'"
                    label="Especie"
                    dense
                    outlined
                    readonly
                  />
                </div>
                <div class="col-12 col-md-6">
                  <q-input
                    :model-value="selectedDenuncia.mascota?.raza?.nombre || selectedDenuncia.raza?.nombre || '-'"
                    label="Raza"
                    dense
                    outlined
                    readonly
                  />
                </div>
                <div class="col-12">
                  <q-input :model-value="tiposTexto(selectedDenuncia.tipos)" label="Tipos" dense outlined readonly />
                </div>
                <div class="col-12 col-md-6">
                  <q-input :model-value="selectedDenuncia.direccion" label="Direccion" dense outlined readonly />
                </div>
                <div class="col-12 col-md-6">
                  <q-input :model-value="selectedDenuncia.zona" label="Zona" dense outlined readonly />
                </div>
                <div class="col-12">
                  <q-input :model-value="selectedDenuncia.descripcion" label="Descripcion" dense outlined type="textarea" readonly />
                </div>
                <div class="col-12 col-md-4">
                  <q-input :model-value="selectedDenuncia.color" label="Color" dense outlined readonly />
                </div>
                <div class="col-12 col-md-4">
                  <q-input :model-value="selectedDenuncia.tamanio" label="Tamanio" dense outlined readonly />
                </div>
                <div class="col-12 col-md-4">
                  <q-input :model-value="selectedDenuncia.observacion" label="Observacion" dense outlined readonly />
                </div>
                <div class="col-12 col-md-4">
                  <q-input :model-value="selectedDenuncia.fiscalia" label="Fiscalia" dense outlined readonly />
                </div>
              </div>

              <q-separator />

              <div class="row items-center justify-between">
                <div>
                  <div class="text-subtitle1 text-weight-bold">Logs</div>
                  <div class="text-caption text-grey-7">Seguimiento por proceso.</div>
                </div>
                <q-btn
                  color="positive"
                  icon="sym_r_add_task"
                  label="Registrar log"
                  :disable="!canAdvance(selectedDenuncia)"
                  @click="openLogDialog(selectedDenuncia)"
                />
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
                no-data-label="Sin logs registrados"
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

                <template #body-cell-tipo="props">
                  <q-td :props="props">
                    {{ props.row.denunciaTipo?.nombre || '-' }}
                  </q-td>
                </template>
              </q-table>
            </q-card-section>
          </q-card>

          <q-card v-else class="app-soft-card">
            <q-card-section>
              <div class="text-subtitle1 text-weight-bold">Detalle</div>
              <div class="text-caption text-grey-7">Selecciona una denuncia para ver su seguimiento.</div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <q-dialog v-model="dialog" persistent full-width>
      <q-card class="app-soft-card">
        <q-card-section class="bg-primary text-white">
          <div class="text-h6">{{ form.id ? 'Modificar denuncia' : 'Registrar denuncia' }}</div>
        </q-card-section>

        <q-card-section>
          <q-form class="q-gutter-lg" @submit.prevent="save">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-input v-model="form.fec_denuncia" type="datetime-local" label="Fecha y hora" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input
                  v-model="form.persona_cinit"
                  label="Cedula"
                  outlined
                  dense
                  :loading="buscandoPersona"
                  @blur="buscarPersonaPorDocumento"
                />
              </div>
              <div class="col-12 col-md-4">
                <q-input
                  v-model="form.persona_complemento"
                  label="Complemento"
                  outlined
                  dense
                  hint="Opcional"
                  :loading="buscandoPersona"
                  @blur="buscarPersonaPorDocumento"
                />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.persona_nombre" label="Nombre" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.persona_paterno" label="Paterno" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.persona_materno" label="Materno" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.persona_telefono" label="Telefono" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.persona_emergencia" label="Emergencia" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-select
                  v-model="form.mascota_id"
                  :options="mascotaOptions"
                  option-label="label"
                  option-value="value"
                  emit-value
                  map-options
                  use-input
                  fill-input
                  hide-selected
                  input-debounce="300"
                  label="Mascota registrada"
                  outlined
                  dense
                  clearable
                  :loading="buscandoMascota"
                  hint="Busca por codigo o nombre. Si no aparece, completa los datos manuales."
                  @filter="buscarMascotas"
                  @update:model-value="syncMascota"
                />
              </div>

              <div class="col-12" v-if="!form.mascota_id">
                <q-banner rounded class="bg-orange-1 text-orange-10">
                  Si la mascota no esta registrada, complete especie, raza, color y tamano manualmente.
                </q-banner>
              </div>
              <div class="col-12" v-if="personaSeleccionadaForm">
                <q-banner rounded class="bg-blue-1 text-blue-10">
                  Se recuperaron los datos de {{ personaNombre(personaSeleccionadaForm) }}.
                </q-banner>
              </div>
              <div class="col-12 col-md-3">
                <q-select
                  v-model="form.especie_id"
                  :options="especieOptions"
                  option-label="label"
                  option-value="value"
                  emit-value
                  map-options
                  label="Especie"
                  outlined
                  dense
                  :disable="!!form.mascota_id"
                  :rules="!form.mascota_id ? [val => !!val || 'Seleccione una especie'] : []"
                  @update:model-value="onEspecieChange"
                />
              </div>
              <div class="col-12 col-md-3">
                <q-select
                  v-model="form.raza_id"
                  :options="razaOptionsFiltradas"
                  option-label="label"
                  option-value="value"
                  emit-value
                  map-options
                  use-input
                  input-debounce="0"
                  label="Raza"
                  outlined
                  dense
                  :disable="!!form.mascota_id || !form.especie_id"
                  :rules="!form.mascota_id ? [val => !!val || 'Seleccione una raza'] : []"
                  @filter="filtrarRazas"
                />
              </div>
              <div class="col-12 col-md-3">
                <q-select
                  v-model="form.color"
                  :options="colorOptions"
                  label="Color"
                  outlined
                  dense
                  clearable
                  :disable="!!form.mascota_id"
                  hint="Selecciona un color o escribe uno personalizado."
                  use-input
                  new-value-mode="add-unique"
                />
              </div>
              <div class="col-12 col-md-3">
                <q-select
                  v-model="form.tamanio"
                  :options="tamanioOptions"
                  label="Tamano"
                  outlined
                  dense
                  clearable
                  :disable="!!form.mascota_id"
                  hint="Selecciona un tamaño o escribe uno personalizado."
                  use-input
                  new-value-mode="add-unique"
                />
              </div>
              <div class="col-12">
                <q-select
                  v-model="form.denuncia_tipo_ids"
                  :options="denunciaTipoOptions"
                  option-label="label"
                  option-value="id"
                  emit-value
                  map-options
                  multiple
                  use-chips
                  label="Tipos de denuncia"
                  outlined
                  dense
                  hint="Selecciona uno o varios tipos."
                />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.direccion" label="Direccion" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.zona" label="Zona" outlined dense />
              </div>

              <div class="col-12 col-md-4">
                <q-input v-model="form.fiscalia" label="Fiscalia" outlined dense hint="Opcional" />
              </div>
              <div class="col-12">
                <q-input v-model="form.descripcion" label="Descripcion" outlined dense type="textarea" autogrow />
              </div>
              <div class="col-12">
                <q-input v-model="form.observacion" label="Observacion" outlined dense type="textarea" autogrow />
              </div>

            </div>

            <q-card v-if="requiresBiteFields" flat bordered class="q-pa-md">
              <div class="row items-center justify-between q-mb-md">
                <div>
                  <div class="text-subtitle1 text-weight-bold">Datos de mordedura</div>
                  <div class="text-caption text-grey-7">Se habilitan cuando se selecciona el tipo Mordedura.</div>
                </div>
                <q-badge color="negative" rounded>Requerido</q-badge>
              </div>

              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-4">
                  <q-input v-model="form.nom_afectado" label="Nombre afectado" outlined dense />
                </div>
                <div class="col-12 col-md-2">
                  <q-input v-model="form.edad" label="Edad" outlined dense />
                </div>
                <div class="col-12 col-md-3">
                  <q-input v-model="form.telefono" label="Telefono" outlined dense />
                </div>
                <div class="col-12 col-md-3">
                  <q-input v-model="form.dias_obser" label="Dias de observacion" outlined dense />
                </div>
                <div class="col-12">
                  <q-input v-model="form.dir_inicidente" label="Direccion del incidente" outlined dense />
                </div>
                <div class="col-12 col-md-4">
                  <q-input v-model="form.tipo_lesion" label="Tipo de lesion" outlined dense />
                </div>
                <div class="col-12 col-md-4">
                  <q-input v-model="form.resultado" label="Resultado" outlined dense />
                </div>
                <div class="col-12 col-md-4">
                  <q-input v-model="form.obs" label="Observacion adicional" outlined dense />
                </div>
              </div>
            </q-card>

            <div class="row justify-end q-gutter-sm">
              <q-btn flat label="Cancelar" color="negative" v-close-popup />
              <q-btn color="primary" type="submit" :loading="saving" :label="form.id ? 'Actualizar' : 'Guardar denuncia'" />
            </div>
          </q-form>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="logDialog" persistent full-width>
      <q-card class="app-soft-card">
        <q-card-section class="bg-positive text-white">
          <div class="text-h6">Registrar log</div>
          <div class="text-caption text-white-7">
            {{ selectedDenuncia ? `${selectedDenuncia.numero} - ${personaNombre(selectedDenuncia.persona)}` : '' }}
          </div>
        </q-card-section>

        <q-card-section>
          <q-form class="q-gutter-lg" @submit.prevent="saveLog">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-select
                  v-model="logForm.proceso_id"
                  :options="nextProcessOptions"
                  option-label="label"
                  option-value="id"
                  emit-value
                  map-options
                  label="Siguiente proceso"
                  outlined
                  dense
                  :disable="!nextProcessOptions.length"
                />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="logForm.resultado" label="Resultado" outlined dense />
              </div>
              <div class="col-12">
                <q-input v-model="logForm.actividad" label="Actividad" outlined dense />
              </div>
              <div class="col-12">
                <q-input v-model="logForm.obser" label="Observacion" outlined dense type="textarea" autogrow />
              </div>
            </div>

            <div class="row justify-end q-gutter-sm">
              <q-btn flat label="Cancelar" color="negative" v-close-popup />
              <q-btn color="positive" type="submit" :loading="savingLog" label="Guardar log" />
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
import { globalStore } from 'src/stores/globalStore'

const emptyForm = () => ({
  id: null,
  fec_denuncia: moment().format('YYYY-MM-DDTHH:mm'),
  persona_id: null,
  persona_cinit: '',
  persona_complemento: '',
  persona_nombre: '',
  persona_paterno: '',
  persona_materno: '',
  persona_telefono: '',
  persona_emergencia: '',
  mascota_id: null,
  especie_id: null,
  denuncia_tipo_ids: [],
  direccion: '',
  descripcion: '',
  zona: '',
  color: '',
  tamanio: '',
  observacion: '',
  fiscalia: '',
  nom_afectado: '',
  edad: '',
  telefono: '',
  dir_inicidente: '',
  tipo_lesion: '',
  dias_obser: '',
  resultado: '',
  obs: '',
  raza_id: null
})

const emptyLogForm = () => ({
  proceso_id: null,
  actividad: '',
  resultado: '',
  obser: ''
})

const COLOR_OPTIONS = [
  'Blanco',
  'Negro',
  'Marron',
  'Gris',
  'Cafe',
  'Dorado',
  'Beige',
  'Moteado',
  'Tricolor'
]

const TAMANIO_OPTIONS = [
  'Pequeno',
  'Mediano',
  'Grande',
  'Muy grande'
]

export default {
  name: 'DenunciasPage',
  components: {
    AppSectionHeader
  },
  data () {
    return {
      store: globalStore(),
      loading: false,
      saving: false,
      savingLog: false,
      filter: '',
      rows: [],
      especies: [],
      razas: [],
      procesos: [],
      denunciaTipos: [],
      dialog: false,
      logDialog: false,
      form: emptyForm(),
      logForm: emptyLogForm(),
      selectedDenuncia: null,
      mascotaOptions: [],
      mascotaSeleccionadaOption: null,
      buscandoMascota: false,
      mascotaSearchSeq: 0,
      razaFiltro: '',
      buscandoPersona: false,
      personaSeleccionadaForm: null,
      columns: [
        { name: 'numero', label: 'Nro', field: 'numero', align: 'left', sortable: true },
        { name: 'fecha', label: 'Fecha', field: 'fec_denuncia', align: 'left', sortable: true },
        { name: 'persona', label: 'Persona', field: 'persona', align: 'left' },
        { name: 'mascota', label: 'Mascota', field: 'mascota', align: 'left' },
        { name: 'tipos', label: 'Tipos', field: 'tipos', align: 'left' },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left' },
        { name: 'actions', label: 'Acciones', field: 'actions', align: 'right' }
      ],
      logColumns: [
        { name: 'fecha', label: 'Fecha', field: 'fechaHora', align: 'left' },
        { name: 'proceso', label: 'Proceso', field: 'proceso', align: 'left' },
        { name: 'actividad', label: 'Actividad', field: 'actividad', align: 'left' },
        { name: 'resultado', label: 'Resultado', field: 'resultado', align: 'left' }
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
          row.numero,
          row.estado,
          this.personaNombre(row.persona),
          this.mascotaNombre(row.mascota),
          this.tiposTexto(row.tipos)
        ].some(value => String(value || '').toLowerCase().includes(term))
      })
    },
    denunciaTipoOptions () {
      return this.denunciaTipos.map(tipo => ({
        ...tipo,
        label: tipo.nombre,
        value: tipo.id
      }))
    },
    colorOptions () {
      return COLOR_OPTIONS
    },
    tamanioOptions () {
      return TAMANIO_OPTIONS
    },
    selectedDenunciaTipoIds () {
      return Array.isArray(this.form.denuncia_tipo_ids) ? this.form.denuncia_tipo_ids : []
    },
    especieOptions () {
      return this.especies.map(especie => ({
        ...especie,
        label: especie.nombre,
        value: especie.id
      }))
    },
    razaOptionsFiltradas () {
      const especieId = Number(this.form.especie_id)
      const term = this.normalizeText(this.razaFiltro)

      return this.razas
        .filter(raza => {
          const matchesEspecie = !especieId || Number(raza.especie_id) === especieId
          const matchesTerm = !term || this.normalizeText(raza.nombre).includes(term)
          return matchesEspecie && matchesTerm
        })
        .map(raza => ({
          ...raza,
          label: `${raza.nombre}${raza.especie?.nombre ? ` (${raza.especie.nombre})` : ''}`,
          value: raza.id
        }))
    },
    requiresBiteFields () {
      return this.selectedDenunciaTipoIds.some(id => {
        const tipo = this.denunciaTipos.find(item => Number(item.id) === Number(id))
        return this.normalizeText(tipo?.nombre) === 'MORDEDURA'
      })
    },
    nextProcessOptions () {
      if (!this.selectedDenuncia) {
        return []
      }

      const currentOrder = this.currentProcessOrder(this.selectedDenuncia)
      return this.procesos
        .filter(proceso => Number(proceso.orden) >= Number(currentOrder) + 1)
        .map(proceso => ({
          ...proceso,
          label: `${proceso.orden}. ${proceso.descripcion}`,
          value: proceso.id
        }))
    }
  },
  created () {
    if (!this.store.isLoggedIn) {
      this.$router.push('/')
      return
    }

    if (!this.store.bool_denuncia) {
      this.$router.push('/home')
      return
    }

    this.loadInitialData()
  },
  methods: {
    async loadInitialData () {
      this.loading = true
      try {
        const [denunciasRes, tiposRes, procesosRes, especiesRes, razasRes] = await Promise.all([
          this.$api.get('denuncia'),
          this.$api.get('denuncia-tipo'),
          this.$api.get('proceso'),
          this.$api.get('especie'),
          this.$api.get('raza')
        ])

        this.rows = Array.isArray(denunciasRes.data) ? denunciasRes.data : []
        this.denunciaTipos = Array.isArray(tiposRes.data) ? tiposRes.data : []
        this.procesos = Array.isArray(procesosRes.data) ? procesosRes.data : []
        this.especies = Array.isArray(especiesRes.data) ? especiesRes.data : []
        this.razas = Array.isArray(razasRes.data) ? razasRes.data : []

        if (this.rows.length && !this.selectedDenuncia) {
          this.selectDenuncia(null, this.rows[0])
        }
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar las denuncias.')
      } finally {
        this.loading = false
      }
    },
    async loadData () {
      await this.loadInitialData()
    },
    openCreate () {
      this.form = emptyForm()
      this.personaSeleccionadaForm = null
      this.mascotaOptions = []
      this.mascotaSeleccionadaOption = null
      this.razaFiltro = ''
      this.dialog = true
    },
    async save () {
      this.saving = true
      try {
        await this.buscarPersonaPorDocumento()

        if (!this.form.mascota_id && (!this.form.especie_id || !this.form.raza_id)) {
          this.notifyError(null, 'Seleccione una mascota o complete especie y raza.')
          return
        }

        const payload = {
          ...this.form,
          denuncia_tipo_ids: this.form.denuncia_tipo_ids,
          especie_id: this.form.especie_id,
          raza_id: this.form.raza_id,
          fec_denuncia: this.form.fec_denuncia,
          mascota_id: this.form.mascota_id
        }

        const { data } = this.form.id
          ? await this.$api.put(`denuncia/${this.form.id}`, payload)
          : await this.$api.post('denuncia', payload)

        this.$q.notify({
          message: data.message || 'Denuncia guardada.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.dialog = false
        await this.loadInitialData()
        if (data?.data) {
          this.selectedDenuncia = data.data
        }
      } catch (error) {
        this.notifyError(error, 'No se pudo guardar la denuncia.')
      } finally {
        this.saving = false
      }
    },
    async buscarPersonaPorDocumento () {
      const cinit = String(this.form.persona_cinit || '').trim()
      const complemento = String(this.form.persona_complemento || '').trim()

      if (!cinit) {
        this.form.persona_id = null
        this.personaSeleccionadaForm = null
        return null
      }

      this.buscandoPersona = true
      try {
        const { data } = await this.$api.get('buscar-documento', {
          params: {
            cinit,
            complemento: complemento || undefined
          }
        })

        const persona = data || null
        if (!persona) {
          this.form.persona_id = null
          this.personaSeleccionadaForm = null
          return null
        }

        this.cargarPersonaEnFormulario(persona)
        return persona
      } catch (error) {
        this.notifyError(error, 'No se pudo recuperar la persona por documento.')
        return null
      } finally {
        this.buscandoPersona = false
      }
    },
    cargarPersonaEnFormulario (persona) {
      this.form.persona_id = persona.id || null
      this.form.persona_cinit = persona.cinit || ''
      this.form.persona_complemento = persona.complemento || ''
      this.form.persona_nombre = persona.nombre || ''
      this.form.persona_paterno = persona.paterno || ''
      this.form.persona_materno = persona.materno || ''
      this.form.persona_telefono = persona.telefono || ''
      this.form.persona_emergencia = persona.emergencia || ''
      this.personaSeleccionadaForm = persona
    },
    async buscarMascotas (val, update, abort) {
      const term = String(val || '').trim()

      if (!term) {
        update(() => {
          this.mascotaOptions = this.mascotaSeleccionadaOption ? [this.mascotaSeleccionadaOption] : []
        })
        return
      }

      const currentSeq = (this.mascotaSearchSeq || 0) + 1
      this.mascotaSearchSeq = currentSeq

      try {
        this.buscandoMascota = true
        const { data } = await this.$api.get('mascota', {
          params: {
            q: term,
            limit: 20
          }
        })

        if (currentSeq !== this.mascotaSearchSeq) {
          abort()
          return
        }

        const mascotas = Array.isArray(data) ? data : []
        update(() => {
          const options = mascotas.map(mascota => ({
            ...mascota,
            label: `${mascota.codigo || 'SIN CODIGO'} - ${mascota.nombre || 'SIN NOMBRE'}`,
            value: mascota.id
          }))

          if (this.mascotaSeleccionadaOption && !options.some(item => Number(item.value) === Number(this.form.mascota_id))) {
            options.unshift(this.mascotaSeleccionadaOption)
          }

          this.mascotaOptions = options
        })
      } catch (error) {
        if (currentSeq === this.mascotaSearchSeq) {
          update(() => {
            this.mascotaOptions = this.mascotaSeleccionadaOption ? [this.mascotaSeleccionadaOption] : []
          })
          this.notifyError(error, 'No se pudo buscar la mascota.')
        } else {
          abort()
        }
      } finally {
        if (currentSeq === this.mascotaSearchSeq) {
          this.buscandoMascota = false
        }
      }
    },
    syncMascota (mascotaId) {
      const mascota = this.mascotaOptions.find(item => Number(item.value) === Number(mascotaId))
      this.mascotaSeleccionadaOption = mascota || null

      if (!mascota) {
        this.form.especie_id = null
        this.form.raza_id = null
        this.form.color = ''
        this.form.tamanio = ''
        return
      }

      this.form.especie_id = mascota.raza?.especie?.id || null
      this.form.raza_id = mascota.raza?.id || mascota.raza_id || null
      this.form.color = mascota.color_principal || ''
      this.form.tamanio = mascota.tamano || ''

      if (mascota.persona) {
        this.cargarPersonaEnFormulario(mascota.persona)
      }
    },
    onEspecieChange (especieId) {
      this.form.raza_id = null
      this.razaFiltro = ''
    },
    filtrarRazas (val, update) {
      this.razaFiltro = val || ''
      update(() => {})
    },
    selectDenuncia (_evt, row) {
      this.selectedDenuncia = row
      if (row) {
        this.logForm = emptyLogForm()
      }
    },
    openLogDialog (row) {
      this.selectedDenuncia = row
      const nextProcess = this.nextProcessOptions[0] || null

      this.logForm = {
        proceso_id: nextProcess?.id || null,
        actividad: nextProcess ? nextProcess.descripcion : '',
        resultado: nextProcess ? nextProcess.descripcion : '',
        obser: ''
      }

      this.logDialog = true
    },
    async saveLog () {
      if (!this.selectedDenuncia) {
        return
      }

      this.savingLog = true
      try {
        const { data } = await this.$api.post(`denuncia/${this.selectedDenuncia.id}/logs`, this.logForm)
        this.$q.notify({
          message: data.message || 'Log registrado.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.logDialog = false
        this.selectedDenuncia = data?.data || this.selectedDenuncia
        await this.loadInitialData()
        if (data?.data) {
          this.selectedDenuncia = data.data
        }
      } catch (error) {
        this.notifyError(error, 'No se pudo registrar el log.')
      } finally {
        this.savingLog = false
      }
    },
    canAdvance (row) {
      return this.currentProcessOrder(row) < this.procesos.length
    },
    currentProcessOrder (row) {
      const logs = Array.isArray(row?.logs) ? row.logs : []
      const last = logs.reduce((acc, log) => {
        const order = Number(log?.proceso?.orden || 0)
        if (order > acc) {
          return order
        }
        return acc
      }, 0)

      return last
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
