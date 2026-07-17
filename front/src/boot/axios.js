import { boot } from 'quasar/wrappers'
import axios from 'axios'
import {globalStore} from 'stores/globalStore';


const api = axios.create({ baseURL: import.meta.env.VITE_API })

export default boot(({ app,router }) => {
  // for use inside Vue files (Options API) through this.$axios and this.$api

  app.config.globalProperties.$url = import.meta.env.VITE_API
  app.config.globalProperties.$axios = axios
  app.config.globalProperties.$api = api
  // ^ ^ ^ this will allow you to use this.$api (for Vue Options API form)
  //       so you can easily perform requests against your app's API
  const store = globalStore();
    function resetPermissions() {
      //todos los permisos en store bool_... en false
    store.bool_roles = false;
    store.bool_usuarios = false;
    store.bool_registrar_rol = false;
    store.bool_modificar_rol = false;
    store.bool_modificar_permiso = false;
    store.bool_registrar_usuarios = false,
    store.bool_modificar_usuarios = false,
    store.bool_modificar_password = false,
    store.bool_activar_usuario = false
  }

  function setPermissions(permisos) {
    resetPermissions();
    const store = globalStore();
    permisos.forEach(r => {
      if(r.id===1) store.bool_roles =true
      if(r.id===2) store.bool_usuarios =true
      if(r.id===3) store.bool_registrar_rol =true
      if(r.id===4) store.bool_modificar_rol=true
      if(r.id===5) store.bool_modificar_permiso=true
      if(r.id===6) store.bool_registrar_usuarios=true
      if(r.id===7) store.bool_modificar_usuarios=true
      if(r.id===8) store.bool_modificar_password=true
      if(r.id===9) store.bool_activar_usuario=true


    });
  }

  async function checkAuth() {
    const token = localStorage.getItem('tokenExpendio');
    if (!token) {
      forceLogout();
      return;
    }
    api.defaults.headers.common.Authorization = `Bearer ${token}`;
    try {
      const { data } = await api.post('me');
      store.user = data;
      store.rol = data.rol;
      store.isLoggedIn = true;
      setPermissions(data.rol.permisos);
    } catch (error) {
      console.error('Error de sesión', error);
      forceLogout();
    }
  }

  function login(datos) {
          store.user = datos.user
          store.rol = datos.user.rol
          store.isLoggedIn = true
          setPermissions(datos.user.rol.permisos)
      localStorage.setItem('tokenExpendio', datos.token);
      api.defaults.headers.common.Authorization = `Bearer ${datos.token}`;
      router.push('/home');

      //await checkAuth();
  }
  async function logout() {
    app.config.globalProperties.$q.dialog({
      title: 'Cerrar sesión',
      message: '¿Está seguro que desea cerrar sesión?',
      cancel: true,
      persistent: true
    }).onOk(async () => {
      app.config.globalProperties.$q.loading.show();
      try {
        await api.post('logout', store.user);
      } catch (error) {
        console.error('Error al cerrar sesión en servidor', error);
      }
      store.user = {};
      store.rol = {};
      store.isLoggedIn = false;
      resetPermissions();
      localStorage.removeItem('tokenExpendio');
      delete api.defaults.headers.common.Authorization;
      router.push('/');
      app.config.globalProperties.$q.loading.hide();
    });
  }

  function forceLogout() {
    store.user = {};
    store.isLoggedIn = false;
    store.rol = {};
    resetPermissions(); 
    localStorage.removeItem('tokenExpendio');
    delete api.defaults.headers.common.Authorization;
    router.push('/');
  }
  checkAuth();

  // Hacer checkAuth accesible globalmente
  app.config.globalProperties.$checkAuth = checkAuth;
  app.config.globalProperties.$logout = logout;
  app.config.globalProperties.$forceLogout = forceLogout;
  app.config.globalProperties.$login = login;

  
});

export { api };
