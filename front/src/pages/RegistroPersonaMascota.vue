<template>
  <q-page class="q-pa-md bg-grey-1">
    <q-card class="shadow-3">
      <q-card-section class="bg-primary text-white">
        <div class="text-h6">Registro de persona y mascota</div>
        <div class="text-caption">
          Primero registre o cargue una persona. Luego se habilita el registro y actualizacion de mascota.
        </div>
      </q-card-section>

      <q-card-section>
        <q-banner v-if="mensajePersona" rounded class="q-mb-md" :class="bannerClass">
          {{ mensajePersona }}
        </q-banner>

        <q-tabs
          v-model="tab"
          dense
          align="left"
          class="text-primary"
          active-color="primary"
          indicator-color="primary"
          @update:model-value="verMapa"
        >
          <q-tab name="persona" icon="person" label="Persona" />
          <q-tab name="mascota" icon="pets" label="Mascota" :disable="!personaForm.id" />
        </q-tabs>

        <q-tab-panels v-model="tab" animated class="bg-white">
          <q-tab-panel name="persona">
            <q-form class="q-gutter-md" @submit.prevent="guardarPersona">
              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-3">
                  <q-input
                    v-model="personaForm.cinit"
                    label="CINIT"
                    outlined
                    dense
                    :loading="verificandoPersona"
                    @blur="verificarPersonaExistente"
                  />
                </div>
                <div class="col-12 col-md-3">
                  <q-input
                    v-model="personaForm.complemento"
                    label="Complemento"
                    outlined
                    dense
                    hint="Opcional"
                    :loading="verificandoPersona"
                    @blur="verificarPersonaExistente"
                  />
                </div>
                <div class="col-12 col-md-6">
                  <q-input v-model="personaForm.nombre" label="Nombre" outlined dense />
                </div>
                <div class="col-12 col-md-6">
                  <q-input v-model="personaForm.paterno" label="Paterno" outlined dense />
                </div>
                <div class="col-12 col-md-6">
                  <q-input v-model="personaForm.materno" label="Materno" outlined dense />
                </div>
                <div class="col-12 col-md-8">
                  <q-input v-model="personaForm.direccion" label="Direccion" outlined dense />
                </div>
                <div class="col-12 col-md-4">
                  <q-input v-model="personaForm.telefono" label="Telefono" outlined dense />
                </div>
                <div class="col-12 col-md-4">
                  <q-input v-model="personaForm.emergencia" label="Emergencia" outlined dense />
                </div>
                <div class="col-12 col-md-4">
                  <q-input v-model="personaForm.luz_agua" label="Luz/Agua" outlined dense />
                </div>
                <div class="col-12 col-md-2">
                  <q-input v-model="personaForm.lat" label="Latitud" outlined dense readonly />
                </div>
                <div class="col-12 col-md-2">
                  <q-input v-model="personaForm.lng" label="Longitud" outlined dense readonly />
                </div>
              </div>

              <q-card bordered flat class="map-card">
                <q-card-section class="row items-center justify-between q-gutter-sm">
                  <div>
                    <div class="text-subtitle1">Ubicacion en mapa</div>
                    <div class="text-caption text-grey-7">
                      Haga clic en el mapa o arrastre el marcador para guardar latitud y longitud.
                    </div>
                  </div>
                  <q-btn outline color="primary" icon="my_location" label="Centrar mapa" @click="syncMapFromForm" />
                </q-card-section>
                <q-separator />
                <q-card-section class="q-pa-none">
                  <div ref="map" style="height: 400px; width: 100%;"></div>
                </q-card-section>
              </q-card>

              <div class="row justify-end q-gutter-sm">
                <q-btn flat label="Limpiar" color="negative" @click="resetPersonaForm" />
                <q-btn color="positive" icon="save" :label="personaForm.id ? 'Actualizar persona' : 'Guardar persona'" type="submit" :loading="guardandoPersona" />
              </div>
            </q-form>
          </q-tab-panel>

          <q-tab-panel name="mascota">
            <q-banner v-if="!personaForm.id" rounded class="q-mb-md bg-orange-1 text-orange-10">
              Primero debe guardar o cargar una persona para registrar o actualizar la mascota.
            </q-banner>

            <div class="row justify-between items-center q-mb-md q-gutter-sm">
              <div class="text-subtitle1">Mascotas registradas</div>
              <q-btn
                color="positive"
                icon="add"
                label="Registrar mascota"
                :disable="!personaForm.id"
                @click="abrirDialogMascotaNueva"
              />
            </div>

            <q-table
              :rows="mascotas"
              :columns="mascotaColumns"
              row-key="id"
              dense
              flat
              :pagination="{ rowsPerPage: 8 }"
            >
              <template #body-cell-foto="props">
                <q-td :props="props">
                  <q-avatar v-if="props.row.fotoUrl" square size="48px">
                    <img :src="props.row.fotoUrl" alt="Foto de mascota" />
                  </q-avatar>
                  <span v-else class="text-grey-7">Sin foto</span>
                </q-td>
              </template>

              <template #body-cell-vacunas="props">
                <q-td :props="props">
                  {{ props.row.vacunas ? props.row.vacunas.length : 0 }}
                </q-td>
              </template>

              <template #body-cell-acciones="props">
                <q-td :props="props">
                  <q-btn flat dense icon="edit" color="primary" @click="editarMascota(props.row)" />
                  <q-btn flat dense icon="photo_camera" color="secondary" @click="cambiarFotoMascota(props.row)" />
                </q-td>
              </template>
            </q-table>

            <q-dialog v-model="dialogMascota" persistent @hide="resetMascotaForm">
              <q-card class="dialog-card">
                <q-card-section class="bg-primary text-white row items-center justify-between">
                  <div>
                    <div class="text-h6">{{ mascotaForm.id ? 'Editar mascota' : 'Registrar mascota' }}</div>
                    <div class="text-caption">Datos generales de la mascota</div>
                  </div>
                  <q-btn flat round icon="close" color="white" v-close-popup />
                </q-card-section>

                <q-card-section class="q-pa-md">
                  <q-form class="q-gutter-md" @submit.prevent="guardarMascota">
                    <div class="row q-col-gutter-md items-start">
                      <div class="col-12 col-md-4">
                        <q-input :model-value="personaResumen" label="Persona vinculada" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.codigo" label="Codigo" outlined dense hint="Si se deja vacio se genera automaticamente" />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.nombre" label="Nombre" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.tipo" label="Tipo" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.fec_nac" type="date" label="Fecha de nacimiento" outlined dense />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input v-model.number="mascotaForm.edad" type="number" min="0" label="Edad" outlined dense />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input v-model.number="mascotaForm.tamano" label="Tamano" outlined dense />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input v-model.number="mascotaForm.peso" type="number" step="0.01" label="Peso" outlined dense />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-select v-model="mascotaForm.estado" :options="estadoOptions" label="Estado" outlined dense emit-value map-options />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.raza" label="Raza" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.color" label="Color" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.sexo" :options="sexoOptions" label="Sexo" outlined dense emit-value map-options />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.categoria" :options="categoriaOptions" label="Categoria" outlined dense emit-value map-options />
                      </div>
                      <div class="col-12 col-md-4 flex items-center">
                        <q-checkbox v-model="mascotaForm.esterilizado" label="Esterilizado" />
                      </div>
                      <div class="col-12">
                        <q-input v-model="mascotaForm.observacion" type="textarea" autogrow label="Observacion" outlined dense />
                      </div>
                    </div>

                    <div class="row justify-end q-gutter-sm q-mt-md">
                      <q-btn flat label="Cancelar" color="negative" v-close-popup />
                      <q-btn
                        color="positive"
                        icon="save"
                        :label="mascotaForm.id ? 'Actualizar mascota' : 'Guardar mascota'"
                        type="submit"
                        :loading="guardandoMascota"
                        :disable="!personaForm.id"
                      />
                    </div>
                  </q-form>
                </q-card-section>
              </q-card>
            </q-dialog>

            <q-dialog v-model="dialogFotoMascota" persistent @hide="resetMascotaFotoForm">
              <q-card class="dialog-card dialog-card--photo">
                <q-card-section class="bg-secondary text-white row items-center justify-between">
                  <div>
                    <div class="text-h6">Modificar foto</div>
                    <div class="text-caption">{{ mascotaFotoForm.nombre || 'Mascota seleccionada' }}</div>
                  </div>
                  <q-btn flat round icon="close" color="white" v-close-popup />
                </q-card-section>

                <q-card-section class="q-pa-md">
                  <q-form class="q-gutter-md" @submit.prevent="guardarFotoMascota">
                    <div class="row q-col-gutter-md items-start">
                      <div class="col-12 col-md-6">
                        <q-input :model-value="personaResumen" label="Persona vinculada" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-6">
                        <q-input :model-value="mascotaFotoForm.codigo" label="Codigo" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-6">
                        <q-input :model-value="mascotaFotoForm.nombre" label="Nombre" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-6">
                        <q-file v-model="mascotaFotoForm.foto" label="Nueva foto" outlined dense accept="image/*" clearable />
                      </div>
                      <div class="col-12">
                        <div class="text-caption text-grey-7 q-mb-sm">Foto actual</div>
                        <div v-if="mascotaFotoForm.fotoActualUrl" class="row items-center q-gutter-md">
                          <q-avatar square size="120px">
                            <img :src="mascotaFotoForm.fotoActualUrl" alt="Foto actual de mascota" />
                          </q-avatar>
                          <div class="text-caption text-grey-7">
                            Seleccione una nueva imagen y guarde para reemplazar la anterior.
                          </div>
                        </div>
                        <div v-else class="text-grey-7">
                          Esta mascota no tiene foto registrada.
                        </div>
                      </div>
                    </div>

                    <div class="row justify-end q-gutter-sm q-mt-md">
                      <q-btn flat label="Cancelar" color="negative" v-close-popup />
                      <q-btn
                        color="secondary"
                        icon="save"
                        label="Guardar foto"
                        type="submit"
                        :loading="guardandoFotoMascota"
                        :disable="!personaForm.id || !mascotaFotoForm.foto"
                      />
                    </div>
                  </q-form>
                </q-card-section>
              </q-card>
            </q-dialog>
          </q-tab-panel>
        </q-tab-panels>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const emptyPersona = () => ({
  id: null,
  cinit: '',
  complemento: '',
  nombre: '',
  paterno: '',
  materno: '',
  direccion: '',
  telefono: '',
  emergencia: '',
  lat: '',
  lng: '',
  luz_agua: ''
})

const emptyMascota = () => ({
  id: null,
  codigo: '',
  nombre: '',
  tipo: '',
  fec_nac: '',
  edad: null,
  raza: '',
  color: '',
  tamano: null,
  peso: null,
  estado: 'VIVO',
  observacion: '',
  sexo: 'MACHO',
  categoria: 'CASA',
  esterilizado: false
})

const emptyMascotaFoto = () => ({
  id: null,
  codigo: '',
  nombre: '',
  foto: null,
  fotoActualUrl: '',
  tipo: '',
  fec_nac: '',
  edad: null,
  raza: '',
  color: '',
  tamano: null,
  peso: null,
  estado: 'VIVO',
  observacion: '',
  sexo: 'MACHO',
  categoria: 'CASA',
  esterilizado: false
})

export default {
  name: 'RegistroPersonaMascota',
  data () {
    return {
      tab: 'persona',
      verificandoPersona: false,
      guardandoPersona: false,
      guardandoMascota: false,
      guardandoFotoMascota: false,
      dialogMascota: false,
      dialogFotoMascota: false,
      mensajePersona: '',
      mensajeTipo: '',
      personaForm: emptyPersona(),
      mascotaForm: emptyMascota(),
      mascotaFotoForm: emptyMascotaFoto(),
      mascotas: [],
      mapInstance: null,
      mapMarker: null,
      centro: [-17.969629, -67.114384],
      estadoOptions: [
        { label: 'VIVO', value: 'VIVO' },
        { label: 'FALLECIDO', value: 'FALLECIDO' }
      ],
      sexoOptions: [
        { label: 'MACHO', value: 'MACHO' },
        { label: 'HEMBRA', value: 'HEMBRA' }
      ],
      categoriaOptions: [
        { label: 'CASA', value: 'CASA' },
        { label: 'EMPRESA', value: 'EMPRESA' },
        { label: 'OTRO', value: 'OTRO' }
      ],
      mascotaColumns: [
        { name: 'foto', label: 'FOTO', field: 'foto', align: 'left' },
        { name: 'codigo', label: 'CODIGO', field: 'codigo', align: 'left', sortable: true },
        { name: 'nombre', label: 'NOMBRE', field: 'nombre', align: 'left', sortable: true },
        { name: 'tipo', label: 'TIPO', field: 'tipo', align: 'left' },
        { name: 'raza', label: 'RAZA', field: 'raza', align: 'left' },
        { name: 'vacunas', label: 'VACUNAS', field: row => (row.vacunas || []).length, align: 'center' },
        { name: 'acciones', label: 'ACCIONES', field: 'acciones', align: 'right' }
      ]
    }
  },
  computed: {
    personaResumen () {
      if (!this.personaForm.id) {
        return 'Sin persona seleccionada'
      }

      return [this.personaForm.nombre, this.personaForm.paterno, this.personaForm.materno]
        .filter(Boolean)
        .join(' ')
    },
    bannerClass () {
      if (this.mensajeTipo === 'success') {
        return 'bg-green-1 text-green-10'
      }

      if (this.mensajeTipo === 'warning') {
        return 'bg-orange-1 text-orange-10'
      }

      if (this.mensajeTipo === 'negative') {
        return 'bg-red-1 text-red-10'
      }

      if (this.mensajeTipo === 'info') {
        return 'bg-blue-1 text-blue-10'
      }

      return 'bg-grey-2 text-dark'
    }
  },
  mounted () {
    this.$nextTick(() => {
      if (this.$refs.map) {
        this.cargarmapa()
      }
    })
  },
  methods: {
    verMapa () {
      this.$nextTick(() => {
        if (this.mapInstance) {
          this.mapInstance.invalidateSize()
          return
        }

        if (this.$refs.map) {
          this.cargarmapa()
        }
      })
    },
    getMapCenter () {
      const lat = Number.parseFloat(this.personaForm.lat)
      const lng = Number.parseFloat(this.personaForm.lng)

      if (Number.isFinite(lat) && Number.isFinite(lng)) {
        return { lat, lng, zoom: 15 }
      }

      return { lat: this.centro[0], lng: this.centro[1], zoom: 12 }
    },
    cargarmapa () {
      if (!this.$refs.map) {
        return
      }

      if (this.mapInstance) {
        this.mapInstance.remove()
        this.mapInstance = null
        this.mapMarker = null
      }

      const center = this.getMapCenter()
      const map = L.map(this.$refs.map).setView([center.lat, center.lng], center.zoom)
      this.mapInstance = map

      const callejero = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OSM contributors'
      })

      const satelite = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://carto.com/">Carto</a>',
        maxNativeZoom: 19,
        maxZoom: 25
      })

      callejero.addTo(map)
      L.control.layers({
        'Callejero (OSM)': callejero,
        'Satelite (Esri)': satelite
      }).addTo(map)

      /*const icon = L.icon({
        iconUrl: '/img/pin.png',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32]
      })*/

      this.mapMarker = L.marker([center.lat, center.lng], { draggable: true }).addTo(map)
      this.mapMarker.on('dragend', e => {
        const { lat, lng } = e.target.getLatLng()
        this.personaForm.lat = lat
        this.personaForm.lng = lng
      })

      map.on('click', e => {
        this.personaForm.lat = e.latlng.lat
        this.personaForm.lng = e.latlng.lng
        if (this.mapMarker) {
          this.mapMarker.setLatLng(e.latlng)
        }
      })
    },
    syncMapFromForm () {
      if (!this.mapInstance) {
        this.$nextTick(() => this.verMapa())
        return
      }

      const center = this.getMapCenter()
      this.mapInstance.setView([center.lat, center.lng], center.zoom)

      if (this.mapMarker) {
        this.mapMarker.setLatLng([center.lat, center.lng])
      }
    },
    async verificarPersonaExistente () {
      const cinit = (this.personaForm.cinit || '').trim()
      const complemento = (this.personaForm.complemento || '').trim()

      if (!cinit) {
        this.mensajePersona = ''
        this.mensajeTipo = ''
        return
      }

      this.verificandoPersona = true

      try {
        const { data } = await this.$api.get('buscar-documento', {
          params: { cinit, complemento }
        })

        const persona = data?.data ?? data ?? null

        if (persona) {
          this.cargarPersonaEnFormulario(persona)
          this.mostrarMensaje('La persona ya existe y fue cargada.', 'info')
        } else {
          this.personaForm.id = null
          this.mostrarMensaje('No existe una persona con esos datos. Se registrara como nueva.', 'warning')
        }
      } catch (error) {
        this.mostrarMensaje(error?.response?.data?.message || 'No se pudo verificar la persona.', 'negative')
      } finally {
        this.verificandoPersona = false
      }
    },
    cargarPersonaEnFormulario (persona) {
      this.personaForm = {
        id: persona.id,
        cinit: persona.cinit || '',
        complemento: persona.complemento || '',
        nombre: persona.nombre || '',
        paterno: persona.paterno || '',
        materno: persona.materno || '',
        direccion: persona.direccion || '',
        telefono: persona.telefono || '',
        emergencia: persona.emergencia || '',
        lat: persona.lat || '',
        lng: persona.lng || '',
        luz_agua: persona.luz_agua || ''
      }

      this.mascotas = Array.isArray(persona.mascotas)
        ? persona.mascotas.map(mascota => this.normalizarMascota(mascota))
        : []

      this.dialogMascota = false
      this.dialogFotoMascota = false
      this.syncMapFromForm()
    },
    normalizarMascota (mascota) {
      return {
        ...mascota,
        fotoUrl: mascota.foto ? this.buildPublicUrl(`mascotas/${mascota.foto}`) : '',
        vacunas: Array.isArray(mascota.vacunas) ? mascota.vacunas : []
      }
    },
    buildPublicUrl (path) {
      const base = (this.$url || '').replace(/\/api\/?$/, '')
      return `${base}/${path}`
    },
    async guardarPersona () {
      try {
        this.guardandoPersona = true
        const payload = { ...this.personaForm }

        const { data } = this.personaForm.id
          ? await this.$api.put(`persona/${this.personaForm.id}`, payload)
          : await this.$api.post('persona', payload)

        this.cargarPersonaEnFormulario(data.data)
        this.mostrarMensaje(data.message || 'Persona guardada.', 'success')
        this.tab = 'mascota'
      } catch (error) {
        this.mostrarMensaje(error?.response?.data?.message || 'No se pudo guardar la persona.', 'negative')
      } finally {
        this.guardandoPersona = false
      }
    },
    abrirDialogMascotaNueva () {
      this.resetMascotaForm()
      this.dialogMascota = true
    },
    editarMascota (mascota) {
      this.mascotaForm = {
        id: mascota.id,
        codigo: mascota.codigo || '',
        nombre: mascota.nombre || '',
        tipo: mascota.tipo || '',
        fec_nac: mascota.fec_nac || '',
        edad: mascota.edad ?? null,
        raza: mascota.raza || '',
        color: mascota.color || '',
        tamano: mascota.tamano ?? null,
        peso: mascota.peso ?? null,
        estado: mascota.estado || 'VIVO',
        observacion: mascota.observacion || '',
        sexo: mascota.sexo || 'MACHO',
        categoria: mascota.categoria || 'CASA',
        esterilizado: !!mascota.esterilizado
      }
      this.dialogMascota = true
    },
    cambiarFotoMascota (mascota) {
      this.mascotaFotoForm = {
        id: mascota.id,
        codigo: mascota.codigo || '',
        nombre: mascota.nombre || '',
        foto: null,
        fotoActualUrl: mascota.fotoUrl || '',
        tipo: mascota.tipo || '',
        fec_nac: mascota.fec_nac || '',
        edad: mascota.edad ?? null,
        raza: mascota.raza || '',
        color: mascota.color || '',
        tamano: mascota.tamano ?? null,
        peso: mascota.peso ?? null,
        estado: mascota.estado || 'VIVO',
        observacion: mascota.observacion || '',
        sexo: mascota.sexo || 'MACHO',
        categoria: mascota.categoria || 'CASA',
        esterilizado: !!mascota.esterilizado
      }
      this.dialogFotoMascota = true
    },
    async guardarMascota () {
      if (!this.personaForm.id) {
        this.mostrarMensaje('Primero debe guardar o cargar una persona.', 'warning')
        return
      }

      try {
        this.guardandoMascota = true
        const payload = this.buildMascotaPayload(this.mascotaForm)
        const { data } = this.mascotaForm.id
          ? await this.$api.put(`mascota/${this.mascotaForm.id}`, payload)
          : await this.$api.post('mascota', payload)

        const personaResponse = await this.$api.get(`persona/${this.personaForm.id}`)
        this.cargarPersonaEnFormulario(personaResponse.data.data)
        this.mostrarMensaje(data.message || 'Mascota guardada.', 'success')
      } catch (error) {
        const apiError = error?.response?.data
        const mensaje = apiError?.errors?.codigo?.[0] || apiError?.message || 'No se pudo guardar la mascota.'
        this.mostrarMensaje(mensaje, 'negative')
      } finally {
        this.guardandoMascota = false
      }
    },
    async guardarFotoMascota () {
      if (!this.personaForm.id) {
        this.mostrarMensaje('Primero debe guardar o cargar una persona.', 'warning')
        return
      }

      try {
        this.guardandoFotoMascota = true
        const payload = this.buildMascotaPayload(this.mascotaFotoForm)
        const { data } = await this.$api.post(`mascota/${this.mascotaFotoForm.id}`, payload)

        const personaResponse = await this.$api.get(`persona/${this.personaForm.id}`)
        this.cargarPersonaEnFormulario(personaResponse.data.data)
        this.mostrarMensaje(data.message || 'Foto actualizada.', 'success')
      } catch (error) {
        const apiError = error?.response?.data
        const mensaje = apiError?.errors?.foto?.[0] || apiError?.message || 'No se pudo actualizar la foto.'
        this.mostrarMensaje(mensaje, 'negative')
      } finally {
        this.guardandoFotoMascota = false
      }
    },
    buildMascotaPayload (form) {
      const payload = new FormData()
      
      payload.append('persona_id', this.personaForm.id)
      payload.append('codigo', form.codigo || '')
      payload.append('nombre', form.nombre || '')
      payload.append('tipo', form.tipo || '')
      payload.append('fec_nac', form.fec_nac || '')
      payload.append('edad', form.edad ?? '')
      payload.append('raza', form.raza || '')
      payload.append('color', form.color || '')
      payload.append('tamano', form.tamano ?? '')
      payload.append('peso', form.peso ?? '')
      payload.append('estado', form.estado || 'VIVO')
      payload.append('observacion', form.observacion || '')
      payload.append('sexo', form.sexo || 'MACHO')
      payload.append('categoria', form.categoria || 'CASA')
      payload.append('esterilizado', form.esterilizado ? '1' : '0')

      if (form.foto) {
        payload.append('foto', form.foto)
      }

      if (form.id) {
        payload.append('_method', 'PUT')
      }

      return payload
    },
    resetPersonaForm () {
      this.personaForm = emptyPersona()
      this.mascotas = []
      this.syncMapFromForm()
    },
    resetMascotaForm () {
      this.mascotaForm = emptyMascota()
    },
    resetMascotaFotoForm () {
      this.mascotaFotoForm = emptyMascotaFoto()
    },
    limpiarTodo () {
      this.resetPersonaForm()
      this.resetMascotaForm()
      this.resetMascotaFotoForm()
      this.mensajePersona = ''
      this.mensajeTipo = ''
      this.tab = 'persona'
    },
    mostrarMensaje (mensaje, tipo) {
      this.mensajePersona = mensaje
      this.mensajeTipo = tipo
    }
  }
}
</script>

<style scoped>
.map-card {
  overflow: hidden;
}

.dialog-card {
  width: 95vw;
  max-width: 1100px;
}

.dialog-card--photo {
  max-width: 760px;
}

@media (max-width: 599px) {
  .dialog-card {
    width: 100vw;
    max-width: 100vw;
  }
}
</style>
