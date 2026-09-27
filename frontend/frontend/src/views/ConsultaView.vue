<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Consulta PuxadaVip 🟡</h1>
      <p class="page-sub">Realize consultas de documentos e verificação de cadastros</p>
    </div>

    <div class="card form-card">
      <form @submit.prevent="handleConsulta" style="display:flex;gap:1rem;">
        <input v-model="documento" type="text" placeholder="Digite o CPF/CNPJ..." required style="flex:1;padding:0.75rem;background:var(--surface-2);border:1px solid var(--hairline);border-radius:8px;color:var(--ink);" />
        <button type="submit" style="padding:0.75rem 1.5rem;background:var(--accent);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;">Consultar 🔍</button>
      </form>
    </div>

    <div class="card table-card" style="margin-top:1.5rem;">
      <h3 style="padding:1rem 1.25rem;border-bottom:1px solid var(--hairline);">Histórico de Consultas</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Documento</th>
            <th>Tipo</th>
            <th>Status</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="consultas.length === 0"><td colspan="4" style="text-align:center;padding:2rem;color:var(--ink-muted);">Nenhuma consulta realizada.</td></tr>
          <tr v-for="c in consultas" :key="c.id">
            <td><code>{{ c.documento }}</code></td>
            <td>{{ c.tipo }}</td>
            <td><span class="badge badge-success">{{ c.resultado?.status || 'REGULAR' }}</span></td>
            <td>{{ new Date(c.created_at).toLocaleDateString('pt-BR') }}</td>
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

const documento = ref('');
const consultas = ref<any[]>([]);

const loadConsultas = async () => {
  const res = await axios.get(`${API_URL}/consultas`);
  consultas.value = res.data;
};

const handleConsulta = async () => {
  await axios.post(`${API_URL}/consultas`, { documento: documento.value });
  documento.value = '';
  await loadConsultas();
};

onMounted(() => loadConsultas());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.25rem; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.badge-success { background: var(--success-soft); color: var(--success); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.75rem; }
</style>