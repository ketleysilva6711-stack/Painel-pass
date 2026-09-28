<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const authStore = useAuthStore();
const route = useRoute();
const router = useRouter();

const id = route.params.id as string | undefined;
const isEdit = id && id !== 'novo';

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'user' as string,
  expiration_days: 30,
});

const loading = ref(false);
const saving = ref(false);
const error = ref('');
const success = ref('');

const loadUser = async () => {
  if (!id || id === 'novo') return;
  loading.value = true;
  error.value = '';
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    const res = await axios.get(`${API_URL}/revendedor/usuarios/${id}`, { headers });
    const data = res.data.data || res.data;
    form.value.name = data.name || '';
    form.value.email = data.email || '';
    form.value.expiration_days = data.expiration_days || 30;
    form.value.role = 'user';
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao carregar usuário';
  } finally {
    loading.value = false;
  }
};

const handleSubmit = async () => {
  saving.value = true;
  error.value = '';
  success.value = '';
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` };
    if (isEdit && id) {
      await axios.put(`${API_URL}/revendedor/usuarios/${id}`, form.value, { headers });
      success.value = 'Usuário atualizado com sucesso!';
    } else {
      await axios.post(`${API_URL}/revendedor/usuarios`, form.value, { headers });
      success.value = 'Usuário criado com sucesso!';
    }
    setTimeout(() => router.push('/revendedor/usuarios'), 1000);
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao salvar usuário';
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  if (isEdit) loadUser();
});
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">{{ isEdit ? 'Editar Usuário' : 'Novo Usuário' }}</h1>
      <p class="page-sub">{{ isEdit ? 'Altere os dados deste usuário' : 'Crie um novo usuário sob este revendedor' }}</p>
    </div>

    <div class="card">
      <div v-if="loading" class="loading-state">Carregando...</div>

      <div v-if="error" class="error-state">
        <p>{{ error }}</p>
        <button class="btn btn-secondary" @click="isEdit ? loadUser() : (error = '')">Tentar novamente</button>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="form">
        <div class="form-group">
          <label class="form-label" for="name">Nome</label>
          <input id="name" v-model="form.name" class="form-input" type="text" required placeholder="Nome completo" />
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input id="email" v-model="form.email" class="form-input" type="email" required placeholder="email@exemplo.com" />
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Senha</label>
          <input id="password" v-model="form.password" class="form-input" type="password" :required="!isEdit" :placeholder="isEdit ? 'Deixe em branco para manter a atual' : 'Defina uma senha'" />
        </div>

        <div class="form-group">
          <label class="form-label" for="role">Role</label>
          <select id="role" v-model="form.role" class="form-input" disabled>
            <option value="user">user</option>
          </select>
          <p class="form-hint">O role é fixo como "user" para usuários vinculados a revendedores.</p>
        </div>

        <div class="form-group">
          <label class="form-label" for="expiration_days">Dias de Expiração</label>
          <input id="expiration_days" v-model.number="form.expiration_days" class="form-input" type="number" min="1" required />
        </div>

        <div v-if="success" class="success-state">
          <p>{{ success }}</p>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary" :disabled="saving">
            {{ saving ? 'Salvando...' : (isEdit ? 'Atualizar' : 'Criar') }}
          </button>
          <button type="button" class="btn btn-secondary" @click="router.push('/revendedor/usuarios')">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; overflow: hidden; }
.form { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-label { font-size: 0.85rem; font-weight: 600; color: var(--ink); }
.form-input {
  padding: 0.65rem 0.85rem; border: 1px solid var(--hairline); border-radius: var(--r-sm);
  background: var(--surface-1); color: var(--ink); font-size: 0.9rem; outline: none;
  transition: border-color 0.2s;
}
.form-input:focus { border-color: var(--accent); }
.form-input:disabled { opacity: 0.6; }
.form-hint { font-size: 0.75rem; color: var(--ink-muted); margin: 0; }
.form-actions { display: flex; gap: 0.75rem; margin-top: 0.5rem; }
.btn { padding: 0.65rem 1.25rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; transition: all 0.2s; }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:hover:not(:disabled) { opacity: 0.9; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.loading-state { padding: 2rem; text-align: center; color: var(--ink-muted); }
.error-state { padding: 1rem; text-align: center; color: var(--accent); background: var(--accent-soft); border-radius: var(--r-sm); }
.error-state p { margin-bottom: 0.75rem; }
.success-state { padding: 1rem; text-align: center; color: var(--accent); background: var(--accent-soft); border-radius: var(--r-sm); }
</style>
