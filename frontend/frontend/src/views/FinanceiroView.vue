<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Financeiro 💰</h1>
      <p class="page-sub">Receitas, assinaturas ativas e gestão financeira</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">Total de Receitas</span>
        <span class="stat-val">R$ {{ totalReceitas }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Assinaturas Ativas</span>
        <span class="stat-val">{{ assinaturasAtivas }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Plano Ativo</span>
        <span class="stat-val">{{ planoAtivo }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Usuários</span>
        <span class="stat-val">{{ totalUsuarios }}</span>
      </div>
    </div>

    <!-- Subscriptions Table -->
    <div class="card table-card">
      <div class="table-header">
        <h3>Assinaturas Ativas</h3>
        <button class="btn btn-secondary" @click="loadData">🔄 Atualizar</button>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>USUÁRIO</th>
            <th>PLANO</th>
            <th>STATUS</th>
            <th>EXPIRA EM</th>
            <th>VALOR</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="subscriptions.length === 0">
            <td colspan="5" class="empty-cell">Nenhuma assinatura ativa.</td>
          </tr>
          <tr v-for="sub in subscriptions" :key="sub.id">
            <td><strong>{{ sub.name }}</strong></td>
            <td>{{ sub.plan?.name || '—' }}</td>
            <td><span class="badge badge-success">Ativo</span></td>
            <td>{{ sub.conta_expira_em ? new Date(sub.conta_expira_em).toLocaleDateString('pt-BR') : 'Vitalício' }}</td>
            <td>R$ {{ sub.plan?.price || 0 }}</td>
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

const subscriptions = ref<any[]>([]);
const totalReceitas = ref('0');
const assinaturasAtivas = ref(0);
const planoAtivo = ref('—');
const totalUsuarios = ref(0);
const loading = ref(false);

const loadData = async () => {
  loading.value = true;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const res = await axios.get(`${API_URL}/financeiro`, { headers });
    const data = res.data;
    if (data.subscriptions) {
      const list = data.subscriptions.data || data.subscriptions;
      subscriptions.value = Array.isArray(list) ? list : [];
    }
    // Chaves reais do backend: total_revenue, active_plans
    if (data.total_revenue !== undefined) {
      totalReceitas.value = Number(data.total_revenue).toFixed(2);
    }
    if (data.active_plans !== undefined) {
      assinaturasAtivas.value = data.active_plans;
    }
    totalUsuarios.value = subscriptions.value.length;
    const first = subscriptions.value[0];
    planoAtivo.value = first?.plan?.name || '—';
  } catch (e) {
    console.error('Erro ao carregar dados financeiros:', e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => { loadData(); });
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.stat-card { background: var(--surface-1); border: 1px solid var(--hairline); padding: 1.25rem; border-radius: var(--r-md); }
.stat-label { font-size: 0.7rem; color: var(--ink-muted); text-transform: uppercase; }
.stat-val { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.table-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); }
.table-header h3 { font-family: var(--font-display); font-size: 1rem; color: var(--ink); }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
.badge-success { background: var(--success-soft); color: var(--success); }
.empty-cell { padding: 2.5rem; text-align: center; color: var(--ink-muted); }
.btn { padding: 0.5rem 1rem; border-radius: var(--r-sm); border: 1px solid var(--hairline); font-weight: 600; cursor: pointer; background: var(--surface-2); color: var(--ink); }
</style>