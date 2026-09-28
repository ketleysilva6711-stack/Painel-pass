<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api'

const route = useRoute()
const authStore = useAuthStore()

const id = route.params.id as string
const ticket = ref<any>(null)
const newMessage = ref('')
const loading = ref(false)
const sending = ref(false)

const loadConversation = async () => {
  loading.value = true
  try {
    const res = await axios.get(`${API_URL}/admin/suporte/${id}`, {
      headers: { Authorization: `Bearer ${authStore.token}` }
    })
    ticket.value = res.data
  } catch (err) {
    console.error('Failed to load conversation:', err)
  } finally {
    loading.value = false
  }
}

const sendReply = async () => {
  if (!newMessage.value.trim()) return
  sending.value = true
  try {
    await axios.post(
      `${API_URL}/admin/suporte/${id}/messages`,
      { message: newMessage.value.trim() },
      { headers: { Authorization: `Bearer ${authStore.token}` } }
    )
    newMessage.value = ''
    await loadConversation()
  } catch (err) {
    console.error('Failed to send reply:', err)
  } finally {
    sending.value = false
  }
}

const formatDate = (dateStr: string) => {
  return new Date(dateStr).toLocaleString('pt-BR', {
    day: '2-digit', month: '2-digit', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

const isAdminMsg = (msg: any) => msg.sender_role === 'admin' || msg.sender_type === 'admin'

onMounted(() => loadConversation())
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Suporte — Conversa #{{ id }}</h1>
      <p class="page-sub">Detalhes do ticket e histórico de mensagens</p>
    </div>

    <div v-if="loading" class="card">
      <p style="text-align:center; color: var(--ink-muted);">Carregando...</p>
    </div>

    <div v-else-if="!ticket" class="card">
      <p style="text-align:center; color: var(--ink-muted);">Ticket não encontrado.</p>
    </div>

    <div v-else class="detail-layout">
      <div class="ticket-meta">
        <div class="meta-grid">
          <div class="meta-item">
            <span class="meta-label">Assunto</span>
            <span class="meta-value">{{ ticket.subject }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Status</span>
            <span class="status-badge" :class="ticket.status">{{ ticket.status }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Prioridade</span>
            <span class="meta-value">{{ ticket.priority }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Criado em</span>
            <span class="meta-value">{{ formatDate(ticket.created_at) }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Usuário</span>
            <span class="meta-value">{{ ticket.user?.name || ticket.user?.email || '—' }}</span>
          </div>
          <div class="meta-item">
            <span class="meta-label">Email</span>
            <span class="meta-value">{{ ticket.user?.email || '—' }}</span>
          </div>
        </div>
      </div>

      <div class="chat-container">
        <div v-if="!ticket.messages || ticket.messages.length === 0" class="empty-chat">
          <p>Nenhuma mensagem ainda.</p>
        </div>
        <div v-else class="messages-list">
          <div
            v-for="(msg, index) in ticket.messages"
            :key="msg.id || index"
            class="message-row"
            :class="{ 'is-admin': isAdminMsg(msg), 'is-user': !isAdminMsg(msg) }"
          >
            <div class="chat-bubble">
              <div class="bubble-header">
                <span class="sender-name">{{ msg.sender_name || msg.sender?.name || (isAdminMsg(msg) ? 'Admin' : 'Usuário') }}</span>
                <span class="timestamp">{{ formatDate(msg.created_at || msg.timestamp) }}</span>
              </div>
              <div class="bubble-text">{{ msg.message || msg.text }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="reply-bar">
        <form @submit.prevent="sendReply" class="reply-form">
          <textarea
            v-model="newMessage"
            placeholder="Digite sua resposta..."
            rows="3"
            class="reply-input"
          />
          <button type="submit" class="reply-btn" :disabled="sending || !newMessage.trim()">
            {{ sending ? 'Enviando...' : 'Enviar Resposta' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.page-title {
  font-family: var(--font-display);
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--ink);
}

.page-sub {
  color: var(--ink-muted);
  font-size: 0.875rem;
}

.card {
  background: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: var(--r-lg);
  padding: 1.25rem;
}

.detail-layout {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* Ticket metadata */
.ticket-meta {
  background: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: var(--r-lg);
  padding: 1.25rem;
}

.meta-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 0.75rem;
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.meta-label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--ink-muted);
}

.meta-value {
  font-size: 0.9rem;
  color: var(--ink);
  font-weight: 500;
}

.status-badge {
  display: inline-block;
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: capitalize;
  width: fit-content;
}

.status-badge.open {
  background: var(--accent-soft);
  color: var(--accent);
}

.status-badge.closed {
  background: var(--surface-2);
  color: var(--ink-muted);
}

.status-badge.pending {
  background: #fff3cd;
  color: #856404;
}

.status-badge.resolved {
  background: #d4edda;
  color: #155724;
}

/* Chat layout */
.chat-container {
  background: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: var(--r-lg);
  padding: 1.25rem;
  max-height: 500px;
  overflow-y: auto;
}

.empty-chat {
  text-align: center;
  padding: 3rem 1rem;
  color: var(--ink-muted);
}

.messages-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.message-row {
  display: flex;
  flex-direction: column;
}

.message-row.is-admin {
  align-items: flex-start;
}

.message-row.is-user {
  align-items: flex-end;
}

.chat-bubble {
  max-width: 75%;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  position: relative;
}

.message-row.is-admin .chat-bubble {
  background: var(--surface-2);
  border: 1px solid var(--hairline);
  border-bottom-left-radius: 4px;
}

.message-row.is-user .chat-bubble {
  background: var(--accent);
  color: white;
  border-bottom-right-radius: 4px;
}

.bubble-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  margin-bottom: 0.35rem;
}

.sender-name {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.message-row.is-admin .sender-name {
  color: var(--accent);
}

.message-row.is-user .sender-name {
  color: rgba(255, 255, 255, 0.85);
}

.timestamp {
  font-size: 0.65rem;
  opacity: 0.7;
}

.bubble-text {
  font-size: 0.875rem;
  line-height: 1.5;
  white-space: pre-wrap;
  word-break: break-word;
}

/* Reply bar */
.reply-bar {
  background: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: var(--r-lg);
  padding: 1.25rem;
}

.reply-form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.reply-input {
  background: var(--surface-2);
  border: 1px solid var(--hairline);
  border-radius: 8px;
  padding: 0.75rem;
  color: var(--ink);
  font-size: 0.875rem;
  resize: vertical;
  font-family: inherit;
}

.reply-input:focus {
  outline: none;
  border-color: var(--accent);
}

.reply-btn {
  align-self: flex-end;
  padding: 0.6rem 1.5rem;
  background: var(--accent);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
  transition: opacity 0.15s;
}

.reply-btn:hover:not(:disabled) {
  opacity: 0.85;
}

.reply-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
