<template>
  <q-page class="app-page">
    <div class="column q-gutter-lg ">
      <AppSectionHeader
        title="Registro de vacunas"
        subtitle="Listado por rango de fechas y registro con captura de fotografia."
        icon="sym_r_vaccines"        
      >
        <template #actions>
          <q-btn outline color="primary" icon="sym_r_refresh" label="Recargar" :loading="loading" @click="loadData" />
          <q-btn v-if="store.bool_registrar_registro_vacuna" color="primary" icon="sym_r_add_photo_alternate" label="Nuevo registro" @click="openDialog" />
        </template>
      </AppSectionHeader>

      <q-card class="app-soft-card">
        <q-card-section class="row q-col-gutter-md items-end">
          <div class="col-12 col-md-3">
            <q-input v-model="filters.fecha_desde" type="date" label="Desde" outlined dense />
          </div>
          <div class="col-12 col-md-3">
            <q-input v-model="filters.fecha_hasta" type="date" label="Hasta" outlined dense />
          </div>
          <div class="col-12 col-md-auto">
            <q-btn color="primary" icon="sym_r_search" label="Buscar" :loading="loading" @click="loadData" />
          </div>
          <div class="col-12 col-md-auto">
            <q-btn flat color="grey-8" label="Limpiar" @click="clearFilters" />
          </div>
        </q-card-section>

        <q-separator />

        <q-card-section class="q-pa-none">
          <q-table
            :rows="rows"
            :columns="columns"
            row-key="id"
            dense
            flat
            bordered
            :loading="loading"
            no-data-label="No hay registros en el rango seleccionado"
          >
            <template #body-cell-fecha_vacuna="props">
              <q-td :props="props">
                {{ formatDateTime(props.row.fecha_vacuna) }}
              </q-td>
            </template>

            <template #body-cell-campania="props">
              <q-td :props="props">
                {{ props.row.campania?.nombre || 'Sin campaña' }}
              </q-td>
            </template>

            <template #body-cell-place="props">
              <q-td :props="props">
                {{ props.row.place?.nombre || 'Sin lugar' }}
              </q-td>
            </template>

            <template #body-cell-foto="props">
              <q-td :props="props">
                <q-btn
                  v-if="props.row.foto"
                  outline
                  color="primary"
                  dense
                  icon="sym_r_image"
                  label="Ver foto"
                  @click="openFotoDialog(props.row)"
                />
                <span v-else class="text-grey-7">Sin foto</span>
              </q-td>
            </template>

            <template #body-cell-estado="props">
              <q-td :props="props">
                <q-badge :color="String(props.row.estado || 'ACTIVO').toUpperCase() === 'ANULADO' ? 'grey-7' : 'positive'" text-color="white">
                  {{ props.row.estado || 'ACTIVO' }}
                </q-badge>
              </q-td>
            </template>

            <template #body-cell-acciones="props">
              <q-td :props="props" class="text-right">
                <q-btn
                  v-if="canAnular(props.row)"
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

    <q-dialog v-model="fotoDialog" persistent @hide="closeFotoDialog">
      <q-card class="app-soft-card foto-dialog-card">
        <q-card-section class="bg-primary text-white row items-center justify-between">
          <div>
            <div class="text-h6">Foto del registro</div>
            <div class="text-caption text-white-7">{{ fotoDialogTitle }}</div>
          </div>
          <q-btn flat round icon="sym_r_close" color="white" v-close-popup />
        </q-card-section>

        <q-card-section class="foto-dialog-card__body">
          <div v-if="fotoDialogLoading" class="row justify-center q-pa-xl">
            <q-spinner color="primary" size="42px" />
          </div>
          <div v-else-if="fotoDialogSrc" class="foto-dialog-card__image-wrap">
            <img :src="fotoDialogSrc" alt="Foto del registro" class="foto-dialog-card__image" />
          </div>
          <q-banner v-else rounded class="bg-grey-2 text-grey-9">
            No se pudo cargar la foto.
          </q-banner>
        </q-card-section>
      </q-card>
    </q-dialog>

    <q-dialog v-model="dialog" persistent full-width @hide="closeDialog">
      <q-card class="app-soft-card dialog-card-vacuna">
        <q-card-section class="bg-primary text-white row items-center justify-between">
          <div>
            <div class="text-h6">Registrar vacuna</div>
            <div class="text-caption text-white-7">Primero selecciona la campaña vigente por defecto.</div>
          </div>
          <q-btn flat round icon="sym_r_close" color="white" v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-form class="q-gutter-lg" @submit.prevent="save">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-select
                  v-model="form.campania_id"
                  :options="campaniaOptions"
                  option-label="label"
                  option-value="value"
                  emit-value
                  map-options
                  :label="$requiredLabel('Campaña')"
                  outlined
                  dense
                  :rules="[val => !!val || 'Seleccione una campaña vigente']"
                />
              </div>

              <div class="col-12 col-md-4">
                <q-input v-model="form.cedula" label="Carnet de Identidad" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.nombre" label="Nombre Responsable" outlined dense />
              </div>
              <div class="col-12 col-md-4">
                <q-input v-model="form.celular" label="Celular" outlined dense />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.domicilio" label="Domicilio" outlined dense />
              </div>
              <div class="col-12 col-md-6">
                <q-input v-model="form.nombre_mascota" label="Nombre de mascota" outlined dense />
              </div>

              <div class="col-12 col-md-4">
                <q-select
                  v-model="form.especie_id"
                  :options="especieOptions"
                  option-label="label"
                  option-value="value"
                  emit-value
                  map-options
                  :label="$requiredLabel('Especie')"
                  outlined
                  dense
                  :rules="[val => !!val || 'Seleccione una especie']"
                  @update:model-value="syncEspecie"
                />
              </div>
              <div class="col-12 col-md-4">
                <q-select
                  v-model="form.raza_id"
                  :options="razaOptionsFiltered"
                  option-label="label"
                  option-value="value"
                  emit-value
                  map-options
                  label="Raza"
                  outlined
                  dense
                  use-input
                  input-debounce="0"
                  clearable
                  :disable="!form.especie_id"
                  @filter="filterRazas"
                  @update:model-value="syncRaza"
                />
              </div>

              <div class="col-12 col-md-6">
                <q-option-group
                  v-model="form.menor"
                  :options="menorOptions"
                  type="radio"
                  inline
                  :label="$requiredLabel('Edad')"
                />
              </div>
            </div>

            <q-card flat bordered class="q-pa-md">
              <div class="row items-center justify-between q-gutter-sm q-mb-md">
                <div>
                  <div class="text-subtitle1 text-weight-bold">Fotografía</div>
                  <div class="text-caption text-grey-7">Usa cámara o carga un archivo. La imagen se ve antes de guardar.</div>
                </div>
                <div class="row q-gutter-sm">
                  <q-btn outline color="primary" icon="sym_r_photo_camera" label="Abrir cámara" @click="startCamera" />
                  <q-btn outline color="secondary" icon="sym_r_refresh" label="Repetir captura" :disable="!form.foto_preview_url" @click="retakePhoto" />
                  <q-btn outline color="negative" label="Detener" :disable="!cameraActive" @click="stopCamera" />
                </div>
              </div>

              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-7">
                  <div class="camera-frame">
                    <video v-show="cameraActive" ref="videoRef" autoplay playsinline muted class="camera-frame__video"></video>
                    <img v-if="!cameraActive && form.foto_preview_url" :src="form.foto_preview_url" alt="Vista previa" class="camera-frame__image" />
                    <div v-if="!cameraActive && !form.foto_preview_url" class="camera-frame__empty">
                      <q-icon name="sym_r_photo_camera" size="44px" />
                      <div>Activa la cámara o captura una imagen.</div>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-5 q-gutter-md">
                  <q-file
                    v-model="form.foto_file"
                    label="Seleccionar foto (opcional)"
                    outlined
                    dense
                    accept="image/*"
                    @update:model-value="onFileSelected"
                  />
                  <q-btn color="secondary" class="full-width" label="Capturar foto" :disable="!cameraActive" @click="capturePhoto" />
                  <q-banner v-if="cameraError" rounded class="bg-orange-1 text-orange-10">
                    {{ cameraError }}
                  </q-banner>
                </div>
              </div>
            </q-card>

            <div class="row justify-end q-gutter-sm">
              <q-btn flat label="Cancelar" color="negative" v-close-popup />
              <q-btn color="positive" icon="sym_r_save" label="Guardar registro" type="submit" :loading="saving" />
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

const defaultForm = (defaultCampaniaId = null, defaultPlaceId = null) => ({
  id: null,
  cedula: '',
  nombre: '',
  domicilio: '',
  celular: '',
  nombre_mascota: '',
  especie: '',
  raza: '',
  menor: false,
  fecha_vacuna: moment().format('YYYY-MM-DD HH:mm'),
  campania_id: defaultCampaniaId,
  especie_id: null,
  raza_id: null,
  place_id: defaultPlaceId,
  lat: null,
  lng: null,
  foto_file: null,
  foto_preview_url: ''
})

export default {
  name: 'RegistroVacunasPage',
  components: { AppSectionHeader },
  data () {
    return {
      store: globalStore(),
      loading: false,
      saving: false,
      rows: [],
      dialog: false,
      fotoDialog: false,
      fotoDialogLoading: false,
      fotoDialogTitle: '',
      fotoDialogSrc: '',
      cameraActive: false,
      cameraError: '',
      mediaStream: null,
      filters: {
        fecha_desde: moment().startOf('month').format('YYYY-MM-DD'),
        fecha_hasta: moment().format('YYYY-MM-DD')
      },
      campanias: [],
      places: [],
      especies: [],
      razas: [],
      razaFilter: '',
      menorOptions: [
        { label: 'Mayor de 1 año', value: false },
        { label: 'Menor de 1 año', value: true }
      ],
      form: defaultForm(),
      columns: [
        { name: 'fecha_vacuna', label: 'Fecha', field: 'fecha_vacuna', align: 'left', sortable: true },
        { name: 'cedula', label: 'Cedula', field: 'cedula', align: 'left' },
        { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left' },
        { name: 'nombre_mascota', label: 'Mascota', field: 'nombre_mascota', align: 'left' },
        { name: 'campania', label: 'Campaña', field: row => row.campania?.nombre || '', align: 'left' },
        { name: 'place', label: 'Lugar', field: row => row.place?.nombre || '', align: 'left' },
        { name: 'estado', label: 'Estado', field: 'estado', align: 'left' },
        { name: 'foto', label: 'Foto', field: 'foto', align: 'left' },
        { name: 'acciones', label: 'Acciones', field: 'acciones', align: 'right' }
      ]
    }
  },
  computed: {
    campaniaOptions () {
      return this.campanias.map(campania => ({
        label: `${campania.nombre}${campania.fec_ini ? ` (${moment(campania.fec_ini).format('DD/MM/YYYY')})` : ''}`,
        value: campania.id
      }))
    },
    placeOptions () {
      return this.places.map(place => ({
        label: place.nombre,
        value: place.id
      }))
    },
    especieOptions () {
      return this.especies.map(especie => ({
        label: especie.nombre,
        value: especie.id
      }))
    },
    razaOptionsFiltered () {
      const speciesId = Number(this.form.especie_id)
      const filter = this.razaFilter.trim().toLowerCase()

      return this.razas
        .filter(raza => !speciesId || Number(raza.especie_id) === speciesId)
        .filter(raza => {
          if (!filter) return true
          return [raza.nombre, raza.especie?.nombre].some(value => String(value || '').toLowerCase().includes(filter))
        })
        .map(raza => ({
          label: `${raza.nombre}`,
          value: raza.id
        }))    
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

    this.loadInitialData()
  },
  beforeUnmount () {
    this.stopCamera()
  },
  methods: {
    async loadInitialData () {
      await Promise.all([
        this.loadCatalogs(),
        this.loadData()
      ])
    },
    async loadCatalogs () {
      try {
        const [campaniasRes, placesRes, especiesRes, razasRes] = await Promise.all([
          this.$api.get('campania', { params: { vigentes: 1, vacunacion: 1 } }),
          this.$api.get('place'),
          this.$api.get('especie'),
          this.$api.get('raza')
        ])

        this.campanias = Array.isArray(campaniasRes.data) ? campaniasRes.data : []
        this.places = Array.isArray(placesRes.data) ? placesRes.data : []
        this.especies = Array.isArray(especiesRes.data) ? especiesRes.data : []
        this.razas = Array.isArray(razasRes.data) ? razasRes.data : []
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar los catálogos.')
      }
    },
    async loadData () {
      this.loading = true
      try {
        const { data } = await this.$api.get('registro-vacuna', {
          params: { ...this.filters }
        })

        this.rows = Array.isArray(data?.data) ? data.data : []
      } catch (error) {
        this.notifyError(error, 'No se pudieron cargar los registros de vacunas.')
      } finally {
        this.loading = false
      }
    },
    async openFotoDialog (row) {
      if (!row?.foto) {
        this.notifySimple('Este registro no tiene foto.')
        return
      }

      this.fotoDialogTitle = `${row.nombre_mascota || 'Registro'} - ${row.cedula || 'Sin cédula'}`
      this.fotoDialogSrc = ''
      this.fotoDialogLoading = true
      this.fotoDialog = true

      try {
        const { data } = await this.$api.get(`registro-vacuna/${row.id}/foto`)
        this.fotoDialogSrc = data?.data?.foto_base64 || ''
        if (!this.fotoDialogSrc) {
          this.notifySimple('La foto no está disponible.')
        }
      } catch (error) {
        this.fotoDialogSrc = ''
        this.notifyError(error, 'No se pudo cargar la foto del registro.')
      } finally {
        this.fotoDialogLoading = false
      }
    },
    closeFotoDialog () {
      this.fotoDialog = false
      this.fotoDialogLoading = false
      this.fotoDialogTitle = ''
      this.fotoDialogSrc = ''
    },
    clearFilters () {
      this.filters = {
        fecha_desde: moment().startOf('month').format('YYYY-MM-DD'),
        fecha_hasta: moment().endOf('month').format('YYYY-MM-DD')
      }
      this.loadData()
    },
    openDialog () {
      const defaultCampaniaId = this.campanias[0]?.id ?? null
      const defaultPlaceId = this.places[0]?.id ?? null
      this.form = defaultForm(defaultCampaniaId, defaultPlaceId)
      this.cameraError = ''
      this.dialog = true
      this.loadCurrentLocation()
      this.$nextTick(() => {
        this.startCamera()
      })
    },
    closeDialog () {
      this.stopCamera()
      if (this.form.foto_preview_url) {
        URL.revokeObjectURL(this.form.foto_preview_url)
      }
      this.form = defaultForm(this.campanias[0]?.id ?? null, this.places[0]?.id ?? null)
    },
    async loadCurrentLocation () {
      if (!navigator.geolocation) {
        return
      }

      try {
        const position = await new Promise((resolve, reject) => {
          navigator.geolocation.getCurrentPosition(resolve, reject, {
            enableHighAccuracy: true,
            timeout: 8000,
            maximumAge: 0
          })
        })

        this.form.lat = String(position.coords.latitude)
        this.form.lng = String(position.coords.longitude)
      } catch (error) {
        this.form.lat = null
        this.form.lng = null
      }
    },
    async startCamera () {
      this.cameraError = ''
      if (!navigator.mediaDevices?.getUserMedia) {
        this.cameraError = 'Este navegador no soporta acceso a cámara.'
        return
      }

      try {
        this.stopCamera()
        const stream = await navigator.mediaDevices.getUserMedia({
          video: { facingMode: 'environment' },
          audio: false
        })
        this.mediaStream = stream
        this.cameraActive = true

        await this.$nextTick()
        const video = this.$refs.videoRef
        if (video) {
          video.srcObject = stream
          await video.play()
        }
      } catch (error) {
        this.cameraError = 'No se pudo acceder a la cámara.'
      }
    },
    stopCamera () {
      if (this.mediaStream) {
        this.mediaStream.getTracks().forEach(track => track.stop())
      }
      this.mediaStream = null
      this.cameraActive = false

      const video = this.$refs.videoRef
      if (video) {
        video.srcObject = null
      }
    },
    capturePhoto () {
      const video = this.$refs.videoRef
      if (!video || !this.cameraActive) {
        return
      }

      const canvas = document.createElement('canvas')
      canvas.width = video.videoWidth || 1280
      canvas.height = video.videoHeight || 720
      const context = canvas.getContext('2d')

      if (!context) {
        this.cameraError = 'No se pudo procesar la imagen.'
        return
      }

      context.drawImage(video, 0, 0, canvas.width, canvas.height)
      canvas.toBlob((blob) => {
        if (!blob) {
          this.cameraError = 'No se pudo capturar la fotografia.'
          return
        }

        if (this.form.foto_preview_url) {
          URL.revokeObjectURL(this.form.foto_preview_url)
        }

        const file = new File([blob], `registro-vacuna-${Date.now()}.jpg`, { type: 'image/jpeg' })
        this.form.foto_file = file
        this.form.foto_preview_url = URL.createObjectURL(blob)
        this.stopCamera()
      }, 'image/jpeg', 0.92)
    },
    retakePhoto () {
      if (this.form.foto_preview_url) {
        URL.revokeObjectURL(this.form.foto_preview_url)
      }

      this.form.foto_file = null
      this.form.foto_preview_url = ''
      this.startCamera()
    },
    onFileSelected (file) {
      if (!file) {
        if (this.form.foto_preview_url) {
          URL.revokeObjectURL(this.form.foto_preview_url)
        }
        this.form.foto_preview_url = ''
        return
      }

      if (this.form.foto_preview_url) {
        URL.revokeObjectURL(this.form.foto_preview_url)
      }

      this.form.foto_preview_url = URL.createObjectURL(file)
    },
    syncEspecie (value) {
      const especie = this.especies.find(item => Number(item.id) === Number(value))
      if (especie) {
        this.form.especie = especie.nombre || ''
      }
      this.form.raza_id = null
    },
    syncRaza (value) {
      if (!value) {
        this.form.raza_id = null
        this.form.raza = ''
        return
      }

      const raza = this.razas.find(item => Number(item.id) === Number(value))
      this.form.raza = raza?.nombre || this.form.raza || ''
      if (!this.form.especie_id && raza?.especie_id) {
        this.form.especie_id = raza.especie_id
        this.syncEspecie(raza.especie_id)
      }
    },
    filterRazas (val, update) {
      this.razaFilter = val || ''
      update(() => {})
    },
    async save () {
      if (!this.store.bool_registrar_registro_vacuna) {
        this.notifySimple('No tiene permisos para registrar vacunas.')
        return
      }

      this.saving = true
      try {
        await this.loadCurrentLocation()
        const payload = new FormData()
        payload.append('cedula', this.form.cedula || '')
        payload.append('nombre', this.form.nombre || '')
        payload.append('domicilio', this.form.domicilio || '')
        payload.append('celular', this.form.celular || '')
        payload.append('nombre_mascota', this.form.nombre_mascota || '')
        payload.append('especie', this.form.especie || '')
        payload.append('menor', this.form.menor ? '1' : '0')
        payload.append('fecha_vacuna', this.form.fecha_vacuna || '')
        payload.append('campania_id', this.form.campania_id ?? '')
        payload.append('especie_id', this.form.especie_id ?? '')
        payload.append('raza_id', this.form.raza_id ?? '')
        payload.append('lat', this.form.lat ?? '')
        payload.append('lng', this.form.lng ?? '')
        if (this.form.foto_file) {
          payload.append('foto', this.form.foto_file)
        }

        const { data } = await this.$api.post('registro-vacuna', payload, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })

        this.$q.notify({
          message: data.message || 'Registro guardado.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        this.dialog = false
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo guardar el registro.')
      } finally {
        this.saving = false
      }
    },
    confirmAnular (row) {
      this.$q.dialog({
        title: 'Anular registro',
        message: 'Esta accion solo se puede realizar una vez y dejara el registro en estado ANULADO.',
        cancel: true,
        persistent: true
      }).onOk(() => this.anular(row))
    },
    async anular (row) {
      if (!this.store.bool_anular_registro_vacuna) {
        this.notifySimple('No tiene permisos para anular registros.')
        return
      }

      try {
        const { data } = await this.$api.put(`registro-vacuna/${row.id}/anular`)
        this.$q.notify({
          message: data.message || 'Registro anulado.',
          color: 'positive',
          position: 'top',
          timeout: 2000
        })
        await this.loadData()
      } catch (error) {
        this.notifyError(error, 'No se pudo anular el registro.')
      }
    },
    canAnular (row) {
      return this.store.bool_anular_registro_vacuna && String(row?.estado || 'ACTIVO').toUpperCase() === 'ACTIVO'
    },
    formatDateTime (value) {
      if (!value) return ''
      return moment(value).format('DD/MM/YYYY HH:mm')
    },
    notifySimple (message) {
      this.$q.notify({
        message,
        color: 'warning',
        position: 'top',
        timeout: 2000
      })
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

<style scoped>
.dialog-card-vacuna {
  width: 96vw;
  max-width: 1280px;
}

.foto-dialog-card {
  width: 92vw;
  max-width: 920px;
}

.foto-dialog-card__body {
  min-height: 320px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.foto-dialog-card__image-wrap {
  width: 100%;
}

.foto-dialog-card__image {
  width: 100%;
  max-height: 72vh;
  object-fit: contain;
  display: block;
  border-radius: 14px;
  background: #0f172a;
}

.camera-frame {
  min-height: 320px;
  border-radius: 18px;
  overflow: hidden;
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.94));
  border: 1px solid rgba(148, 163, 184, 0.18);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.camera-frame__video,
.camera-frame__image {
  width: 100%;
  height: 100%;
  min-height: 320px;
  object-fit: cover;
}

.camera-frame__empty {
  color: #e2e8f0;
  display: grid;
  justify-items: center;
  gap: 10px;
  padding: 24px;
  text-align: center;
}

@media (max-width: 599px) {
  .dialog-card-vacuna {
    width: 100vw;
    max-width: 100vw;
  }

  .foto-dialog-card {
    width: 100vw;
    max-width: 100vw;
  }
}
</style>
