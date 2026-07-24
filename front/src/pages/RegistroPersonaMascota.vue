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
                <div class="col-12 col-md-4">
                  <q-input v-model="personaForm.correo" label="Correo" outlined dense type="email" />
                </div>
                <div class="col-12 col-md-4">
                  <q-input v-model="personaForm.zona" label="Zona" outlined dense />
                </div>
                <div class="col-12 col-md-4">
                  <q-input v-model="personaForm.distrito" label="Distrito" outlined dense />
                </div>
                <div class="col-12 col-md-3">
                  <q-input v-model="personaForm.fecha" type="date" label="Fecha" outlined dense />
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
                  <q-btn outline color="primary" icon="my_location" label="Centrar mapa" @click="myLocation" />
                </q-card-section>
                <q-separator />
                <q-card-section class="q-pa-none">
                  <div ref="map" id="map" style="height: 400px; width: 100%;"></div>
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
                        <q-input :model-value="mascotaForm.fec_reg" label="Fecha de registro" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.fec_nac" type="date" label="Fecha de nacimiento" outlined dense />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input v-model.number="mascotaForm.edad" type="number" min="0" label="Edad" outlined dense />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input v-model="mascotaForm.tamano" label="Tamano" outlined dense />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-input v-model.number="mascotaForm.peso" type="number" step="0.01" label="Peso" outlined dense />
                      </div>
                      <div class="col-12 col-md-3">
                        <q-select v-model="mascotaForm.estado" :options="estadoOptions" label="Estado" outlined dense emit-value map-options />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input :model-value="mascotaForm.especie" label="Especie" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select
                          v-model="mascotaForm.raza_id"
                          :options="razaOptions"
                          label="Raza"
                          outlined
                          dense
                          emit-value
                          map-options
                          @update:model-value="sincronizarEspecieDesdeRaza"
                        />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.sexo" :options="sexoOptions" label="Sexo" outlined dense emit-value map-options />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.categoria_id" :options="categoriaOptions" label="Categoria" outlined dense emit-value map-options clearable />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.campania_id" :options="campaniaOptions" label="Campania" outlined dense emit-value map-options clearable />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.color_principal" label="Color principal" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.color_secundario" label="Color secundario" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.particular" label="Particular" outlined dense />
                      </div>
                      <div class="col-12 col-md-4 flex items-center">
                        <q-checkbox v-model="mascotaForm.esterilizado" label="Esterilizado" />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.fec_esterilizacion" type="date" label="Fecha de esterilizacion" outlined dense />
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
import moment from 'moment'
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
  luz_agua: '',
  correo: '',
  zona: '',
  distrito: '',
  fecha: ''
})

const emptyMascota = () => ({
  id: null,
  codigo: '',
  nombre: '',
  fec_reg: moment().format('YYYY-MM-DD'),
  especie: '',
  fec_nac: '',
  edad: null,
  color_principal: '',
  color_secundario: '',
  tamano: '',
  peso: null,
  estado: 'ACTIVO',
  particular: '',
  observacion: '',
  sexo: 'MACHO',
  esterilizado: false,
  fec_esterilizacion: '',
  campania_id: null,
  categoria_id: null,
  raza_id: null
})

const emptyMascotaFoto = () => ({
  id: null,
  codigo: '',
  nombre: '',
  foto: null,
  fotoActualUrl: '',
  fec_reg: moment().format('YYYY-MM-DD'),
  especie: '',
  fec_nac: '',
  edad: null,
  color_principal: '',
  color_secundario: '',
  tamano: '',
  peso: null,
  estado: 'ACTIVO',
  particular: '',
  observacion: '',
  sexo: 'MACHO',
  esterilizado: false,
  fec_esterilizacion: '',
  campania_id: null,
  categoria_id: null,
  raza_id: null
})

export default {
  name: 'RegistroPersonaMascota',
  data () {
    return {
      tamannios:['PEQUEÑO','MEDIANO','GRANDE','GIGANTE'],
      tab: 'persona',
      map: null,
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
      especies: [],
      categorias: [],
      razas: [],
      campanias: [],
      mapInstance: null,
      mapMarker: null,
      centro: [-17.969629, -67.114384],
      lat : -17.969629,
      lng : -67.114384,
      zoom: 15,
      estadoOptions: [
        { label: 'ACTIVO', value: 'ACTIVO' },
        { label: 'PERDIDO', value: 'PERDIDO' },
        { label: 'ENCONTRADO', value: 'ENCONTRADO' },
        { label: 'FALLECIDO', value: 'FALLECIDO' },
        { label: 'ADOPTADO', value: 'ADOPTADO' },
        { label: 'OTRO', value: 'OTRO' }
      ],
      sexoOptions: [
        { label: 'MACHO', value: 'MACHO' },
        { label: 'HEMBRA', value: 'HEMBRA' }
      ],
      mascotaColumns: [
        { name: 'foto', label: 'FOTO', field: 'foto', align: 'left' },
        { name: 'codigo', label: 'CODIGO', field: 'codigo', align: 'left', sortable: true },
        { name: 'nombre', label: 'NOMBRE', field: 'nombre', align: 'left', sortable: true },
        { name: 'especie', label: 'ESPECIE', field: row => row.especie || row.raza?.especie?.nombre || '-', align: 'left' },
        { name: 'raza', label: 'RAZA', field: row => row.raza?.nombre || '-', align: 'left' },
        { name: 'categoria', label: 'CATEGORIA', field: row => row.categoria?.nombre || '-', align: 'left' },
        { name: 'estado', label: 'ESTADO', field: 'estado', align: 'left' },
        { name: 'vacunas', label: 'VACUNAS', field: row => (row.vacunas || []).length, align: 'center' },
        { name: 'acciones', label: 'ACCIONES', field: 'acciones', align: 'right' }
      ]
    }
  },
  computed: {
    razaOptions () {
      return this.razas.map(raza => ({
        label: raza.especie?.nombre ? `${raza.nombre} (${raza.especie.nombre})` : raza.nombre,
        value: raza.id
      }))
    },
    categoriaOptions () {
      return this.categorias.map(categoria => ({
        label: categoria.nombre,
        value: categoria.id
      }))
    },
    campaniaOptions () {
      return this.campanias.map(campania => ({
        label: campania.nombre,
        value: campania.id
      }))
    },
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
    this.cargarCatalogos()
    this.$nextTick(() => {
      if (this.$refs.map) {
        this.cargarmapa()
      }
    })
  },
  methods: {
    async cargarCatalogos () {
      try {
        const [especiesRes, categoriasRes, razasRes, campaniasRes] = await Promise.all([
          this.$api.get('especie'),
          this.$api.get('categoria'),
          this.$api.get('raza'),
          this.$api.get('campania')
        ])

        this.especies = especiesRes.data || []
        this.categorias = categoriasRes.data || []
        this.razas = razasRes.data || []
        this.campanias = campaniasRes.data || []
      } catch (error) {
        this.mostrarMensaje(error?.response?.data?.message || 'No se pudieron cargar los catalogos.', 'negative')
      }
    },
    myLocation () {
      this.centro = [-17.969629, -67.114384]
      this.zoom = 15

      if (this.map) {
        this.map.setView(this.centro, this.zoom)
        this.actualizarMarcadorMapa(this.lat, this.lng)
      }
    },
    verMapa () {
      this.$nextTick(() => {
        if (this.$refs.map) {
          this.cargarmapa()
        }
      })
    },
    cargarmapa () {
      if (this.map) {
        this.map.remove()
      }

      this.map = L.map('map').setView(this.centro, this.zoom)

      // Definir capas base
      const callejero = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OSM contributors'
      })

      const satelite = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://carto.com/">Carto</a>',
        maxNativeZoom: 19,
        maxZoom: 25
      })

      // Guardar en arreglo para alternar
      const baseLayers = {
        'Callejero (OSM)': callejero,
        'Satélite (Esri)': satelite
      }

      // Capa inicial
      callejero.addTo(this.map)
      L.control.layers(baseLayers).addTo(this.map)

      this.mapMarker = L.marker(this.obtenerLatLngPersona(), { draggable: true }).addTo(this.map)
        .bindPopup('📍 Ubicación de la persona')
        .openPopup()

      // Evento: arrastrar marcador
      this.mapMarker.on('dragend', e => {
        const { lat: newLat, lng: newLng } = e.target.getLatLng()
        this.lat = newLat
        this.lng = newLng
        this.personaForm.lat = newLat
        this.personaForm.lng = newLng
      })

      // Evento: click en el mapa
      this.map.on('click', e => {
        this.lat = e.latlng.lat
        this.lng = e.latlng.lng
        this.personaForm.lat = e.latlng.lat
        this.personaForm.lng = e.latlng.lng
        this.actualizarMarcadorMapa(e.latlng.lat, e.latlng.lng)
      })
    },
    obtenerLatLngPersona () {
      const lat = this.personaForm.lat !== '' && this.personaForm.lat !== null && this.personaForm.lat !== undefined
        ? this.personaForm.lat
        : this.lat
      const lng = this.personaForm.lng !== '' && this.personaForm.lng !== null && this.personaForm.lng !== undefined
        ? this.personaForm.lng
        : this.lng

      return [lat, lng]
    },
    actualizarMarcadorMapa (lat = null, lng = null) {
      const latValue = lat !== null && lat !== undefined
        ? lat
        : (this.personaForm.lat !== '' && this.personaForm.lat !== null && this.personaForm.lat !== undefined ? this.personaForm.lat : this.lat)
      const lngValue = lng !== null && lng !== undefined
        ? lng
        : (this.personaForm.lng !== '' && this.personaForm.lng !== null && this.personaForm.lng !== undefined ? this.personaForm.lng : this.lng)

      const latLng = [latValue, lngValue]

      if (this.mapMarker) {
        this.mapMarker.setLatLng(latLng)
        this.mapMarker.openPopup()
      } else if (this.map) {
        this.mapMarker = L.marker(latLng, { draggable: true }).addTo(this.map)
          .bindPopup('📍 Ubicación de la persona')
          .openPopup()
      }

      if (this.map) {
        this.map.panTo(latLng)
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
        luz_agua: persona.luz_agua || '',
        correo: persona.correo || '',
        zona: persona.zona || '',
        distrito: persona.distrito || '',
        fecha: this.formatDateValue(persona.fecha)
      }
      this.lat = persona.lat !== undefined && persona.lat !== null && persona.lat !== '' ? persona.lat : this.lat
      this.lng = persona.lng !== undefined && persona.lng !== null && persona.lng !== '' ? persona.lng : this.lng
      this.actualizarMarcadorMapa(this.lat, this.lng)

      this.mascotas = Array.isArray(persona.mascotas)
        ? persona.mascotas.map(mascota => this.normalizarMascota(mascota))
        : []

      this.dialogMascota = false
      this.dialogFotoMascota = false
    },
    normalizarMascota (mascota) {
      return {
        ...mascota,
        especie: mascota.especie || mascota.raza?.especie?.nombre || '',
        razaNombre: mascota.raza?.nombre || '',
        categoriaNombre: mascota.categoria?.nombre || '',
        campaniaNombre: mascota.campania?.nombre || '',
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
        fec_reg: this.formatDateValue(mascota.fec_reg, moment().format('YYYY-MM-DD')),
        especie: mascota.especie || mascota.raza?.especie?.nombre || '',
        fec_nac: this.formatDateValue(mascota.fec_nac),
        edad: mascota.edad ?? null,
        color_principal: mascota.color_principal || '',
        color_secundario: mascota.color_secundario || '',
        tamano: mascota.tamano || '',
        peso: mascota.peso ?? null,
        estado: mascota.estado || 'ACTIVO',
        particular: mascota.particular || '',
        observacion: mascota.observacion || '',
        sexo: mascota.sexo || 'MACHO',
        esterilizado: !!mascota.esterilizado,
        fec_esterilizacion: this.formatDateValue(mascota.fec_esterilizacion),
        campania_id: mascota.campania_id ?? mascota.campania?.id ?? null,
        categoria_id: mascota.categoria_id ?? mascota.categoria?.id ?? null,
        raza_id: mascota.raza_id ?? mascota.raza?.id ?? null
      }
      this.sincronizarEspecieDesdeRaza(this.mascotaForm.raza_id)
      this.dialogMascota = true
    },
    cambiarFotoMascota (mascota) {
      this.mascotaFotoForm = {
        id: mascota.id,
        codigo: mascota.codigo || '',
        nombre: mascota.nombre || '',
        foto: null,
        fotoActualUrl: mascota.fotoUrl || '',
        fec_reg: this.formatDateValue(mascota.fec_reg, moment().format('YYYY-MM-DD')),
        especie: mascota.especie || mascota.raza?.especie?.nombre || '',
        fec_nac: this.formatDateValue(mascota.fec_nac),
        edad: mascota.edad ?? null,
        color_principal: mascota.color_principal || '',
        color_secundario: mascota.color_secundario || '',
        tamano: mascota.tamano || '',
        peso: mascota.peso ?? null,
        estado: mascota.estado || 'ACTIVO',
        particular: mascota.particular || '',
        observacion: mascota.observacion || '',
        sexo: mascota.sexo || 'MACHO',
        esterilizado: !!mascota.esterilizado,
        fec_esterilizacion: this.formatDateValue(mascota.fec_esterilizacion),
        campania_id: mascota.campania_id ?? mascota.campania?.id ?? null,
        categoria_id: mascota.categoria_id ?? mascota.categoria?.id ?? null,
        raza_id: mascota.raza_id ?? mascota.raza?.id ?? null
      }
      this.sincronizarEspecieDesdeRaza(this.mascotaFotoForm.raza_id, true)
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
      payload.append('fec_reg', form.fec_reg || '')
      payload.append('especie', form.especie || '')
      payload.append('fec_nac', form.fec_nac || '')
      payload.append('edad', form.edad ?? '')
      payload.append('color_principal', form.color_principal || '')
      payload.append('color_secundario', form.color_secundario || '')
      payload.append('tamano', form.tamano || '')
      payload.append('peso', form.peso ?? '')
      payload.append('particular', form.particular || '')
      payload.append('estado', form.estado || 'ACTIVO')
      payload.append('observacion', form.observacion || '')
      payload.append('sexo', form.sexo || 'MACHO')
      payload.append('fec_esterilizacion', form.fec_esterilizacion || '')
      payload.append('esterilizado', form.esterilizado ? '1' : '0')
      payload.append('campania_id', form.campania_id ?? '')
      payload.append('categoria_id', form.categoria_id ?? '')
      payload.append('raza_id', form.raza_id ?? '')

      if (form.foto) {
        payload.append('foto', form.foto)
      }

      if (form.id) {
        payload.append('_method', 'PUT')
      }

      return payload
    },
    sincronizarEspecieDesdeRaza (razaId, isFoto = false) {
      const raza = this.razas.find(item => Number(item.id) === Number(razaId))
      const especieNombre = raza?.especie?.nombre || ''

      if (isFoto) {
        this.mascotaFotoForm.especie = especieNombre
        return
      }

      this.mascotaForm.especie = especieNombre
    },
    formatDateValue (value, fallback = '') {
      if (!value) {
        return fallback
      }

      const formatted = moment(value)
      return formatted.isValid() ? formatted.format('YYYY-MM-DD') : fallback
    },
    resetPersonaForm () {
      this.personaForm = emptyPersona()
      this.mascotas = []
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
