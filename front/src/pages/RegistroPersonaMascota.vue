<template>
  <q-page class="app-page">
    <q-card class="app-soft-card">
      <q-card-section class="app-hero text-white">
        <div class="row items-center justify-between q-col-gutter-md">
          <div class="col-12 col-md-8">
            <div class="text-overline text-white-7">Operacion clinica</div>
            <div class="text-h5 text-weight-bolder">Registro de persona y mascota</div>
            <div class="text-body2 q-mt-sm">
              Primero registre o cargue una persona. Luego se habilita el registro y actualizacion de mascota.
            </div>
          </div>
          <div class="col-12 col-md-auto">
            <q-chip class="app-brand-chip" square>
              <q-icon name="sym_r_health_and_safety" class="q-mr-xs" />
              Flujo asistido
            </q-chip>
          </div>
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
          class="text-primary q-mb-md"
          active-color="primary"
          indicator-color="primary"
          @update:model-value="verMapa"
        >
          <q-tab name="persona" icon="sym_r_person" label="Persona" />
          <q-tab name="mascota" icon="sym_r_pets" label="Mascota" :disable="!personaForm.id" />
        </q-tabs>

        <q-tab-panels v-model="tab" animated class="bg-transparent">
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
                <div class="col-12 col-md-2">
                  <q-input v-model="personaForm.lat" label="Latitud" outlined dense readonly />
                </div>
                <div class="col-12 col-md-2">
                  <q-input v-model="personaForm.lng" label="Longitud" outlined dense readonly />
                </div>
              </div>

              <q-card bordered flat class="map-card app-soft-card">
                <q-card-section class="row items-center justify-between q-gutter-sm">
                  <div>
                    <div class="text-subtitle1">Ubicacion en mapa</div>
                    <div class="text-caption text-grey-7">
                      Haga clic en el mapa o arrastre el marcador para guardar latitud y longitud.
                    </div>
                  </div>
                  <q-btn outline color="primary" icon="sym_r_my_location" label="Centrar mapa" @click="myLocation" />
                </q-card-section>
                <q-separator />
                <q-card-section class="q-pa-none">
                  <div ref="map" id="map" style="height: 400px; width: 100%;"></div>
                </q-card-section>
              </q-card>

              <div class="row justify-end q-gutter-sm">
                <q-btn flat label="Limpiar" color="negative" @click="resetPersonaForm" />
                <q-btn color="positive" icon="sym_r_save" :label="personaForm.id ? 'Actualizar persona' : 'Guardar persona'" type="submit" :loading="guardandoPersona" />
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
                icon="sym_r_add"
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
              class="app-table"
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
                  <q-btn v-if="props.row.estado !== 'FALLECIDO'" flat dense icon="sym_r_edit" color="primary" @click="editarMascota(props.row)" />
                  <q-btn v-if="props.row.estado !== 'FALLECIDO'" flat dense icon="sym_r_photo_camera" color="secondary" @click="cambiarFotoMascota(props.row)" />
                  <q-btn flat dense icon="sym_r_skull" color="negative"  @click="abrirDialogFallecimiento(props.row)" />
                  <q-btn v-if="props.row.estado !== 'FALLECIDO'" flat dense icon="sym_r_picture_as_pdf" color="info" :loading="generandoCredencial" @click="abrirCredencialMascota(props.row)" />
                </q-td>
              </template>
            </q-table>

            <div v-if="credencialMascota" class="credential-stage no-print" aria-hidden="true">
              <section ref="credentialFrontRef" class="credential-card credential-card--front">
                <div class="credential-card__top">
                  <div>
                    <div class="credential-card__label">Municipalidad</div>
                    <div class="credential-card__title">Credencial de mascota</div>
                  </div>
                  <div class="credential-card__code">{{ credencialMascota.codigo }}</div>
                </div>

                <div class="credential-front">
                  <div class="credential-photo">
                    <img v-if="credencialMascota.fotoUrl" :src="credencialMascota.fotoUrl" alt="Foto de mascota" crossorigin="anonymous" />
                    <div v-else class="credential-photo__empty">
                      <q-icon name="sym_r_pets" size="56px" />
                      <div>Sin foto</div>
                    </div>
                  </div>

                  <div class="credential-data">
                    <div class="credential-data__row">
                      <span>Nombre</span>
                      <strong>{{ credencialMascota.nombre }}</strong>
                    </div>
                    <div class="credential-data__row">
                      <span>Especie</span>
                      <strong>{{ credencialMascota.especie }}</strong>
                    </div>
                    <div class="credential-data__row">
                      <span>Raza</span>
                      <strong>{{ credencialMascota.raza }}</strong>
                    </div>
                    <div class="credential-data__row">
                      <span>Color</span>
                      <strong>{{ credencialMascota.color }}</strong>
                    </div>
                    <div class="credential-data__row">
                      <span>Tamano</span>
                      <strong>{{ credencialMascota.tamano }}</strong>
                    </div>
                  </div>
                </div>

                <div class="credential-owner">
                  <div class="credential-owner__label">Propietario</div>
                  <div class="credential-owner__name">{{ credencialMascota.propietario.nombre }}</div>
                  <div class="credential-owner__meta">
                    {{ credencialMascota.propietario.cinit }} | {{ credencialMascota.propietario.telefono }}
                  </div>
                </div>
              </section>

              <section ref="credentialBackRef" class="credential-card credential-card--back">
                <div class="credential-card__top credential-card__top--back">
                  <div>
                    <div class="credential-card__label">Control interno</div>
                    <div class="credential-card__title">Datos del propietario</div>
                  </div>
                  <q-badge color="white" text-color="primary" rounded>Impresion</q-badge>
                </div>

                <div class="back-notes">
                  <div class="back-notes__item">
                    <span>Codigo</span>
                    <strong>{{ credencialMascota.codigo }}</strong>
                  </div>
                  <div class="back-notes__item">
                    <span>Propietario</span>
                    <strong>{{ credencialMascota.propietario.nombre }}</strong>
                  </div>
                  <div class="back-notes__item">
                    <span>Telefono</span>
                    <strong>{{ credencialMascota.propietario.telefono }}</strong>
                  </div>
                  <div class="back-notes__item">
                    <span>Direccion</span>
                    <strong>{{ credencialMascota.propietario.direccion }}</strong>
                  </div>
                  <div class="back-notes__item">
                    <span>Zona / Distrito</span>
                    <strong>{{ credencialMascota.propietario.zona }} / {{ credencialMascota.propietario.distrito }}</strong>
                  </div>
                </div>

                <div class="qr-panel q-mt-md">
                  <img v-if="credencialMascota.qrSrc" :src="credencialMascota.qrSrc" crossorigin="anonymous" alt="Codigo QR de mascota" class="qr-panel__img" />
                  <div class="qr-panel__text">
                    <div class="text-weight-bold">{{ credencialMascota.publicLink }}</div>
                    <div class="text-caption">
                      Escanee el codigo para abrir la ficha publica de la mascota sin iniciar sesion.
                    </div>
                  </div>
                </div>
              </section>
            </div>

            <q-dialog v-model="dialogMascota" persistent @hide="resetMascotaForm">
              <q-card class="dialog-card app-soft-card">
                <q-card-section class="bg-primary text-white row items-center justify-between">
                  <div>
                    <div class="text-h6">{{ mascotaForm.id ? 'Editar mascota' : 'Registrar mascota' }}</div>
                    <div class="text-caption">Datos generales de la mascota</div>
                  </div>
                  <q-btn flat round icon="sym_r_close" color="white" v-close-popup />
                </q-card-section>

                <q-card-section class="q-pa-lg">
                  <q-form ref="mascotaFormRef" class="q-gutter-md" @submit.prevent="guardarMascota">
                    <div class="row q-col-gutter-md items-start">
                      <div class="col-12 col-md-4">
                        <q-input :model-value="personaResumen" label="Persona vinculada" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model.number="mascotaForm.numero" type="number" min="1" label="Numero" outlined dense hint="Opcional: si se deja vacio se autogenera" />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input :model-value="codigoMascotaPreview || 'Se generara automaticamente'" label="Codigo" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.nombre" label="Nombre" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model="mascotaForm.fec_nac" type="date" label="Fecha de nacimiento" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model.number="mascotaForm.edad" type="number" min="0" label="Edad" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.tamano" :options="tamanoOptions" label="Tamano" outlined dense use-input emit-value map-options />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input v-model.number="mascotaForm.peso" type="number" step="0.01" label="Peso" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.sexo" :options="sexoOptions" label="Sexo" outlined dense emit-value map-options />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.estado" :options="estadoOptions" label="Estado" outlined dense emit-value map-options />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.color_principal" :options="colorOptions" label="Color principal" outlined dense use-input input-debounce="0" emit-value map-options />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.color_secundario" :options="colorOptions" label="Color secundario" outlined dense use-input input-debounce="0" emit-value map-options clearable />
                      </div>

                      <div class="col-12 col-md-4">
                        <q-select
                          v-model="mascotaForm.especie_id"
                          :options="especieOptions"
                          label="Especie"
                          outlined
                          dense
                          emit-value
                          map-options
                          :rules="[val => !!val || 'Seleccione una especie']"
                          @update:model-value="onEspecieChange"
                        />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select
                          v-model="mascotaForm.raza_id"
                          :options="razaOptionsFiltradas"
                          label="Raza"
                          outlined
                          dense
                          use-input
                          input-debounce="0"
                          emit-value
                          map-options
                          :disable="!mascotaForm.especie_id"
                          :rules="[val => !!val || 'Seleccione una raza']"
                          @filter="filtrarRazas"
                          @update:model-value="sincronizarEspecieDesdeRaza"
                        />
                      </div>

                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.categoria_id" :options="categoriaOptions" label="Categoria" outlined dense emit-value map-options clearable />
                      </div>
                      <div class="col-12 col-md-12">
                        <q-input v-model="mascotaForm.particular" label="Particularidad" outlined dense />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-select v-model="mascotaForm.campania_id" :options="campaniaOptions" label="Campania" outlined dense emit-value map-options clearable />
                      </div>


                      <div class="col-12 col-md-4 flex items-center">
                        <q-checkbox v-model="mascotaForm.esterilizado" label="Esterilizado" @update:model-value="onEsterilizadoChange" />
                      </div>
                      <div class="col-12 col-md-4">
                        <q-input
                          v-model="mascotaForm.fec_esterilizacion"
                          type="date"
                          label="Fecha de esterilizacion"
                          outlined
                          dense
                          :disable="!mascotaForm.esterilizado"
                          :rules="[val => !mascotaForm.esterilizado || !!val || 'Ingrese la fecha de esterilizacion']"
                        />
                      </div>
                      <div class="col-12">
                        <q-input v-model="mascotaForm.observacion" type="textarea" autogrow label="Observacion" outlined dense />
                      </div>
                    </div>

                    <div class="row justify-end q-gutter-sm q-mt-md">
                      <q-btn flat label="Cancelar" color="negative" v-close-popup />
                      <q-btn
                        color="positive"
                        icon="sym_r_save"
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
              <q-card class="dialog-card dialog-card--photo app-soft-card">
                <q-card-section class="bg-secondary text-white row items-center justify-between">
                  <div>
                    <div class="text-h6">Modificar foto</div>
                    <div class="text-caption">{{ mascotaFotoForm.nombre || 'Mascota seleccionada' }}</div>
                  </div>
                  <q-btn flat round icon="sym_r_close" color="white" v-close-popup />
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
                        icon="sym_r_save"
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

            <q-dialog v-model="dialogFallecimientoMascota" persistent @hide="resetMascotaFallecimientoForm">
              <q-card class="dialog-card app-soft-card">
                <q-card-section class="bg-negative text-white row items-center justify-between">
                  <div>
                    <div class="text-h6">{{ mascotaFallecimientoForm.id ? 'Actualizar fallecimiento' : 'Registrar fallecimiento' }}</div>
                    <div class="text-caption">{{ mascotaFallecimientoForm.nombre || 'Mascota seleccionada' }}</div>
                  </div>
                  <q-btn flat round icon="sym_r_close" color="white" v-close-popup />
                </q-card-section>

                <q-card-section class="q-pa-lg">
                  <q-form class="q-gutter-md" @submit.prevent="guardarFallecimientoMascota">
                    <q-banner class="bg-red-1 text-red-10" rounded>
                      En este formulario solo se actualizan el estado, la fecha de fallecimiento, la causa y la observacion.
                    </q-banner>

                    <div class="row q-col-gutter-md items-start">
                      <div class="col-12 col-md-6">
                        <q-input :model-value="personaResumen" label="Persona vinculada" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-6">
                        <q-input :model-value="mascotaFallecimientoForm.codigo" label="Codigo" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-6">
                        <q-input :model-value="mascotaFallecimientoForm.nombre" label="Nombre" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-6">
                        <q-input model-value="FALLECIDO" label="Estado" outlined dense readonly />
                      </div>
                      <div class="col-12 col-md-6">
                        <q-input
                          v-model="mascotaFallecimientoForm.fec_fallecimiento"
                          type="date"
                          label="Fecha de fallecimiento"
                          outlined
                          dense
                        />
                      </div>
                      <div class="col-12 col-md-6">
                        <q-input v-model="mascotaFallecimientoForm.causa_fallecimiento" label="Causa de fallecimiento" outlined dense />
                      </div>
                      <div class="col-12">
                        <q-input v-model="mascotaFallecimientoForm.observacion" type="textarea" autogrow label="Observacion" outlined dense />
                      </div>
                    </div>

                    <div class="row justify-end q-gutter-sm q-mt-md">
                      <q-btn flat label="Cancelar" color="negative" v-close-popup />
                      <q-btn
                        color="negative"
                        icon="sym_r_save"
                        :label="mascotaFallecimientoForm.id ? 'Actualizar fallecimiento' : 'Registrar fallecimiento'"
                        type="submit"
                        :loading="guardandoFallecimientoMascota"
                        :disable="!personaForm.id"
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
import QRCode from 'qrcode'
import { globalStore } from 'src/stores/globalStore'
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
  distrito: ''
})

const emptyMascota = () => ({
  id: null,
  codigo: '',
  numero: null,
  nombre: '',
  fec_reg: moment().format('YYYY-MM-DD'),
  especie: '',
  especie_id: null,
  fec_nac: '',
  edad: null,
  color_principal: '',
  color_secundario: '',
  tamano: '',
  peso: null,
  estado: 'ACTIVO',
  fec_fallecimiento: '',
  causa_fallecimiento: '',
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
  numero: null,
  nombre: '',
  foto: null,
  fotoActualUrl: '',
  fec_reg: moment().format('YYYY-MM-DD'),
  especie: '',
  especie_id: null,
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

const emptyMascotaFallecimiento = () => ({
  id: null,
  codigo: '',
  nombre: '',
  fec_fallecimiento: '',
  causa_fallecimiento: '',
  observacion: ''
})

export default {
  name: 'RegistroPersonaMascota',
  data () {
    return {
      store: globalStore(),
      tamanoOptions: [
        { label: 'PEQUENO', value: 'PEQUENO' },
        { label: 'MEDIANO', value: 'MEDIANO' },
        { label: 'GRANDE', value: 'GRANDE' },
        { label: 'GIGANTE', value: 'GIGANTE' }
      ],
      colorOptions: [
        'BLANCO',
        'NEGRO',
        'GRIS',
        'CAFE',
        'MARRON',
        'CREMA',
        'BEIGE',
        'ROJO',
        'NARANJA',
        'AMARILLO',
        'AZUL',
        'VERDE',
        'DORADO',
        'PLATEADO',
        'CHOCOLATE',
        'ATIGRADO',
        'MANCHADO',
        'MOTEADO'
      ],
      tab: 'persona',
      map: null,
      verificandoPersona: false,
      guardandoPersona: false,
      guardandoMascota: false,
      guardandoFotoMascota: false,
      guardandoFallecimientoMascota: false,
      generandoCredencial: false,
      dialogMascota: false,
      dialogFotoMascota: false,
      dialogFallecimientoMascota: false,
      modoMascotaForm: 'normal',
      credencialMascota: null,
      mensajePersona: '',
      mensajeTipo: '',
      personaForm: emptyPersona(),
      mascotaForm: emptyMascota(),
      mascotaFotoForm: emptyMascotaFoto(),
      mascotaFallecimientoForm: emptyMascotaFallecimiento(),
      mascotas: [],
      especies: [],
      categorias: [],
      razas: [],
      razaFiltro: '',
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
    especieOptions () {
      return this.especies.map(especie => ({
        label: especie.nombre,
        value: especie.id
      }))
    },
    codigoMascotaPreview () {
      return this.generarCodigoMascota(this.mascotaForm.especie_id, this.mascotaForm.numero)
    },
    razaOptionsFiltradas () {
      const especieId = this.mascotaForm.especie_id
      const texto = (this.razaFiltro || '').trim().toLowerCase()

      return this.razas
        .filter(raza => especieId && Number(raza.especie_id) === Number(especieId))
        .filter(raza => {
          if (!texto) {
            return true
          }

          const especieNombre = raza.especie?.nombre || ''
          return `${raza.nombre} ${especieNombre}`.toLowerCase().includes(texto)
        })
        .map(raza => ({
          label: raza.nombre,
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
    if (!this.store.isLoggedIn) {
      this.$router.push('/')
      return
    }

    if (!this.store.bool_registro_persona_mascota) {
      this.$router.push('/home')
      return
    }

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

        if (this.mascotaForm.especie_id) {
          this.sincronizarEspecieDesdeSeleccion(this.mascotaForm.especie_id)
        }
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
      }
      this.lat = persona.lat !== undefined && persona.lat !== null && persona.lat !== '' ? persona.lat : this.lat
      this.lng = persona.lng !== undefined && persona.lng !== null && persona.lng !== '' ? persona.lng : this.lng
      this.actualizarMarcadorMapa(this.lat, this.lng)

      this.mascotas = Array.isArray(persona.mascotas)
        ? persona.mascotas.map(mascota => this.normalizarMascota(mascota))
        : []

      this.dialogMascota = false
      this.dialogFotoMascota = false
      this.dialogFallecimientoMascota = false
    },
    normalizarMascota (mascota) {
      return {
        ...mascota,
        especie: mascota.especie || mascota.raza?.especie?.nombre || '',
        especie_id: mascota.raza?.especie?.id || null,
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
      if ((mascota.estado || '').toString().toUpperCase() === 'FALLECIDO') {
        this.abrirDialogFallecimiento(mascota)
        return
      }

      const especieId = mascota.raza?.especie?.id ?? null
      const especieCodigo = mascota.raza?.especie?.codigo || this.especies.find(item => Number(item.id) === Number(especieId))?.codigo || ''
      this.modoMascotaForm = 'normal'
      this.mascotaForm = {
        id: mascota.id,
        codigo: mascota.codigo || '',
        numero: this.extraerNumeroDesdeCodigo(mascota.codigo || '', especieCodigo),
        nombre: mascota.nombre || '',
        fec_reg: this.formatDateValue(mascota.fec_reg, moment().format('YYYY-MM-DD')),
        especie: mascota.especie || mascota.raza?.especie?.nombre || '',
        especie_id: especieId,
        fec_nac: this.formatDateValue(mascota.fec_nac),
        edad: mascota.edad ?? null,
        color_principal: mascota.color_principal || '',
        color_secundario: mascota.color_secundario || '',
        tamano: mascota.tamano || '',
        peso: mascota.peso ?? null,
        estado: mascota.estado || 'ACTIVO',
        fec_fallecimiento: this.formatDateValue(mascota.fec_fallecimiento),
        causa_fallecimiento: mascota.causa_fallecimiento || '',
        particular: mascota.particular || '',
        observacion: mascota.observacion || '',
        sexo: mascota.sexo || 'MACHO',
        esterilizado: !!mascota.esterilizado,
        fec_esterilizacion: this.formatDateValue(mascota.fec_esterilizacion),
        campania_id: mascota.campania_id ?? mascota.campania?.id ?? null,
        categoria_id: mascota.categoria_id ?? mascota.categoria?.id ?? null,
        raza_id: mascota.raza_id ?? mascota.raza?.id ?? null
      }
      this.sincronizarEspecieDesdeSeleccion(this.mascotaForm.especie_id)
      this.sincronizarEspecieDesdeRaza(this.mascotaForm.raza_id)
      this.dialogMascota = true
    },
    abrirDialogFallecimiento (mascota) {
      this.modoMascotaForm = 'fallecimiento'
      this.dialogMascota = false
      this.dialogFotoMascota = false
      this.mascotaFallecimientoForm = {
        id: mascota.id,
        codigo: mascota.codigo || '',
        nombre: mascota.nombre || '',
        fec_fallecimiento: this.formatDateValue(mascota.fec_fallecimiento),
        causa_fallecimiento: mascota.causa_fallecimiento || '',
        observacion: mascota.observacion || ''
      }
      this.dialogFallecimientoMascota = true
    },
    cambiarFotoMascota (mascota) {
      if ((mascota.estado || '').toString().toUpperCase() === 'FALLECIDO') {
        this.mostrarMensaje('La mascota fallecida solo permite actualizar datos de fallecimiento.', 'warning')
        return
      }

      this.mascotaFotoForm = {
        id: mascota.id,
        codigo: mascota.codigo || '',
        numero: this.extraerNumeroDesdeCodigo(mascota.codigo || '', mascota.raza?.especie?.codigo || ''),
        nombre: mascota.nombre || '',
        foto: null,
        fotoActualUrl: mascota.fotoUrl || '',
        fec_reg: this.formatDateValue(mascota.fec_reg, moment().format('YYYY-MM-DD')),
        especie: mascota.especie || mascota.raza?.especie?.nombre || '',
        especie_id: mascota.raza?.especie?.id ?? null,
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
      this.sincronizarEspecieDesdeSeleccion(this.mascotaFotoForm.especie_id, true)
      this.sincronizarEspecieDesdeRaza(this.mascotaFotoForm.raza_id, true)
      this.dialogFotoMascota = true
    },
    async abrirCredencialMascota (mascota) {
      if (!mascota?.codigo) {
        this.mostrarMensaje('La mascota no tiene codigo para generar la credencial.', 'warning')
        return
      }

      this.credencialMascota = await this.buildCredencialMascota(mascota)

      try {
        const { data, headers } = await this.$api.get(`public/mascota/${encodeURIComponent(mascota.codigo)}/pdf`, {
          responseType: 'blob'
        })

        const blob = new Blob([data], {
          type: headers?.['content-type'] || 'application/pdf'
        })
        const blobUrl = URL.createObjectURL(blob)
        const win = window.open(blobUrl, '_blank', 'noopener,noreferrer')


        window.setTimeout(() => URL.revokeObjectURL(blobUrl), 60000)
        this.mostrarMensaje('Credencial PDF abierta correctamente.', 'success')
      } catch (error) {
        this.mostrarMensaje('No se pudo abrir la credencial PDF.', 'negative')
      }
    },
    async guardarMascota () {
      if (!this.personaForm.id) {
        this.mostrarMensaje('Primero debe guardar o cargar una persona.', 'warning')
        return
      }

      try {
        const valido = await this.$refs.mascotaFormRef?.validate?.()
        if (valido === false) {
          return
        }

        if (this.mascotaForm.esterilizado && !this.mascotaForm.fec_esterilizacion) {
          this.mostrarMensaje('Ingrese la fecha de esterilizacion.', 'warning')
          return
        }

        if (this.mascotaForm.numero !== null && this.mascotaForm.numero !== '' && this.normalizeMascotaNumero(this.mascotaForm.numero) === null) {
          this.mostrarMensaje('Ingrese un numero valido para la mascota.', 'warning')
          return
        }

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
        const mensaje = apiError?.errors?.numero?.[0] || apiError?.errors?.codigo?.[0] || apiError?.message || 'No se pudo guardar la mascota.'
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

      if (!this.mascotaFotoForm.id) {
        this.mostrarMensaje('Primero debe seleccionar una mascota.', 'warning')
        return
      }

      if (!this.mascotaFotoForm.foto) {
        this.mostrarMensaje('Seleccione una foto para actualizar.', 'warning')
        return
      }

      try {
        this.guardandoFotoMascota = true
        const payload = new FormData()
        payload.append('foto', this.mascotaFotoForm.foto)

        const { data } = await this.$api.post(`mascota/${this.mascotaFotoForm.id}/foto`, payload)

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
    async guardarFallecimientoMascota () {
      if (!this.personaForm.id) {
        this.mostrarMensaje('Primero debe guardar o cargar una persona.', 'warning')
        return
      }

      if (!this.mascotaFallecimientoForm.id) {
        this.mostrarMensaje('Primero debe seleccionar una mascota.', 'warning')
        return
      }

      try {
        this.guardandoFallecimientoMascota = true
        const payload = new FormData()
        payload.append('fec_fallecimiento', this.mascotaFallecimientoForm.fec_fallecimiento || '')
        payload.append('causa_fallecimiento', this.mascotaFallecimientoForm.causa_fallecimiento || '')
        payload.append('observacion', this.mascotaFallecimientoForm.observacion || '')

        const { data } = await this.$api.post(`mascota/${this.mascotaFallecimientoForm.id}/fallecimiento`, payload)

        const personaResponse = await this.$api.get(`persona/${this.personaForm.id}`)
        this.cargarPersonaEnFormulario(personaResponse.data.data)
        this.mostrarMensaje(data.message || 'Fallecimiento actualizado.', 'success')
      } catch (error) {
        const apiError = error?.response?.data
        const mensaje = apiError?.errors?.fec_fallecimiento?.[0] || apiError?.message || 'No se pudo registrar el fallecimiento.'
        this.mostrarMensaje(mensaje, 'negative')
      } finally {
        this.guardandoFallecimientoMascota = false
      }
    },
    buildMascotaPayload (form) {
      const payload = new FormData()

      payload.append('persona_id', this.personaForm.id)
      payload.append('numero', form.numero ?? '')
      payload.append('nombre', form.nombre || '')
      payload.append('fec_reg', form.fec_reg || '')
      payload.append('especie', form.especie || '')
      payload.append('especie_id', form.especie_id ?? '')
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
    sincronizarEspecieDesdeSeleccion (especieId, isFoto = false) {
      const especie = this.especies.find(item => Number(item.id) === Number(especieId))
      const especieNombre = especie?.nombre || ''
      const especieValue = especie?.id ?? null

      if (isFoto) {
        this.mascotaFotoForm.especie = especieNombre
        this.mascotaFotoForm.especie_id = especieValue
        return
      }

      this.mascotaForm.especie = especieNombre
      this.mascotaForm.especie_id = especieValue
    },
    generarCodigoMascota (especieId, numero) {
      const especie = this.especies.find(item => Number(item.id) === Number(especieId))
      const especieCodigo = (especie?.codigo || '').toString().trim().toUpperCase()
      const numeroValue = this.normalizeMascotaNumero(numero)

      if (!especieCodigo || numeroValue === null) {
        return ''
      }

      return `${especieCodigo}-${numeroValue}`
    },
    normalizeMascotaNumero (numero) {
      if (numero === null || numero === undefined || numero === '') {
        return null
      }

      const parsed = Number.parseInt(numero, 10)
      return Number.isFinite(parsed) && parsed > 0 ? parsed : null
    },
    extraerNumeroDesdeCodigo (codigo, especieCodigo) {
      const codigoNormalizado = (codigo || '').trim().toUpperCase()
      const prefijo = (especieCodigo || '').trim().toUpperCase()

      if (!codigoNormalizado || !prefijo || !codigoNormalizado.startsWith(prefijo)) {
        return null
      }

      const resto = codigoNormalizado.slice(prefijo.length).replace(/^[\s-]+/, '')
      const parsed = Number.parseInt(resto, 10)
      return Number.isFinite(parsed) && parsed > 0 ? parsed : null
    },
    sincronizarEspecieDesdeRaza (razaId, isFoto = false) {
      const raza = this.razas.find(item => Number(item.id) === Number(razaId))
      const especieNombre = raza?.especie?.nombre || ''
      const especieId = raza?.especie?.id || null

      if (isFoto) {
        this.mascotaFotoForm.especie = especieNombre
        if (!this.mascotaFotoForm.especie_id) {
          this.mascotaFotoForm.especie_id = especieId
        }
        return
      }

      this.mascotaForm.especie = especieNombre
      if (!this.mascotaForm.especie_id) {
        this.mascotaForm.especie_id = especieId
      }
    },
    onEspecieChange (especieId) {
      this.razaFiltro = ''
      this.mascotaForm.raza_id = null
      this.sincronizarEspecieDesdeSeleccion(especieId)
    },
    onEsterilizadoChange (value) {
      if (!value) {
        this.mascotaForm.fec_esterilizacion = ''
      }
    },
    filtrarRazas (val, update) {
      this.razaFiltro = val || ''
      update(() => {})
    },
    formatDateValue (value, fallback = '') {
      if (!value) {
        return fallback
      }

      const formatted = moment(value)
      return formatted.isValid() ? formatted.format('YYYY-MM-DD') : fallback
    },
    async buildCredencialMascota (mascota) {
      const persona = mascota?.persona || this.personaForm || {}
      const nombrePropietario = [persona.nombre, persona.paterno, persona.materno].filter(Boolean).join(' ') || '-'
      const publicLink = this.buildPublicLink(mascota?.codigo || '')
      const qrSrc = publicLink
        ? await QRCode.toDataURL(publicLink, {
            margin: 1,
            width: 260,
            errorCorrectionLevel: 'M',
            color: {
              dark: '#0f172a',
              light: '#ffffff'
            }
          })
        : ''

      return {
        id: mascota.id,
        codigo: mascota.codigo || '-',
        nombre: mascota.nombre || '-',
        fotoUrl: mascota.fotoUrl || '',
        especie: mascota.especie || mascota.raza?.especie?.nombre || '-',
        raza: mascota.raza?.nombre || '-',
        color: [mascota.color_principal, mascota.color_secundario].filter(Boolean).join(' / ') || '-',
        tamano: mascota.tamano || '-',
        publicLink,
        qrSrc,
        propietario: {
          nombre: nombrePropietario,
          cinit: [persona.cinit, persona.complemento].filter(Boolean).join(' ') || '-',
          telefono: persona.telefono || '-',
          direccion: persona.direccion || '-',
          zona: persona.zona || '-',
          distrito: persona.distrito || '-'
        }
      }
    },
    buildPublicLink (codigo) {
      const codigoNormalizado = (codigo || '').trim()

      if (!codigoNormalizado) {
        return ''
      }

      const routePath = this.$router.resolve({
        path: `/credencial-mascota/${encodeURIComponent(codigoNormalizado)}`
      }).href

      return `${window.location.origin}${routePath}`
    },
    waitForMedia (root) {
      const media = root?.querySelectorAll?.('img') || []

      return Promise.all(Array.from(media).map((element) => {
        if (element.complete) {
          return Promise.resolve()
        }

        return new Promise((resolve) => {
          element.onload = () => resolve()
          element.onerror = () => resolve()
        })
      }))
    },
    resetPersonaForm () {
      this.personaForm = emptyPersona()
      this.mascotas = []
    },
    resetMascotaForm () {
      this.mascotaForm = emptyMascota()
      this.razaFiltro = ''
    },
    resetMascotaFotoForm () {
      this.mascotaFotoForm = emptyMascotaFoto()
    },
    resetMascotaFallecimientoForm () {
      this.mascotaFallecimientoForm = emptyMascotaFallecimiento()
      this.modoMascotaForm = 'normal'
    },
    limpiarTodo () {
      this.resetPersonaForm()
      this.resetMascotaForm()
      this.resetMascotaFotoForm()
      this.resetMascotaFallecimientoForm()
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

.credential-stage {
  position: fixed;
  left: -1200px;
  top: 0;
  width: 856px;
  pointer-events: none;
}

.credential-card {
  min-height: 0;
  aspect-ratio: 1.586;
  border-radius: 24px;
  overflow: hidden;
  background:
    linear-gradient(180deg, rgba(255,255,255,.96), rgba(248,250,252,.98)),
    linear-gradient(135deg, #ffffff, #eff6ff);
  box-shadow: 0 18px 44px rgba(15,23,42,.18);
  border: 1px solid rgba(15,23,42,.12);
  position: relative;
  padding: 18px;
}

.credential-card--back {
  background:
    radial-gradient(circle at top right, rgba(14,165,233,.12), transparent 30%),
    linear-gradient(180deg, #ffffff 0%, #eef2ff 100%);
}

.credential-card__top {
  display: flex;
  justify-content: space-between;
  align-items: start;
  gap: 12px;
  margin-bottom: 18px;
}

.credential-card__top--back {
  align-items: center;
}

.credential-card__label {
  font-size: .68rem;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: #0f766e;
}

.credential-card__title {
  font-size: 1.16rem;
  font-weight: 900;
  color: #0f172a;
}

.credential-card__code {
  font-size: .9rem;
  font-weight: 900;
  color: #0f172a;
  background: #dbeafe;
  padding: 8px 12px;
  border-radius: 999px;
}

.credential-front {
  display: grid;
  grid-template-columns: 132px minmax(0, 1fr);
  gap: 14px;
  align-items: start;
}

.credential-photo {
  border-radius: 18px;
  overflow: hidden;
  background: linear-gradient(180deg, #e2e8f0, #cbd5e1);
  aspect-ratio: 3 / 4;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 0 0 1px rgba(15,23,42,.08);
}

.credential-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.credential-photo__empty {
  color: #334155;
  display: grid;
  justify-items: center;
  gap: 8px;
  font-weight: 700;
}

.credential-data {
  display: grid;
  gap: 8px;
}

.credential-data__row {
  padding: 10px 12px;
  background: #f8fafc;
  border-radius: 14px;
  border: 1px solid rgba(15,23,42,.06);
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.credential-data__row span,
.back-notes__item span {
  font-size: .78rem;
  text-transform: uppercase;
  letter-spacing: .12em;
  color: #64748b;
}

.credential-owner {
  margin-top: 14px;
  padding: 14px 16px;
  border-radius: 18px;
  background: linear-gradient(135deg, #0f766e, #0891b2);
  color: #fff;
}

.credential-owner__label {
  font-size: .66rem;
  text-transform: uppercase;
  letter-spacing: .16em;
  color: rgba(255,255,255,.72);
}

.credential-owner__name {
  margin-top: 4px;
  font-size: 1rem;
  font-weight: 900;
}

.credential-owner__meta {
  margin-top: 6px;
  color: rgba(255,255,255,.86);
  font-size: .82rem;
}

.qr-panel {
  display: grid;
  justify-items: center;
  gap: 12px;
  margin-top: 14px;
  padding: 16px;
  border-radius: 20px;
  background: rgba(255,255,255,.72);
  border: 1px solid rgba(15,23,42,.08);
}

.qr-panel__img {
  width: 190px;
  height: 190px;
  object-fit: contain;
  background: #fff;
  padding: 10px;
  border-radius: 18px;
}

.qr-panel__text {
  text-align: center;
  max-width: 300px;
  color: #0f172a;
  font-size: .88rem;
  word-break: break-all;
}

.back-notes {
  display: grid;
  gap: 10px;
  margin-top: 14px;
}

.back-notes__item {
  padding: 12px 14px;
  border-radius: 14px;
  background: #fff;
  border: 1px solid rgba(15,23,42,.08);
  display: flex;
  justify-content: space-between;
  gap: 10px;
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
