<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">QR Code 📱</h1>
        <p class="page-sub">Gere códigos QR para redirecionamento rápido de links e aplicativos</p>
      </div>
      <button class="btn btn-primary" @click="showModal = true">+ Gerar QR Code</button>
    </div>

    <!-- Stats rápidas -->
    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">QR Codes Gerados</span>
        <span class="stat-value">{{ qrcodes.length }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Limite Diário</span>
        <span class="stat-value">15</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Uso Hoje</span>
        <span class="stat-value">{{ qrcodes.length }} / 15</span>
      </div>
    </div>

    <!-- Tabela de QR Codes -->
    <div class="card table-card">
      <div class="card-header">
        <h3>Seus QR Codes</h3>
      </div>
      <div v-if="isLoading" class="loading-state">Carregando QR Codes...</div>
      <div v-else-if="qrcodes.length === 0" class="empty-state">
        Nenhum QR Code gerado ainda. Clique em "+ Gerar QR Code" para começar.
      </div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Título</th>
            <th>URL Destino</th>
            <th>QR Code</th>
            <th>Criado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="qr in qrcodes" :key="qr.id">
            <td><strong>{{ qr.title || qr.name || 'QR Code' }}</strong></td>
            <td><code>{{ qr.url || qr.target_url }}</code></td>
            <td>
              <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=${encodeURIComponent(qr.url || qr.target_url)}`" alt="QR Code" class="qr-img" />
            </td>
            <td>{{ formatDate(qr.created_at) }}</td>
            <td class="actions-cell">
              <button class="btn-sm btn-secondary" @click="deleteQr(qr.id)">Excluir 🗑️</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Novo QR Code -->
    <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Gerar Novo QR Code</h3>
          <button class="btn-close" @click="showModal = false">✕</button>
        </div>
        <form @submit.prevent="handleCreateQr">
          <div class="form-group">
            <label>Título do QR Code</label>
            <input v-model="form.title" type="text" placeholder="Ex: Divulgação Nubank" required />
          </div>
          <div class="form-group">
            <label>URL de Destino</label>
            <input v-model="form.target_url" type="url" placeholder="https://..." required />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showModal = false">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
              {{ isSubmitting ? 'Gerando...' : 'Gerar QR Code' }}
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

const qrcodes = ref<any[]>([]);
const isLoading = ref(true);
const showModal = ref(false);
const isSubmitting = ref(false);

const form = ref({
  title: '',
  target_url: ''
});

const loadQrcodes = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(`${API_URL}/qr-code-links`);
    qrcodes.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Erro ao carregar QR Codes:', err);
  } finally {
    isLoading.value = false;
  }
};

const handleCreateQr = async () => {
  isSubmitting.value = true;
  try {
    await axios.post(`${API_URL}/qr-code-links`, form.value);
    showModal.value = false;
    form.value = { title: '', target_url: '' };
    await loadQrcodes();
  } catch (err) {
    alert('Erro ao gerar QR Code.');
  } finally {
    isSubmitting.value = false;
  }
};

const deleteQr = async (id: number) => {
  if (!confirm('Deseja excluir este QR Code?')) return;
  try {
    await axios.delete(`${API_URL}/qr-code-links/${id}`);
    await loadQrcodes();
  } catch (err) {
    alert('Erro ao excluir QR Code.');
  }
};

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('pt-BR');
};

onMounted(() => {
  loadQrcodes();
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

.qr-img { width: 50px; height: 50px; border-radius: 4px; border: 1px solid var(--hairline); }

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