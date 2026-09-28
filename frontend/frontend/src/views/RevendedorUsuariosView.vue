<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Meus Usuários 👥</h1>
      <p class="page-sub">Gerencie os usuários atribuídos a este revendedor</p>
    </div>

    <div class="card">
      <div class="table-header">
        <h3>Usuários do Revendedor</h3>
        <button class="btn btn-secondary" @click="loadUsers">🔄 Atualizar</button>
      </div>

      <div v-if="loading" class="loading-state">Carregando usuários...</div>

      <div v-if="error" class="error-state">
        <p>{{ error }}</p>
        <button class="btn btn-secondary" @click="loadUsers">Tentar novamente</button>
      </div>

      <table class="data-table" v-else-if="users.length > 0">
        <thead>
          <tr>
            <th>NOME</th>
            <th>EMAIL</th>
            <th>ROLE</th>
            <th>STATUS</th>
            <th>REVENDEDOR</th>
            <th>AÇÕES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td><strong>{{ user.name }}</strong></td>
            <td>{{ user.email }}</td>
            <td><span class="role-badge" :class="roleClass(user.role)">{{ user.role }}</span></td>
            <td><span class="badge" :class="statusClass(user.status)">{{ user.status }}</span></td>
            <td>{{ user.reseller?.name || user.reseller_name || '—' }}</td>
            <td class="actions">
              <button class="btn btn-sm btn-secondary" @click="viewUser(user.id)">👁</button>
              <button class="btn btn-sm btn-danger" @click="toggleStatus(user.id)">⏸</button>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="empty-state">
        <h4>Nenhum usuário encontrado</h4>
        <p>Não há usuários atribuídos a este revendedor.</p>
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

const users = ref<any[]>([]);
const loading = ref(false);
const error = ref('');

const loadUsers = async () => {
  loading.value = true;
  error.value = '';
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const params = new URLSearchParams();
    if (authStore.user?.id) params.set('reseller_id', authStore.user.id);
    if (authStore.user?.reseller_id) params.set('reseller_id', String(authStore.user.reseller_id));
    const res = await axios.get(`${API_URL}/admin/users-list`, { headers, params });
    users.value = res.data.data || res.data || [];
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao carregar usuários';
    console.error('Erro ao carregar usuários:', e);
  } finally {
    loading.value = false;
  }
};

const roleClass = (role: string) => {
  const classes: Record<string, string> = {
    admin: 'role-admin',
    revendedor: 'role-rev',
    user: 'role-user',
  };
  return classes[role] || 'role-user';
};

const statusClass = (status: string) => {
  const classes: Record<string, string> = {
    active: 'badge-success',
    inactive: 'badge-default',
    pending: 'badge-warning',
    suspended: 'badge-danger',
  };
  return classes[status] || 'badge-default';
};

const viewUser = (id: number) => {
  window.open(`/admin/users/${id}`, '_blank');
};

const toggleStatus = async (id: number) => {
  if (!confirm('Deseja alterar o status deste usuário?')) return;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
     await axios.put(`${API_URL}/admin/users-list/${id}`, { role: 'user' }, { headers });
    await loadUsers();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao alterar status');
  }
};

onMounted(() => loadUsers());
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
.role-badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
.role-admin { background: var(--role-admin) / 14%; color: var(--role-admin); }
.role-rev { background: var(--role-rev) / 14%; color: var(--role-rev); }
.role-user { background: var(--role-user) / 14%; color: var(--role-user); }
.badge { padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.7rem; text-transform: uppercase; }
.badge-success { background: var(--success-soft); color: var(--success); }
.badge-warning { background: var(--warning-soft); color: var(--warning); }
.badge-danger { background: var(--danger-soft); color: var(--danger); }
.badge-default { background: var(--surface-2); color: var(--ink-muted); }
.btn { padding: 0.75rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; }
.btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.btn-danger { background: var(--danger); color: white; }
.actions { display: flex; gap: 0.35rem; }
.loading-state { padding: 3rem; text-align: center; color: var(--ink-muted); }
.error-state { padding: 2rem; text-align: center; color: var(--danger); }
.error-state p { margin-bottom: 1rem; }
.empty-state { text-align: center; padding: 3rem; color: var(--ink-muted); }
.empty-state h4 { margin-bottom: 0.5rem; color: var(--ink); }
.empty-state p { color: var(--ink-muted); }
</style>
