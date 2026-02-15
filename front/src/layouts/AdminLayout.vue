<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter, useRoute } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()

const sidebarCollapsed = ref(false)

const userInitial = computed(() => {
  const name = auth.user?.name || auth.user?.email || 'U'
  return name.charAt(0).toUpperCase()
})

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

const navItems = [
  {
    to: '/admin/dashboard',
    label: 'Dashboard',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
  },
  {
    to: '/admin/courses',
    label: 'Cursos',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>`,
  },
  {
    to: '/admin/professors',
    label: 'Professores',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>`,
  },
  {
    to: '/admin/subjects',
    label: 'Disciplinas',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>`,
  },
  {
    to: '/admin/students',
    label: 'Alunos',
    icon: `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>`,
  },
]

function isActive(to) {
  if (to === '/admin/dashboard') {
    return route.path === to
  }
  return route.path.startsWith(to)
}
</script>

<template>
  <div class="admin-layout" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
    <aside class="sidebar" role="navigation" aria-label="Menu principal">
      <div class="sidebar-header">
        <div class="sidebar-brand">
          <div class="sidebar-logo" aria-hidden="true">
            <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
              <rect width="32" height="32" rx="10" fill="url(#sl-grad)"/>
              <path d="M8 22V10l8 5 8-5v12l-8-4-8 4z" fill="white" opacity="0.9"/>
              <defs>
                <linearGradient id="sl-grad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                  <stop stop-color="#818cf8"/>
                  <stop offset="1" stop-color="#a78bfa"/>
                </linearGradient>
              </defs>
            </svg>
          </div>
          <span class="sidebar-brand-name">Prof. Jubilut</span>
        </div>
      </div>

      <nav class="sidebar-nav">
        <p class="sidebar-nav-label">Menu</p>
        <RouterLink
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="sidebar-nav-item"
          :class="{ 'is-active': isActive(item.to) }"
        >
          <span class="sidebar-nav-icon" v-html="item.icon" aria-hidden="true"></span>
          <span class="sidebar-nav-label-text">{{ item.label }}</span>
        </RouterLink>
      </nav>

      <div class="sidebar-footer">
        <div class="sidebar-user">
          <div class="avatar">{{ userInitial }}</div>
          <div class="sidebar-user-info">
            <span class="sidebar-user-name">{{ auth.user?.name || auth.user?.email }}</span>
            <span class="sidebar-user-role">Administrador</span>
          </div>
        </div>
        <button class="sidebar-logout-btn" @click="handleLogout" title="Sair">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </button>
      </div>
    </aside>

    <div class="admin-content-wrapper">
      <header class="admin-topbar">
        <button class="topbar-menu-btn" @click="sidebarCollapsed = !sidebarCollapsed" aria-label="Toggle menu">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
        <span class="topbar-brand">Prof. Jubilut</span>
        <div class="avatar topbar-avatar">{{ userInitial }}</div>
      </header>

      <main class="admin-content" role="main">
        <RouterView />
      </main>
    </div>

    <div
      v-if="!sidebarCollapsed"
      class="sidebar-overlay"
      @click="sidebarCollapsed = true"
      aria-hidden="true"
    ></div>
  </div>
</template>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
  background: var(--bg-page);
}

.sidebar {
  width: var(--sidebar-width);
  min-height: 100vh;
  background: linear-gradient(180deg, var(--sidebar-bg-from) 0%, var(--sidebar-bg-to) 100%);
  display: flex;
  flex-direction: column;
  flex-shrink: 0;
  position: fixed;
  top: 0;
  left: 0;
  height: 100%;
  z-index: 50;
  transition: transform 0.3s ease;
  border-right: 1px solid rgba(255, 255, 255, 0.05);
}

.sidebar-header {
  padding: 1.5rem 1.25rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.sidebar-logo {
  flex-shrink: 0;
}

.sidebar-brand-name {
  font-size: 1.125rem;
  font-weight: 800;
  color: #fff;
  letter-spacing: -0.02em;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem 0.875rem;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
  overflow-y: auto;
}

.sidebar-nav-label {
  font-size: 0.65rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.35);
  padding: 0 0.5rem;
  margin: 0 0 0.5rem;
}

.sidebar-nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 0.75rem;
  border-radius: 8px;
  color: rgba(255, 255, 255, 0.65);
  font-size: 0.875rem;
  font-weight: 500;
  text-decoration: none;
  transition: background 0.15s ease, color 0.15s ease;
  white-space: nowrap;
}

.sidebar-nav-item:hover {
  background: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.95);
}

.sidebar-nav-item.is-active {
  background: rgba(99, 102, 241, 0.3);
  color: #c7d2fe;
  box-shadow: inset 3px 0 0 #818cf8;
}

.sidebar-nav-icon {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.sidebar-nav-label-text {
  flex: 1;
}

.sidebar-footer {
  padding: 1rem 0.875rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sidebar-user {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  flex: 1;
  min-width: 0;
}

.sidebar-user-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.sidebar-user-name {
  font-size: 0.8rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.sidebar-user-role {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.45);
}

.sidebar-logout-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: transparent;
  color: rgba(255, 255, 255, 0.55);
  cursor: pointer;
  flex-shrink: 0;
  transition: background 0.15s ease, color 0.15s ease;
}

.sidebar-logout-btn:hover {
  background: rgba(239, 68, 68, 0.2);
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.3);
}

.admin-content-wrapper {
  flex: 1;
  margin-left: var(--sidebar-width);
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.admin-content {
  flex: 1;
  padding: 2rem;
}

.admin-topbar {
  display: none;
  align-items: center;
  justify-content: space-between;
  height: var(--navbar-height);
  padding: 0 1rem;
  background: linear-gradient(90deg, var(--sidebar-bg-from) 0%, var(--sidebar-bg-to) 100%);
  position: sticky;
  top: 0;
  z-index: 40;
  border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.topbar-menu-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  border-radius: 8px;
  background: transparent;
  border: none;
  color: rgba(255, 255, 255, 0.8);
  cursor: pointer;
  transition: background 0.15s;
}

.topbar-menu-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.topbar-brand {
  font-size: 1rem;
  font-weight: 800;
  color: #fff;
}

.topbar-avatar {
  width: 34px;
  height: 34px;
  font-size: 0.8rem;
}

.sidebar-overlay {
  display: none;
}

@media (max-width: 768px) {
  .admin-topbar {
    display: flex;
  }

  .admin-content-wrapper {
    margin-left: 0;
  }

  .admin-content {
    padding: 1.25rem 1rem;
  }

  .sidebar {
    transform: translateX(-100%);
  }

  .admin-layout:not(.sidebar-collapsed) .sidebar {
    transform: translateX(0);
  }

  .sidebar-overlay {
    display: block;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 45;
    backdrop-filter: blur(2px);
  }
}

@media (min-width: 769px) {
  .admin-layout.sidebar-collapsed .sidebar {
    transform: translateX(-100%);
  }

  .admin-layout.sidebar-collapsed .admin-content-wrapper {
    margin-left: 0;
  }
}
</style>
