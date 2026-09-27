<template>
  <div class="admin-dashboard">
    <div class="admin-header">
      <h1 class="page-title">Painel de Gestão Admin 👑</h1>
      <p class="page-sub">Visão geral da plataforma The Vault Panel</p>
    </div>

    <div class="stats-row">
      <div class="stat-card">
        <span class="stat-label">Total de Usuários</span>
        <span class="stat-val">128</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Revendedores</span>
        <span class="stat-val">14</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Apps Criados</span>
        <span class="stat-val">342</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Status do Sistema</span>
        <span class="stat-val status-online">100% Online</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
</script>

<style scoped>
.admin-dashboard { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; color: var(--ink); }
.page-sub { font-size: 0.875rem; color: var(--ink-muted); }
.stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
.stat-card { background: var(--surface-1); border: 1px solid var(--hairline); padding: 1.25rem; border-radius: var(--r-md); display: flex; flex-direction: column; gap: 0.25rem; }
.stat-label { font-size: 0.75rem; color: var(--ink-muted); text-transform: uppercase; }
.stat-val { font-family: var(--font-display); font-size: 1.75rem; font-weight: 700; color: var(--ink); }
.status-online { color: var(--success); }
</style>