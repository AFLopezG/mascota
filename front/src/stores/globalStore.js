import { defineStore } from 'pinia'

export const globalStore = defineStore('global', {
  state: () => ({
    counter: 0,
    user: {},
    rol: {},
    bool_roles: false,
    bool_usuarios: false,
    bool_registrar_rol: false,
    bool_modificar_rol: false,
    bool_modificar_permiso: false,
    bool_registrar_usuarios: false,
    bool_modificar_usuarios: false,
    bool_modificar_password: false,
    bool_activar_usuario: false,
    bool_especies: false,
    bool_registrar_especies: false,
    bool_modificar_especies: false,
    bool_eliminar_especies: false,
    bool_razas: false,
    bool_registrar_razas: false,
    bool_modificar_razas: false,
    bool_eliminar_razas: false,
    bool_categorias: false,
    bool_registrar_categorias: false,
    bool_modificar_categorias: false,
    bool_eliminar_categorias: false,
    bool_campania_tipos: false,
    bool_registrar_campania_tipos: false,
    bool_modificar_campania_tipos: false,
    bool_eliminar_campania_tipos: false,
    bool_campanias: false,
    bool_registrar_campanias: false,
    bool_modificar_campanias: false,
    bool_anular_campanias: false,
    bool_registro_persona_mascota: false,
    bool_busqueda: false,
    bool_denuncia: false,
    bool_reporte_denuncia: false,
    bool_tipo_denuncia: false,
    bool_anular_registro_vacuna: false,
    bool_places: false,
    bool_registrar_places: false,
    bool_modificar_places: false,
    bool_eliminar_places: false,
    bool_health_centers: false,
    bool_registrar_health_centers: false,
    bool_modificar_health_centers: false,
    bool_eliminar_health_centers: false,
    bool_registro_vacunas: false,
    bool_registrar_registro_vacuna: false,
    isLoggedIn: !!localStorage.getItem('tokenMascota'),
  }),
  getters: {
    doubleCount: (state) => state.counter * 2
  },
  actions: {
    increment () {
      this.counter++
    }
  }
})
