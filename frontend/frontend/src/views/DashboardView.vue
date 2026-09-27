<template>
  <div class="app-shell" :class="{ 'sidebar-open': sidebarOpen }">

    <!-- Backdrop mobile -->
    <div class="backdrop" v-if="sidebarOpen" @click="sidebarOpen = false"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar">
      <!-- Brand -->
      <div class="brand">
        <img src="/icons/android-troll.png" alt="Logo" class="brand-logo" />
        <div class="brand-info">
          <span class="brand-name">The Vault Panel</span>
          <span class="brand-version">v2.0</span>
        </div>
      </div>

      <!-- Status badge -->
      <div class="status-badge" :class="`status-${statusKind}`">
        {{ statusLabel }}
      </div>

      <!-- Nav por role -->
      <nav class="nav">
        <template v-for="group in navGroups" :key="group.eyebrow">
          <span class="nav-eyebrow">{{ group.eyebrow }}</span>
          <template v-for="item in group.items" :key="item.to">
            <router-link
              v-if="!item.flag || hasFuncionalidade(item.flag)"
              :to="item.to"
              class="nav-item"
              active-class="active"
            >
              <span class="nav-icon" :style="{ color: item.color }">●</span>
              {{ item.label }}
            </router-link>
          </template>
        </template>
      </nav>

      <!-- Footer do usuário -->
      <div class="sidebar-footer">
        <div class="avatar" :style="{ background: roleColor }">{{ initials }}</div>
        <div class="user-info">
          <span class="user-name">{{ user?.name }}</span>
          <span class="user-sub">{{ eyebrow }} · {{ roleLabel }}</span>
        </div>
        <button class="btn-icon-only" @click="handleLogout" title="Sair">✕</button>
      </div>
    </aside>

    <!-- MAIN -->
    <main class="main">
      <!-- Topbar -->
      <header class="topbar">
        <button class="sidebar-toggle" @click="sidebarOpen = !sidebarOpen">☰</button>
        <div class="topbar-right">
          <span v-if="statusKind !== 'ok'" class="expira-badge">
            ⚠ {{ statusLabel }}
          </span>
          <button class="btn-theme" @click="toggleTheme" title="Alternar tema">◑</button>
        </div>
      </header>

      <!-- Content -->
      <div class="content">
        <router-view />
      </div>
    </main>

    <!-- FAB Suporte (só não-admin) -->
    <a v-if="role !== 'admin'" :href="role === 'revendedor' ? '/revendedor/suporte' : '/suporte'" class="fab-suporte" title="Suporte">💬</a>

  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';
import { navUser, navAdmin, navRevendedor } from '../composables/useNavItems';

const authStore = useAuthStore();
const router = useRouter();
const sidebarOpen = ref(false);

const user = computed(() => authStore.user);
const role = computed(() => user.value?.role || 'user');
const roleLabel = computed(() => user.value?.roleLabel || 'Usuário');
const eyebrow = computed(() => user.value?.eyebrow || 'Meu espaço');
const initials = computed(() => user.value?.initials || 'US');
const statusLabel = computed(() => user.value?.contaStatusLabel || 'Conta ativa');
const statusKind = computed(() => user.value?.contaStatusKind || 'ok');
const funcionalidades = computed(() => user.value?.funcionalidades || []);

const roleColor = computed(() => {
  if (role.value === 'admin') return '#818cf8';
  if (role.value === 'revendedor') return '#f5a623';
  return '#34d399';
});

const navGroups = computed(() => {
  if (role.value === 'admin') return navAdmin;
  if (role.value === 'revendedor') return navRevendedor;
  return navUser;
});

const hasFuncionalidade = (flag: string) => {
  if (!flag) return true;
  return funcionalidades.value.includes(flag);
};

const handleLogout = async () => {
  await authStore.logout();
  router.push('/play-fake');
};

const toggleTheme = () => {
  const html = document.documentElement;
  const isDark = html.classList.contains('dark');
  html.classList.toggle('dark', !isDark);
  localStorage.setItem('vp-theme', isDark ? 'light' : 'dark');
};
</script>

<style scoped>
.app-shell { display: flex; min-height: 100vh; background: var(--canvas); color: var(--ink); position: relative; }
.backdrop { position: fixed; inset: 0; background: oklch(0 0 0 / 50%); z-index: 40; }

/* Sidebar */
.sidebar { width: 220px; min-width: 220px; background: var(--surface-1); border-right: 1px solid var(--hairline); display: flex; flex-direction: column; padding: 1.25rem 0.75rem; gap: 0.25rem; z-index: 50; }
.brand { display: flex; align-items: center; gap: 0.75rem; padding: 0 0.5rem 1rem; border-bottom: 1px solid var(--hairline); margin-bottom: 0.75rem; }
.brand-logo { width: 36px; height: 36px; border-radius: var(--r-pill); object-fit: contain; }
.brand-name { font-family: var(--font-display); font-size: 0.9rem; font-weight: 700; color: var(--ink); display: block; }
.brand-version { font-size: 0.7rem; color: var(--ink-faint); }

.status-badge { font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.6rem; border-radius: var(--r-pill); text-align: center; margin-bottom: 1rem; }
.status-ok   { background: var(--success-soft); color: var(--success); }
.status-warn { background: var(--warning-soft); color: var(--warning); }
.status-bad  { background: var(--danger-soft);  color: var(--danger);  }

.nav { flex: 1; display: flex; flex-direction: column; gap: 0.1rem; overflow-y: auto; }
.nav-eyebrow { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.08em; color: var(--ink-faint); padding: 0.75rem 0.5rem 0.25rem; }
.nav-item { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border-radius: var(--r-sm); color: var(--ink-muted); text-decoration: none; font-size: 0.875rem; transition: all 0.15s; }
.nav-item:hover { background: var(--surface-2); color: var(--ink); }
.nav-item.active { background: var(--surface-2); color: var(--ink); font-weight: 600; }
.nav-icon { font-size: 0.5rem; }

.sidebar-footer { display: flex; align-items: center; gap: 0.5rem; padding: 0.75rem 0.5rem 0; border-top: 1px solid var(--hairline); margin-top: 0.5rem; }
.avatar { width: 32px; height: 32px; min-width: 32px; border-radius: var(--r-pill); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700; color: white; }
.user-name { font-size: 0.8rem; font-weight: 600; color: var(--ink); display: block; }
.user-sub { font-size: 0.65rem; color: var(--ink-faint); }
.btn-icon-only { background: none; border: none; color: var(--ink-muted); cursor: pointer; margin-left: auto; padding: 0.25rem; }

/* Main */
.main { flex: 1; display: flex; flex-direction: column; min-width: 0; }
.topbar { height: 52px; display: flex; align-items: center; justify-content: space-between; padding: 0 1.5rem; border-bottom: 1px solid var(--hairline); background: var(--surface-1); gap: 1rem; }
.sidebar-toggle { background: none; border: none; color: var(--ink-muted); cursor: pointer; font-size: 1.25rem; }
.topbar-right { display: flex; align-items: center; gap: 0.75rem; }
.expira-badge { font-size: 0.75rem; font-weight: 600; color: var(--warning); background: var(--warning-soft); padding: 0.2rem 0.6rem; border-radius: var(--r-pill); }
.btn-theme { background: none; border: 1px solid var(--hairline); border-radius: var(--r-sm); color: var(--ink-muted); cursor: pointer; padding: 0.25rem 0.5rem; }
.content { flex: 1; padding: 2rem; overflow-y: auto; }

/* FAB */
.fab-suporte { position: fixed; bottom: 1.5rem; right: 1.5rem; width: 48px; height: 48px; border-radius: var(--r-pill); background: var(--accent); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; text-decoration: none; box-shadow: var(--shadow-md); z-index: 60; }
</style>