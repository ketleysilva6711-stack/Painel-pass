<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Top Rank 🏆</h1>
      <p class="page-sub">Líderes da plataforma — veja quem está no topo</p>
    </div>

    <div class="leaderboard-card">
      <div class="lb-header">
        <h3>Ranking Global</h3>
        <span class="lb-count">{{ topUsers.length }} posições</span>
      </div>

      <div v-if="loading" class="loading-state">Carregando ranking...</div>
      <div v-else-if="error" class="error-box">{{ error }}</div>

      <div v-else>
        <div v-if="topUsers.length > 0" class="lb-grid">
          <div
            v-for="(user, idx) in topUsers"
            :key="user.id"
            class="lb-item"
            :class="{
              'lb-top-1': idx === 0,
              'lb-top-2': idx === 1,
              'lb-top-3': idx === 2,
            }"
          >
            <div class="lb-rank">
              <span v-if="idx < 3" class="medal" :class="`medal-${idx + 1}`">
                {{ medalIcon(idx) }}
              </span>
              <span v-else class="rank-num">#{{ idx + 1 }}</span>
            </div>
            <div class="lb-avatar">
              <img :src="user.avatar || defaultAvatar" :alt="user.name" />
            </div>
            <div class="lb-info">
              <span class="lb-name">{{ user.name || 'Usuário' }}</span>
              <span class="lb-level">{{ user.level ? `Level ${user.level}` : '' }}</span>
            </div>
            <div class="lb-score">
              <span class="points-val">{{ user.points ?? user.score ?? 0 }} pts</span>
            </div>
          </div>
        </div>

        <div v-else class="empty-state">
          <h4>Nenhum ranking disponível</h4>
          <p>Seja o primeiro a entrar no topo!</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();

const topUsers = ref<any[]>([]);
const loading = ref(false);
const error = ref('');

const medalIcon = (idx: number) => {
  if (idx === 0) return '🥇';
  if (idx === 1) return '🥈';
  if (idx === 2) return '🥉';
  return '';
};

const defaultAvatar = '/img/avatar.png';

const loadTopRank = async () => {
  loading.value = true;
  error.value = '';
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const res = await axios.get(`${API_URL}/ranking/podio`, { headers });
    topUsers.value = res.data.data || res.data.rank || res.data.top || [];
  } catch (e: any) {
    console.error('Erro ao carregar top rank:', e);
    error.value = e.response?.data?.message || 'Erro ao carregar ranking.';
  } finally {
    loading.value = false;
  }
};

onMounted(() => loadTopRank());
</script>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.page-title {
  font-family: var(--font-display);
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--ink);
}

.page-sub {
  color: var(--ink-muted);
  margin-top: 0.25rem;
}

.leaderboard-card {
  background: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: var(--r-lg);
  overflow: hidden;
}

.lb-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid var(--hairline);
}

.lb-header h3 {
  font-family: var(--font-display);
  font-size: 1rem;
  color: var(--ink);
}

.lb-count {
  padding: 0.2rem 0.6rem;
  border-radius: var(--r-sm);
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  background: var(--surface-2);
  color: var(--ink-muted);
}

.lb-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
  padding: 1.25rem;
}

.lb-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.25rem;
  background: var(--surface-2);
  border: 1px solid var(--hairline);
  border-radius: var(--r-md);
  transition: all 0.2s ease;
}

.lb-item:hover {
  box-shadow: var(--shadow-card-hover);
  transform: translateY(-1px);
}

.lb-top-1 {
  border-color: #f5a623;
  background: oklch(85% 0.12 70 / 10%);
}

.lb-top-2 {
  border-color: #c0c0c0;
  background: oklch(85% 0.05 200 / 10%);
}

.lb-top-3 {
  border-color: #cd7f32;
  background: oklch(85% 0.1 40 / 10%);
}

.lb-rank {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.medal {
  font-size: 1.5rem;
  line-height: 1;
}

.medal-1 { color: var(--warning); }
.medal-2 { color: var(--ink-muted); }
.medal-3 { color: #cd7f32; }

.rank-num {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.1rem;
  color: var(--accent);
}

.lb-avatar {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: var(--surface-1);
  border: 2px solid var(--hairline);
}

.lb-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.lb-info {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  flex: 1;
  min-width: 0;
}

.lb-name {
  font-weight: 700;
  font-size: 0.95rem;
  color: var(--ink);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.lb-level {
  font-size: 0.8rem;
  color: var(--ink-muted);
  font-weight: 500;
}

.lb-score {
  flex-shrink: 0;
}

.points-val {
  font-family: var(--font-mono);
  font-weight: 700;
  font-size: 1rem;
  color: var(--success);
}

.loading-state, .error-box, .empty-state {
  padding: 3rem;
  text-align: center;
  color: var(--ink-muted);
}

.error-box {
  color: var(--danger);
}

.empty-state h4 {
  margin-bottom: 0.5rem;
  color: var(--ink);
}

.empty-state p {
  color: var(--ink-muted);
}

@media (max-width: 640px) {
  .lb-grid {
    grid-template-columns: 1fr;
  }

  .page-title {
    font-size: 1.35rem;
  }
}
</style>
