<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Gestão de Planos 💳</h1>
      <p class="page-sub">Gerencie os planos de assinatura e os limites por conta</p>
    </div>
    <div class="card table-card">
      <table class="data-table">
        <thead>
          <tr><th>Plano</th><th>Preço</th><th>Limite Apps</th><th>Status</th></tr>
        </thead>
        <tbody>
          <tr><td><strong>Cortesia 7 Dias</strong></td><td>R$ 0,00</td><td>15</td><td><span class="badge badge-success">Ativo</span></td></tr>
          <tr><td><strong>Plano Mensal PRO</strong></td><td>R$ 149,90</td><td>100</td><td><span class="badge badge-success">Ativo</span></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<script setup lang="ts"></script>
<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); padding: 1.25rem; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.badge-success { background: var(--success-soft); color: var(--success); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.75rem; }
</style>