<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Tutoriais 📚</h1>
      <p class="page-sub">Central de ajuda e instruções de uso</p>
    </div>
    <div class="tut-grid">
      <div class="card tut-card">
        <h3>🎮 Como publicar um Play Fake</h3>
        <p>Aprenda a publicar aplicativos e configurar os links de download.</p>
      </div>
      <div class="card tut-card">
        <h3>🔴 Guia de regras do Bypass</h3>
        <p>Como distribuir binários e configurar regras de redirecionamento.</p>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts"></script>
<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.tut-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.5rem; }
.tut-card h3 { font-size: 1.1rem; font-weight: 700; color: var(--ink); margin-bottom: 0.5rem; }
.tut-card p { font-size: 0.875rem; color: var(--ink-muted); }
</style>