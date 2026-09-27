<template>
  <!-- Tela de Encerramento -->
  <div v-if="sistemaCerrado && !acessoLiberado" class="encerramento-screen">
    <div class="encerramento-card">
      <img src="/icons/android-troll.png" alt="Logo" class="logo-img" />
      <h2>{{ aviso.titulo || 'Sistema temporariamente encerrado' }}</h2>
      <p>{{ aviso.texto || 'Voltaremos em breve. Aguarde.' }}</p>
    </div>
  </div>

  <!-- Tela de Login -->
  <div v-else class="login-screen">
    <div class="login-card">
      <div class="brand">
        <img src="/icons/android-troll.png" alt="Logo" class="logo-img" />
        <h1>The Vault Panel</h1>
        <p class="sub">Acesso exclusivo — contas criadas pelo Admin</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div v-if="errorMessage" class="alert alert-danger">
          {{ errorMessage }}
        </div>

        <div class="input-icon-wrap">
          <svg class="input-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10zm0 2c-5.33 0-8 2.67-8 4v2h16v-2c0-1.33-2.67-4-8-4z"/>
          </svg>
          <input
            id="email"
            v-model="email"
            type="text"
            placeholder="seu.usuario"
            required
            :disabled="isLoading"
            autocomplete="username"
            data-testid="login-username-input"
          />
        </div>

        <div class="input-icon-wrap">
          <svg class="input-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2z"/>
          </svg>
          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="••••••••"
            required
            :disabled="isLoading"
            autocomplete="current-password"
            data-testid="login-password-input"
          />
        </div>

        <button
          type="submit"
          class="btn btn-primary btn-block"
          :disabled="isLoading || !email || !password"
          data-testid="login-submit-button"
        >
          <svg v-if="!isLoading" class="btn-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
          </svg>
          <span>{{ isLoading ? 'Entrando...' : 'Entrar' }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useRouter } from 'vue-router';

const email = ref('');
const password = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const sistemaCerrado = ref(false);
const acessoLiberado = ref(false);
const aviso = ref({ titulo: '', texto: '' });

const authStore = useAuthStore();
const router = useRouter();

// Atalho secreto: digitar "acesso" libera o login quando encerrado
let digitado = '';
const ATALHO = 'acesso';
const handleKeydown = (e: KeyboardEvent) => {
  digitado += e.key.toLowerCase();
  if (digitado.length > ATALHO.length) {
    digitado = digitado.slice(-ATALHO.length);
  }
  if (digitado === ATALHO) {
    acessoLiberado.value = true;
    digitado = '';
  }
};

onMounted(() => {
  window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown);
});

const handleLogin = async () => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    await authStore.login(email.value, password.value);
    // Redirect por role
    const role = authStore.role;
    if (role === 'admin') {
      router.push('/admin/dashboard');
    } else if (role === 'revendedor') {
      router.push('/revendedor/usuarios');
    } else {
      router.push('/dashboard');
    }
  } catch (error: any) {
    const status = error.response?.status;
    const msg = error.response?.data?.message;
    if (status === 403) {
      errorMessage.value = msg || 'Acesso negado.';
    } else if (status === 401) {
      errorMessage.value = 'Usuário ou senha inválidos.';
    } else {
      errorMessage.value = msg || 'Usuário ou senha inválidos.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.login-screen, .encerramento-screen {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--canvas);
  padding: 1.5rem;
}

.login-card, .encerramento-card {
  width: 100%;
  max-width: 420px;
  background-color: var(--surface-1);
  border: 1px solid var(--hairline);
  border-radius: var(--r-xl);
  padding: 2.5rem;
  box-shadow: var(--shadow-card);
}

.brand {
  text-align: center;
  margin-bottom: 2rem;
}

.logo-img {
  width: 64px;
  height: 64px;
  border-radius: var(--r-pill);
  margin-bottom: 1rem;
  object-fit: contain;
}

.brand h1 {
  font-family: var(--font-display);
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--ink);
  margin: 0 0 0.5rem 0;
}

.sub {
  color: var(--ink-muted);
  font-size: 0.875rem;
  margin: 0;
}

.input-icon-wrap {
  position: relative;
  margin-bottom: 1.25rem;
}

.input-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: var(--ink-faint);
}

.input-icon-wrap input {
  width: 100%;
  box-sizing: border-box;
  padding: 0.75rem 1rem 0.75rem 2.75rem;
  background-color: var(--surface-2);
  border: 1px solid var(--hairline);
  border-radius: var(--r-sm);
  color: var(--ink);
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.2s;
}

.input-icon-wrap input:focus {
  border-color: var(--accent);
}

.btn-block {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem;
  background-color: var(--accent);
  color: white;
  border: none;
  border-radius: var(--r-sm);
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.2s;
}

.btn-icon {
  width: 18px;
  height: 18px;
}

.btn-block:hover:not(:disabled) {
  background-color: var(--accent-hover);
}

.btn-block:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.alert-danger {
  background-color: var(--danger-soft);
  color: var(--danger);
  border: 1px solid var(--danger);
  padding: 0.75rem;
  border-radius: var(--r-sm);
  font-size: 0.875rem;
  margin-bottom: 1.25rem;
  text-align: center;
}

.encerramento-card {
  text-align: center;
}

.encerramento-card h2 {
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
  color: var(--ink);
}

.encerramento-card p {
  color: var(--ink-muted);
}
</style>