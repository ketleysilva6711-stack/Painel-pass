<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Tela de Encerramento 🔒</h1>
      <p class="page-sub">Configure e gerencie o encerramento agendado da sessão</p>
    </div>

    <div class="config-grid">
      <!-- Status Card -->
      <div class="card status-card">
        <h3>📊 Status Atual</h3>
        <div class="status-row">
          <span class="status-label">Estado</span>
          <span class="badge" :class="statusBadgeClass">{{ statusLabel }}</span>
        </div>
        <div class="status-row">
          <span class="status-label">Próximo Encerramento</span>
          <span class="status-value">{{ scheduledTime ? new Date(scheduledTime).toLocaleString('pt-BR') : 'Não definido' }}</span>
        </div>
        <div class="status-row">
          <span class="status-label">Aviso Prévio</span>
          <span class="status-value">{{ warningMinutes }} min</span>
        </div>
      </div>

      <!-- Configuration Form -->
      <div class="card">
        <h3>⚙️ Configurar Encerramento</h3>
        <form @submit.prevent="saveConfig" class="config-form">
          <div class="form-group">
            <label>Data/Hora do Encerramento</label>
            <input v-model="form.scheduled_at" type="datetime-local" />
          </div>
          <div class="form-group">
            <label>Motivo</label>
            <textarea v-model="form.reason" rows="3" placeholder="Motivo do encerramento..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary" :disabled="saving">
            {{ saving ? 'Salvando...' : '💾 Salvar Configuração' }}
          </button>
        </form>
      </div>

      <!-- Quick Actions -->
      <div class="card actions-card">
        <h3>🚀 Ações Rápidas</h3>
        <div class="quick-actions">
          <button class="btn btn-primary" @click="scheduleNow">⏰ Agendar</button>
          <button class="btn btn-warning" @click="triggerWarning">🔔 Emitir Aviso</button>
          <button class="btn btn-danger" @click="triggerShutdown">⛔ Encerrar Agora</button>
        </div>
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

const status = ref('idle');
const scheduledTime = ref<string | null>(null);
const warningMinutes = ref(10);
const saving = ref(false);

const statusLabel = ref('Idle');
const statusBadgeClass = ref('badge-default');

const form = ref({
  scheduled_at: '',
  reason: '',
});

const loadConfig = async () => {
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const res = await axios.get(`${API_URL}/encerramento`, { headers });
    const data = res.data;
    status.value = data.status || 'idle';
    scheduledTime.value = data.scheduled_at || null;
    warningMinutes.value = data.warning_minutes || 10;
    form.value = { scheduled_at: data.scheduled_at ? data.scheduled_at.slice(0, 16) : '', reason: data.reason || '' };
    statusLabel.value = data.status === 'active' ? 'Agendado' : 'Idle';
    statusBadgeClass.value = data.status === 'active' ? 'badge-warning' : 'badge-default';
  } catch (e) {
    console.error('Erro ao carregar configuração:', e);
  }
};

const saveConfig = async () => {
  saving.value = true;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    await axios.post(`${API_URL}/encerramento`, form.value, { headers });
    status.value = 'scheduled';
    statusLabel.value = 'Agendado';
    statusBadgeClass.value = 'badge-warning';
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao salvar configuração');
  } finally {
    saving.value = false;
  }
};

const scheduleNow = async () => {
  const now = new Date();
  const scheduled = new Date(now.getTime() + 60 * 60000);
  form.value.scheduled_at = scheduled.toISOString().slice(0, 16);
  form.value.reason = 'Encerramento agendado automaticamente';
  await saveConfig();
};

const triggerWarning = async () => {
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    await axios.post(`${API_URL}/encerramento/warning`, {}, { headers });
    status.value = 'warning';
    statusLabel.value = 'Aviso';
    statusBadgeClass.value = 'badge-info';
  } catch (e) {}
};

const triggerShutdown = async () => {
  if (!confirm('Tem certeza que deseja encerrar a sessão agora?')) return;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    await axios.post(`${API_URL}/encerramento/shutdown`, {}, { headers });
    status.value = 'closed';
    statusLabel.value = 'Encerrado';
    statusBadgeClass.value = 'badge-danger';
  } catch (e) {}
};

onMounted(() => loadConfig());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.config-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.25rem; }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; }
.status-row { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--hairline); }
.status-row:last-child { border-bottom: none; }
.status-label { color: var(--ink-muted); font-size: 0.85rem; }
.status-value { color: var(--ink); font-weight: 600; }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
.badge-default { background: var(--surface-2); color: var(--ink-muted); }
.badge-warning { background: var(--warning-soft); color: var(--warning); }
.badge-info { background: var(--accent-soft); color: var(--accent); }
.badge-danger { background: var(--danger-soft); color: var(--danger); }
.config-form { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-group label { font-size: 0.8rem; font-weight: 600; color: var(--ink-muted); text-transform: uppercase; }
.form-group input, .form-group textarea { padding: 0.6rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); }
.form-group textarea { resize: vertical; }
.btn { padding: 0.75rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; }
.btn-primary { background: var(--accent); color: white; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.btn-warning { background: var(--warning); color: white; }
.btn-danger { background: var(--danger); color: white; }
.btn:disabled { opacity: 0.6; cursor: not-allowed; }
.quick-actions { display: flex; flex-direction: column; gap: 0.75rem; }
.quick-actions .btn { width: 100%; }
</style>