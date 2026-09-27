<template>
  <div class="page-container">
    <header class="page-header"><h1>Bypass — Módulo de Proteção</h1><p>Configure regras de bypass de análise de ambiente.</p></header>
    <div class="card form-card">
      <h3>Nova Regra de Bypass</h3>
      <form @submit.prevent="createItem" class="form-row">
        <input v-model="ruleName" type="text" placeholder="Nome da Regra (ex: Anti-Emulador Root)" required />
        <input v-model="targetPackage" type="text" placeholder="Package Alvo (ex: com.bank.app)" required />
        <button type="submit" class="btn btn-primary">Ativar Regra</button>
      </form>
    </div>
    <div class="card list-card">
      <h3>Regras Ativas</h3>
      <div v-if="items.length === 0" class="empty">Nenhuma regra ativa.</div>
      <table v-else class="data-table">
        <thead><tr><th>ID</th><th>Regra</th><th>Package Alvo</th><th>Status</th></tr></thead>
        <tbody>
          <tr v-for="i in items" :key="i.id"><td>#{{ i.id }}</td><td><strong>{{ i.rule_name }}</strong></td><td><code>{{ i.target_package }}</code></td><td><span class="badge">{{ i.status }}</span></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
const API_URL = import.meta.env.VITE_API_URL || '/api';
const items = ref<any[]>([]);
const ruleName = ref('');
const targetPackage = ref('');
const fetchItems = async () => {
  const token = localStorage.getItem('token');
  const res = await axios.get(`${API_URL}/tools/bypass`, { headers: { Authorization: `Bearer ${token}` } });
  items.value = res.data.items;
};
const createItem = async () => {
  const token = localStorage.getItem('token');
  await axios.post(`${API_URL}/tools/bypass`, { rule_name: ruleName.value, target_package: targetPackage.value }, { headers: { Authorization: `Bearer ${token}` } });
  ruleName.value = ''; targetPackage.value = '';
  await fetchItems();
};
onMounted(fetchItems);
</script>
<style scoped>
.page-container { padding: 2rem; }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-md); padding: 1.5rem; margin-bottom: 2rem; }
.form-row { display: flex; gap: 1rem; margin-top: 1rem; }
.form-row input { flex: 1; padding: 0.75rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); }
.btn-primary { background: var(--accent); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: var(--r-sm); font-weight: 600; cursor: pointer; }
.data-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--hairline); }
.badge { background: var(--success-soft); color: var(--success); padding: 0.25rem 0.5rem; border-radius: var(--r-sm); font-size: 0.75rem; }
.empty { color: var(--ink-muted); font-style: italic; }
</style>