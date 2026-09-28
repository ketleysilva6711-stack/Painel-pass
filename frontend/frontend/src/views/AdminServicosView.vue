<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Gestão de Serviços ⚙️</h1>
      <p class="page-sub">Gerencie os serviços disponíveis na plataforma — adicione, edite ou exclua</p>
    </div>

    <div class="card">
      <div class="table-header">
        <h3>Todos os Serviços</h3>
        <button class="btn btn-primary" @click="openAddForm">+ Novo Serviço</button>
      </div>

      <div v-if="loading" class="loading-state">Carregando serviços...</div>

      <table class="data-table" v-else-if="servicos.length > 0">
        <thead>
          <tr>
            <th>Ícone</th>
            <th>NOME</th>
            <th>DESCRIÇÃO</th>
            <th>PREÇO</th>
            <th>DURAÇÃO (dias)</th>
            <th>STATUS</th>
            <th>AÇÕES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in servicos" :key="s.id">
            <td><span class="icon-emoji">{{ s.icon || '📦' }}</span></td>
            <td><strong>{{ s.name }}</strong></td>
            <td>{{ s.description || '—' }}</td>
            <td>R$ {{ parseFloat(s.price).toFixed(2) }}</td>
            <td>{{ s.duration_days ?? '—' }}</td>
            <td><span class="badge" :class="s.status === 'active' ? 'badge-success' : 'badge-default'">{{ s.status }}</span></td>
            <td class="actions">
              <button class="btn btn-sm btn-secondary" @click="openEditForm(s)">✏️ Editar</button>
              <button class="btn btn-sm btn-danger" @click="deleteServico(s.id)">🗑 Excluir</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="empty-state">
        <h4>Nenhum serviço encontrado</h4>
        <p>Adicione um novo serviço para começar.</p>
      </div>
    </div>

    <div v-if="showForm" class="modal-overlay" @click.self="closeForm">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ editingId ? 'Editar Serviço' : 'Novo Serviço' }}</h3>
          <button class="modal-close" @click="closeForm">✕</button>
        </div>
        <form @submit.prevent="saveServico" class="servico-form">
          <div class="form-group">
            <label for="svc-icon">Ícone</label>
            <input id="svc-icon" v-model="form.icon" type="text" placeholder="Ex: ⚡, 🔧, 🛡" required />
          </div>
          <div class="form-group">
            <label for="svc-name">Nome</label>
            <input id="svc-name" v-model="form.name" type="text" placeholder="Nome do serviço" required />
          </div>
          <div class="form-group">
            <label for="svc-desc">Descrição</label>
            <textarea id="svc-desc" v-model="form.description" placeholder="Descrição do serviço" rows="3" />
          </div>
          <div class="form-group">
            <label for="svc-price">Preço (R$)</label>
            <input id="svc-price" v-model="form.price" type="number" step="0.01" min="0" placeholder="0.00" required />
          </div>
          <div class="form-group">
            <label for="svc-duration">Duração (dias)</label>
            <input id="svc-duration" v-model="form.duration_days" type="number" min="1" placeholder="30" required />
          </div>
          <div class="form-group">
            <label for="svc-status">Status</label>
            <select id="svc-status" v-model="form.status">
              <option value="active">Ativo</option>
              <option value="inactive">Inativo</option>
            </select>
          </div>
          <div v-if="formError" class="error-msg">{{ formError }}</div>
          <div class="form-actions">
            <button type="button" class="btn btn-secondary" @click="closeForm">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="saving">{{ saving ? 'Salvando...' : (editingId ? 'Atualizar' : 'Criar') }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();

const servicos = ref<any[]>([]);
const loading = ref(false);
const saving = ref(false);
const showForm = ref(false);
const editingId = ref<number | null>(null);
const formError = ref('');

const form = reactive({
  icon: '',
  name: '',
  description: '',
  price: '',
  duration_days: '',
  status: 'active' as string,
});

const loadServicos = async () => {
  loading.value = true;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const res = await axios.get(`${API_URL}/servicos`, { headers });
    servicos.value = res.data.data || res.data || [];
  } catch (e: any) {
    console.error('Erro ao carregar serviços:', e);
  } finally {
    loading.value = false;
  }
};

const openAddForm = () => {
  editingId.value = null;
  Object.assign(form, { icon: '', name: '', description: '', price: '', duration_days: '', status: 'active' });
  formError.value = '';
  showForm.value = true;
};

const openEditForm = (s: any) => {
  editingId.value = s.id;
  Object.assign(form, {
    icon: s.icon || '',
    name: s.name || '',
    description: s.description || '',
    price: s.price || '',
    duration_days: s.duration_days ?? '',
    status: s.status || 'active',
  });
  formError.value = '';
  showForm.value = true;
};

const closeForm = () => {
  showForm.value = false;
  editingId.value = null;
  formError.value = '';
};

const validateForm = (): boolean => {
  if (!form.name.trim()) { formError.value = 'O nome é obrigatório'; return false; }
  if (!form.price || isNaN(Number(form.price)) || Number(form.price) < 0) { formError.value = 'Preço válido é obrigatório'; return false; }
  if (!form.duration_days || isNaN(Number(form.duration_days)) || Number(form.duration_days) < 1) { formError.value = 'Duração válida é obrigatória'; return false; }
  return true;
};

const saveServico = async () => {
  formError.value = '';
  if (!validateForm()) return;
  saving.value = true;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const payload = {
      icon: form.icon,
      name: form.name.trim(),
      description: form.description.trim(),
      price: Number(form.price),
      duration_days: Number(form.duration_days),
      status: form.status,
    };
    if (editingId.value) {
      await axios.put(`${API_URL}/servicos/${editingId.value}`, payload, { headers });
    } else {
      await axios.post(`${API_URL}/servicos`, payload, { headers });
    }
    closeForm();
    await loadServicos();
  } catch (e: any) {
    formError.value = e.response?.data?.message || 'Erro ao salvar serviço';
  } finally {
    saving.value = false;
  }
};

const deleteServico = async (id: number) => {
  if (!confirm('Tem certeza que deseja excluir este serviço?')) return;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    await axios.delete(`${API_URL}/servicos/${id}`, { headers });
    await loadServicos();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao excluir serviço');
  }
};

onMounted(() => loadServicos());
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
.icon-emoji { font-size: 1.2rem; }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.75rem; text-transform: capitalize; }
.badge-success { background: var(--success-soft); color: var(--success); }
.badge-default { background: var(--surface-2); color: var(--ink-muted); }
.actions { display: flex; gap: 0.35rem; }
.btn { padding: 0.5rem 1rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.35rem; font-size: 0.85rem; }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { opacity: 0.85; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.btn-secondary:hover { background: var(--surface-3); }
.btn-danger { background: var(--danger); color: white; }
.loading-state { padding: 3rem; text-align: center; color: var(--ink-muted); }
.empty-state { text-align: center; padding: 3rem; color: var(--ink-muted); }
.empty-state h4 { margin-bottom: 0.5rem; color: var(--ink); }
.empty-state p { color: var(--ink-muted); }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 1rem; }
.modal-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); width: 100%; max-width: 480px; overflow: hidden; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid var(--hairline); }
.modal-header h3 { font-family: var(--font-display); font-size: 1.1rem; color: var(--ink); }
.modal-close { background: none; border: none; color: var(--ink-muted); cursor: pointer; font-size: 1.2rem; padding: 0.25rem; }
.modal-close:hover { color: var(--ink); }
.servico-form { padding: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-weight: 600; font-size: 0.85rem; color: var(--ink); }
.form-group input, .form-group select, .form-group textarea { padding: 0.65rem 0.75rem; border: 1px solid var(--hairline); border-radius: var(--r-sm); background: var(--surface-2); color: var(--ink); font-size: 0.9rem; outline: none; font-family: inherit; }
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--accent); }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 0.5rem; }
.error-msg { color: var(--danger); font-size: 0.85rem; }
</style>
