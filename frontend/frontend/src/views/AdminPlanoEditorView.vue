<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">{{ isEditMode ? 'Editar Plano' : 'Novo Plano' }} 📋</h1>
      <p class="page-sub">{{ isEditMode ? 'Altere os dados do plano existente' : 'Crie um novo plano de assinatura' }}</p>
    </div>

    <div class="card">
      <form @submit.prevent="savePlan" class="plan-form">
        <div class="form-row">
          <div class="form-group">
            <label for="name">Nome</label>
            <input id="name" v-model="form.name" type="text" placeholder="Ex: Premium Mensal" required />
          </div>
          <div class="form-group">
            <label for="price">Preço (R$)</label>
            <input id="price" v-model.number="form.price" type="number" step="0.01" min="0" placeholder="0.00" required />
          </div>
        </div>

        <div class="form-group">
          <label for="description">Descrição</label>
          <textarea id="description" v-model="form.description" placeholder="Descreva o plano..." rows="3"></textarea>
        </div>

        <div class="form-group">
          <label>Features</label>
          <div class="tags-input">
            <span v-for="(tag, i) in form.features" :key="i" class="tag">
              {{ tag }}
              <button type="button" @click="removeFeature(i)">✕</button>
            </span>
            <input v-model="newFeature" @keyup.enter="addFeature" placeholder="Digite e pressione Enter" />
          </div>
          <p class="hint">Pressione Enter para adicionar</p>
        </div>

        <fieldset class="limits-section">
          <legend>Limites</legend>
          <div class="form-row">
            <div class="form-group">
              <label for="max_users">Max Usuários</label>
              <input id="max_users" v-model.number="form.limits.max_users" type="number" min="0" placeholder="100" />
            </div>
            <div class="form-group">
              <label for="max_apps">Max Apps</label>
              <input id="max_apps" v-model.number="form.limits.max_apps" type="number" min="0" placeholder="10" />
            </div>
            <div class="form-group">
              <label for="max_storage">Max Storage (MB)</label>
              <input id="max_storage" v-model.number="form.limits.max_storage" type="number" min="0" placeholder="5000" />
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="validity_days">Validade (dias)</label>
              <input id="validity_days" v-model.number="form.limits.validity_days" type="number" min="0" placeholder="30" />
            </div>
            <div class="form-group">
              <label for="max_concurrent">Max Concorrente</label>
              <input id="max_concurrent" v-model.number="form.limits.max_concurrent" type="number" min="0" placeholder="5" />
            </div>
          </div>
        </fieldset>

        <div class="form-actions">
          <button type="button" class="btn btn-secondary" @click="$router.push('/admin/planos')">Voltar</button>
          <button type="submit" class="btn btn-primary" :disabled="saving">
            {{ saving ? 'Salvando...' : isEditMode ? 'Atualizar' : 'Criar' }}
          </button>
        </div>

        <div v-if="error" class="error">{{ error }}</div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api'
const authStore = useAuthStore()
const route = useRoute()
const router = useRouter()

const isEditMode = ref(false)
const saving = ref(false)
const error = ref('')
const newFeature = ref('')

const form = reactive({
  name: '',
  price: 0,
  description: '',
  features: [] as string[],
  limits: {
    max_users: 0,
    max_apps: 0,
    max_storage: 0,
    validity_days: 0,
    max_concurrent: 0,
  },
})

const resetForm = () => {
  form.name = ''
  form.price = 0
  form.description = ''
  form.features = []
  form.limits = { max_users: 0, max_apps: 0, max_storage: 0, validity_days: 0, max_concurrent: 0 }
  error.value = ''
  newFeature.value = ''
}

const loadPlan = async (id: string) => {
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` }
    const res = await axios.get(`${API_URL}/planos/${id}`, { headers })
    const data = res.data.data || res.data
    form.name = data.name || ''
    form.price = data.price || 0
    form.description = data.description || ''
    form.features = data.features || []
    form.limits = {
      max_users: data.max_users || 0,
      max_apps: data.max_apps || 0,
      max_storage: data.max_storage || 0,
      validity_days: data.validity_days || 0,
      max_concurrent: data.max_concurrent || 0,
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao carregar plano'
  }
}

const addFeature = () => {
  const f = newFeature.value.trim()
  if (f && !form.features.includes(f)) {
    form.features.push(f)
  }
  newFeature.value = ''
}

const removeFeature = (index: number) => {
  form.features.splice(index, 1)
}

const savePlan = async () => {
  saving.value = true
  error.value = ''
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` }
    const payload = { ...form }

    if (isEditMode.value) {
      await axios.put(`${API_URL}/planos/${route.params.id}`, payload, { headers })
    } else {
      await axios.post(`${API_URL}/planos`, payload, { headers })
    }
    router.push('/admin/planos')
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao salvar plano'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  const id = route.params.id as string
  if (id) {
    isEditMode.value = true
    loadPlan(id)
  } else {
    resetForm()
  }
})
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; }
.plan-form { display: flex; flex-direction: column; gap: 1.25rem; max-width: 800px; }
.form-row { display: flex; gap: 1rem; flex-wrap: wrap; }
.form-group { display: flex; flex-direction: column; gap: 0.35rem; flex: 1; min-width: 200px; }
.form-group label { font-size: 0.8rem; font-weight: 600; color: var(--ink-muted); text-transform: uppercase; }
.form-group input, .form-group textarea { padding: 0.65rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink); font-size: 0.9rem; }
.form-group textarea { resize: vertical; }
.hint { font-size: 0.7rem; color: var(--ink-muted); margin: 0; }
.tags-input { display: flex; flex-wrap: wrap; gap: 0.4rem; padding: 0.5rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: var(--r-sm); min-height: 42px; }
.tag { background: var(--accent-soft); color: var(--accent); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; display: flex; align-items: center; gap: 0.3rem; }
.tag button { background: none; border: none; color: var(--accent); cursor: pointer; font-size: 0.9rem; padding: 0; }
.tags-input input { border: none; background: transparent; color: var(--ink); outline: none; flex: 1; min-width: 100px; font-size: 0.85rem; }
.limits-section { border: 1px solid var(--hairline); border-radius: var(--r-sm); padding: 1rem; background: var(--surface-2); }
.limits-section legend { font-size: 0.8rem; font-weight: 600; color: var(--ink-muted); text-transform: uppercase; padding: 0 0.5rem; }
.form-actions { display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 0.5rem; }
.btn { padding: 0.65rem 1.5rem; border-radius: var(--r-sm); border: none; font-weight: 600; cursor: pointer; font-size: 0.9rem; }
.btn-primary { background: var(--accent); color: white; }
.btn-primary:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-secondary { background: var(--surface-2); color: var(--ink); border: 1px solid var(--hairline); }
.error { color: var(--danger); font-size: 0.85rem; margin-top: 0.5rem; }
</style>
