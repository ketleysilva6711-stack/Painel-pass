<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Play Fake 🎮</h1>
        <p class="page-sub">Crie e gerencie landing pages personalizadas de aplicativos</p>
      </div>
      <button class="btn btn-primary" @click="showModal = true">+ Criar Novo App</button>
    </div>

    <!-- Stats rápidas -->
    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">Apps Publicados</span>
        <span class="stat-value">{{ apps.length }}</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Limite Diário</span>
        <span class="stat-value">15</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Uso Hoje</span>
        <span class="stat-value">{{ apps.length }} / 15</span>
      </div>
    </div>

    <!-- Tabela de Apps -->
    <div class="card table-card">
      <div class="card-header">
        <h3>Seus Aplicativos</h3>
      </div>
      <div v-if="isLoading" class="loading-state">Carregando aplicativos...</div>
      <div v-else-if="apps.length === 0" class="empty-state">
        Nenhum aplicativo publicado ainda. Clique em "+ Criar Novo App" para começar.
      </div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>Aplicativo</th>
            <th>Pacote / Slug</th>
            <th>Status</th>
            <th>Publicado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="app in apps" :key="app.id">
            <td class="app-cell">
              <div class="app-icon-placeholder">{{ app.name?.charAt(0).toUpperCase() }}</div>
              <div>
                <strong class="app-name">{{ app.name }}</strong>
                <span class="app-dev">{{ app.developer || 'Desenvolvedor' }}</span>
              </div>
            </td>
            <td><code>/c/{{ app.slug }}</code></td>
            <td><span class="badge badge-success">Publicado</span></td>
            <td>{{ formatDate(app.created_at) }}</td>
            <td class="actions-cell">
              <a :href="`/c/${app.slug}`" target="_blank" class="btn-sm btn-secondary">Visualizar 🔗</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Novo App -->
    <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>Novo Aplicativo Play Fake</h3>
          <button class="btn-close" @click="showModal = false">✕</button>
        </div>
        <form @submit.prevent="handleCreateApp" enctype="multipart/form-data">
          <div class="form-group">
            <label>Nome do Aplicativo</label>
            <input v-model="form.name" type="text" placeholder="Ex: Nubank, Banco Inter" required />
          </div>
          <div class="form-group">
            <label>Desenvolvedor</label>
            <input v-model="form.developer" type="text" placeholder="Ex: Nu Pagamentos S.A." required />
          </div>
          <div class="form-group">
            <label>URL / Link do APK (opcional se enviar arquivo)</label>
            <input v-model="form.apk_url" type="url" placeholder="https://..." />
          </div>
          <div class="form-group">
            <label>Arquivo APK (máx 50MB)</label>
            <input type="file" accept=".apk" @change="onApkChange" />
          </div>
          <div class="form-group">
            <label>Ícone (máx 5MB)</label>
            <input type="file" accept="image/*" @change="onIconChange" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="showModal = false">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
              {{ isSubmitting ? 'Salvando...' : 'Publicar App' }}
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

const apps = ref<any[]>([]);
const isLoading = ref(true);
const showModal = ref(false);
const isSubmitting = ref(false);

const form = ref({
  name: '',
  developer: '',
  apk_url: ''
});

const loadApps = async () => {
  isLoading.value = true;
  try {
    const res = await axios.get(`${API_URL}/play-fake-apps`);
    apps.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Erro ao carregar apps:', err);
  } finally {
    isLoading.value = false;
  }
};

const apkFile = ref<File | null>(null);
const iconFile = ref<File | null>(null);

const onApkChange = (e: Event) => {
  const input = e.target as HTMLInputElement;
  apkFile.value = input.files?.[0] || null;
};

const onIconChange = (e: Event) => {
  const input = e.target as HTMLInputElement;
  iconFile.value = input.files?.[0] || null;
};

const handleCreateApp = async () => {
  isSubmitting.value = true;
  try {
    const data = new FormData();
    data.append('name', form.value.name);
    data.append('developer', form.value.developer);
    if (form.value.apk_url) data.append('apk_url', form.value.apk_url);
    if (apkFile.value) data.append('apk_file', apkFile.value);
    if (iconFile.value) data.append('icon_file', iconFile.value);
    await axios.post(`${API_URL}/play-fake-apps`, data);
    showModal.value = false;
    form.value = { name: '', developer: '', apk_url: '' };
    apkFile.value = null;
    iconFile.value = null;
    await loadApps();
  } catch (err: any) {
    alert(err.response?.data?.message || 'Erro ao criar aplicativo.');
  } finally {
    isSubmitting.value = false;
  }
};

const formatDate = (dateStr: string) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('pt-BR');
};

onMounted(() => {
  loadApps();
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

.app-cell { display: flex; align-items: center; gap: 0.75rem; }
.app-icon-placeholder { width: 36px; height: 36px; border-radius: var(--r-sm); background: var(--accent); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem; }
.app-name { display: block; font-weight: 600; }
.app-dev { font-size: 0.75rem; color: var(--ink-faint); }

.badge { font-size: 0.7rem; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: var(--r-pill); }
.badge-success { background: var(--success-soft); color: var(--success); }

.btn { padding: 0.6rem 1.2rem; border-radius: var(--r-sm); font-weight: 600; border: none; cursor: pointer; transition: opacity 0.2s; }
.btn-primary { background: var(--accent); color: white; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.75rem; text-decoration: none; border-radius: var(--r-sm); display: inline-flex; align-items: center; }

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