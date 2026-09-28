<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">{{ isEditMode ? 'Editar Tutorial' : 'Novo Tutorial' }} 📝</h1>
      <p class="page-sub">{{ isEditMode ? 'Altere os dados deste tutorial' : 'Crie um novo tutorial para seus clientes' }}</p>
    </div>

    <RouterLink to="/revendedor/tutoriais" class="back-link">← Voltar à lista de tutoriais</RouterLink>

    <div class="card form-card">
      <form @submit.prevent="saveTutorial" class="tutorial-form">
        <div class="form-group">
          <label for="title">Título</label>
          <input id="title" v-model="form.title" type="text" placeholder="Título do tutorial" required />
        </div>

        <div class="form-group">
          <label for="description">Descrição</label>
          <textarea id="description" v-model="form.description" placeholder="Descreva o tutorial brevemente" rows="3" required></textarea>
        </div>

        <div class="form-group">
          <label for="category">Categoria</label>
          <select id="category" v-model="form.category" required>
            <option value="" disabled>Selecione uma categoria</option>
            <option value="guia">Guia</option>
            <option value="video">Vídeo</option>
            <option value="artigo">Artigo</option>
            <option value="dica">Dica</option>
            <option value="outro">Outro</option>
          </select>
        </div>

        <div class="form-group">
          <label for="video_url">URL do Vídeo</label>
          <input id="video_url" v-model="form.video_url" type="url" placeholder="https://youtube.com/..." />
        </div>

        <div class="form-group">
          <label for="content">Conteúdo</label>
          <textarea id="content" v-model="form.content" placeholder="Conteúdo detalhado do tutorial" rows="6" required></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary" :disabled="loading">{{ loading ? 'Salvando...' : (isEditMode ? 'Atualizar' : 'Criar') }}</button>
        </div>

        <div v-if="error" class="error-msg">{{ error }}</div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();
const route = useRoute();

const loading = ref(false);
const error = ref('');
const tutorialId = ref<string | null>(route.params.id as string || null);
const isEditMode = tutorialId.value !== null;

const form = reactive({
  title: '',
  description: '',
  category: '',
  video_url: '',
  content: '',
});

const getHeaders = () => ({
  Authorization: `Bearer ${authStore.token}`,
});

const loadTutorial = async () => {
  if (!tutorialId.value) return;
  loading.value = true;
  try {
    const res = await axios.get(`${API_URL}/revendedor/tutoriais/${tutorialId.value}`, {
      headers: getHeaders(),
    });
    const data = res.data;
    form.title = data.title || '';
    form.description = data.description || '';
    form.category = data.category || '';
    form.video_url = data.video_url || '';
    form.content = data.content || '';
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao carregar tutorial.';
  } finally {
    loading.value = false;
  }
};

const saveTutorial = async () => {
  error.value = '';
  loading.value = true;
  try {
    const headers = getHeaders();
    const payload = {
      title: form.title,
      description: form.description,
      category: form.category,
      video_url: form.video_url,
      content: form.content,
    };

    if (isEditMode && tutorialId.value) {
      await axios.put(`${API_URL}/revendedor/tutoriais/${tutorialId.value}`, payload, { headers });
    } else {
      await axios.post(`${API_URL}/revendedor/tutoriais`, payload, { headers });
    }
    window.location.href = '/revendedor/tutoriais';
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao salvar tutorial.';
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (isEditMode) loadTutorial();
});
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.back-link { color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem; }
.back-link:hover { text-decoration: underline; }
.form-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; }
.tutorial-form { display: flex; flex-direction: column; gap: 1.25rem; max-width: 640px; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-weight: 600; font-size: 0.85rem; color: var(--ink); }
.form-group input,
.form-group textarea,
.form-group select {
  padding: 0.65rem 0.75rem;
  border: 1px solid var(--hairline);
  border-radius: var(--r-sm);
  background: var(--surface-2);
  color: var(--ink);
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s;
  font-family: inherit;
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus { border-color: var(--accent); }
.btn { padding: 0.75rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { opacity: 0.85; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.error-msg { color: var(--danger); font-size: 0.85rem; margin-top: 0.5rem; }
</style>
