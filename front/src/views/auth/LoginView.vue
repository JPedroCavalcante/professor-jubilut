<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

async function handleLogin() {
  loading.value = true
  errorMessage.value = ''
  try {
    const user = await auth.login(email.value, password.value)
    if (user.role === 'admin') {
      router.push('/admin/dashboard')
    } else {
      router.push('/student/dashboard')
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Credenciais invalidas. Verifique seu e-mail e senha.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-card">

    <div class="login-brand">
      <div class="login-logo" aria-hidden="true">
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="32" height="32" rx="10" fill="url(#logo-grad)"/>
          <path d="M8 22V10l8 5 8-5v12l-8-4-8 4z" fill="white" opacity="0.9"/>
          <defs>
            <linearGradient id="logo-grad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
              <stop stop-color="#6366f1"/>
              <stop offset="1" stop-color="#8b5cf6"/>
            </linearGradient>
          </defs>
        </svg>
      </div>
      <div>
        <h1 class="login-title">Prof. Jubilut</h1>
        <p class="login-subtitle">Plataforma de Ensino</p>
      </div>
    </div>

    <hr class="divider" />

    <h2 class="login-heading">Entrar na plataforma</h2>
    <p class="login-description">Acesse com suas credenciais para continuar.</p>


    <div v-if="errorMessage" class="alert alert-error" role="alert">
      <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="flex-shrink:0;margin-top:1px">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      {{ errorMessage }}
    </div>

    <form @submit.prevent="handleLogin" novalidate>
      <div class="form-group">
        <label for="email" class="form-label">E-mail</label>
        <input
          id="email"
          v-model="email"
          type="email"
          class="form-control"
          required
          autocomplete="email"
          placeholder="seu@email.com"
          :disabled="loading"
        />
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Senha</label>
        <input
          id="password"
          v-model="password"
          type="password"
          class="form-control"
          required
          autocomplete="current-password"
          placeholder="Sua senha"
          :disabled="loading"
        />
      </div>

      <button
        type="submit"
        class="btn btn-primary btn-lg login-submit"
        :class="{ 'btn-loading': loading }"
        :disabled="loading"
      >
        {{ loading ? '' : 'Entrar' }}
      </button>
    </form>
  </div>
</template>

<style scoped>
.login-card {
  background: rgba(255, 255, 255, 0.97);
  border-radius: 20px;
  padding: 2.5rem 2rem;
  box-shadow:
    0 25px 50px rgba(0, 0, 0, 0.35),
    0 0 0 1px rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(12px);
  animation: fadeIn 0.4s ease;
}

.login-brand {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  margin-bottom: 1.25rem;
}

.login-logo {
  flex-shrink: 0;
}

.login-title {
  font-size: 1.375rem;
  font-weight: 800;
  color: #111827;
  margin: 0;
  line-height: 1.2;
}

.login-subtitle {
  font-size: 0.8rem;
  color: #6b7280;
  margin: 0;
  font-weight: 500;
}

.login-heading {
  font-size: 1.125rem;
  font-weight: 700;
  color: #111827;
  margin: 1.25rem 0 0.25rem 0;
}

.login-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 1.5rem;
}

.login-submit {
  width: 100%;
  margin-top: 0.5rem;
  letter-spacing: 0.02em;
}

@media (max-width: 480px) {
  .login-card {
    padding: 2rem 1.5rem;
  }
}
</style>
