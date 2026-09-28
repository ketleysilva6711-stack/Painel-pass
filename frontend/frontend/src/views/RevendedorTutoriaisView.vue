<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Tutoriais 📚</h1>
      <p class="page-sub">Guias e instruções para revendedores</p>
    </div>

    <div class="tut-grid">
      <div class="card tut-card" v-for="(tut, idx) in tutorials" :key="idx">
        <div class="tut-icon">{{ tut.icon }}</div>
        <h3>{{ tut.title }}</h3>
        <p>{{ tut.description }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const tutorials = ref([
  { icon: '📤', title: 'Como Gerenciar Apps dos Clientes', description: 'Aprenda a visualizar, filtrar e acompanhar todos os PlayFakeApps publicados pelos seus clientes.' },
  { icon: '🏆', title: 'Entendendo o Top Rank', description: 'Veja como o ranking funciona e acompanhe os melhores colocados na plataforma.' },
  { icon: '🏅', title: 'Ranking — Pódio', description: 'Conheça o sistema de pódio com medalhas para os top 3 e acompanhe os demais colocados.' },
  { icon: '📖', title: 'Guia de Regras e Bypass', description: 'Documentação sobre regras da plataforma e configurações de bypass para revendedores.' },
  { icon: '💰', title: 'Comissões e Pagamentos', description: 'Entenda como funciona o sistema de comissões e acompanhamento de receitas.' },
  { icon: '🔧', title: 'Suporte e Resolução de Problemas', description: 'Canais de suporte e soluções para os problemas mais comuns enfrentados por revendedores.' },
]);
</script>

<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { color: var(--ink-muted); }
.tut-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; }
.tut-card { cursor: default; transition: border-color 0.2s; }
.tut-card:hover { border-color: var(--accent); }
.tut-icon { font-size: 2rem; margin-bottom: 0.75rem; }
.tut-card h3 { font-size: 1.1rem; font-weight: 700; color: var(--ink); margin-bottom: 0.5rem; }
.tut-card p { font-size: 0.875rem; color: var(--ink-muted); line-height: 1.5; }
</style>