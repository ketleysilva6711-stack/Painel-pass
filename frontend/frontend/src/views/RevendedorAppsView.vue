<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Apps dos Clientes 📱</h1>
      <p class="page-sub">Todos os PlayFakeApps dos clientes do seu quadro</p>
    </div>

    <div class="card table-card">
      <div class="section-header">
        <h3>Lista de Apps</h3>
        <span class="badge">{{ apps.length }} apps</span>
      </div>

      <div v-if="loading" class="loading-state">Carregando apps...</div>
      <div v-else-if="error" class="error-box">{{ error }}</div>

      <table class="data-table" v-else>
        <thead>
          <tr>
            <th>NOME</th>
            <th>PACOTE</th>
            <th>USUÁRIO</th>
            <th>STATUS</th>
            <th>PUBLICADO EM</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="apps.length === 0"><td colspan="5" style="text-align:center;padding:2rem;color:var(--ink-muted);">Nenhum app encontrado.</td></tr>
          <tr v-for="app in apps" :key="app.id">
            <td><strong>{{ app.name }}</strong></td>
            <td><code>{{ app.package_name || '—' }}</code></td>
            <td>{{ app.user?.name || app.username || '—' }}</td>
            <td><span class="badge" :class="statusClass(app.status)">{{ app.status }}</span></td>
            <td>{{ app.published_at ? new Date(app.published_at).toLocaleDateString('pt-BR') : '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();

const apps = ref<any[]>([]);
const loading = ref(false);
const error = ref('');

const statusClass = (status: string) => {
  const classes: Record<string, string> = {
    active: 'badge-success',
    inactive: 'badge-warning',
    pending: 'badge-info',
    processing: 'badge-info',
    rejected: 'badge-danger',
  };
  return classes[status] || 'badge-default';
};

const loadApps = async () => {
  loading.value = true;
  error.value = '';
  try {
    const res = await axios.get(`${API_URL}/play-fake-apps`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
      params: { reseller_id: authStore.user?.id },
    });
    apps.value = res.data.data || res.data.apps || res.data || [];
  } catch (e: any) {
    console.error('Erro ao carregar apps:', e);
    error.value = e.response?.data?.message || 'Erro ao carregar apps dos clientes.';
  } finally {
    loading.value = false;
  }
};

onMounted(() => loadApps());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.section-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid var(--hairline); }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; background: var(--surface-2); color: var(--ink-muted); }
.badge-success { background: var(--success-soft); color: var(--success); }
.badge-warning { background: var(--warning-soft); color: var(--warning); }
.badge-info { background: var(--info-soft); color: var(--info); }
.badge-danger { background: var(--danger-soft); color: var(--danger); }
.badge-default { background: var(--surface-2); color: var(--ink-muted); }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.data-table code { font-size: 0.8rem; color: var(--ink-muted); }
.loading-state, .error-box { padding: 3rem; text-align: center; color: var(--ink-muted); }
.error-box { color: var(--danger); }
</style>
