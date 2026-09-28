<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Logs do Sistema 📋</h1>
      <p class="page-sub">Registro de atividades e auditoria de ações</p>
    </div>
    <div class="card table-card">
      <table class="data-table">
        <thead>
          <tr><th>Ator</th><th>Tipo</th><th>Descrição</th><th>IP</th><th>Data</th></tr>
        </thead>
        <tbody>
          <tr v-for="l in logs" :key="l.id">
            <td><strong>{{ l.ator || 'Sistema' }}</strong></td>
            <td><code>{{ l.tipo }}</code></td>
            <td>{{ l.descricao }}</td>
            <td>{{ l.ip || '127.0.0.1' }}</td>
            <td>{{ new Date(l.created_at).toLocaleDateString('pt-BR') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const logs = ref<any[]>([]);
onMounted(async () => {
  try {
    const res = await axios.get(`${API_URL}/admin/logs`);
    logs.value = res.data.data || res.data || [];
  } catch (e) {}
});
</script>
<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
</style>