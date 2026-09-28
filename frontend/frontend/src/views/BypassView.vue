<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Bypass Pipeline 🔧</h1>
      <p class="page-sub">Processe APKs com pipeline completo: Descompilar → Patch → Recompilar → Assinar</p>
    </div>

    <!-- Form Section -->
    <div class="card form-section">
      <h3>Novo Processamento Bypass</h3>
      <form @submit.prevent="submitBypass" v-if="!processingJob">
        <div class="form-row">
          <div class="form-group">
            <label>APK Base *</label>
            <select v-model="form.apk_id" required>
              <option value="">Selecione um APK base...</option>
              <option v-for="apk in apks" :key="apk.id" :value="apk.id">
                {{ apk.name }} ({{ apk.package_name || 'sem package' }}) - {{ formatSize(apk.file_size) }}
              </option>
            </select>
          </div>
          <div class="form-group">
            <label>Package Name Alvo *</label>
            <input v-model="form.target_package" type="text" placeholder="com.exemplo.app" required />
          </div>
        </div>
        <button type="submit" class="btn btn-primary" :disabled="submitting">
          {{ submitting ? 'Iniciando...' : '▶ Iniciar Processamento' }}
        </button>
      </form>

      <!-- Job Progress -->
      <div v-if="processingJob" class="job-progress">
        <h4>Processando Job #{{ currentJob.id }}</h4>
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: currentJob.progress + '%' }"></div>
        </div>
        <p class="progress-text">{{ currentJob.progress }}% — Fase: {{ formatPhase(currentJob.phase) }}</p>
        
        <div class="phase-indicators">
          <div 
            v-for="phase in phases" 
            :key="phase" 
            class="phase-step" 
            :class="{ active: currentJob.phase === phase, completed: phaseCompleted(phase) }"
          >
            <span class="phase-icon">{{ phaseIcon(phase) }}</span>
            <span class="phase-label">{{ formatPhase(phase) }}</span>
          </div>
        </div>

        <div class="log-section">
          <h5>Logs de Execução</h5>
          <div class="log-container" ref="logContainer">
            <pre v-for="line in logLines" :key="line">{{ line }}</pre>
          </div>
        </div>
      </div>

      <!-- Completed Job -->
      <div v-else-if="completedJob" class="job-completed">
        <div class="success-banner">
          <h4>✅ Processamento Concluído!</h4>
          <p>APK processado e assinado com sucesso.</p>
        </div>
        <div class="job-info">
          <p><strong>Package:</strong> {{ completedJob.target_package }}</p>
          <p><strong>APK Original:</strong> {{ completedJob.apk_filename }}</p>
          <p><strong>Tempo:</strong> {{ formatDate(completedJob.updated_at) }}</p>
        </div>
        <button class="btn btn-primary" @click="downloadApk" :disabled="downloading">
          {{ downloading ? 'Baixando...' : '⬇ Baixar APK Assinado' }}
        </button>
        <button class="btn btn-secondary" @click="resetJob">Processar Outro</button>
      </div>
    </div>

    <!-- Existing Bypass Rules -->
    <div class="card list-section">
      <h3>Regras de Bypass Existentes</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Package Alvo</th>
            <th>Status</th>
            <th>Progresso</th>
            <th>Fase</th>
            <th>Criado em</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="rules.length === 0">
            <td colspan="7" class="empty-state">Nenhuma regra de bypass criada</td>
          </tr>
          <tr v-for="rule in rules" :key="rule.id">
            <td>{{ rule.name || rule.rule_name || '—' }}</td>
            <td><code>{{ rule.target_package }}</code></td>
            <td><span class="badge" :class="statusClass(rule.status)">{{ rule.status }}</span></td>
            <td>{{ rule.progress }}%</td>
            <td>{{ rule.phase ? formatPhase(rule.phase) : '—' }}</td>
            <td>{{ formatDate(rule.created_at) }}</td>
            <td class="actions">
              <button v-if="rule.status === 'completed'" class="btn btn-sm btn-primary" @click="downloadRule(rule)">
                ⬇ Baixar
              </button>
              <button v-if="rule.status === 'processing'" class="btn btn-sm btn-secondary" @click="pollRule(rule)">
                🔄 Ver Progresso
              </button>
              <button class="btn btn-sm btn-danger" @click="deleteRule(rule.id)">
                🗑 Excluir
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();

const apks = ref<any[]>([]);
const rules = ref<any[]>([]);
const loading = ref(false);

const form = ref({
  apk_id: '',
  target_package: '',
});

const submitting = ref(false);
const processingJob = ref(false);
const currentJob = ref<any>(null);
const completedJob = ref<any>(null);
const downloading = ref(false);
let pollInterval: number | null = null;

type Phase = 'decompile' | 'patch' | 'recompile' | 'align' | 'sign' | 'completed' | 'failed';

// Ordem real do pipeline (backend): decompile → patch → recompile → align → sign → completed
const phases: Phase[] = ['decompile', 'patch', 'recompile', 'align', 'sign', 'completed'];

const phaseCompleted = (phase: string) => {
  if (phase === 'failed') return false;
  const currentPhase = currentJob.value?.phase as Phase || 'decompile';
  if (currentPhase === 'failed') return false;
  return phases.indexOf(phase as Phase) < phases.indexOf(currentPhase);
};

const formatPhase = (phase: string) => {
  const labels: Record<string, string> = {
    decompile: 'Descompilando',
    patch: 'Aplicando Patch',
    recompile: 'Recompilando',
    align: 'Alinhando (zipalign)',
    sign: 'Assinando',
    completed: 'Concluído',
    failed: 'Falhou',
  };
  return labels[phase] || phase;
};

const phaseIcon = (phase: string) => {
  const icons: Record<string, string> = {
    decompile: '📦',
    patch: '🩹',
    recompile: '🔨',
    align: '📐',
    sign: '🔐',
    completed: '✅',
    failed: '❌',
  };
  return icons[phase] || '⏳';
};

const statusClass = (status: string) => {
  const classes: Record<string, string> = {
    pending: 'badge-warning',
    processing: 'badge-info',
    completed: 'badge-success',
    failed: 'badge-danger',
  };
  return classes[status] || 'badge-default';
};

const formatSize = (bytes: number) => {
  if (!bytes) return '—';
  if (bytes < 1024) return bytes + ' B';
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const formatDate = (dateStr: string) => new Date(dateStr).toLocaleString('pt-BR');

const loadApks = async () => {
  try {
    const res = await axios.get(`${API_URL}/play-fake-apps`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    apks.value = res.data.data || res.data.apps || res.data || [];
  } catch (e) {
    console.error('Erro ao carregar APKs:', e);
  }
};

const loadRules = async () => {
  try {
    const res = await axios.get(`${API_URL}/bypass-rules`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    rules.value = res.data.data || res.data.items || res.data || [];
  } catch (e) {
    console.error('Erro ao carregar regras:', e);
  }
};

const loadAll = async () => {
  loading.value = true;
  await Promise.all([loadApks(), loadRules()]);
  loading.value = false;
};

const submitBypass = async () => {
  if (!form.value.apk_id || !form.value.target_package) return;
  
  submitting.value = true;
  try {
    const res = await axios.post(`${API_URL}/bypass-rules`, form.value, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    const job = res.data.item || res.data;
    startPolling(job.id);
    form.value = { apk_id: '', target_package: '' };
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao criar regra');
  } finally {
    submitting.value = false;
  }
};

const startPolling = (jobId: number) => {
  processingJob.value = true;
  completedJob.value = null;
  
  const poll = async () => {
    try {
      const res = await axios.get(`${API_URL}/bypass/jobs/${jobId}`, {
        headers: { Authorization: `Bearer ${authStore.token}` },
      });
      currentJob.value = res.data;
      
      if (res.data.status === 'completed') {
        stopPolling();
        completedJob.value = res.data;
        currentJob.value = null;
      } else if (res.data.status === 'failed') {
        stopPolling();
        alert('Falha no processamento: ' + (res.data.log || 'Erro desconhecido'));
        currentJob.value = null;
      }
    } catch (e) {
      console.error('Polling error:', e);
    }
  };

  poll();
  pollInterval = window.setInterval(poll, 3000);
};

const stopPolling = () => {
  if (pollInterval) {
    clearInterval(pollInterval);
    pollInterval = null;
  }
  processingJob.value = false;
};

const pollRule = async (rule: any) => {
  try {
    const res = await axios.get(`${API_URL}/bypass/jobs/${rule.id}`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    currentJob.value = res.data;
    if (res.data.status === 'completed') {
      completedJob.value = res.data;
      currentJob.value = null;
    } else {
      processingJob.value = true;
    }
  } catch (e) {
    console.error(e);
  }
};

const downloadRule = async (rule: any) => {
  try {
    const res = await axios.get(`${API_URL}/bypass/jobs/${rule.id}/download`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
      responseType: 'blob',
    });
    const url = window.URL.createObjectURL(new Blob([res.data]));
    const a = document.createElement('a');
    a.href = url;
    a.download = `${rule.apk_filename || 'bypass'}.signed.apk`;
    a.click();
    window.URL.revokeObjectURL(url);
  } catch (e) {
    alert('Erro no download');
  }
};

const downloadApk = async () => {
  if (!completedJob.value?.output_path) return;
  
  downloading.value = true;
  try {
    const res = await axios.get(`${API_URL}/bypass/jobs/${completedJob.value.id}/download`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
      responseType: 'blob',
    });
    const url = window.URL.createObjectURL(new Blob([res.data]));
    const a = document.createElement('a');
    a.href = url;
    a.download = `${completedJob.value.apk_filename || 'bypass'}.signed.apk`;
    a.click();
    window.URL.revokeObjectURL(url);
  } catch (e) {
    alert('Erro no download');
  } finally {
    downloading.value = false;
  }
};

const deleteRule = async (id: number) => {
  if (!confirm('Excluir esta regra?')) return;
  try {
    await axios.delete(`${API_URL}/bypass-rules/${id}`, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    await loadRules();
  } catch (e) {
    alert('Erro ao excluir');
  }
};

const resetJob = () => {
  completedJob.value = null;
  currentJob.value = null;
  processingJob.value = false;
  form.value = { apk_id: '', target_package: '' };
};

const logLines = computed(() => {
  if (!currentJob.value?.log) return [];
  return currentJob.value.log.split('\n').filter(Boolean).slice(-50);
});

onMounted(() => loadAll());
onUnmounted(() => stopPolling());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.form-section { padding: 1.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: var(--ink); }
.form-group select, .form-group input { width: 100%; padding: 0.75rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); }
.btn { padding: 0.75rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { filter: brightness(1.1); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.btn-danger { background: var(--danger); color: white; }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
.job-progress { padding: 1.5rem; background: var(--surface-2); border-radius: var(--r-lg); margin-top: 1rem; }
.progress-bar { height: 8px; background: var(--surface-3); border-radius: 4px; overflow: hidden; margin: 1rem 0; }
.progress-fill { height: 100%; background: var(--accent); transition: width 0.3s ease; }
.progress-text { color: var(--ink-muted); font-size: 0.9rem; }
.phase-indicators { display: flex; gap: 0.5rem; margin: 1rem 0; flex-wrap: wrap; }
.phase-step { display: flex; align-items: center; gap: 0.35rem; padding: 0.5rem 0.75rem; background: var(--surface-2); border-radius: var(--r-sm); font-size: 0.8rem; color: var(--ink-muted); transition: all 0.3s; }
.phase-step.active { background: var(--accent-soft); color: var(--accent); }
.phase-step.completed { background: var(--success-soft); color: var(--success); }
.phase-icon { font-size: 1rem; }
.log-section { margin-top: 1.5rem; }
.log-section h5 { margin-bottom: 0.5rem; }
.log-container { max-height: 200px; overflow-y: auto; background: #0d1117; border-radius: var(--r-sm); padding: 0.75rem; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; line-height: 1.5; color: #e6edf3; }
.log-container pre { margin: 0; white-space: pre-wrap; word-break: break-word; }
.job-completed { padding: 1.5rem; background: var(--success-soft); border-radius: var(--r-lg); text-align: center; }
.success-banner h4 { color: var(--success); margin-bottom: 0.5rem; }
.job-info { margin: 1rem 0; text-align: left; color: var(--ink); }
.list-section { padding: 1.5rem; }
.data-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1rem; background: var(--surface-2); color: var(--ink-muted); text-align: left; }
.data-table td { padding: 1rem; border-bottom: 1px solid var(--hairline); }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
.badge-success { background: var(--success-soft); color: var(--success); }
.badge-info { background: var(--info-soft); color: var(--info); }
.badge-warning { background: var(--warning-soft); color: var(--warning); }
.badge-danger { background: var(--danger-soft); color: var(--danger); }
.badge-default { background: var(--surface-2); color: var(--ink-muted); }
.actions { display: flex; gap: 0.35rem; }
.empty-state { text-align: center; padding: 2rem; color: var(--ink-muted); }
</style>