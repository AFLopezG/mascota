<template>
  <div class="public-card-page">
    <div class="public-shell">
      <div class="public-hero">
        <div class="public-hero__eyebrow">Informacion de mascota</div>
        <h1 class="public-hero__title">Consulta publica por codigo</h1>
        <p class="public-hero__subtitle">
          Ingrese el codigo de la mascota para recuperar sus datos de registro y los datos del propietario.
        </p>

        <q-form class="public-search" @submit.prevent="buscarMascota(codigo)">
          <div class="row q-col-gutter-md items-end">
            <div class="col-12 col-md-8">
              <q-input
                v-model="codigo"
                outlined
                dense
                color="primary"
                label="Codigo de mascota"
                placeholder="Ej: CAN-123"
                class="public-search__input"
                @keyup.enter="buscarMascota(codigo)"
              />
            </div>
            <div class="col-12 col-md-4">
              <q-btn
                class="full-width"
                unelevated
                color="primary"
                icon="sym_r_search"
                label="Buscar"
                :loading="buscando"
                type="submit"
                @click="buscarMascota(codigo)"
              />
            </div>
          </div>
        </q-form>

        <div class="row q-col-gutter-md q-mt-md">
          <div class="col-12 col-md-4">
            <div class="public-metric">
              <div class="public-metric__label">Estado</div>
              <div class="public-metric__value">{{ mascota?.estado || 'Sin consulta' }}</div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="public-metric">
              <div class="public-metric__label">Mascota</div>
              <div class="public-metric__value">{{ mascota?.nombre || '-' }}</div>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="public-metric">
              <div class="public-metric__label">Codigo</div>
              <div class="public-metric__value">{{ mascota?.codigo || '-' }}</div>
            </div>
          </div>
        </div>
      </div>

      <q-banner v-if="mensaje" rounded class="q-mt-lg" :class="mensajeClass">
        {{ mensaje }}
      </q-banner>

      <div v-if="mascota" class="result-shell q-mt-lg">
        <q-card class="result-card">
          <q-card-section>
            <div class="result-card__header">
              <div>
                <div class="result-card__eyebrow">Resultado de consulta</div>
                <div class="result-card__title">{{ mascota.nombre }}</div>
                <div class="result-card__subtitle">Codigo: {{ mascota.codigo }}</div>
              </div>
              <q-badge color="primary" rounded>{{ mascota.estado || 'SIN ESTADO' }}</q-badge>
            </div>

            <div class="result-grid q-mt-lg">
              <div class="result-photo">
                <img v-if="mascota.fotoUrl" :src="mascota.fotoUrl" alt="Foto de mascota" />
                <div v-else class="result-photo__empty">
                  <q-icon name="sym_r_pets" size="56px" />
                  <div>Sin foto</div>
                </div>
              </div>

              <div class="result-panel">
                <div class="result-panel__section">
                  <div class="result-panel__section-title">Datos de la mascota</div>
                  <div class="result-row">
                    <span>Especie</span>
                    <strong>{{ mascota.especie || '-' }}</strong>
                  </div>
                  <div class="result-row">
                    <span>Raza</span>
                    <strong>{{ mascota.raza || '-' }}</strong>
                  </div>
                  <div class="result-row">
                    <span>Color</span>
                    <strong>{{ colorResumen }}</strong>
                  </div>
                  <div class="result-row">
                    <span>Tamano</span>
                    <strong>{{ mascota.tamano || '-' }}</strong>
                  </div>
                </div>

                <div class="result-panel__section q-mt-lg">
                  <div class="result-panel__section-title">Datos del propietario</div>
                  <div class="result-row">
                    <span>Nombre</span>
                    <strong>{{ personaNombre }}</strong>
                  </div>
                  <div class="result-row">
                    <span>CI</span>
                    <strong>{{ personaDocumento }}</strong>
                  </div>
                  <div class="result-row">
                    <span>Telefono</span>
                    <strong>{{ personaTelefono }}</strong>
                  </div>
                  <div class="result-row">
                    <span>Direccion</span>
                    <strong>{{ personaUbicacion }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <q-card v-else class="search-empty q-mt-lg">
        <q-card-section class="text-center q-pa-xl">
          <q-icon name="sym_r_qr_code_2" size="56px" color="primary" />
          <div class="text-h6 q-mt-md">Busque una mascota por su codigo</div>
          <div class="text-body2 text-grey-7">
            Puede ingresar el codigo manualmente o usar el codigo de la URL.
          </div>
        </q-card-section>
      </q-card>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CredencialMascotaPublica',
  data () {
    return {
      codigo: '',
      buscando: false,
      mascota: null,
      mensaje: '',
      sincronizandoRuta: false
    }
  },
  computed: {
    mensajeClass () {
      if (!this.mensaje) {
        return 'bg-blue-1 text-blue-10'
      }

      return this.mascota ? 'bg-green-1 text-green-10' : 'bg-orange-1 text-orange-10'
    },
    personaNombre () {
      const persona = this.mascota?.persona
      return [persona?.nombre, persona?.paterno, persona?.materno].filter(Boolean).join(' ') || '-'
    },
    personaDocumento () {
      const persona = this.mascota?.persona
      const documento = [persona?.cinit, persona?.complemento].filter(Boolean).join('-')
      return documento || '-'
    },
    personaTelefono () {
      const persona = this.mascota?.persona
      return persona?.telefono || '-'
    },
    personaUbicacion () {
      const persona = this.mascota?.persona
      return [persona?.direccion, persona?.zona, persona?.distrito].filter(Boolean).join(' | ') || '-'
    },
    colorResumen () {
      return [this.mascota?.color_principal, this.mascota?.color_secundario].filter(Boolean).join(' / ') || '-'
    }
  },
  watch: {
    '$route': {
      immediate: true,
      handler (route) {
        if (this.sincronizandoRuta) {
          this.sincronizandoRuta = false
          return
        }

        const codigoRuta = String(route?.params?.codigo || route?.query?.codigo || '').trim()
        if (!codigoRuta) {
          this.codigo = ''
          this.mascota = null
          this.mensaje = 'Ingrese un codigo de mascota para recuperar sus datos.'
          return
        }

        this.codigo = codigoRuta
        this.buscarMascota(codigoRuta, false)
      }
    }
  },
  methods: {
    async buscarMascota (codigoIngresado = this.codigo, syncRoute = true) {
      const codigoBase = typeof codigoIngresado === 'string' || typeof codigoIngresado === 'number'
        ? codigoIngresado
        : this.codigo
      const codigo = String(codigoBase || this.$route.params.codigo || this.$route.query.codigo || '').trim()

      if (!codigo) {
        this.mascota = null
        this.mensaje = 'Ingrese un codigo de mascota.'
        return
      }

      this.codigo = codigo
      this.buscando = true
      this.mensaje = ''

      try {
        if (syncRoute && (this.$route.params.codigo !== codigo || this.$route.query.codigo)) {
          this.sincronizandoRuta = true
          await this.$router.replace({
            path: `/credencial-mascota/${encodeURIComponent(codigo)}`
          })
        }

        const { data } = await this.$api.get(`public/mascota/${encodeURIComponent(codigo)}`)
        this.mascota = data?.data || null
        this.mensaje = this.mascota
          ? 'Mascota encontrada.'
          : 'No se encontro la mascota.'
      } catch (error) {
        this.mascota = null
        this.mensaje = error?.response?.data?.message || 'No se pudo recuperar la mascota.'
      } finally {
        this.buscando = false
      }
    }
  }
}
</script>

<style scoped>
.public-card-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top left, rgba(255,255,255,.18), transparent 38%),
    linear-gradient(160deg, #0f766e 0%, #164e63 42%, #0f172a 100%);
  color: #f8fafc;
}

.public-shell {
  max-width: 1180px;
  margin: 0 auto;
  padding: 32px 18px 40px;
}

.public-hero {
  border: 1px solid rgba(255,255,255,.12);
  background: rgba(2,6,23,.35);
  backdrop-filter: blur(10px);
  border-radius: 28px;
  padding: 28px;
  box-shadow: 0 24px 80px rgba(0,0,0,.24);
}

.public-hero__eyebrow {
  text-transform: uppercase;
  letter-spacing: .18em;
  font-size: .75rem;
  color: rgba(255,255,255,.72);
}

.public-hero__title {
  margin: 8px 0 0;
  font-size: clamp(2rem, 5vw, 3.8rem);
  line-height: 1;
  font-weight: 900;
}

.public-hero__subtitle {
  max-width: 760px;
  margin: 14px 0 0;
  font-size: 1rem;
  color: rgba(255,255,255,.82);
}

.public-search {
  margin-top: 22px;
  max-width: 860px;
}

.public-search__input :deep(.q-field__control) {
  border-radius: 18px;
}

.public-metric {
  border-radius: 20px;
  padding: 16px 18px;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.12);
}

.public-metric__label {
  font-size: .78rem;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: rgba(255,255,255,.66);
}

.public-metric__value {
  margin-top: 4px;
  font-size: 1.1rem;
  font-weight: 800;
}

.result-card {
  border-radius: 24px;
  background: rgba(255,255,255,.96);
  color: #0f172a;
  box-shadow: 0 18px 44px rgba(15,23,42,.18);
}

.result-card__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.result-card__eyebrow {
  text-transform: uppercase;
  letter-spacing: .14em;
  font-size: .72rem;
  color: #0f766e;
}

.result-card__title {
  margin-top: 4px;
  font-size: 1.5rem;
  font-weight: 900;
}

.result-card__subtitle {
  margin-top: 4px;
  color: #64748b;
}

.result-grid {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 20px;
}

.result-photo {
  border-radius: 20px;
  overflow: hidden;
  background: linear-gradient(180deg, #e2e8f0, #cbd5e1);
  aspect-ratio: 3 / 4;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: inset 0 0 0 1px rgba(15,23,42,.08);
}

.result-photo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.result-photo__empty {
  color: #334155;
  display: grid;
  justify-items: center;
  gap: 8px;
  font-weight: 700;
}

.result-panel {
  display: grid;
}

.result-panel__section-title {
  margin-bottom: 12px;
  font-size: .78rem;
  font-weight: 800;
  letter-spacing: .14em;
  text-transform: uppercase;
  color: #0f766e;
}

.result-row {
  padding: 10px 12px;
  margin-bottom: 8px;
  background: #f8fafc;
  border-radius: 14px;
  border: 1px solid rgba(15,23,42,.06);
  display: flex;
  justify-content: space-between;
  gap: 12px;
}

.result-row span {
  font-size: .78rem;
  text-transform: uppercase;
  letter-spacing: .12em;
  color: #64748b;
}

.search-empty {
  background: rgba(255,255,255,.92);
  border-radius: 24px;
}

@media (max-width: 1024px) {
  .result-grid {
    grid-template-columns: 1fr;
  }
}
</style>
