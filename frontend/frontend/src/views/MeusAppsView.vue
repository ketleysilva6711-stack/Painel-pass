<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Meus Apps 📱</h1>
      <p class="page-sub">Gerencie seus APKs publicados e acompanhe seus downloads</p>
    </div>

    <!-- Upload Section -->
    <div class="card upload-section">
      <h3>📤 Publicar Novo APK</h3>
      <form @submit.prevent="uploadApk" class="upload-form">
        <div class="form-row">
          <div class="form-group">
            <label>Nome do App *</label>
            <input v-model="form.name" type="text" placeholder="Ex: Meu App Incrível" required />
          </div>
          <div class="form-group">
            <label>Desenvolvedor</label>
            <input v-model="form.developer" type="text" placeholder="Ex: Minha Empresa" />
          </div>
        </div>
        
        <div class="form-group">
          <label>Package Name (opcional)</label>
          <input v-model="form.package_name" type="text" placeholder="com.exemplo.app" />
        </div>

        <div class="form-group">
          <label>Arquivo APK *</label>
          <div class="file-upload" @click="triggerFile" :class="{ 'has-file': form.apk_file }" @dragover.prevent @drop.prevent="handleDrop">
            <input type="file" ref="fileInput" accept=".apk" @change="handleFile" style="display: none" />
            <div v-if="!form.apk_file" class="upload-placeholder">
              <svg class="upload-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/>
              </svg>
              <p>Arraste seu APK aqui ou clique para selecionar</p>
              <span class="upload-hint">Máx. 100MB • .apk apenas</span>
            </div>
            <div v-else class="file-selected">
              <div class="file-info">
                <svg class="file-icon" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <path d="M14 2v6h6"/>
                </svg>
                <div class="file-details">
                  <span class="file-name">{{ form.apk_file?.name }}</span>
                  <span class="file-size">{{ formatSize(form.apk_file?.size) }}</span>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-secondary" @click="removeFile">Remover</button>
            </div>
          </div>
          <div v-if="uploadProgress > 0 && uploadProgress < 100" class="upload-progress">
            <div class="progress-bar">
              <div class="progress-fill" :style="{ width: uploadProgress + '%' }"></div>
            </div>
            <span class="progress-text">{{ uploadProgress }}% - {{ uploadStatus }}</span>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Ícone (opcional)</label>
            <input type="file" accept="image/*" @change="handleIcon" style="display: none" ref="iconInput" />
            <button type="button" class="btn btn-secondary" @click="triggerIconInput">Selecionar Ícone</button>
          </div>
          <div class="form-group">
            <label>URL do APK Externo (opcional)</label>
            <input v-model="form.apk_url" type="url" placeholder="https://exemplo.com/app.apk" />
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg" :disabled="submitting || !form.apk_file">
          {{ submitting ? 'Publicando...' : '🚀 Publicar APK' }}
        </button>
      </form>
    </div>

    <!-- Apps List -->
    <div class="card list-section">
      <div class="section-header">
        <h3>APKs Publicados</h3>
        <span class="badge">{{ apps.length }} apps</span>
      </div>
      
      <table class="data-table" v-if="apps.length > 0">
        <thead>
          <tr>
            <th>Ícone</th>
            <th>App</th>
            <th>Package</th>
            <th>Tamanho</th>
            <th>Downloads</th>
            <th>Status</th>
            <th>Criado</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="app in apps" :key="app.id">
            <td>
              <img v-if="app.icon_path" :src="getIconUrl(app.icon_path)" alt="" class="app-icon" />
              <span v-else class="no-icon">📱</span>
            </td>
            <td>
              <strong>{{ app.name }}</strong>
              <br><small>{{ app.developer || '—' }}</small>
            </td>
            <td><code>{{ app.package_name || '—' }}</code></td>
            <td>{{ app.file_size ? formatSize(app.file_size) : (app.apk_url ? 'URL' : '—') }}</td>
            <td>{{ app.downloads || 0 }}</td>
            <td><span class="badge" :class="statusClass(app.status)">{{ app.status }}</span></td>
            <td>{{ formatDate(app.created_at) }}</td>
            <td class="actions">
              <button class="btn btn-sm btn-secondary" @click="viewPublic(app.slug)">
                🌐 Ver Página
              </button>
              <button class="btn btn-sm btn-primary" @click="downloadApk(app)">
                ⬇ Baixar
              </button>
              <button class="btn btn-sm btn-danger" @click="deleteApp(app.id)">
                🗑
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="empty-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="empty-icon">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
          <polyline points="14 2 14 8 20 8"/>
        </svg>
        <h4>Nenhum APK publicado</h4>
        <p>Publique seu primeiro APK usando o formulário acima</p>
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

const apps = ref<any[]>([]);

const form = ref({
  name: '',
  developer: '',
  package_name: '',
  apk_url: '',
  apk_file: null as File | null,
  icon: null as File | null,
});

const iconInput = ref<HTMLInputElement | null>(null);

const submitting = ref(false);
const uploadProgress = ref(0);
const uploadStatus = ref('');
const loadingApps = ref(false);

const triggerIconInput = () => {
  iconInput.value?.click();
};

const statusClass = (status: string) => {
  const classes: Record<string, string> = {
    active: 'badge-success',
    inactive: 'badge-warning',
    pending: 'badge-info',
    processing: 'badge-info',
  };
  return classes[status] || 'badge-default';
};

const formatDate = (dateStr: string) => new Date(dateStr).toLocaleString('pt-BR');
const formatSize = (bytes: number) => {
  if (!bytes) return '—';
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const loadApps = async () => {
  loadingApps.value = true;
  try {
    const res = await axios.get(`${API_URL}/tools/play-fake`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    apps.value = res.data.data || res.data.apps || res.data || [];
  } catch (e) {
    console.error('Erro ao carregar apps:', e);
  } finally {
    loadingApps.value = false;
  }
};

const triggerFile = () => {
  const input = document.querySelector('input[type="file"]') as HTMLInputElement;
  input?.click();
};

const handleFile = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    const file = target.files[0];
    if (!file.name.endsWith('.apk')) {
      alert('Apenas arquivos .apk são permitidos');
      return;
    }
    if (file.size > 100 * 1024 * 1024) {
      alert('Arquivo muito grande. Máximo 100MB');
      return;
    }
    form.value.apk_file = file;
  }
};

const handleDrop = (e: DragEvent) => {
  e.preventDefault();
  if (e.dataTransfer?.files && e.dataTransfer.files[0]) {
    const file = e.dataTransfer.files[0];
    if (!file.name.endsWith('.apk')) {
      alert('Apenas arquivos .apk são permitidos');
      return;
    }
    if (file.size > 100 * 1024 * 1024) {
      alert('Arquivo muito grande. Máximo 100MB');
      return;
    }
    form.value.apk_file = file;
  }
};

const removeFile = () => {
  form.value.apk_file = null;
};

const handleIcon = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) {
    form.value.icon = target.files[0];
  }
};

const uploadApk = async () => {
  if (!form.value.apk_file || !form.value.name) {
    alert('Nome e arquivo APK são obrigatórios');
    return;
  }

  submitting.value = true;
  uploadProgress.value = 0;
  uploadStatus.value = 'Enviando...';

  try {
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('developer', form.value.developer || '');
    formData.append('package_name', form.value.package_name || '');
    formData.append('apk_url', form.value.apk_url || '');
    if (form.value.apk_file) formData.append('apk_file', form.value.apk_file);
    if (form.value.icon) formData.append('icon', form.value.icon);

    await axios.post(`${API_URL}/tools/play-fake`, formData, {
      headers: { 
        Authorization: `Bearer ${authStore.token}`,
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
          uploadStatus.value = uploadProgress.value < 100 ? 'Enviando...' : 'Processando...';
        }
      },
    });

    uploadProgress.value = 100;
    uploadStatus.value = 'Concluído!';
    form.value = { name: '', developer: '', package_name: '', apk_url: '', apk_file: null, icon: null };
    await loadApps();
    alert('APK publicado com sucesso!');
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao publicar APK');
  } finally {
    submitting.value = false;
    uploadProgress.value = 0;
    uploadStatus.value = '';
  }
};

const viewPublic = (slug: string) => {
  window.open(`${window.location.origin}/c/${slug}`, '_blank');
};

const downloadApk = async (app: any) => {
  try {
    const url = app.file_path 
      ? `${API_URL.replace('/api', '')}/storage/${app.file_path}`
      : app.apk_url;
    
    if (!url) {
      alert('Link de download não disponível');
      return;
    }
    
    window.open(url, '_blank');
  } catch (e) {
    alert('Erro ao baixar');
  }
};

const deleteApp = async (id: number) => {
  if (!confirm('Tem certeza que deseja excluir este APK?')) return;
  
  try {
    await axios.delete(`${API_URL}/play-fake-apps/${id}`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    await loadApps();
  } catch (e) {
    alert('Erro ao excluir');
  }
};

const getIconUrl = (path: string) => {
  return `${API_URL.replace('/api', '')}/storage/${path}`;
};

onMounted(() => loadApps());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.section-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.5rem; border-bottom: 1px solid var(--hairline); }
.upload-section { padding: 1.5rem; }
.upload-form { display: flex; flex-direction: column; gap: 1.25rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--ink); }
.form-group input { width: 100%; padding: 0.75rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); }
.file-upload { position: relative; border: 2px dashed var(--hairline); border-radius: var(--r-lg); padding: 2rem; text-align: center; transition: all 0.2s; cursor: pointer; }
.file-upload:hover { border-color: var(--accent); background: var(--accent-soft); }
.file-upload.has-file { border-color: var(--accent); background: var(--accent-soft); }
.upload-placeholder { display: flex; flex-direction: column; align-items: center; gap: 0.75rem; color: var(--ink-muted); }
.upload-icon { width: 48px; height: 48px; color: var(--accent); }
.upload-hint { font-size: 0.8rem; color: var(--ink-faint); }
.file-selected { display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: var(--surface-2); border-radius: var(--r-sm); }
.file-info { display: flex; align-items: center; gap: 0.75rem; }
.file-icon { width: 32px; height: 32px; color: var(--accent); }
.file-details { display: flex; flex-direction: column; }
.file-name { font-weight: 500; color: var(--ink); }
.file-size { font-size: 0.8rem; color: var(--ink-muted); }
.upload-progress { margin-top: 0.5rem; }
.progress-bar { height: 6px; background: var(--surface-3); border-radius: 3px; overflow: hidden; }
.progress-fill { height: 100%; background: var(--accent); transition: width 0.3s ease; }
.progress-text { font-size: 0.8rem; color: var(--ink-muted); }
.btn { padding: 0.75rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
.btn-lg { padding: 1rem 2rem; font-size: 1rem; width: 100%; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { filter: brightness(1.1); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.btn-danger { background: var(--danger); color: white; }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
.badge-success { background: var(--success-soft); color: var(--success); }
.badge-warning { background: var(--warning-soft); color: var(--warning); }
.badge-info { background: var(--info-soft); color: var(--info); }
.badge-danger { background: var(--danger-soft); color: var(--danger); }
.badge-default { background: var(--surface-2); color: var(--ink-muted); }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1rem; background: var(--surface-2); color: var(--ink-muted); text-align: left; }
.data-table td { padding: 1rem; border-bottom: 1px solid var(--hairline); }
.app-icon { width: 36px; height: 36px; border-radius: 8px; object-fit: contain; }
.no-icon { font-size: 1.5rem; }
.actions { display: flex; gap: 0.35rem; }
.empty-state { text-align: center; padding: 3rem; color: var(--ink-muted); }
.empty-icon { width: 64px; height: 64px; color: var(--ink-faint); margin-bottom: 1rem; }
.empty-state h4 { margin-bottom: 0.5rem; color: var(--ink); }
.empty-state p { color: var(--ink-muted); }
</style>
