<template>
  <div class="page-container">
    <div class="page-header">
      <h1 class="page-title">Domínios Play Fake 🌐</h1>
      <p class="page-sub">Configure domínios personalizados e certificados SSL para suas landing pages</p>
    </div>
    <div class="card table-card">
      <div class="card-header">
        <h3>Domínios Ativos</h3>
      </div>
      <table class="data-table">
        <thead>
          <tr>
            <th>Domínio</th>
            <th>Status SSL</th>
            <th>Tipo</th>
            <th>Criado em</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code>thevaultpanel.vip</code></td>
            <td><span class="badge badge-success">Ativo (SSL)</span></td>
            <td>Principal</td>
            <td>27/09/2026</td>
          </tr>
          <tr>
            <td><code>play-fake-cdn.net</code></td>
            <td><span class="badge badge-success">Ativo (SSL)</span></td>
            <td>CDN Mídia</td>
            <td>27/09/2026</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts"></script>
<style scoped>
.page-container { display: flex; flex-direction: column; gap: 1.5rem; }
.page-title { font-family: var(--font-display); font-size: 1.5rem; font-weight: 700; color: var(--ink); }
.page-sub { font-size: 0.875rem; color: var(--ink-muted); }
.card { background: var(--surface-1); border: 1px solid var(--hairline); border-radius: var(--r-lg); overflow: hidden; }
.card-header { padding: 1.25rem; border-bottom: 1px solid var(--hairline); }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem; }
.data-table th { padding: 0.75rem 1.25rem; background: var(--surface-2); color: var(--ink-muted); }
.data-table td { padding: 1rem 1.25rem; border-bottom: 1px solid var(--hairline); color: var(--ink); }
.badge-success { background: var(--success-soft); color: var(--success); padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: 600; font-size: 0.75rem; }
</style>