<template>
  <div class="page-container">
    <div class="detail-layout">
      <div class="preview-section">
        <iframe :srcdoc="template.html" class="preview-iframe" sandbox=""></iframe>
      </div>

      <div class="info-section">
        <h1 class="detail-title">{{ template.title }}</h1>
        <p class="detail-description">{{ template.description }}</p>
        <div class="detail-meta">
          <span class="meta-item">Criado em: {{ formattedDate }}</span>
          <span class="meta-item">Autor: {{ template.author }}</span>
          <span class="meta-item">Categoria: {{ template.category }}</span>
        </div>
      </div>

      <div class="actions-section">
        <button class="btn btn-secondary" @click="copyHtml">Copiar HTML 📋</button>
        <button class="btn btn-primary" @click="downloadZip">Baixar .ZIP 📦</button>
        <button class="btn btn-back" @click="goBack">Voltar ←</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const template = ref<any>(null)
const isLoading = ref(true)

const templateId = ref<string>('')

const authHeaders = () => ({
  headers: { Authorization: `Bearer ${authStore.token}` }
})

const formattedDate = ref('')

const loadTemplate = async () => {
  isLoading.value = true
  try {
    const id = route.params.id as string
    templateId.value = id
    const res = await axios.get(`${API_URL}/templates/${id}`, authHeaders())
    template.value = res.data
    const date = new Date(res.data.created_at || res.data.created)
    formattedDate.value = date.toLocaleDateString('pt-BR')
  } catch (err) {
    console.error('Erro ao carregar template:', err)
  } finally {
    isLoading.value = false
  }
}

const copyHtml = async () => {
  try {
    await navigator.clipboard.writeText(template.value.html)
    alert('Código HTML copiado para a área de transferência!')
  } catch (err) {
    alert('Não foi possível copiar. Seu navegador bloqueou o acesso.')
  }
}

const downloadZip = async () => {
  try {
    const res = await axios.get(`${API_URL}/templates/${templateId.value}/download`, {
      ...authHeaders(),
      responseType: 'blob',
    })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const a = document.createElement('a')
    a.href = url
    a.download = `template_${templateId.value}.zip`
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    alert('Erro ao baixar o ZIP.')
  }
}

const goBack = () => {
  router.back()
}

onMounted(() => {
  loadTemplate()
})
</script>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 1.5rem;
  min-height: 100vh;
  background: var(--surface-1);
}

.detail-layout {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.preview-section {
  background: #000;
  border: 1px solid var(--hairline);
  border-radius: var(--r-lg);
  overflow: hidden;
}

.preview-iframe {
  width: 100%;
  height: 400px;
  border: none;
  display: block;
}

.info-section {
  background: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: var(--r-lg);
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.detail-title {
  font-family: var(--font-display);
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--ink);
  margin: 0;
}

.detail-description {
  font-size: 0.9rem;
  color: var(--ink);
  margin: 0;
  line-height: 1.6;
}

.detail-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-top: 0.5rem;
}

.meta-item {
  font-size: 0.8rem;
  color: var(--ink-muted, var(--ink));
  background: var(--surface-2, var(--surface-1));
  padding: 0.25rem 0.75rem;
  border-radius: var(--r-sm);
  border: 1px solid var(--hairline);
}

.actions-section {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  justify-content: flex-start;
}

.btn {
  padding: 0.6rem 1.25rem;
  border-radius: var(--r-sm);
  font-size: 0.85rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  text-decoration: none;
  text-align: center;
  transition: opacity 0.2s;
}

.btn:hover {
  opacity: 0.85;
}

.btn-primary {
  background: var(--accent);
  color: white;
}

.btn-secondary {
  background: var(--surface-2, var(--surface-1));
  color: var(--ink);
  border: 1px solid var(--hairline);
}

.btn-back {
  background: transparent;
  color: var(--ink);
  border: 1px solid var(--hairline);
}

@media (min-width: 768px) {
  .detail-layout {
    flex-direction: row;
    flex-wrap: wrap;
  }

  .preview-section {
    flex: 1 1 100%;
  }

  .preview-iframe {
    height: 500px;
  }

  .info-section {
    flex: 1 1 calc(100% - 2rem);
  }

  .actions-section {
    flex: 1 1 100%;
    justify-content: flex-end;
  }
}
</style>
