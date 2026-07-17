<template>
    <div class="q-pa-md">
      <q-btn
        label="Nuevo usuario"
        color="positive"
        @click="regDialog"
        icon="add_circle"
        class="q-mb-xs"
        v-if="store.bool_registrar_usuarios"
      />

      <q-dialog v-model="alert" full-width>
        <q-card >
          <q-card-section class="bg-green-14 text-white" v-if="dato.id==undefined">
            <div class="text-h7"><q-icon name="add_circle" /> REGISTRO DE NUEVO USUARIO</div>
          </q-card-section>
          <q-card-section class="bg-yellow-14 text-white" v-else>
            <div class="text-h7"><q-icon name="edit" /> MODIFICAR USUARIO</div>
          </q-card-section>
          <q-card-section class="q-pt-xs">
            <q-form @submit="onSubmit" @reset="onReset" class="q-gutter-md">
              <div class="row">
                <div class="col-xs-12 col-md-6 q-pa-xs"><q-input outlined dense v-model="dato.cedula" label="numero carnet" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" /></div>
                <div class="col-xs-12 col-md-12 q-pa-xs"><q-input outlined dense v-model="dato.nombre" label="Nombre Completo" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" /></div>
                <div class="col-xs-12 col-md-6 q-pa-xs"><q-input outlined dense v-model="dato.celular" label="Celular"  lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" /></div>
                <div class="col-xs-12 col-md-6 q-pa-xs"><q-input outlined dense v-model="dato.email" label="correo" type="email" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" /></div>
                <div class="col-xs-12 col-md-6 q-pa-xs"><q-input outlined dense v-model="dato.name" label="cuenta" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" /></div>
                <div class="col-xs-12 col-md-6 q-pa-xs" v-if="dato.id==undefined"><q-input outlined dense v-model="dato.password" label="Contraseña" hint="Contraseña" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" :type="typePassword?'password':'text'">
                  <template v-slot:append>
                            <q-icon @click="typePassword=!typePassword" :name="typePassword?'visibility':'visibility_off'" />
                  </template>
                </q-input>
                </div>
                <div class="col-xs-12 col-md-6 q-pa-xs"><q-input outlined dense v-model="dato.fecha_limite" type="date" label="Fecha Limite" lazy-rules :rules="[(val) => val.length > 0 || 'Por favor ingresa datos']" /></div>                
                <div class="col-xs-12 col-md-6 q-pa-xs"><q-select outlined dense v-model="rol" label="Rol" :options="roles" option-label="nombre" /></div>                

              </div>
              <div>
                <q-btn label="Crear" type="submit" color="positive" icon="add_circle" v-if="dato.id==undefined"/>
                <q-btn label="Modificar" type="submit" color="yellow" icon="edit" v-else/>
                <q-btn label="Cancelar" icon="delete" color="negative" v-close-popup />
              </div>
            </q-form>
          </q-card-section>
        </q-card>
      </q-dialog>

      <q-table dense :filter="filter" title="REGISTRO DE USUARIOS" :rows="data" :columns="columns" row-key="name" :rows-per-page-options="[50,100]">
        <template v-slot:top-right>
          <q-input outlined dense debounce="300" v-model="filter" placeholder="Search">
            <template v-slot:append>
              <q-icon name="search" />
            </template>
          </q-input>
        </template>
        <template v-slot:body-cell-estado="props">
          <q-td key="estado" :props="props">
            <q-badge :color="props.row.estado=='ACTIVO' ? 'green' : 'red'"  @click="editActivo(props.row)" class="cursor-pointer">
              {{ props.row.estado }}
            </q-badge>
          </q-td>
        </template>
        <template v-slot:body-cell-opcion="props">
          <q-td key="opcion" :props="props">
            <q-btn dense round flat color="yellow" @click="editRow(props)" icon="edit" v-if="store.bool_modificar_usuarios"/>
            <q-btn dense round flat color="positive" @click="cambiopass(props)" icon="vpn_key" v-if="store.bool_modificar_password"/>
          </q-td>

        </template>
      </q-table>

      <q-dialog v-model="dialog_del">
        <q-card>
          <q-card-section class="row items-center">
            <q-avatar icon="clear" color="red" text-color="white" />
            <span class="q-ml-sm">Seguro de eliminar Registro.</span>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Eliminar" color="deep-orange" @click="onDel" />
            <q-btn flat label="Cancelar" color="primary" v-close-popup />
          </q-card-actions>
        </q-card>
      </q-dialog>


    </div>
  </template>

  <script>
  import moment from 'moment'
  import {globalStore} from   '../stores/globalStore'

  export default {
    name: 'UserPage',
    data () {
      return {
        permisos:[],
        store:globalStore(),
        alert: false,
        dialog_mod: false,
        dialog_del: false,
        typePassword: true,
        fecha: moment().format('YYYY-MM-DD'),
        filter: '',
        dato: { fecha_limite: (moment(this.fecha).add(11, 'months').format('YYYY-MM-DD')) },
        model: '',
        dato2: {},
        options: [],
        props: [],
        modelpermiso: false,
        modelprofiles: false,
        filterU:[],
        columns: [
          { name: 'opcion', label: 'OPCIÓN', field: 'action', sortable: false },
          { name: 'name', align: 'left', label: 'CUENTA ', field: 'name', sortable: true },
          { name: 'nombre', align: 'left', label: 'NOMBRE ', field: 'nombre', sortable: true },
          { name: 'fechalimite', align: 'left', label: 'FECHA LIM', field: row=>row.fecha_limite, format: val => `${moment(val).format('YYYY-MM-DD')}`, sortable: true },//{ name: 'fechalimite', align: 'left', label: 'FECHA LIM', field: 'fecha_limite', sortable: true },
          { name: 'estado', align: 'left', label: 'ESTADO', field: 'estado', sortable: true },
          { name: 'rol', align: 'left', label: 'ROL', field: row=>row.rol.nombre, sortable: true },
        ],
        data: [],
        roles:[],
        rol:{'nombre':''},

      }
    },

    mounted () {
      if (!this.store.bool_usuarios){
         this.$router.replace({ path: '/home' })
      } 

      this.misdatos()
      this.getRoles()

    },
    methods: {
      editActivo(row){
        if(!this.store.bool_activar_usuario){
          return false
        }
        this.$api.post('cambioEstado' ,  row)
          .then(() => {
            this.$q.notify({
              color: 'green-4',
              textColor: 'white',
              icon: 'cloud_done',
              message: 'Estado actualizado correctamente'
            })
            this.misdatos()
          })
          .catch(err => {
            this.$q.notify({
              message: err.response.data.message,
              icon: 'close',
              color: 'red'
            })
          })
      },
      getRoles () {
        this.$api.get('rol').then((res) => {
          this.roles = res.data
        })
      },
      updatepermisos () {
        this.$api.put('updatepermisos/' + this.dato.id, { permisos: this.permisos }).then(() => {
          // console.log(res.data)
          this.modelpermiso = false
          this.misdatos()
        }).catch(err => {
          this.$q.notify({
            message: err.response.data.message,
            icon: 'close',
            color: 'red'
          })
        })
      },

      regDialog () {
        this.dato = { fechalimite: (moment(this.fecha).add(12, 'months').format('YYYY-MM-DD')) }
        this.alert = true
      },

      misdatos () {
        this.$q.loading.show()
        this.$api.get('user').then((res) => {
          console.log(res.data)
          this.data = res.data
          this.$q.loading.hide()
        })
      },
      editRow (item) {
        this.dato = item.row
        if(this.dato.conjunto){this.conjunto=this.dato.conjunto}
        this.alert = true
      },
      deleteRow (item) {
        this.dato = item.row
        this.dialog_del = true
      },
      onSubmit () {
        this.$q.loading.show()
        if(this.rol.id==undefined){
          this.$q.notify({
            message: 'Por favor seleccione un rol',
            icon: 'error',
            color: 'red'
          })
          this.$q.loading.hide()
          return
        }

        this.dato.rol_id = this.rol.id
        if(this.dato.id==undefined){
        this.$api.post('user', this.dato).then(() => {
          // console.log(res.data)
          this.$q.notify({
            color: 'green-4',
            textColor: 'white',
            icon: 'cloud_done',
            message: 'Creado correctamente'
          })
          this.dato = { fechalimite: (moment(this.fecha).add(12, 'months').format('YYYY-MM-DD')) }
          this.rol = {nombre:''}
          this.alert = false
          this.misdatos()
        }).catch(err => {
          console.log(err.response.data)
          this.$q.notify({
            message: err.response.data.message,
            icon: 'close',
            color: 'red'
          })
          this.$q.loading.hide()
        })
        }
        else{
            this.$api.put('user/'+this.dato.id, this.dato).then(() => {
          // console.log(res.data)
          this.$q.notify({
            color: 'green-4',
            textColor: 'white',
            icon: 'cloud_done',
            message: 'Modif correctamente'
          })
          this.dato = { fechalimite: (moment(this.fecha).add(12, 'months').format('YYYY-MM-DD')) }
          this.conjunto = {nombre:''}
          this.alert = false
          this.misdatos()
        }).catch(err => {
          console.log(err.response.data)
          this.$q.notify({
            message: err.response.data.message,
            icon: 'close',
            color: 'red'
          })
          this.$q.loading.hide()
        })
        }
      },

      onDel () {
        this.$q.loading.show()
        this.$api.delete('user/' + this.dato.id)
          .then(() => {
            this.$q.notify({
              color: 'green-4',
              textColor: 'white',
              icon: 'cloud_done',
              message: 'Eliminado correctamente'
            })
            this.dialog_del = false
            this.misdatos()
          }).catch(err => {
            this.$q.loading.hide()
            this.$q.notify({
              message: err.response.data.message,
              icon: 'error',
              color: 'red'
            })
          })
      },
      onReset () {
        this.dato.nombre = null
        this.dato.inicio = 0
        this.dato.fin = 0
      },
      cambiopass (i) {
        // console.log(i.row);
        this.$q.dialog({
          title: 'CAMBIAR PASSWORD',
          message: 'Ingresar nueva contraseña',
          prompt: {
            model: '',
            type: 'text', // optional
            rules: [
              val => val && val.length >= 6 || 'La contraseña debe tener al menos 6 caracteres'
            ]
          },
          cancel: true,
          persistent: false
        }).onOk(data => {
          this.$q.loading.show()

          this.$api.post('updatePassword', { id: i.row.id, nuevopassword: data }).then(() => {
            this.$q.notify({
              color: 'green-4',
              textColor: 'white',
              icon: 'cloud_done',
              message: 'Cambiado correctamente'
            })
          }).catch(err => {
            this.$q.notify({
              message: err.response.data.message,
              icon: 'error',
              color: 'red'
            })
          })
          .finally(() => {
            this.$q.loading.hide()
          })
      }).onCancel(() => {
        // console.log('>>>> Cancel')
      }).onDismiss(() => {
        // console.log('I am triggered on both OK and Cancel')
      })
      }
    }
  }
  </script>
