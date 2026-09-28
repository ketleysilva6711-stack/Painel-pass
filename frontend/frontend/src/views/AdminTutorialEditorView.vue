<template>
  <div class="editor-page">
    <div class="editor-header">
      <RouterLink to="/admin/tutoriais" class="back-btn">&larr; Voltar</RouterLink>
      <h1 class="page-title">{{ isEditMode ? 'Editar Tutorial' : 'Novo Tutorial' }}</h1>
    </div>

    <form class="editor-form" @submit.prevent="handleSave">
      <div class="form-group">
        <label for="title">Título *</label>
        <input id="title" v-model="form.title" type="text" required placeholder="Título do tutorial" />
      </div>

      <div class="form-group">
        <label for="description">Descrição *</label>
        <textarea id="description" v-model="form.description" required placeholder="Descrição do tutorial" rows="4"></textarea>
      </div>

      <div class="form-group">
        <label for="category">Categoria *</label>
        <select id="category" v-model="form.category" required>
          <option value="" disabled>Selecione uma categoria</option>
          <option value="guia">Guia</option>
          <option value="video">Vídeo</option>
          <option value="texto">Texto</option>
          <option value="outro">Outro</option>
        </select>
      </div>

      <div class="form-group">
        <label for="video_url">URL do Vídeo</label>
        <input id="video_url" v-model="form.video_url" type="url" placeholder="https://..." />
      </div>

      <div class="form-group">
        <label for="thumbnail_url">URL da Thumbnail</label>
        <input id="thumbnail_url" v-model="form.thumbnail_url" type="url" placeholder="https://..." />
      </div>

      <div class="form-group">
        <label for="content">Conteúdo *</label>
        <textarea id="content" v-model="form.content" required placeholder="Conteúdo em Markdown ou HTML" rows="10"></textarea>
      </div>

      <div class="form-actions">
        <button type="submit" class="btn btn-primary" :disabled="saving">
          {{ saving ? 'Salvando...' : (isEditMode ? 'Atualizar' : 'Criar') }}
        </button>
      </div>

      <div v-if="error" class="error-message">{{ error }}</div>
      <div v-if="success" class="success-message">{{ success }}</div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api'
const authStore = useAuthStore()
const route = useRoute()

const isEditMode = ref(false)
const saving = ref(false)
const error = ref('')
const success = ref('')

const form = ref({
  title: '',
  description: '',
  category: '',
  video_url: '',
  thumbnail_url: '',
  content: '',
})

const loadTutorial = async (id: string) => {
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` }
    const res = await axios.get(`${API_URL}/tutoriais/${id}`, { headers })
    const data = res.data.data || res.data
    form.value = {
      title: data.title || '',
      description: data.description || '',
      category: data.category || '',
      video_url: data.video_url || '',
      thumbnail_url: data.thumbnail_url || '',
      content: data.content || '',
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao carregar tutorial'
  }
}

const handleSave = async () => {
  saving.value = true
  error.value = ''
  success.value = ''
  try {
    const headers: Record<string, string> = { Authorization: `Bearer ${authStore.token}` }
    if (isEditMode.value) {
      const id = route.params.id as string
      await axios.put(`${API_URL}/tutoriais/${id}`, form.value, { headers })
      success.value = 'Tutorial atualizado com sucesso!'
    } else {
      await axios.post(`${API_URL}/tutoriais`, form.value, { headers })
      success.value = 'Tutorial criado com sucesso!'
      form.value = { title: '', description: '', category: '', video_url: '', thumbnail_url: '', content: '' }
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao salvar tutorial'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  if (route.path === '/admin/tutoriais/novo') {
    isEditMode.value = false
  } else if (route.params.id) {
    isEditMode.value = true
    loadTutorial(route.params.id as string)
  }
})
</script>

<style scoped>
.editor-page {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  max-width: 720px;
}

.editor-header {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.back-btn {
  color: var(--accent);
  text-decoration: none;
  font-size: 0.875rem;
  font-weight: 600;
}

.back-btn:hover {
  text-decoration: underline;
}

.page-title {
  font-family: var(--font-display);
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--ink);
  margin: 0;
}

.editor-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  background: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: var(--r-lg);
  padding: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.form-group label {
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--ink);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group input,
.form-group textarea,
.form-group select {
  font-family: var(--sans);
  font-size: 0.9rem;
  color: var(--ink);
  background: var(--surface-2, #f4f3ec);
  border: 1px solid var(--hairline);
  border-radius: var(--r-sm);
  padding: 0.625rem 0.75rem;
  outline: none;
  transition: border-color 0.2s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
  border-color: var(--accent);
}

.form-group textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.btn {
  padding: 0.625rem 1.5rem;
  border-radius: var(--r-sm);
  border: none;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 0.9rem;
}

.btn-primary {
  background: var(--accent);
  color: #fff;
}

.btn-primary:hover:not(:disabled) {
  opacity: 0.85;
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-message {
  color: #dc2626;
  font-size: 0.875rem;
  padding: 0.75rem;
  background: rgba(220, 38, 38, 0.08);
  border-radius: var(--r-sm);
  border: 1px solid rgba(220, 38, 38, 0.2);
}

.success-message {
  color: #16a34a;
  font-size: 0.875rem;
  padding: 0.75rem;
  background: rgba(22, 163, 74, 0.08);
  border-radius: var(--r-sm);
  border: 1px solid rgba(22, 163, 74, 0.2);
}
</style>
