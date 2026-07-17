<template>
    <q-page padding>
        <div class="text-bold text-center text-h6">ROLES Y PERMISOS</div>

        <q-table
            :rows="roles"
            :columns="columns"
            row-key="id"
            :loading="loading"
            :filter="filter"
            >
            <template v-slot:top-right>
                <q-btn color="green" label="Nuevo Rol" @click="rol={};dialogreg=true" v-if="store.bool_registrar_rol" dense />
                <q-input v-model="filter" class="q-mb-md" placeholder="Buscar..." debounce="300" outlined dense style="width: 200px;" append-icon="search" />

            </template>
            <template v-slot:body-cell-permisos="props">
                <q-td :props="props">
                    <ul>
                        <li v-for="n in props.row.permisos" :key="n">{{n.nombre }}</li>  
                    </ul>
                </q-td>
            </template>

            <template v-slot:body-cell-op="props">
                <q-td key="op" :props="props">
                    <q-btn color="yellow" icon="edit" @click="cargar(props.row)"  dense flat v-if="store.bool_modificar_rol"/>
                    <q-btn color="purple" icon="list" @click="verpermiso(props.row)" dense flat v-if="store.bool_modificar_permiso"/>
                </q-td>
            </template>
        </q-table>

        <q-dialog v-model="dialogreg" >
        <q-card style="min-width: 350px">
            <q-card-section>
            <div class="text-h6" v-if="rol.id==undefined">REGISTRAR ROL</div>
            <div class="text-h6" v-else>MODIFICAR ROL</div>
            </q-card-section>
            <q-form key=""
                @submit="enviarRol"
                class="q-gutter-md">
            <q-card-section >
                <q-input outlined dense v-model="rol.nombre" label="Nombre" class="q-mb-md" required/>
            </q-card-section>

            <q-card-actions align="right" class="text-primary">
            <q-btn flat label="Cancelar" color="red" v-close-popup />
            <q-btn flat label="Registrar" color="green" type="submit"  v-if="rol.id==undefined"/>
            <q-btn flat label="Modificar" color="yellow" type="submit" v-else/>
            </q-card-actions>
            </q-form>
        </q-card>
    </q-dialog>
    
        <q-dialog v-model="dialogpermisos" persistent>
            <q-card style="min-width: 350px">
                <q-card-section>
                    <div class="text-h6">{{rol.nombre}} PERMISOS</div>
                </q-card-section>

                <q-card-section class="q-pt-none">
                <div v-for="(permiso, pIndex) in permisos" :key="pIndex">
                    <!-- Permiso padre -->
                    <q-checkbox
                    :label="permiso.nombre"
                    v-model="permiso.estado"
                    @update:model-value="togglePadre(pIndex, $event)"
                    />

                    <!-- Permisos hijos solo si el padre está marcado -->
                    <div v-if="permiso.estado" style="padding-left: 20px;">
                        <div v-for="(hijo, hIndex) in permiso.sub_permisos" :key="hIndex">
                            <q-checkbox
                                :label="hijo.nombre"
                                v-model="hijo.estado"
                            />
                        </div>
                    </div>
                </div>
                </q-card-section>

                <q-card-actions align="right" class="text-primary">
                    <q-btn flat label="Cancelar" color="red" v-close-popup />
                    <q-btn flat label="Modificar" color="yellow" @click="enviarPermisos"/>
                </q-card-actions>
            </q-card>
        </q-dialog>
    </q-page>
</template>
<script>
  import {globalStore} from   '../stores/globalStore'

export default {
    name:'RolesPage',
    data() {
        return {
            store:globalStore(),
            dialogreg: false,
            dialogpermisos: false,
            filter: '',
            loading: false,
            permisos:[],
            roles: [],
            dialog: false,
            rol: {},
            columns:[
                { name: 'op', label: 'OP', align: 'left', field: row => row.id, sortable: true },
                { name: 'nombre', label: 'Nombre', align: 'left', field: row => row.nombre, sortable: true },
                { name: 'permisos', label: 'Permisos', align: 'left', field: row => 'permisos' },
            ]
        }
    },
    created() {
        if(!this.store.bool_roles) {
            this.$router.push('/home');
        }
        this.getRoles()
        this.getPermisos()
    },
    methods: {

          togglePadre(pIndex, estado) {
            if (!estado) {
            // Si se desmarca el padre → desmarcar y ocultar hijos
            this.permisos[pIndex].sub_permisos.forEach(h => {
                h.estado = false
            })
            }
        },

        enviarPermisos(){
            this.rol.permisos =this.permisos
            console.log(this.rol)
        this.$api.post('updatepermisos',  this.rol).then(() => {
          // console.log(res.data)
          this.dialogpermisos = false
          this.getRoles()
        }).catch(err => {
          this.$q.notify({
            message: err.response.data.message,
            icon: 'close',
            color: 'red'
          })
        })
      },
        enviarRol(){
            this.loading = true
            let url = 'rol'
            let method = 'post'
            if (this.rol.id !== undefined) {
                url = 'rol/' + this.rol.id
                method = 'put'
            }
            this.$api[method](url, this.rol).then(res => {
                console.log(res.data)
                this.$q.notify({
                    message: res.data.message,
                    color: 'positive',
                    position: 'top',
                    timeout: 2000
                })
                this.dialogreg = false
                this.getRoles()
            }).catch(error => {
                console.log(error)
                this.$q.notify({
                    message: error.response.data.message,
                    color: 'negative',
                    position: 'top',
                    timeout: 2000
                })
            }).finally(() => {
                this.loading = false
            })
        },
        cargar(dato){
            console.log(dato)
            this.rol = dato
            this.dialogreg = true
        },
        verpermiso(dato){
            console.log(dato)
            this.rol = dato
            let p 
            
            this.permisos.forEach(pe => {
           console.log(pe);
          p = this.rol.permisos.find(r => r.pivot.permiso_id === pe.id)
          // console.log(p)
          if (p !== undefined) { pe.estado = true } else { pe.estado = false }
            pe.sub_permisos.forEach(sp => {
                let subp = this.rol.permisos.find(r => r.pivot.permiso_id === sp.id)
                if (subp !== undefined) { sp.estado = true } else { sp.estado = false }
            })
          // console.log(p)
        })

            this.dialogpermisos = true

        },
        getPermisos() {
            this.loading = true
            this.$api.get('permiso').then(res => {
                console.log(res.data)
                res.data.forEach((r) => {
                    r.estado = false
                    r.sub_permisos.forEach(sp => {
                        sp.estado = false
                    })
                })
                this.permisos = res.data
            }).catch(error => {
                console.log(error)
                this.$q.notify({
                    message: error.response,
                    color: 'negative',
                    position: 'top',
                    timeout: 2000
                })
            }).finally(() => {
                this.loading = false
            })
        },
        getRoles() {
            this.loading = true
            this.$api.get('rol').then(res => {
                this.roles = res.data
                console.log(res.data)
            }).catch(error => {
                console.log(error)
                this.$q.notify({
                    message: error.response.data.message,
                    color: 'negative',
                    position: 'top',
                    timeout: 2000
                })
            }).finally(() => {
                this.loading = false
            })
        },

    }
}
</script>