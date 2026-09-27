<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Bypass 🔴</h1>
        <p class="page-sub">Gerencie regras de desvio e distribuição de binários APK</p>
      </div>
      <button class="btn btn-primary" @click="showModal = true">+ Nova Regra Bypass</button>
    </div>

    <!-- Stats rápidas -->
    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">Regras Ativas</span>
        <span class="stat-value">{{ rules.length }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Limite Diário</span>
        <span class="stat-value">15</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Uso Hoje</span>
        <span class="stat-value">{{ rules.length }} / 15</span>
      </div>
    </div>

    <!-- Tabela de Regras Bypass -->
    <div class="card table-card">
      <div class="card-header">
        <h3>Regras Cadastradas</h3>
      </div>
      <div v-if="isLoading" class="loading-state">Carregando regras...</div>
      <div v-else-if="rules.length === 0" class="empty-state">
        Nenhuma regra bypass cadastrada. Clique em "+ Nova Regra Bypass" para criar.
      </div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Nome da Regra</th>
            <th>Target / Destino</th>
            <th>Status</th>
            <th>Criado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="rule in rules" :key="rule.id">
            <td><strong>{{ rule.name }}</strong></td>
            <td><code>{{ rule.target || rule.target_package || 'Global' }}</code></td>
            <td><span class="badge badge-success">Ativo</span></td>
            <td>{{ formatDate(rule.created_at) }}</td>
            <td class="actions-cell">
              <button class="btn-sm btn-secondary" @click="deleteRule(rule.id)">Excluir 🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Nova Regra -->
    <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Nova Regra Bypass</h3>
          <button class="btn-close" @click="showModal = false">✕</button>
        </div>
        <form @submit.prevent="handleCreateRule">
          <div class="form-group">
            <label>Nome da Regra</label>
            <input v-model="form.name" type="text" placeholder="Ex: Bypass Nubank 2.0" required />
          </div>
          <div class="form-group">
            <label>Pacote Alvo / Target</label>
            <input v-model="form.target_package" type="text" placeholder="Ex: com.nu.production" required />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showModal = false">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
              {{ isSubmitting ? 'Criando...' : 'Salvar Regra' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';

const rules = ref<any[]>([]);
const isLoading = ref(true);
const showModal = ref(false);
const isSubmitting = ref(false);

const form = ref({
  name: '',
  target_package: ''
});

const loadRules = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(`${API_URL}/bypass-rules`);
    rules.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Erro ao carregar regras:', err);
  } finally {
    isLoading.value = false;
  }
};

const handleCreateRule = async () => {
  isSubmitting.value = true;
  try {
    await axios.post(`${API_URL}/bypass-rules`, form.value);
    showModal.value = false;
    form.value = { name: '', target_package: '' };
    await loadRules();
  } catch (err) {
    alert('Erro ao criar regra.');
  } finally {
    isSubmitting.value = false;
  }
};

const deleteRule = async (id: number) => {
  if (!confirm('Deseja realmente excluir esta regra?')) return;
  try {
    await axios.delete(`${API_URL}/bypass-rules/${id}`);
    await loadRules();
  } catch (err) {
    alert('Erro ao excluir regra.');
  }
};

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('pt-BR');
};

onMounted(() => {
  loadRules();
});
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-header { display: flex; align-items: center; justify-content: space-between; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { font-size: 0.875rem; color: var(--ink-muted); }

.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.stat-card { background: var(--surface-1); border: 1px solid var(--hairline); padding: 1.25rem; border-radius: var(--r-md); display: flex; flex-direction: column; gap: 0.25rem; }
.stat-label { font-size: 0.75rem; color: var(--ink-muted); text-transform: uppercase; letter-spacing: 0.05em; }
.stat-value { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }

.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.card-header { padding: 1.25rem; border-bottom: 1px solid var(--hairline); }
.card-header h3 { font-size: 1rem; font-weight: 600; color: var(--ink); }

.loading-state, .empty-state { padding: 3rem; text-align: center; color: var(--ink-muted); }

.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); font-weight: 600; border-bottom: 1px solid var(--hairline); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }

.badge { font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: var(--r-pill); }
.badge-success { background: var(--success-soft); color: var(--success); }

.btn { padding: 0.6rem 1.2rem; border-radius: var(--r-sm); font-weight: 600; border: none; cursor: pointer; transition: opacity 0.2s; }
.btn-primary { background: var(--accent); color: white; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.75rem; border-radius: var(--r-sm); border: none; cursor: pointer; }

.modal-backdrop { position: fixed; inset: 0; background: oklch(0 0 0 / 60%); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); width: 100%; max-width: 480px; padding: 1.5rem; box-shadow: var(--shadow-modal); }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; }
.btn-close { background: none; border: none; color: var(--ink-muted); font-size: 1.25rem; cursor: pointer; }

.form-group { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; }
.form-group label { font-size: 0.85rem; font-weight: 500; color: var(--ink-muted); }
.form-group input { padding: 0.65rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); outline: none; }
.form-group input:focus { border-color: var(--accent); }

.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
</style>