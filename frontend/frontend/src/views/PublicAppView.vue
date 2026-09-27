<template>
  <div class="store-page">
    <div v-if="isLoading" class="loading">Carregando aplicativo...</div>
    <div v-else-if="error" class="error-box">{{ error }}</div>
    <div v-else class="store-card">
      <div class="store-header">
        <div class="app-icon">{{ app.name?.charAt(0).toUpperCase() }}</div>
        <div class="app-main-info">
          <h1 class="store-app-title">{{ app.name }}</h1>
          <span class="store-dev">{{ app.developer }}</span>
          <span class="store-badge">Livre</span>
        </div>
      </div>

      <div class="metrics-bar">
        <div class="metric">
          <strong>{{ app.rating }} ★</strong>
          <span>{{ app.reviews }}</span>
        </div>
        <div class="divider"></div>
        <div class="metric">
          <strong>{{ app.size }}</strong>
          <span>Tamanho</span>
        </div>
        <div class="divider"></div>
        <div class="metric">
          <strong>{{ app.downloads }}</strong>
          <span>Downloads</span>
        </div>
      </div>

      <a :href="app.apk_url || '#'" target="_blank" class="btn-install">
        Instalar
      </a>

      <div class="store-section">
        <h3>Sobre este app</h3>
        <p>Aplicativo oficial verificado pelo Play Protect. Desfrute de todas as funcionalidades de forma segura e rápida.</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';

const app = ref<any>({});
const isLoading = ref(true);
const error = ref('');

onMounted(async () => {
  const slug = route.params.slug;
  try {
    const res = await axios.get(`${API_URL}/c/${slug}`);
    app.value = res.data;
  } catch (err: any) {
    error.value = 'Aplicativo não encontrado ou indisponível.';
  } finally {
    isLoading.value = false;
  }
});
</script>

<style scoped>
.store-page { min-height: 100vh; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; padding: 1.5rem; font-family: system-ui, sans-serif; }
.store-card { width: 100%; max-width: 480px; background: #1e293b; border-radius: 24px; padding: 2rem; box-shadow: 0 20px 40px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.1); }

.store-header { display: flex; gap: 1.25rem; align-items: center; margin-bottom: 1.75rem; }
.app-icon { width: 72px; height: 72px; border-radius: 18px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; color: white; box-shadow: 0 8px 16px rgba(59,130,246,0.3); }

.store-app-title { font-size: 1.4rem; font-weight: 700; margin: 0; color: white; }
.store-dev { font-size: 0.875rem; color: #01875f; font-weight: 600; display: block; margin: 0.2rem 0; }
.store-badge { font-size: 0.7rem; color: #94a3b8; background: rgba(255,255,255,0.05); padding: 0.15rem 0.5rem; border-radius: 4px; }

.metrics-bar { display: flex; align-items: center; justify-content: space-around; padding: 1rem 0; border-top: 1px solid rgba(255,255,255,0.08); border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 1.75rem; }
.metric { text-align: center; }
.metric strong { display: block; font-size: 0.95rem; color: white; }
.metric span { font-size: 0.75rem; color: #94a3b8; }
.divider { width: 1px; height: 24px; background: rgba(255,255,255,0.1); }

.btn-install { display: block; width: 100%; text-align: center; padding: 0.875rem; background: #01875f; color: white; font-weight: 700; border-radius: 12px; text-decoration: none; font-size: 1rem; transition: background 0.2s; box-shadow: 0 4px 12px rgba(1,135,95,0.3); }
.btn-install:hover { background: #00704f; }

.store-section { margin-top: 1.75rem; }
.store-section h3 { font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; color: white; }
.store-section p { font-size: 0.85rem; color: #94a3b8; line-height: 1.5; }

.loading, .error-box { text-align: center; color: #94a3b8; }
</style>