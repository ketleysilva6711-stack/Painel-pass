<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Suporte 24h 💬</h1>
      <p class="page-sub">Abra chamados de atendimento e tire suas dúvidas</p>
    </div>

    <div class="card form-card">
      <h3>Abrir Novo Chamado</h3>
      <form @submit.prevent="handleCreateTicket" style="margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem;">
        <input v-model="subject" type="text" placeholder="Assunto do chamado" required style="padding: 0.75rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: 8px; color: var(--ink);" />
        <textarea v-model="message" placeholder="Descreva sua solicitação em detalhes..." required style="padding: 0.75rem; background: var(--surface-2); border: 1px solid var(--hairline); border-radius: 8px; color: var(--ink); min-height: 100px;"></textarea>
        <button type="submit" style="align-self: flex-start; padding: 0.75rem 1.5rem; background: var(--accent); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">Enviar Chamado</button>
      </form>
    </div>

    <div class="card table-card" style="margin-top: 1.5rem;">
      <h3 style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline);">Seus Chamados</h3>
      <div v-if="tickets.length === 0" style="padding: 2rem; text-align: center; color: var(--ink-muted);">Nenhum chamado aberto.</div>
      <div v-for="t in tickets" :key="t.id" style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline);">
        <strong>#{{ t.id }} - {{ t.subject }}</strong>
        <span style="margin-left: 1rem; font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 4px; background: var(--success-soft); color: var(--success);">{{ t.status }}</span>
        <p style="margin-top: 0.5rem; font-size: 0.85rem; color: var(--ink-muted);" v-if="t.messages && t.messages.length > 0">{{ t.messages[0].message }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';

const subject = ref('');
const message = ref('');
const tickets = ref<any[]>([]);

const loadTickets = async () => {
  const res = await axios.get(`${API_URL}/support/tickets`);
  tickets.value = res.data;
};

const handleCreateTicket = async () => {
  await axios.post(`${API_URL}/support/tickets`, { subject: subject.value, message: message.value });
  subject.value = '';
  message.value = '';
  alert('Chamado enviado com sucesso!');
  await loadTickets();
};

onMounted(() => loadTickets());
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.25rem; }
</style>