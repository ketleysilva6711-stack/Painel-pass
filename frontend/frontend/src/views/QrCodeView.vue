<template>
  <div class="page-container">
    <header class="page-header"><h1>QR Code Generator</h1><p>Gere QR Codes inteligentes com rastreamento de acessos.</p></header>
    <div class="card form-card">
      <h3>Novo QR Code</h3>
      <form @submit.prevent="createItem" class="form-row">
        <input v-model="title" type="text" placeholder="Título (ex: Campanha Banco X)" required />
        <input v-model="targetUrl" type="url" placeholder="URL Destino (https://...)" required />
        <button type="submit" class="btn btn-primary">Gerar QR Code</button>
      </form>
    </div>
    <div class="card list-card">
      <h3>Seus QR Codes</h3>
      <div v-if="items.length === 0" class="empty">Nenhum QR Code gerado.</div>
      <table v-else class="data-table">
        <thead><tr><th>ID</th><th>Título</th><th>URL Destino</th><th>Scans</th></tr></thead>
        <tbody>
          <tr v-for="i in items" :key="i.id"><td>#{{ i.id }}</td><td><strong>{{ i.title }}</strong></td><td>{{ i.target_url }}</td><td><span class="badge">{{ i.scans }} scans</span></td></tr>
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
const title = ref('');
const targetUrl = ref('');
const fetchItems = async () => {
  const token = localStorage.getItem('token');
  const res = await axios.get(`${API_URL}/tools/qr-code`, { headers: { Authorization: `Bearer ${token}` } });
  items.value = res.data.items;
};
const createItem = async () => {
  const token = localStorage.getItem('token');
  await axios.post(`${API_URL}/tools/qr-code`, { title: title.value, target_url: targetUrl.value }, { headers: { Authorization: `Bearer ${token}` } });
  title.value = ''; targetUrl.value = '';
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
.badge { background: var(--accent-soft); color: var(--accent); padding: 0.25rem 0.5rem; border-radius: var(--r-sm); font-size: 0.75rem; }
.empty { color: var(--ink-muted); font-style: italic; }
</style>