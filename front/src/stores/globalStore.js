import { defineStore } from 'pinia'
import axios from 'axios';
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
    isLoggedIn: !!localStorage.getItem('tokenExpendio'),
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
