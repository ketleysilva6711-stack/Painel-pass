<template>
  <div class="dashboard-container">
    <!-- Header -->
    <div class="header-section">
      <div>
        <a href="/dashboard" class="eyebrow-link">« MINHA CONTA</a>
        <h1 class="main-title">Olá, {{ user?.name || 'luiz77' }}!</h1>
        <p class="sub-title">Bem-vindo de volta. Aqui está o resumo da sua conta.</p>
      </div>
      <div class="quick-actions">
        <router-link to="/tools/play-fake" class="pill-btn pill-btn-white">→ Publicar Play Fake</router-link>
        <router-link to="/tools/bypass" class="pill-btn pill-btn-white">→ Novo Bypass</router-link>
      </div>
    </div>

    <!-- Card de Plano & Expiração -->
    <div class="plan-card">
      <div class="plan-metrics">
        <div class="metric-group">
          <span class="metric-label">PLANO</span>
          <span class="metric-val">{{ user?.planoNome || 'Cortesia 7 Dias' }}</span>
        </div>
        <div class="metric-group">
          <span class="metric-label">EXPIRA EM</span>
          <span class="metric-val">{{ user?.expiraEmDias ?? 3 }} dias restantes</span>
        </div>
      </div>
      <div class="warning-banner">
        Sua conta expira em breve. <a href="#" class="renew-link">Clique aqui pra renovar.</a>
      </div>
    </div>

    <!-- 4 Cards de Estatísticas com Caixas Coloridas -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon-box box-emerald">📦</div>
        <div class="stat-content">
          <span class="stat-number">{{ appsCount }}</span>
          <span class="stat-name">Apps publicados</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-box box-cyan">✳</div>
        <div class="stat-content">
          <span class="stat-number">0</span>
          <span class="stat-name">Processando</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-box box-rose">!</div>
        <div class="stat-content">
          <span class="stat-number">0</span>
          <span class="stat-name">Com erro</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-box box-indigo">⠿</div>
        <div class="stat-content">
          <span class="stat-number">{{ activeFeaturesCount }}</span>
          <span class="stat-name">Funcionalidades ativas</span>
        </div>
      </div>
    </div>

    <!-- Seção Acesso Rápido -->
    <div class="section-block">
      <div class="section-header">
        <h2>⚡ Acesso rápido</h2>
        <span class="section-sub">Suas funcionalidades habilitadas</span>
      </div>

      <div class="tools-grid">
        <router-link to="/tools/play-fake" class="tool-item">
          <div class="tool-box box-emerald">🎮</div>
          <div class="tool-details">
            <strong>Play Fake</strong>
            <span>Uso hoje: {{ appsCount }}/15</span>
          </div>
        </router-link>

        <router-link to="/tools/bypass" class="tool-item">
          <div class="tool-box box-orange">🔴</div>
          <div class="tool-details">
            <strong>Bypass</strong>
            <span>Uso hoje: {{ bypassCount }}/15</span>
          </div>
        </router-link>

        <router-link to="/tools/fura-banco" class="tool-item">
          <div class="tool-box box-purple">🟣</div>
          <div class="tool-details">
            <strong>Fura Banco</strong>
            <span>Uso hoje: 0/5</span>
          </div>
        </router-link>

        <router-link to="/tools/consulta" class="tool-item">
          <div class="tool-box box-amber">🔍</div>
          <div class="tool-details">
            <strong>Consulta</strong>
            <span>Uso hoje: 0/150</span>
          </div>
        </router-link>

        <router-link to="/tools/tela-preta" class="tool-item">
          <div class="tool-box box-teal">🟩</div>
          <div class="tool-details">
            <strong>Tela Preta</strong>
            <span>Sem limite</span>
          </div>
        </router-link>

        <router-link to="/tools/inject-bancos" class="tool-item">
          <div class="tool-box box-indigo">💉</div>
          <div class="tool-details">
            <strong>Inject Bancos</strong>
            <span>Sem limite</span>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Seção Últimos Apps -->
    <div class="section-block">
      <div class="section-header">
        <h2>🔄 Últimos apps</h2>
        <span class="section-sub">Atividade recente dos seus uploads</span>
      </div>

      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>NOME</th>
              <th>FUNCIONALIDADE</th>
              <th>STATUS</th>
              <th>PUBLICADO EM</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="latestApps.length === 0">
              <td colspan="4" class="empty-cell">Nenhum aplicativo publicado recentemente.</td>
            </tr>
            <tr v-for="app in latestApps" :key="app.id">
              <td class="app-title-cell">
                <div class="mini-app-icon">{{ app.name?.charAt(0) }}</div>
                <strong>{{ app.name }}</strong>
              </td>
              <td>Play Fake</td>
              <td><span class="status-pill status-published">Publicado</span></td>
              <td>{{ formatDate(app.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
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

const loadData = async () => {
  try {
    const resApps = await axios.get(`${API_URL}/play-fake-apps`);
    const appsList = resApps.data.data || resApps.data || [];
    appsCount.value = appsList.length;
    latestApps.value = appsList.slice(0, 5);

    const resBypass = await axios.get(`${API_URL}/bypass-rules`);
    const bypassList = resBypass.data.data || resBypass.data || [];
    bypassCount.value = bypassList.length;
  } catch (e) {}
};

const formatDate = (d: string) => {
  if (!d) return '-';
  const date = new Date(d);
  return `${date.toLocaleDateString('pt-BR')} ${date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' })}`;
};

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.dashboard-container { display: flex; flex-direction: column; gap: 1.75rem; }

.eyebrow-link { font-size: 0.75rem; font-weight: 700; color: #38bdf8; text-decoration: none; letter-spacing: 0.05em; display: block; margin-bottom: 0.4rem; }
.header-section { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; }
.main-title { font-family: var(--font-display); font-size: 1.85rem; font-weight: 700; color: var(--ink); margin: 0; }
.sub-title { font-size: 0.9rem; color: var(--ink-muted); margin-top: 0.2rem; }

.quick-actions { display: flex; gap: 0.75rem; }
.pill-btn { padding: 0.6rem 1.25rem; border-radius: 100px; font-weight: 600; font-size: 0.875rem; text-decoration: none; transition: transform 0.15s, opacity 0.15s; }
.pill-btn-white { background: #ffffff; color: #0b1120; }
.pill-btn:hover { opacity: 0.9; transform: translateY(-1px); }

/* Plan card */
.plan-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem; }
.plan-metrics { display: flex; gap: 4rem; }
.metric-group { display: flex; flex-direction: column; gap: 0.3rem; }
.metric-label { font-size: 0.7rem; font-weight: 700; color: var(--ink-faint); letter-spacing: 0.08em; }
.metric-val { font-size: 1.25rem; font-weight: 700; color: var(--ink); }

.warning-banner { background: var(--warning-soft); color: var(--warning); border: 1px solid oklch(.78 .17 70 / 30%); padding: 0.75rem 1rem; border-radius: var(--r-sm); font-size: 0.875rem; font-weight: 500; }
.renew-link { color: var(--warning); font-weight: 700; text-decoration: underline; margin-left: 0.25rem; }

/* Stats row */
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.stat-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-md); padding: 1.25rem; display: flex; align-items: center; gap: 1rem; }
.stat-icon-box { width: 44px; height: 44px; border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; }
.stat-content { display: flex; flex-direction: column; }
.stat-number { font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; color: var(--ink); line-height: 1.1; }
.stat-name { font-size: 0.8rem; color: var(--ink-muted); margin-top: 0.2rem; }

/* Icon Box Colors */
.box-emerald { background: oklch(.74 .18 162 / 15%); color: oklch(.74 .18 162); }
.box-cyan    { background: oklch(.76 .15 195 / 15%); color: oklch(.76 .15 195); }
.box-rose    { background: oklch(.68 .22 15 / 15%);  color: oklch(.68 .22 15);  }
.box-indigo  { background: oklch(.62 .2 264 / 15%);  color: oklch(.62 .2 264);  }
.box-orange  { background: oklch(.74 .19 50 / 15%);  color: oklch(.74 .19 50);  }
.box-purple  { background: oklch(.67 .2 293 / 15%);  color: oklch(.67 .2 293);  }
.box-amber   { background: oklch(.78 .17 70 / 15%);  color: oklch(.78 .17 70);  }
.box-teal    { background: oklch(.72 .16 183 / 15%); color: oklch(.72 .16 183); }

/* Section Block */
.section-block { display: flex; flex-direction: column; gap: 1rem; }
.section-header { display: flex; align-items: baseline; justify-content: space-between; }
.section-header h2 { font-family: var(--font-display); font-size: 1.15rem; font-weight: 700; color: var(--ink); margin: 0; }
.section-sub { font-size: 0.75rem; color: var(--ink-faint); }

.tools-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
.tool-item { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-md); padding: 1.25rem; display: flex; align-items: center; gap: 1rem; text-decoration: none; color: var(--ink); transition: border-color 0.2s; }
.tool-item:hover { border-color: var(--accent); }
.tool-box { width: 40px; height: 40px; border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.tool-details { display: flex; flex-direction: column; gap: 0.15rem; }
.tool-details strong { font-size: 0.95rem; font-weight: 600; }
.tool-details span { font-size: 0.75rem; color: var(--ink-faint); }

/* Table */
.table-container { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.875rem 1.25rem; background: var(--surface-2); color: var(--ink-faint); font-weight: 700; font-size: 0.7rem; letter-spacing: 0.05em; border-bottom: 1px solid var(--hairline); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.app-title-cell { display: flex; align-items: center; gap: 0.75rem; }
.mini-app-icon { width: 28px; height: 28px; border-radius: 6px; background: var(--surface-3); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; color: var(--accent); }
.status-pill { font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.6rem; border-radius: 100px; }
.status-published { background: oklch(.74 .18 162 / 15%); color: oklch(.74 .18 162); }
.empty-cell { padding: 2.5rem; text-align: center; color: var(--ink-muted); }
</style>