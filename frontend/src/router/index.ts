import { createRouter, createWebHistory } from '@ionic/vue-router';
import type { RouteRecordRaw } from 'vue-router';

const routes: Array<RouteRecordRaw> = [
  {
  path: '/admin',
  name: 'Admin',
  component: () => import('../views/AdminPage.vue'),
  meta: { requiresAuth: true },
},
  {
    path: '/',
    redirect: '/home',
  },
  {
    path: '/home',
    name: 'Home',
    component: () => import('../views/HomePage.vue'),
  },
  {
    path: '/complejo/:slug',
    name: 'ComplejoDetalle',
    component: () => import('../views/ComplejoDetallePage.vue'),
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/LoginPage.vue'),
  },
  {
    path: '/panel',
    name: 'Panel',
    component: () => import('../views/PanelPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/panel/canchas',
    name: 'PanelCanchas',
    component: () => import('../views/CanchasPage.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/panel/canchas/:canchaId/horarios',
    name: 'PanelHorarios',
    component: () => import('../views/HorariosPage.vue'),
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
});

router.beforeEach((to) => {
  const requiresAuth = to.meta.requiresAuth;
  const token = localStorage.getItem('token');

  if (requiresAuth && !token) {
    return '/login';
  }
});

export default router;