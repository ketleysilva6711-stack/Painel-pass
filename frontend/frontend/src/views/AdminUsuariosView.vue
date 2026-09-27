<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Gestão de Usuários 👑</h1>
      <p class="page-sub">Gerencie acessos, prazos de expiração e funções dos usuários</p>
    </div>

    <div class="card table-card">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Expiração</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id">
            <td><strong>{{ u.name }}</strong></td>
            <td>{{ u.email }}</td>
            <td><span class="role-badge">{{ u.role }}</span></td>
            <td><span class="badge badge-success">{{ u.conta_status }}</span></td>
            <td>{{ u.conta_expira_em ? new Date(u.conta_expira_em).toLocaleDateString('pt-BR') : 'Vitalício' }}</td>
            <td>
              <button class="btn-sm btn-secondary" @click="renovarDias(u.id, 30)">+30 Dias ⚡</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const users = ref<any[]>([]);

const loadUsers = async () => {
  const res = await axios.get(`${API_URL}/admin/users-list`);
  users.value = res.data;
};

const renovarDias = async (id: number, dias: number) => {
  await axios.put(`${API_URL}/admin/users-list/${id}`, { dias_expiracao: dias });
  alert('Acesso renovado com sucesso!');
  await loadUsers();
};

onMounted(() => loadUsers());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.role-badge { background: var(--accent-soft); color: var(--accent); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.75rem; }
.btn-sm { padding: 0.35rem 0.75rem; border-radius: var(--r-sm); border: 1px solid var(--hairline); background: var(--surface-2); color: var(--ink); cursor: pointer; }
</style>