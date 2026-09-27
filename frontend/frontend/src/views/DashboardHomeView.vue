<template>
  <div class="dashboard-home">
    <div class="stats-grid">
      <div class="stat-card">
        <span class="stat-label">Plano Ativo</span>
        <span class="stat-value text-accent">{{ authStore.user?.plan || 'Recadastramento (Completo)' }}</span>
        <span class="stat-sub">Validade: {{ authStore.user?.expires_in_days ?? 14 }} dias restantes</span>
      </div>

      <div class="stat-card">
        <span class="stat-label">Uso Hoje</span>
        <span class="stat-value">0 / 15</span>
        <span class="stat-sub">Requisições utilizadas</span>
      </div>

      <div class="stat-card">
        <span class="stat-label">Funcionalidades Ativas</span>
        <span class="stat-value text-success">{{ funcionalidades.length }} Ativas</span>
        <span class="stat-sub">100% Operacional</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
const funcionalidades = computed(() => authStore.user?.funcionalidades || []);
</script>

<style scoped>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; }
.stat-card { background-color: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-md); padding: 1.5rem; box-shadow: var(--shadow-card); display: flex; flex-direction: column; }
.stat-label { font-size: 0.85rem; color: var(--ink-muted); margin-bottom: 0.5rem; }
.stat-value { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; }
.stat-sub { font-size: 0.75rem; color: var(--ink-faint); }
.text-accent { color: var(--accent); }
.text-success { color: var(--success); }
</style>