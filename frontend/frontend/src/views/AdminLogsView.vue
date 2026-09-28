<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Logs do Sistema 📋</h1>
      <p class="page-sub">Visualize todas as ações registradas no sistema</p>
    </div>

    <!-- 2FA Challenge -->
    <div v-if="needsTotp" class="card totp-challenge">
      <h3>🔐 Autenticação de Dois Fatores Necessária</h3>
      <p>Esta área requer autenticação de dois fatores (2FA).</p>
      <div class="totp-form">
        <input
          v-model="totpCode"
          type="text"
          maxlength="6"
          placeholder="Digite o código de 6 dígitos"
          @keyup.enter="verifyTotp"
          autocomplete="one-time-code"
        />
        <button class="btn btn-primary" @click="verifyTotp" :disabled="verifying">
          {{ verifying ? 'Verificando...' : 'Verificar' }}
        </button>
        <p v-if="totpError" class="error">{{ totpError }}</p>
      </div>
    </div>

    <!-- Logs Table -->
    <div v-else class="card table-card">
      <div class="table-header">
        <h3>Logs do Sistema</h3>
        <div class="filters">
          <select v-model="filterLevel" @change="loadLogs" class="filter-select">
            <option value="">Todos os níveis</option>
            <option value="critical">🔴 Crítico</option>
            <option value="warning">🟡 Aviso</option>
            <option value="info">🔵 Info</option>
          </select>
          <button class="btn btn-secondary" @click="loadLogs">🔄 Atualizar</button>
        </div>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Usuário</th>
            <th>Tipo</th>
            <th>Categoria</th>
            <th>Descrição</th>
            <th>IP</th>
            <th>Data/Hora</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="logs.length === 0">
            <td colspan="7" class="empty-state">Nenhum log encontrado</td>
          </tr>
          <tr v-for="log in logs" :key="log.id">
            <td><code>#{{ log.id }}</code></td>
            <td>
              <div class="user-info">
                <span class="user-name">{{ log.user?.name || '—' }}</span>
                <span class="user-email">{{ log.ator || log.user?.email || '—' }}</span>
              </div>
            </td>
            <td><span class="badge badge-type">{{ log.tipo }}</span></td>
            <td><span class="badge badge-category">{{ log.categoria }}</span></td>
            <td class="description">{{ log.descricao }}</td>
            <td><code>{{ log.ip }}</code></td>
            <td>{{ formatDate(log.created_at) }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="pagination">
        <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1">« Anterior</button>
        <span>Página {{ pagination.current_page }} de {{ pagination.last_page }}</span>
        <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page">Próxima »</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';

const authStore = useAuthStore();
const needsTotp = ref(false);
const totpCode = ref('');
const verifying = ref(false);
const totpError = ref('');
const logs = ref<any[]>([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const filterLevel = ref('');
const loading = ref(false);

const loadLogs = async () => {
  loading.value = true;
  try {
    const headers: Record<string, string> = {
      Authorization: `Bearer ${authStore.token}`,
    };
    if (authStore.user?.totp_enabled && authStore.totpVerified) {
      headers['X-TOTP-Code'] = authStore.totpCode || '';
    }

    const params = new URLSearchParams({
      page: pagination.value.current_page.toString(),
      ...(filterLevel.value && { level: filterLevel.value }),
    });

    const res = await axios.get(`${API_URL}/admin/logs?${params}`, { headers });
    logs.value = res.data.data;
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total,
    };
    needsTotp.value = false;
  } catch (error: any) {
    if (error.response?.status === 401 && error.response.data?.action === 'verify_totp') {
      needsTotp.value = true;
    } else {
      console.error('Erro ao carregar logs:', error);
    }
  } finally {
    loading.value = false;
  }
};

const verifyTotp = async () => {
  if (!totpCode.value || totpCode.value.length !== 6) {
    totpError.value = 'Código deve ter 6 dígitos';
    return;
  }
  verifying.value = true;
  try {
    await axios.post(`${API_URL}/auth/verify-totp`, { code: totpCode.value }, {
      headers: { Authorization: `Bearer ${authStore.token}` },
    });
    authStore.totpVerified = true;
    authStore.totpCode = totpCode.value;
    needsTotp.value = false;
    totpCode.value = '';
    await loadLogs();
  } catch (error: any) {
    totpError.value = error.response?.data?.message || 'Código inválido';
  } finally {
    verifying.value = false;
  }
};

const changePage = (page: number) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page;
    loadLogs();
  }
};

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleString('pt-BR');
};

onMounted(() => loadLogs());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.table-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); }
.filters { display: flex; gap: 0.75rem; align-items: center; }
.filter-select { padding: 0.5rem; border: 1px solid var(--hairline); border-radius: var(--r-sm); background: var(--surface-2); color: var(--ink); }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); text-align: left; }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.data-table tr:hover td { background: var(--surface-2); }
.user-info { display: flex; flex-direction: column; gap: 0.125rem; }
.user-name { font-weight: 600; color: var(--ink); }
.user-email { font-size: 0.75rem; color: var(--ink-muted); }
.description { max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.empty-state { text-align: center; padding: 2rem; color: var(--ink-muted); }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
.badge-type { background: var(--accent-soft); color: var(--accent); }
.badge-category { background: var(--info-soft); color: var(--info); }
.totp-challenge { padding: 2rem; text-align: center; }
.totp-form { margin-top: 1rem; display: flex; flex-direction: column; gap: 0.75rem; max-width: 300px; margin-left: auto; margin-right: auto; }
.totp-form input { padding: 0.75rem; font-size: 1.25rem; letter-spacing: 0.5em; text-align: center; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); }
.error { color: var(--danger); font-size: 0.85rem; }
.pagination { display: flex; justify-content: center; gap: 0.75rem; padding: 1rem; border-top: 1px solid var(--hairline); }
.pagination button { padding: 0.5rem 1rem; border: 1px solid var(--hairline); background: var(--surface-2); color: var(--ink); border-radius: var(--r-sm); cursor: pointer; }
.pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
.pagination span { display: flex; align-items: center; color: var(--ink-muted); }
</style>