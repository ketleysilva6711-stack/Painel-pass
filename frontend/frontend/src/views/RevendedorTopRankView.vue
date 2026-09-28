<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Top Rank 🏆</h1>
      <p class="page-sub">Classificação geral dos usuários mais ativos</p>
    </div>

    <div class="card table-card">
      <div class="section-header">
        <h3>Ranking Global</h3>
        <span class="badge">{{ rank.length }} posições</span>
      </div>

      <div v-if="loading" class="loading-state">Carregando ranking...</div>
      <div v-else-if="error" class="error-box">{{ error }}</div>

      <table class="data-table" v-else>
        <thead>
          <tr>
            <th>RANK</th>
            <th>USUÁRIO</th>
            <th>PONTOS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="rank.length === 0"><td colspan="3" style="text-align:center;padding:2rem;color:var(--ink-muted);">Sem dados de ranking.</td></tr>
          <tr v-for="(r, idx) in rank" :key="r.id">
            <td><span class="rank-num" :class="{ 'rank-gold': idx === 0, 'rank-silver': idx === 1, 'rank-bronze': idx === 2 }">#{{ idx + 1 }}</span></td>
            <td><strong>{{ r.user?.name || 'Usuário' }}</strong></td>
            <td><span class="points-val">{{ r.points }} pts</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();

const rank = ref<any[]>([]);
const loading = ref(false);
const error = ref('');

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
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.rank-num { font-weight: 800; font-size: 1.1rem; }
.rank-gold { color: #f5a623; }
.rank-silver { color: #c0c0c0; }
.rank-bronze { color: #cd7f32; }
.points-val { font-weight: 700; color: var(--success); }
.loading-state, .error-box { padding: 3rem; text-align: center; color: var(--ink-muted); }
.error-box { color: var(--danger); }
</style>
