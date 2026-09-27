<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Top Rank & Pódio 🏆</h1>
      <p class="page-sub">Classificação dos usuários mais ativos da plataforma</p>
    </div>

    <div class="podium-grid">
      <div v-for="(r, idx) in rank" :key="r.id" class="podium-card" :class="`rank-${idx + 1}`">
        <div class="rank-badge">#{{ idx + 1 }}</div>
        <div class="user-name">{{ r.user?.name || 'Usuário' }}</div>
        <div class="points-val">{{ r.points }} pts</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const rank = ref<any[]>([]);

onMounted(async () => {
  const res = await axios.get(`${API_URL}/ranking/podio`);
  rank.value = res.data;
});
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.podium-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-top: 1rem; }
.podium-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; }
.rank-1 { border-color: #f5a623; background: oklch(.78 .17 70 / 10%); }
.rank-badge { font-size: 1.5rem; font-weight: 800; color: var(--accent); }
.user-name { font-weight: 700; font-size: 1.1rem; color: var(--ink); }
.points-val { font-size: 0.9rem; color: var(--success); font-weight: 600; }
</style>