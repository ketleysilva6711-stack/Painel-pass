<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Gestão de Planos 💳</h1>
        <p class="page-sub">Gerencie os planos de assinatura e os limites por conta</p>
      </div>
      <router-link to="/admin/planos/novo" class="btn btn-primary">+ Novo Plano</router-link>
    </div>
    <div class="card table-card">
      <div v-if="isLoading" class="loading-state">Carregando planos...</div>
      <table v-else class="data-table">
        <thead>
          <tr><th>Plano</th><th>Preço</th><th>Limite Apps</th><th>Status</th><th>Ações</th></tr>
        </thead>
        <tbody>
          <tr v-if="plans.length === 0"><td colspan="5" class="empty-cell">Nenhum plano cadastrado.</td></tr>
          <tr v-for="plan in plans" :key="plan.id">
            <td><strong>{{ plan.name }}</strong></td>
            <td>R$ {{ Number(plan.price || 0).toFixed(2) }}</td>
            <td>{{ plan.limits?.apps ?? '—' }}</td>
            <td><span class="badge" :class="plan.active ? 'badge-success' : 'badge-muted'">{{ plan.active ? 'Ativo' : 'Inativo' }}</span></td>
            <td><router-link :to="`/admin/planos/${plan.id}`" class="link-edit">Editar</router-link></td>
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

const plans = ref<any[]>([]);
const isLoading = ref(true);

const loadPlans = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(`${API_URL}/planos`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    const list = res.data.data || res.data.items || res.data || [];
    plans.value = Array.isArray(list) ? list : [];
  } catch (e) {
    console.error('Erro ao carregar planos:', e);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => loadPlans());
</script>
<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.25rem; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.badge-success { background: var(--success-soft); color: var(--success); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.75rem; }
.badge-muted { background: var(--surface-2); color: var(--ink-muted); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.75rem; }
.loading-state, .empty-cell { padding: 2.5rem; text-align: center; color: var(--ink-muted); }
.page-header { display: flex; align-items: center; justify-content: space-between; }
.btn { padding: 0.6rem 1.2rem; border-radius: var(--r-sm); font-weight: 600; text-decoration: none; }
.btn-primary { background: var(--accent); color: white; }
.link-edit { color: var(--accent); font-weight: 600; text-decoration: none; }
</style>