<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
const route = useRoute();
const authStore = useAuthStore();

const conversationId = route.params.conversationId as string;

const subject = ref('');
const status = ref('');
const messages = ref<any[]>([]);
const replyText = ref('');
const loading = ref(false);
const error = ref('');

const getHeaders = () => ({
  Authorization: `Bearer ${authStore.token}`,
});

const loadConversation = async () => {
  loading.value = true;
  error.value = '';
  try {
    const res = await axios.get(`${API_URL}/revendedor/suporte/${conversationId}`, { headers: getHeaders() });
    const data = res.data;
    subject.value = data.subject;
    status.value = data.status;
    messages.value = data.messages || [];
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Erro ao carregar conversa';
    console.error(e);
  } finally {
    loading.value = false;
  }
};

const sendReply = async () => {
  if (!replyText.value.trim()) return;
  try {
    await axios.post(`${API_URL}/revendedor/suporte/${conversationId}/messages`, { message: replyText.value.trim() }, { headers: getHeaders() });
    replyText.value = '';
    await loadConversation();
  } catch (e: any) {
    alert(e.response?.data?.message || 'Erro ao enviar resposta');
    console.error(e);
  }
};

const formatTimestamp = (ts: string) => {
  if (!ts) return '—';
  const day = '2-digit' as const;
  const month = '2-digit' as const;
  const year = 'numeric' as const;
  const hour = '2-digit' as const;
  const minute = '2-digit' as const;
  return new Date(ts).toLocaleString('pt-BR', { day, month, year: year, hour, minute });
};

const statusClass = (s: string): string => {
  if (!s) return 'default';
  const v = s.toLowerCase();
  if (['open', 'pending', 'answered'].includes(v)) return v;
  if (['closed', 'resolved'].includes(v)) return v;
  return 'default';
};

onMounted(() => loadConversation());
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">{{ subject }}</h1>
      <p class="page-sub">Status: <span class="status-badge" :class="statusClass(status)">{{ status }}</span></p>
    </div>

    <div class="card chat-card">
      <div v-if="loading" class="loading-state">Carregando conversa...</div>
      <div v-else-if="error" class="error-state"><p>{{ error }}</p></div>
      <div v-else class="chat-layout">
        <div class="chat-messages">
          <div v-for="(msg, index) in messages" :key="index" class="chat-bubble-wrapper" :class="{ 'sent': msg.sender === 'revendedor', 'received': msg.sender !== 'revendedor' }">
            <div class="chat-bubble">
              <div class="bubble-sender">{{ msg.sender }}</div>
              <div class="bubble-text">{{ msg.text || msg.message }}</div>
              <div class="bubble-time">{{ formatTimestamp(msg.created_at || msg.timestamp) }}</div>
            </div>
          </div>
          <div v-if="messages.length === 0" class="empty-state">
            <p>Nenhuma mensagem ainda.</p>
          </div>
        </div>

        <div class="chat-input-area">
          <form @submit.prevent="sendReply" class="reply-form">
            <textarea v-model="replyText" placeholder="Digite sua resposta..." rows="3" class="reply-input"></textarea>
            <button type="submit" class="btn-reply">Enviar Resposta</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 1.25rem;
  max-width: 900px;
  margin: 0 auto;
}
.page-title {
  font-family: var(--font-display);
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--ink);
  margin: 0;
}
.page-sub {
  color: var(--ink-muted);
  margin: 0.25rem 0 0 0;
  font-size: 0.9rem;
}
.status-badge {
  padding: 0.2rem 0.6rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}
.status-badge.open, .status-badge.pending { background: var(--warning-soft); color: var(--warning); }
.status-badge.closed, .status-badge.resolved { background: var(--success-soft); color: var(--success); }
.status-badge.priority, .status-badge.urgent { background: var(--danger-soft); color: var(--danger); }
.status-badge.default { background: var(--surface-2); color: var(--ink-muted); }

.chat-card {
  background: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: var(--r-lg);
  overflow: hidden;
}

.chat-layout {
  display: flex;
  flex-direction: column;
  height: 60vh;
  min-height: 400px;
}

.chat-messages {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.chat-bubble-wrapper {
  display: flex;
  justify-content: flex-end;
}
.chat-bubble-wrapper.received {
  justify-content: flex-start;
}
.chat-bubble-wrapper.sent {
  justify-content: flex-end;
}

.chat-bubble {
  max-width: 75%;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  background: var(--surface-2);
  border: 1px solid var(--hairline);
  color: var(--ink);
}
.chat-bubble-wrapper.sent .chat-bubble {
  background: var(--accent);
  color: white;
  border-color: var(--accent);
}

.bubble-sender {
  font-weight: 700;
  font-size: 0.7rem;
  text-transform: uppercase;
  margin-bottom: 0.25rem;
  opacity: 0.8;
}
.bubble-text {
  font-size: 0.9rem;
  line-height: 1.5;
  white-space: pre-wrap;
}
.bubble-time {
  font-size: 0.7rem;
  text-align: right;
  margin-top: 0.25rem;
  opacity: 0.6;
}

.chat-input-area {
  border-top: 1px solid var(--hairline);
  padding: 1rem 1.25rem;
  background: var(--surface-2);
}
.reply-form {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.reply-input {
  padding: 0.75rem;
  background: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: 8px;
  color: var(--ink);
  font-size: 0.9rem;
  resize: vertical;
  font-family: inherit;
}
.reply-input:focus {
  outline: none;
  border-color: var(--accent);
}
.btn-reply {
  align-self: flex-end;
  padding: 0.6rem 1.5rem;
  background: var(--accent);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  font-size: 0.85rem;
}
.btn-reply:hover {
  opacity: 0.9;
}

.loading-state, .empty-state {
  padding: 3rem;
  text-align: center;
  color: var(--ink-muted);
}
.error-state {
  padding: 2rem;
  text-align: center;
  color: var(--danger);
}
.error-state p {
  margin-bottom: 1rem;
}
</style>
