<template>
  <q-page class="public-card-page">
    <div class="public-shell">
      <div class="public-hero">
        <div class="public-hero__eyebrow">Credencial de mascota</div>
        <h1 class="public-hero__title">Consulta pública por código</h1>
        <p class="public-hero__subtitle">
          Ingrese el código de la mascota para recuperar sus datos esenciales y generar una credencial lista para impresión.
        </p>

        <div class="public-search">
          <q-input
            v-model="codigo"
            outlined
            dense
            bg-color="white"
            color="primary"
            label="Codigo de mascota"
            placeholder="Ej: CAN-123"
            class="public-search__input"
            @keyup.enter="buscarMascota"
          >
            <template #append>
              <q-btn
                unelevated
                color="primary"
                icon="sym_r_search"
                label="Buscar"
                :loading="buscando"
                @click="buscarMascota"
              />
            </template>
          </q-input>
        </div>

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

      <div v-if="mascota" class="print-area q-mt-lg">
        <div class="credential-grid">
          <section ref="credentialFrontRef" class="credential-card credential-card--front">
            <div class="credential-card__top">
              <div>
                <div class="credential-card__label">Municipalidad</div>
                <div class="credential-card__title">Credencial Canina</div>
              </div>
              <div class="credential-card__code">{{ mascota.codigo }}</div>
            </div>

            <div class="credential-front">
              <div class="credential-photo">
                <img v-if="mascota.fotoUrl" :src="mascota.fotoUrl" alt="Foto de mascota" />
                <div v-else class="credential-photo__empty">
                  <q-icon name="sym_r_pets" size="56px" />
                  <div>Sin foto</div>
                </div>
              </div>

              <div class="credential-data">
                <div class="credential-data__row">
                  <span>Nombre</span>
                  <strong>{{ mascota.nombre }}</strong>
                </div>
                <div class="credential-data__row">
                  <span>Especie</span>
                  <strong>{{ mascota.especie || '-' }}</strong>
                </div>
                <div class="credential-data__row">
                  <span>Raza</span>
                  <strong>{{ mascota.raza || '-' }}</strong>
                </div>
                <div class="credential-data__row">
                  <span>Color</span>
                  <strong>{{ colorResumen }}</strong>
                </div>
                <div class="credential-data__row">
                  <span>Tamaño</span>
                  <strong>{{ mascota.tamano || '-' }}</strong>
                </div>
              </div>
            </div>

            <div class="credential-owner">
              <div class="credential-owner__label">Propietario</div>
              <div class="credential-owner__name">{{ personaNombre }}</div>
              <div class="credential-owner__meta">{{ personaMeta }}</div>
            </div>
          </section>

          <section ref="credentialBackRef" class="credential-card credential-card--back">
            <div class="credential-card__top credential-card__top--back">
              <div>
                <div class="credential-card__label">Verificacion</div>
                <div class="credential-card__title">Codigo QR</div>
              </div>
              <q-badge color="white" text-color="primary" rounded>Publico</q-badge>
            </div>

            <div class="qr-panel">
              <img v-if="qrSrc" :src="qrSrc" crossorigin="anonymous" alt="Codigo QR de mascota" class="qr-panel__img" />
              <div class="qr-panel__text">
                <div class="text-weight-bold">{{ publicLink }}</div>
                <div class="text-caption">
                  Escanee el codigo o ingrese el enlace en el navegador para ver solo la informacion esencial.
                </div>
              </div>
            </div>

            <div class="back-notes">
              <div class="back-notes__item">
                <span>Codigo</span>
                <strong>{{ mascota.codigo }}</strong>
              </div>
              <div class="back-notes__item">
                <span>Propietario</span>
                <strong>{{ personaNombre }}</strong>
              </div>
              <div class="back-notes__item">
                <span>Telefono</span>
                <strong>{{ mascota.persona?.telefono || '-' }}</strong>
              </div>
            </div>
          </section>
        </div>

        <div class="row justify-end q-gutter-sm q-mt-lg no-print">
          <q-btn unelevated color="primary" icon="sym_r_picture_as_pdf" label="Generar PDF" :loading="generandoPdf" @click="generatePdfCredential" />
          <q-btn outline color="primary" icon="sym_r_print" label="Imprimir credencial" @click="printCredential" />
          <q-btn flat color="secondary" icon="sym_r_content_copy" label="Copiar enlace" @click="copyLink" />
        </div>
      </div>

      <q-card v-else class="search-empty q-mt-lg">
        <q-card-section class="text-center q-pa-xl">
          <q-icon name="sym_r_qr_code_2" size="56px" color="primary" />
          <div class="text-h6 q-mt-md">Busque una mascota por su codigo</div>
          <div class="text-body2 text-grey-7">
            Puede ingresar el codigo manualmente o usar el enlace impreso en la credencial.
          </div>
        </q-card-section>
      </q-card>
    </div>
  </q-page>
</template>

<script>
import QRCode from 'qrcode'

export default {
  name: 'CredencialMascotaPublica',
  data () {
    return {
      codigo: '',
      buscando: false,
      generandoPdf: false,
      mascota: null,
      mensaje: '',
      qrSrc: ''
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
    personaMeta () {
      const persona = this.mascota?.persona
      return [persona?.cinit, persona?.direccion, persona?.zona].filter(Boolean).join(' | ') || '-'
    },
    colorResumen () {
      return [this.mascota?.color_principal, this.mascota?.color_secundario].filter(Boolean).join(' / ') || '-'
    },
    publicLink () {
      if (!this.mascota?.codigo) {
        return ''
      }

      const routePath = this.$router.resolve({
        path: `/credencial-mascota/${encodeURIComponent(this.mascota.codigo)}`
      }).href

      return `${window.location.origin}${routePath}`
    }
  },
  watch: {
    '$route.params.codigo': {
      immediate: true,
      handler (value) {
        this.codigo = value ? String(value) : this.codigo

        if (value) {
          this.buscarMascota()
        }
      }
    },
    publicLink: {
      immediate: true,
      handler (value) {
        this.actualizarQr(value)
      }
    }
  },
  methods: {
    async buscarMascota () {
      const codigo = String(this.codigo || this.$route.params.codigo || '').trim()

      if (!codigo) {
        this.mascota = null
        this.mensaje = 'Ingrese un codigo de mascota.'
        return
      }

      this.buscando = true
      this.mensaje = ''

      try {
        const { data } = await this.$api.get(`public/mascota/${encodeURIComponent(codigo)}`)
        this.mascota = data?.data || null
        await this.actualizarQr(this.publicLink)
        this.mensaje = this.mascota
          ? 'Mascota encontrada. Puede imprimir la credencial.'
          : 'No se encontro la mascota.'
      } catch (error) {
        this.mascota = null
        this.qrSrc = ''
        this.mensaje = error?.response?.data?.message || 'No se pudo recuperar la mascota.'
      } finally {
        this.buscando = false
      }
    },
    async actualizarQr (value) {
      if (!value) {
        this.qrSrc = ''
        return
      }

      try {
        this.qrSrc = await QRCode.toDataURL(value, {
          margin: 1,
          width: 260,
          errorCorrectionLevel: 'M',
          color: {
            dark: '#0f172a',
            light: '#ffffff'
          }
        })
      } catch (error) {
        this.qrSrc = ''
      }
    },
    async copyLink () {
      if (!this.publicLink) {
        return
      }

      try {
        await navigator.clipboard.writeText(this.publicLink)
        this.$q.notify({
          color: 'positive',
          message: 'Enlace copiado'
        })
      } catch (error) {
        this.$q.notify({
          color: 'negative',
          message: 'No se pudo copiar el enlace'
        })
      }
    },
    async generatePdfCredential () {
      if (!this.mascota) {
        return
      }

      this.generandoPdf = true

      try {
        const { data, headers } = await this.$api.get(`public/mascota/${encodeURIComponent(this.mascota.codigo)}/pdf`, {
          responseType: 'blob'
        })

        const blob = new Blob([data], {
          type: headers?.['content-type'] || 'application/pdf'
        })
        const blobUrl = URL.createObjectURL(blob)
        const win = window.open(blobUrl, '_blank', 'noopener,noreferrer')

        if (!win) {
          const link = document.createElement('a')
          link.href = blobUrl
          link.target = '_blank'
          link.rel = 'noopener noreferrer'
          link.click()
        }

        window.setTimeout(() => URL.revokeObjectURL(blobUrl), 60000)
      } catch (error) {
        this.$q.notify({
          color: 'negative',
          message: 'No se pudo abrir el PDF.'
        })
      } finally {
        this.generandoPdf = false
      }
    },
    printCredential () {
      const triggerPrint = () => {
        window.print()
      }

      if (document.readyState === 'complete') {
        window.requestAnimationFrame(triggerPrint)
        return
      }

      window.addEventListener('load', triggerPrint, { once: true })
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
  max-width: 760px;
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

.print-area {
  color: #0f172a;
}

.credential-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
  align-items: start;
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
}

.credential-card--front {
  padding: 18px;
}

.credential-card--back {
  padding: 18px;
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

.search-empty {
  background: rgba(255,255,255,.92);
  border-radius: 24px;
}

@media (max-width: 1024px) {
  .credential-grid {
    grid-template-columns: 1fr;
  }
}

@media print {
  @page {
    size: landscape;
    margin: 8mm;
  }

  :global(html),
  :global(body) {
    background: #fff !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }

  .no-print,
  .public-hero,
  .public-search,
  .public-metric,
  .q-banner,
  .search-empty {
    display: none !important;
  }

  .public-card-page {
    background: #fff !important;
    color: #000 !important;
  }

  .public-shell {
    padding: 0;
    max-width: 100%;
  }

  .print-area {
    margin: 0;
  }

  .credential-grid {
    display: block;
  }

  .credential-card {
    box-shadow: none;
    break-inside: avoid;
    page-break-inside: avoid;
    border: 1px solid #cbd5e1;
    margin: 0 0 12px;
  }

  .credential-card--front,
  .credential-card--back {
    padding: 16px;
  }

  .credential-card--back {
    page-break-before: always;
  }

  .credential-owner {
    background: #e2e8f0 !important;
    color: #0f172a !important;
  }

  .qr-panel {
    background: #f8fafc !important;
    border-color: #cbd5e1 !important;
  }
}
</style>
