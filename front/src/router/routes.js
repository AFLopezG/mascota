
const routes = [
  {
    path: '/credencial-mascota/:codigo?',
    component: () => import('pages/CredencialMascotaPublica.vue'),
    meta: { public: true }
  },
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
      { path: 'buscar-persona', component: () => import('pages/BusquedaPersona.vue') },
      { path: 'registro-vacunas', component: () => import('pages/RegistroVacunas.vue') },
      { path: 'denuncias', component: () => import('pages/Denuncias.vue') },
      { path: 'reporte-denuncias', component: () => import('pages/ReporteDenuncias.vue') },
      { path: 'denuncia-tipos', component: () => import('pages/DenunciaTipos.vue') },
      { path: 'especies', component: () => import('pages/Especies.vue') },
      { path: 'razas', component: () => import('pages/Razas.vue') },
      { path: 'places', component: () => import('pages/Places.vue') },
      { path: 'campania-tipos', component: () => import('pages/CampaniaTipos.vue') },
      { path: 'campanias', component: () => import('pages/Campanias.vue') },
      { path: 'categorias', component: () => import('pages/Categorias.vue') }
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
