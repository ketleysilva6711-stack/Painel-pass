<template>
  <div class="page-container">
    <header class="page-header">
      <h1>Play Fake — Gerenciador de Publicações</h1>
      <p>Crie e gerencie seus links de publicação simulados.</p>
    </header>

    <div class="card form-card">
      <h3>Novo App Play Fake</h3>
      <form @submit.prevent="createApp" class="form-row">
        <input v-model="name" type="text" placeholder="Nome do App (ex: Itaú Mobile)" required />
        <input v-model="packageName" type="text" placeholder="Package Name (ex: com.itau.mobile)" required />
        <button type="submit" class="btn btn-primary" :disabled="loading">Criar App</button>
      </form>
    </div>

    <div class="card list-card">
      <h3>Seus Apps Publicados</h3>
      <div v-if="apps.length === 0" class="empty">Nenhum app publicado ainda.</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Package</th>
            <th>Link Público</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="app in apps" :key="app.id">
            <td>#{{ app.id }}</td>
            <td><strong>{{ app.name }}</strong></td>
            <td><code>{{ app.package_name }}</code></td>
            <td><a :href="'/c/' + app.slug" target="_blank">/c/{{ app.slug }}</a></td>
            <td><span class="badge badge-success">{{ app.status }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || '/api';
const apps = ref<any[]>([]);
const name = ref('');
const packageName = ref('');
const loading = ref(false);

const fetchApps = async () => {
  try {
    const token = localStorage.getItem('token');
    const res = await axios.get(`${API_URL}/tools/play-fake`, {
      headers: { Authorization: `Bearer ${token}` }
    });
    apps.value = res.data.apps;
  } catch (err) {
    console.error(err);
  }
};

const createApp = async () => {
  loading.value = true;
  try {
    const token = localStorage.getItem('token');
    await axios.post(`${API_URL}/tools/play-fake`, {
      name: name.value,
      package_name: packageName.value
    }, {
      headers: { Authorization: `Bearer ${token}` }
    });
    name.value = '';
    packageName.value = '';
    await fetchApps();
  } catch (err) {
    alert('Erro ao criar app');
  } finally {
    loading.value = false;
  }
};

onMounted(fetchApps);
</script>

<style scoped>
.page-container { padding: 2rem; }
.page-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0 0 0.5rem 0; }
.page-header p { color: var(--ink-muted); margin-bottom: 2rem; }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-md); padding: 1.5rem; margin-bottom: 2rem; }
.form-row { display: flex; gap: 1rem; margin-top: 1rem; }
.form-row input { flex: 1; padding: 0.75rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); }
.btn-primary { background: var(--accent); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: var(--r-sm); font-weight: 600; cursor: pointer; }
.data-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--hairline); }
.data-table th { color: var(--ink-muted); font-size: 0.85rem; }
.badge-success { background: var(--success-soft); color: var(--success); padding: 0.25rem 0.5rem; border-radius: var(--r-sm); font-size: 0.75rem; }
.empty { color: var(--ink-muted); font-style: italic; margin-top: 1rem; }
</style>