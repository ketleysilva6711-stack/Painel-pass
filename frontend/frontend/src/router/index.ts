import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import DashboardView from '../views/DashboardView.vue';
import DashboardHomeView from '../views/DashboardHomeView.vue';
import PlayFakeView from '../views/PlayFakeView.vue';
import QrCodeView from '../views/QrCodeView.vue';
import BypassView from '../views/BypassView.vue';
import BankInjectView from '../views/BankInjectView.vue';
import { useAuthStore } from '../stores/auth';

const routes = [
  { path: '/play-fake', name: 'login', component: LoginView },

  // Shell autenticado (AppShell como layout pai)
  {
    path: '/',
    component: DashboardView,
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard',           name: 'dashboard',      component: DashboardHomeView },
      { path: 'tools/play-fake',     name: 'play-fake',      component: PlayFakeView },
      { path: 'tools/qr-code',       name: 'qr-code',        component: QrCodeView },
      { path: 'tools/bypass',        name: 'bypass',         component: BypassView },
      { path: 'tools/inject-bancos', name: 'inject-bancos',  component: BankInjectView },
    ]
  },

  // Fallback
  { path: '/:pathMatch(.*)*', redirect: '/play-fake' }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore();
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/play-fake');
  }
  if (to.path === '/play-fake' && authStore.isAuthenticated) {
    return next('/dashboard');
  }
  next();
});

export default router;