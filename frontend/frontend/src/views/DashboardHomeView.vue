<template>
  <div class="dashboard-home">
    <!-- Header com Saudação e Ações Rápida -->
    <div class="welcome-header">
      <div>
        <h1 class="greeting">Olá, {{ user?.name || 'Usuário' }}!</h1>
        <p class="subgreeting">Bem-vindo de volta ao seu painel de controle.</p>
      </div>
      <div class="header-actions">
        <router-link to="/tools/play-fake" class="btn btn-primary">→ Publicar Play Fake</router-link>
        <router-link to="/tools/bypass" class="btn btn-secondary">→ Novo Bypass</router-link>
      </div>
    </div>

    <!-- Card de Plano & Expiração -->
    <div class="card plan-banner">
      <div class="plan-info">
        <div class="plan-col">
          <span class="plan-label">PLANO ATIVO</span>
          <strong class="plan-name">Recadastramento (Completo)</strong>
        </div>
        <div class="plan-col">
          <span class="plan-label">EXPIRA EM</span>
          <strong class="plan-exp">14 dias restantes</strong>
        </div>
      </div>
      <div class="plan-alert">
        ⚠ Seu plano expira em breve. Mantenha seus acessos renovados.
      </div>
    </div>

    <!-- 4 Cards de Estatísticas -->
    <div class="stats-grid">
      <div class="stat-card">
        <span class="stat-label">Apps publicados</span>
        <span class="stat-num">{{ appsCount }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Processando</span>
        <span class="stat-num">0</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Com erro</span>
        <span class="stat-num">0</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Funcionalidades ativas</span>
        <span class="stat-num highlight-green">{{ activeFeaturesCount }}</span>
      </div>
    </div>

    <!-- Seção Acesso Rápido -->
    <div class="section">
      <h2 class="section-title">Acesso rápido</h2>
      <div class="tools-grid">
        <router-link to="/tools/play-fake" class="tool-card">
          <div class="tool-icon" style="color: #38bdf8;">🎮</div>
          <div class="tool-info">
            <strong>Play Fake</strong>
            <span class="tool-usage">Uso hoje: {{ appsCount }}/15</span>
          </div>
        </router-link>

        <router-link to="/tools/bypass" class="tool-card">
          <div class="tool-icon" style="color: #fb923c;">🔴</div>
          <div class="tool-info">
            <strong>Bypass</strong>
            <span class="tool-usage">Uso hoje: {{ bypassCount }}/15</span>
          </div>
        </router-link>

        <router-link to="/tools/fura-banco" class="tool-card">
          <div class="tool-icon" style="color: #a855f7;">🟣</div>
          <div class="tool-info">
            <strong>Fura Banco</strong>
            <span class="tool-usage">Uso hoje: 0/5</span>
          </div>
        </router-link>

        <router-link to="/tools/consulta" class="tool-card">
          <div class="tool-icon" style="color: #f5a623;">🟡</div>
          <div class="tool-info">
            <strong>Consulta</strong>
            <span class="tool-usage">Uso hoje: 0/150</span>
          </div>
        </router-link>

        <router-link to="/tools/tela-preta" class="tool-card">
          <div class="tool-icon" style="color: #34d399;">🟩</div>
          <div class="tool-info">
            <strong>Tela Preta</strong>
            <span class="tool-usage">Sem limite</span>
          </div>
        </router-link>

        <router-link to="/tools/inject-bancos" class="tool-card">
          <div class="tool-icon" style="color: #a855f7;">🟦</div>
          <div class="tool-info">
            <strong>Inject Bancos</strong>
            <span class="tool-usage">Sem limite</span>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Tabela Últimos Apps -->
    <div class="card table-section">
      <div class="card-header">
        <h3>Últimos apps</h3>
      </div>
      <div v-if="latestApps.length === 0" class="empty-msg">
        Nenhum aplicativo publicado recentemente.
      </div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Funcionalidade</th>
            <th>Status</th>
            <th>Publicado em</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="app in latestApps" :key="app.id">
            <td class="app-name-cell">
              <strong>{{ app.name }}</strong>
            </td>
            <td>Play Fake</td>
            <td><span class="badge badge-success">Publicado</span></td>
            <td>{{ formatDate(app.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const authStore = useAuthStore();
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';

const user = computed(() => authStore.user);
const activeFeaturesCount = computed(() => user.value?.funcionalidades?.length || 6);

const appsCount = ref(0);
const bypassCount = ref(0);
const latestApps = ref<any[]>([]);

const loadDashboardData = async () => {
  try {
    const resApps = await axios.get(`${API_URL}/play-fake-apps`);
    const appsList = resApps.data.data || resApps.data || [];
    appsCount.value = appsList.length;
    latestApps.value = appsList.slice(0, 5);

    const resBypass = await axios.get(`${API_URL}/bypass-rules`);
    const bypassList = resBypass.data.data || resBypass.data || [];
    bypassCount.value = bypassList.length;
  } catch (err) {
    console.error('Erro ao carregar dados do dashboard:', err);
  }
};

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('pt-BR');
};

onMounted(() => {
  loadDashboardData();
});
</script>

<style scoped>
.dashboard-home { display: flex; flex-direction: column; gap: 1.75rem; }

.welcome-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
.greeting { font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; color: var(--ink); }
.subgreeting { font-size: 0.9rem; color: var(--ink-muted); margin-top: 0.25rem; }

.header-actions { display: flex; gap: 0.75rem; }
.btn { padding: 0.65rem 1.25rem; border-radius: var(--r-sm); font-weight: 600; text-decoration: none; font-size: 0.875rem; transition: opacity 0.2s; }
.btn-primary { background: var(--accent); color: white; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }

/* Plan banner */
.plan-banner { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem; }
.plan-info { display: flex; gap: 3rem; }
.plan-col { display: flex; flex-direction: column; gap: 0.25rem; }
.plan-label { font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em; color: var(--ink-faint); }
.plan-name { font-size: 1.1rem; color: var(--ink); }
.plan-exp { font-size: 1.1rem; color: var(--warning); }
.plan-alert { background: var(--warning-soft); color: var(--warning); border: 1px solid var(--warning); padding: 0.6rem 1rem; border-radius: var(--r-sm); font-size: 0.85rem; font-weight: 500; }

/* Stats grid */
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.stat-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-md); padding: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem; }
.stat-label { font-size: 0.8rem; color: var(--ink-muted); }
.stat-num { font-family: var(--font-display); font-size: 2rem; font-weight: 700; color: var(--ink); }
.highlight-green { color: var(--success); }

/* Acesso rápido */
.section { display: flex; flex-direction: column; gap: 1rem; }
.section-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; color: var(--ink); }
.tools-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.tool-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-md); padding: 1.25rem; display: flex; align-items: center; gap: 1rem; text-decoration: none; color: var(--ink); transition: border-color 0.2s; }
.tool-card:hover { border-color: var(--accent); }
.tool-icon { font-size: 1.5rem; }
.tool-info { display: flex; flex-direction: column; gap: 0.2rem; }
.tool-usage { font-size: 0.75rem; color: var(--ink-faint); }

/* Table */
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); }
.card-header h3 { font-size: 1rem; font-weight: 600; color: var(--ink); }
.empty-msg { padding: 2rem; text-align: center; color: var(--ink-muted); font-size: 0.875rem; }

.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); font-weight: 600; border-bottom: 1px solid var(--hairline); }
.data-table td { padding: 0.875rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }

.badge { font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: var(--r-pill); }
.badge-success { background: var(--success-soft); color: var(--success); }
</style>