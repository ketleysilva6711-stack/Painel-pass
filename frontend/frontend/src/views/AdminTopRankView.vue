<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Gestão Top Rank 🏆</h1>
      <p class="page-sub">Gerencie os pontos e posições do ranking top</p>
    </div>

    <div class="card">
      <div class="table-header">
        <h3>Ranking — Pódio</h3>
        <button class="btn btn-secondary" @click="loadRanking">🔄 Atualizar</button>
      </div>

      <div v-if="loading" class="loading-state">Carregando ranking...</div>

      <table class="data-table" v-else-if="rankings.length > 0">
        <thead>
          <tr>
            <th>POSIÇÃO</th>
            <th>USUÁRIO</th>
            <th>PONTOS</th>
            <th>AÇÕES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in rankings" :key="r.id">
            <td>
              <span class="rank-number" :class="`rank-${r.position || r.rank}`">#{{ r.position || r.rank }}</span>
            </td>
            <td><strong>{{ r.user?.name || r.user_name || '—' }}</strong></td>
            <td>
              <div class="points-edit">
                <span class="points-val">{{ r.points }} pts</span>
                <input v-if="editingId === r.id" v-model.number="editPoints" type="number" class="points-input" />
              </div>
            </td>
            <td class="actions">
              <template v-if="editingId === r.id">
                <button class="btn btn-sm btn-primary" @click="savePoints(r.id)">💾 Salvar</button>
                <button class="btn btn-sm btn-secondary" @click="cancelEdit">Cancelar</button>
              </template>
              <template v-else>
                <button class="btn btn-sm btn-secondary" @click="startEdit(r)">✏️ Editar</button>
              </template>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="empty-state">
        <h4>Nenhum ranking encontrado</h4>
        <p>Nenhum usuário no pódio ainda.</p>
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

const rankings = ref<any[]>([]);
const loading = ref(false);
const editingId = ref<number | null>(null);
const editPoints = ref<number>(0);

const loadRanking = async () => {
  loading.value = true;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const res = await axios.get(`${API_URL}/ranking`, { headers });
    rankings.value = res.data.data || res.data || [];
  } catch (e) {
    console.error('Erro ao carregar ranking:', e);
  } finally {
    loading.value = false;
  }
};

const startEdit = (r: any) => {
  editingId.value = r.id;
  editPoints.value = r.points || 0;
};

const cancelEdit = () => {
  editingId.value = null;
  editPoints.value = 0;
};

const savePoints = async (id: number) => {
  try {
     await axios.put(`${API_URL}/ranking/${id}`, { points: editPoints.value }, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    editingId.value = null;
    await loadRanking();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao atualizar pontos');
  }
};

onMounted(() => loadRanking());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.table-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); }
.table-header h3 { font-family: var(--font-display); font-size: 1rem; color: var(--ink); }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); text-align: left; }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.rank-number { font-family: var(--font-display); font-weight: 800; font-size: 1.25rem; color: var(--accent); }
.rank-1 { color: #f5a623; }
.rank-2 { color: #c0c0c0; }
.rank-3 { color: #cd7f32; }
.points-edit { display: flex; align-items: center; gap: 0.75rem; }
.points-val { font-weight: 700; color: var(--success); }
.points-input { width: 80px; padding: 0.35rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); font-family: var(--font-display); }
.btn { padding: 0.75rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.btn-primary { background: var(--accent); color: white; }
.actions { display: flex; gap: 0.35rem; align-items: center; }
.loading-state { padding: 3rem; text-align: center; color: var(--ink-muted); }
.empty-state { text-align: center; padding: 3rem; color: var(--ink-muted); }
.empty-state h4 { margin-bottom: 0.5rem; color: var(--ink); }
.empty-state p { color: var(--ink-muted); }
</style>
