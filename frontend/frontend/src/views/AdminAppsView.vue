<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Gestão de Apps 📱</h1>
      <p class="page-sub">Gerencie os apps publicados na PlayFake — visualize, edite e exclua</p>
    </div>

    <div class="card">
      <div class="table-header">
        <h3>Todos os PlayFakeApps</h3>
        <button class="btn btn-secondary" @click="loadApps">🔄 Atualizar</button>
      </div>

      <div v-if="loading" class="loading-state">Carregando apps...</div>

      <table class="data-table" v-else-if="apps.length > 0">
        <thead>
          <tr>
            <th>NOME</th>
            <th>PACOTE</th>
            <th>USUÁRIO</th>
            <th>STATUS</th>
            <th>PUBLICADO EM</th>
            <th>AÇÕES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="app in apps" :key="app.id">
            <td><strong>{{ app.name }}</strong></td>
            <td><code>{{ app.package_name || '—' }}</code></td>
            <td>{{ app.user?.name || app.user_name || '—' }}</td>
            <td><span class="badge" :class="statusClass(app.status)">{{ app.status }}</span></td>
            <td>{{ app.published_at ? new Date(app.published_at).toLocaleDateString('pt-BR') : '—' }}</td>
            <td class="actions">
              <button class="btn btn-sm btn-danger" @click="deleteApp(app.id)">🗑 Excluir</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="empty-state">
        <h4>Nenhum app encontrado</h4>
        <p>Não há apps publicados na plataforma.</p>
      </div>
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

const statusClass = (status: string) => {
  const classes: Record<string, string> = {
    published: 'badge-success',
    pending: 'badge-warning',
    active: 'badge-success',
    inactive: 'badge-default',
    processing: 'badge-info',
  };
  return classes[status] || 'badge-default';
};

const loadApps = async () => {
  loading.value = true;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const res = await axios.get(`${API_URL}/play-fake-apps`, { headers });
    apps.value = res.data.data || res.data || [];
  } catch (e) {
    console.error('Erro ao carregar apps:', e);
  } finally {
    loading.value = false;
  }
};

const deleteApp = async (id: number) => {
  if (!confirm('Tem certeza que deseja excluir este app?')) return;
  try {
     await axios.delete(`${API_URL}/play-fake-apps/${id}`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    await loadApps();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao excluir app');
  }
};

onMounted(() => loadApps());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.table-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); }
.table-header h3 { font-family: var(--font-display); font-size: 1rem; color: var(--ink); }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); text-align: left; }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.data-table code { font-family: var(--font-mono); font-size: 0.8rem; color: var(--ink-muted); }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
.badge-success { background: var(--success-soft); color: var(--success); }
.badge-warning { background: var(--warning-soft); color: var(--warning); }
.badge-info { background: var(--accent-soft); color: var(--accent); }
.badge-default { background: var(--surface-2); color: var(--ink-muted); }
.btn { padding: 0.75rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.btn-danger { background: var(--danger); color: white; }
.actions { display: flex; gap: 0.35rem; }
.loading-state { padding: 3rem; text-align: center; color: var(--ink-muted); }
.empty-state { text-align: center; padding: 3rem; color: var(--ink-muted); }
.empty-state h4 { margin-bottom: 0.5rem; color: var(--ink); }
.empty-state p { color: var(--ink-muted); }
</style>
