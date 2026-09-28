<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Ranking — Pódio 🏅</h1>
      <p class="page-sub">Os melhores colocados na plataforma</p>
    </div>

    <!-- Podium -->
    <div class="podium-grid" v-if="!loading && !error">
      <div v-for="(r, idx) in topThree" :key="r.id" class="podium-card" :class="`rank-${idx + 1}`">
        <div class="medal">{{ medalIcon(idx) }}</div>
        <div class="rank-badge">#{{ idx + 1 }}</div>
        <div class="user-name">{{ r.user?.name || 'Usuário' }}</div>
        <div class="points-val">{{ r.points }} pts</div>
      </div>
    </div>

    <div v-if="loading" class="loading-state">Carregando ranking...</div>
    <div v-else-if="error" class="error-box">{{ error }}</div>

    <!-- Rest of list -->
    <div class="card" v-if="!loading && !error && restList.length > 0">
      <div class="section-header">
        <h3>Demais Colocados</h3>
        <span class="badge">{{ restList.length }} usuários</span>
      </div>
      <table class="data-table">
        <thead>
          <tr><th>#</th><th>USUÁRIO</th><th>PONTOS</th></tr>
        </thead>
        <tbody>
          <tr v-for="(r, idx) in restList" :key="r.id">
            <td>{{ idx + 4 }}</td>
            <td><strong>{{ r.user?.name || 'Usuário' }}</strong></td>
            <td>{{ r.points }} pts</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();

const rank = ref<any[]>([]);
const loading = ref(false);
const error = ref('');

const topThree = computed(() => rank.value.slice(0, 3));
const restList = computed(() => rank.value.slice(3));

const medalIcon = (idx: number) => {
  const icons = ['🥇', '🥈', '🥉'];
  return icons[idx] || `#${idx + 1}`;
};

const loadRank = async () => {
  loading.value = true;
  error.value = '';
  try {
    const res = await axios.get(`${API_URL}/ranking/podio`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    rank.value = res.data.data || res.data.rank || res.data || [];
  } catch (e: any) {
    console.error('Erro ao carregar ranking:', e);
    error.value = e.response?.data?.message || 'Erro ao carregar ranking.';
  } finally {
    loading.value = false;
  }
};

onMounted(() => loadRank());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.section-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid var(--hairline); }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; background: var(--surface-2); color: var(--ink-muted); }

.podium-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-top: 1rem; }
.podium-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 2rem 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.75rem; }
.rank-1 { border-color: #f5a623; background: oklch(.78 .17 70 / 10%); }
.rank-2 { border-color: #c0c0c0; background: oklch(.85 .1 60 / 10%); }
.rank-3 { border-color: #cd7f32; background: oklch(.75 .15 40 / 10%); }
.medal { font-size: 2.5rem; }
.rank-badge { font-size: 1.5rem; font-weight: 800; color: var(--accent); }
.user-name { font-weight: 700; font-size: 1.1rem; color: var(--ink); }
.points-val { font-size: 0.9rem; color: var(--success); font-weight: 600; }

.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }

.loading-state, .error-box { padding: 3rem; text-align: center; color: var(--ink-muted); }
.error-box { color: var(--danger); }
</style>
