<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">{{ isEditMode ? 'Editar Usuário' : 'Novo Usuário' }} 👤</h1>
      <p class="page-sub">{{ isEditMode ? 'Altere os dados deste usuário' : 'Crie um novo usuário com suas credenciais' }}</p>
    </div>

    <RouterLink to="/admin/usuarios" class="back-link">← Voltar à lista de usuários</RouterLink>

    <div class="card form-card">
      <form @submit.prevent="saveUser" class="user-form">
        <div class="form-group">
          <label for="name">Nome completo</label>
          <input id="name" v-model="form.name" type="text" placeholder="João Silva" required />
        </div>

        <div class="form-group">
          <label for="email">E-mail</label>
          <input id="email" v-model="form.email" type="email" placeholder="joao@exemplo.com" required />
        </div>

        <div class="form-group">
          <label for="role">Role</label>
          <select id="role" v-model="form.role" required>
            <option value="user">Usuário</option>
            <option value="revendedor">Revendedor</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="form-group">
          <label for="password">{{ isEditMode ? 'Nova senha (deixe em branco para manter)' : 'Senha' }}</label>
          <input id="password" v-model="form.password" type="password" :placeholder="isEditMode ? 'Mantenha em branco para não alterar' : 'Digite a senha'" />
        </div>

        <div class="form-group">
          <label for="password_confirm">Confirmar senha</label>
          <input id="password_confirm" v-model="form.password_confirm" type="password" placeholder="Confirme a senha" />
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary" :disabled="loading">{{ loading ? 'Salvando...' : (isEditMode ? 'Atualizar' : 'Criar') }}</button>
        </div>

        <div v-if="error" class="error-msg">{{ error }}</div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();
const route = useRoute();

const loading = ref(false);
const error = ref('');
const userId = ref<string | null>(route.params.id as string || null);
const isEditMode = userId.value !== null;

const form = reactive({
  name: '',
  email: '',
  role: 'user' as string,
  password: '',
  password_confirm: '',
});

const loadUser = async () => {
  if (!userId.value) return;
  loading.value = true;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const res = await axios.get(`${API_URL}/admin/users-list/${userId.value}`, { headers });
    const data = res.data;
    form.name = data.name || '';
    form.email = data.email || '';
    form.role = data.role || 'user';
    form.password = '';
    form.password_confirm = '';
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao carregar dados do usuário');
  } finally {
    loading.value = false;
  }
};

const saveUser = async () => {
  error.value = '';
  if (form.password && form.password !== form.password_confirm) {
    error.value = 'As senhas não coincidem';
    return;
  }
  loading.value = true;
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const payload: Record<string, any> = {
      name: form.name,
      email: form.email,
      role: form.role,
    };
    if (form.password) (payload as Record<string, any>).password = form.password;

    if (isEditMode && userId.value) {
      await axios.put(`${API_URL}/admin/users-list/${userId.value}`, payload, { headers });
      alert('Usuário atualizado com sucesso!');
    } else {
      await axios.post(`${API_URL}/admin/users-list`, payload, { headers });
      alert('Usuário criado com sucesso!');
    }
    window.location.href = '/admin/usuarios';
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao salvar usuário');
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  if (isEditMode) loadUser();
});
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.back-link { color: var(--accent); text-decoration: none; font-weight: 600; font-size: 0.9rem; }
.back-link:hover { text-decoration: underline; }
.form-card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; }
.user-form { display: flex; flex-direction: column; gap: 1.25rem; max-width: 480px; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-group label { font-weight: 600; font-size: 0.85rem; color: var(--ink); }
.form-group input, .form-group select {
  padding: 0.65rem 0.75rem; border: 1px solid var(--hairline); border-radius: var(--r-sm);
  background: var(--surface-2); color: var(--ink); font-size: 0.9rem; outline: none; transition: border-color 0.2s;
}
.form-group input:focus, .form-group select:focus { border-color: var(--accent); }
.btn { padding: 0.75rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover { opacity: 0.85; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.error-msg { color: #e53e3e; font-size: 0.85rem; margin-top: 0.5rem; }
</style>
