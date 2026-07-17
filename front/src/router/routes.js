
const routes = [
    {
    path: '/',
    component: () => import('pages/Login.vue'),
  },
  {
    path: '/',component: () => import('layouts/MainLayout.vue'),
    children: [
      { path: 'home', component: () => import('pages/IndexPage.vue') },
      { path: 'roles', component: () => import('pages/Roles.vue') },
      { path: 'usuarios', component: () => import('pages/Usuarios.vue') },
      { path: 'registro-persona-mascota', component: () => import('pages/RegistroPersonaMascota.vue') },
      { path: 'buscar-persona', component: () => import('pages/BusquedaPersona.vue') }
    ]
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue')
  }
]

export default routes
