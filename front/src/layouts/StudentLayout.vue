<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

const mobileMenuOpen = ref(false)

const userInitial = computed(() => {
  const name = auth.user?.name || auth.user?.email || 'U'
  return name.charAt(0).toUpperCase()
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div class="student-layout">
    <header class="student-navbar" role="banner">
      <div class="navbar-inner">
        <div class="navbar-brand">
          <div class="navbar-logo" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="32" height="32" rx="10" fill="url(#sn-grad)"/>
              <path d="M8 22V10l8 5 8-5v12l-8-4-8 4z" fill="white" opacity="0.9"/>
              <defs>
                <linearGradient id="sn-grad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                  <stop stop-color="#818cf8"/>
                  <stop offset="1" stop-color="#a78bfa"/>
                </linearGradient>
              </defs>
            </svg>
          </div>
          <span class="navbar-brand-name">Prof. Jubilut</span>
        </div>

        <nav class="navbar-nav" aria-label="Navegacao do aluno">
          <RouterLink to="/student/dashboard" class="navbar-link" active-class="is-active">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
            </svg>
            Dashboard
          </RouterLink>
          <RouterLink to="/student/profile" class="navbar-link" active-class="is-active">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Meu Perfil
          </RouterLink>
        </nav>

        <div class="navbar-user">
          <span class="navbar-user-name">{{ auth.user?.name }}</span>
          <div class="avatar navbar-avatar">{{ userInitial }}</div>
          <button class="navbar-logout-btn" @click="handleLogout" title="Sair">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span class="sr-only">Sair</span>
          </button>

          <button
            class="navbar-mobile-btn"
            @click="mobileMenuOpen = !mobileMenuOpen"
            aria-label="Abrir menu"
          >
            <svg v-if="!mobileMenuOpen" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
            <svg v-else width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
      </div>

      <div v-if="mobileMenuOpen" class="navbar-mobile-menu">
        <RouterLink to="/student/dashboard" class="mobile-link" @click="mobileMenuOpen = false">
          Dashboard
        </RouterLink>
        <RouterLink to="/student/profile" class="mobile-link" @click="mobileMenuOpen = false">
          Meu Perfil
        </RouterLink>
        <button class="mobile-link mobile-logout" @click="handleLogout">
          Sair
        </button>
      </div>
    </header>

    <main class="student-content" role="main">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.student-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: var(--bg-page);
}

.student-navbar {
  background: linear-gradient(90deg, var(--sidebar-bg-from) 0%, #1e1b4b 50%, #312e81 100%);
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
  position: sticky;
  top: 0;
  z-index: 40;
  box-shadow: 0 2px 16px rgba(0, 0, 0, 0.3);
}

.navbar-inner {
  display: flex;
  align-items: center;
  height: var(--navbar-height);
  padding: 0 1.5rem;
  gap: 1.5rem;
}

.navbar-brand {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  flex-shrink: 0;
}

.navbar-logo {
  flex-shrink: 0;
}

.navbar-brand-name {
  font-size: 1rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.02em;
}

.navbar-nav {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  flex: 1;
}

.navbar-link {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.875rem;
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.65);
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  transition: background 0.15s ease, color 0.15s ease;
}

.navbar-link:hover {
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.95);
}

.navbar-link.is-active {
  background: rgba(99, 102, 241, 0.3);
  color: #c7d2fe;
}

.navbar-user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-left: auto;
}

.navbar-user-name {
  font-size: 0.8rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.7);
}

.navbar-avatar {
  width: 32px;
  height: 32px;
  font-size: 0.75rem;
}

.navbar-logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: transparent;
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.6);
  cursor: pointer;
  transition: background 0.15s, color 0.15s;
}

.navbar-logout-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.35);
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
}

.navbar-mobile-btn {
  display: none;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
}

.navbar-mobile-menu {
  display: flex;
  flex-direction: column;
  padding: 0.5rem 1rem 1rem;
  gap: 0.25rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.mobile-link {
  display: block;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.9rem;
  font-weight: 500;
  text-decoration: none;
  transition: background 0.15s;
  background: transparent;
  border: none;
  text-align: left;
  cursor: pointer;
  font-family: inherit;
  width: 100%;
}

.mobile-link:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #fff;
}

.mobile-logout {
  color: #fca5a5;
}

.student-content {
  flex: 1;
  padding: 2rem;
}

@media (max-width: 640px) {
  .navbar-nav {
    display: none;
  }

  .navbar-user-name {
    display: none;
  }

  .navbar-logout-btn {
    display: none;
  }

  .navbar-mobile-btn {
    display: flex;
  }

  .student-content {
    padding: 1.25rem 1rem;
  }
}
</style>
