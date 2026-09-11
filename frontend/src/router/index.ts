import { createRouter, createWebHistory } from '@ionic/vue-router';
import { RouteRecordRaw } from 'vue-router';
import HomePage from '../views/HomePage.vue';
import ComplejoDetallePage from '../views/ComplejoDetallePage.vue';
import LoginPage from '../views/LoginPage.vue';
import PanelPage from '../views/PanelPage.vue';
import CanchasPage from '../views/CanchasPage.vue';
import HorariosPage from '../views/HorariosPage.vue';

const routes: Array<RouteRecordRaw> = [
  {
    path: '/',
    redirect: '/home',
  },
  {
    path: '/home',
    name: 'Home',
    component: HomePage,
  },
  {
    path: '/complejo/:slug',
    name: 'ComplejoDetalle',
    component: ComplejoDetallePage,
  },
  {
    path: '/login',
    name: 'Login',
    component: LoginPage,
  },
  {
    path: '/panel',
    name: 'Panel',
    component: PanelPage,
    meta: { requiresAuth: true },
  },
  {
    path: '/panel/canchas',
    name: 'PanelCanchas',
    component: CanchasPage,
    meta: { requiresAuth: true },
  },
  {
    path: '/panel/canchas/:canchaId/horarios',
    name: 'PanelHorarios',
    component: HorariosPage,
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