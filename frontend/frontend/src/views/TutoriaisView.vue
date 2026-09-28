<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Tutoriais 📚</h1>
        <p class="page-sub">Central de ajuda e instruções de uso</p>
      </div>
      <router-link v-if="isAdmin" to="/admin/tutoriais/novo" class="btn btn-primary">+ Novo Tutorial</router-link>
    </div>
    <div v-if="isLoading" class="loading-state">Carregando tutoriais...</div>
    <div v-else-if="error" class="empty-state">{{ error }}</div>
    <div v-else-if="tutorials.length === 0" class="empty-state">Nenhum tutorial publicado ainda.</div>
    <div v-else class="tut-grid">
      <div class="card tut-card" v-for="tut in tutorials" :key="tut.id">
        <h3>{{ tut.title }}</h3>
        <p>{{ tut.description }}</p>
        <div class="tut-meta">
          <span v-if="tut.category" class="badge">{{ tut.category }}</span>
          <a v-if="tut.video_url" :href="tut.video_url" target="_blank" class="tut-link">▶ Assistir</a>
        </div>
        <p v-if="tut.content" class="tut-content">{{ tut.content }}</p>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();
const isAdmin = computed(() => authStore.user?.role === 'admin');

const tutorials = ref<any[]>([]);
const isLoading = ref(true);
const error = ref('');

const loadTutorials = async () => {
  isLoading.value = true;
  error.value = '';
  try {
    const res = await axios.get(`${API_URL}/tutoriais`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    const list = res.data.data || res.data.items || res.data || [];
    tutorials.value = Array.isArray(list) ? list : [];
  } catch (e: any) {
    if (e?.response?.status === 403) {
      error.value = 'Acesso restrito: tutoriais disponíveis para administradores.';
    } else {
      error.value = 'Erro ao carregar tutoriais.';
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => loadTutorials());
</script>
<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.tut-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; }
.tut-card h3 { font-size: 1.1rem; font-weight: 700; color: var(--ink); margin-bottom: 0.5rem; }
.tut-card p { font-size: 0.875rem; color: var(--ink-muted); }
.tut-meta { display: flex; align-items: center; gap: 0.75rem; margin-top: 0.75rem; }
.badge { font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 4px; background: var(--surface-2); color: var(--ink-muted); }
.tut-link { font-size: 0.85rem; font-weight: 600; color: var(--accent); text-decoration: none; }
.tut-content { margin-top: 0.75rem; white-space: pre-wrap; }
.loading-state, .empty-state { padding: 3rem; text-align: center; color: var(--ink-muted); }
.page-header { display: flex; align-items: center; justify-content: space-between; }
.btn { padding: 0.6rem 1.2rem; border-radius: var(--r-sm); font-weight: 600; border: none; cursor: pointer; text-decoration: none; }
.btn-primary { background: var(--accent); color: white; }
</style>