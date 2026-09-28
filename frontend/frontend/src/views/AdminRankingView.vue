<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Ranking — Pódio 🏆</h1>
      <p class="page-sub">Os usuários mais pontuados da plataforma</p>
    </div>

    <div class="podium-container">
      <div class="podium-row podium-top">
        <div v-for="(r, idx) in topRankings" :key="r.id" class="podium-card" :class="`rank-${idx + 1}`">
          <div class="medal">{{ medalIcon(idx) }}</div>
          <div class="rank-badge">#{{ idx + 1 }}</div>
          <div class="avatar">{{ r.user?.name?.charAt(0) || '?' }}</div>
          <div class="user-name">{{ r.user?.name || 'Usuário' }}</div>
          <div class="points-val">{{ r.points }} pts</div>
        </div>
      </div>

      <div class="podium-row podium-middle">
        <div v-for="(r, idx) in midRankings" :key="r.id" class="podium-card rank-mid">
          <div class="rank-badge">#{{ idx + 4 }}</div>
          <div class="avatar">{{ r.user?.name?.charAt(0) || '?' }}</div>
          <div class="user-name">{{ r.user?.name || 'Usuário' }}</div>
          <div class="points-val">{{ r.points }} pts</div>
        </div>
      </div>

      <div v-if="bottomRankings.length > 0" class="podium-row podium-bottom">
        <div v-for="(r, idx) in bottomRankings" :key="r.id" class="podium-card rank-bottom">
          <div class="rank-badge">#{{ idx + 7 }}</div>
          <div class="avatar">{{ r.user?.name?.charAt(0) || '?' }}</div>
          <div class="user-name">{{ r.user?.name || 'Usuário' }}</div>
          <div class="points-val">{{ r.points }} pts</div>
        </div>
      </div>
    </div>

    <div class="card full-list">
      <div class="table-header">
        <h3>Ranking Completo — Top 10</h3>
        <button class="btn btn-secondary" @click="loadRanking">🔄 Atualizar</button>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>POSIÇÃO</th>
            <th>USUÁRIO</th>
            <th>PONTOS</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(r, idx) in rankedList" :key="r.id" :class="{ 'rank-1': idx === 0, 'rank-2': idx === 1, 'rank-3': idx === 2 }">
            <td><span class="rank-number">{{ idx + 1 }}</span></td>
            <td><strong>{{ r.user?.name || 'Usuário' }}</strong></td>
            <td><span class="points-val">{{ r.points }} pts</span></td>
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

const rankings = ref<any[]>([]);
const loading = ref(false);

const medalIcon = (idx: number) => {
  const medals = ['🥇', '🥈', '🥉'];
  return medals[idx] || `#${idx + 1}`;
};

const rankedList = computed(() => {
  return [...rankings.value].sort((a, b) => (b.points || 0) - (a.points || 0));
});

const topRankings = computed(() => rankedList.value.slice(0, 3));
const midRankings = computed(() => rankedList.value.slice(3, 6));
const bottomRankings = computed(() => rankedList.value.slice(6, 10));

const loadRanking = async () => {
  loading.value = true;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const res = await axios.get(`${API_URL}/api/ranking/podio`, { headers });
    rankings.value = res.data.data || res.data || [];
  } catch (e) {
    console.error('Erro ao carregar ranking:', e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => loadRanking());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }

.podium-container { display: flex; flex-direction: column; gap: 1rem; }
.podium-row { display: flex; justify-content: center; gap: 1rem; flex-wrap: wrap; }
.podium-top { gap: 1.5rem; }

.podium-card {
  background: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: var(--r-lg);
  padding: 1.25rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  min-width: 140px;
}
.podium-card.rank-1 { border-color: #f5a623; background: oklch(.85 .15 50 / 8%); }
.podium-card.rank-2 { border-color: #c0c0c0; background: oklch(.85 .05 200 / 5%); }
.podium-card.rank-3 { border-color: #cd7f32; background: oklch(.8 .1 30 / 6%); }
.rank-mid { opacity: 0.9; }
.rank-bottom { opacity: 0.8; }

.medal { font-size: 2rem; }
.rank-badge { font-family: var(--font-display); font-weight: 800; font-size: 1.1rem; color: var(--accent); }
.rank-1 .rank-badge { color: #f5a623; }
.rank-2 .rank-badge { color: #c0c0c0; }
.rank-3 .rank-badge { color: #cd7f32; }

.avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--accent);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.25rem;
}
.user-name { font-weight: 700; font-size: 1rem; color: var(--ink); }
.points-val { font-weight: 600; font-size: 0.9rem; color: var(--success); }

.full-list { overflow: hidden; }
.table-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); }
.table-header h3 { font-family: var(--font-display); font-size: 1rem; color: var(--ink); }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); text-align: left; }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.rank-number { font-family: var(--font-display); font-weight: 800; color: var(--accent); }
.rank-1 .rank-number { color: #f5a623; }
.rank-2 .rank-number { color: #c0c0c0; }
.rank-3 .rank-number { color: #cd7f32; }

.btn { padding: 0.75rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.loading-state { padding: 3rem; text-align: center; color: var(--ink-muted); }
</style>
