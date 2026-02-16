<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import enrollmentService from '@/services/enrollmentService'

const auth = useAuthStore()

const enrolledCourses = ref([])
const loading = ref(false)
const errorMessage = ref('')

const userInitial = computed(() => {
  const name = auth.user?.name || auth.user?.email || 'A'
  return name.charAt(0).toUpperCase()
})

async function fetchCourses() {
  loading.value = true
  try {
    const res = await enrollmentService.getMyCourses()
    enrolledCourses.value = res.data.data
  } catch {
    enrolledCourses.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchCourses)
</script>

<template>
  <div class="student-dashboard">
    <div class="welcome-card">
      <div class="welcome-bg-blob" aria-hidden="true"></div>
      <div class="welcome-content">
        <div class="welcome-left">
          <div class="avatar avatar-lg welcome-avatar">{{ userInitial }}</div>
          <div>
            <h1 class="welcome-title">Ola, {{ auth.user?.name?.split(' ')[0] || 'Aluno' }}!</h1>
            <p class="welcome-subtitle">Bem-vindo de volta a plataforma Prof. Jubilut</p>
          </div>
        </div>
        <div class="welcome-stat">
          <div class="welcome-stat-value">{{ enrolledCourses.length }}</div>
          <div class="welcome-stat-label">curso(s) ativo(s)</div>
        </div>
      </div>
    </div>

    <div class="section">
      <div class="section-header">
        <h2 class="section-title">Meus Cursos</h2>
        <span class="badge badge-primary">{{ enrolledCourses.length }}</span>
      </div>

      <div v-if="loading" class="loading-container">
        <div class="spinner"></div>
        <span>Carregando cursos...</span>
      </div>

      <div v-else-if="enrolledCourses.length > 0" class="courses-grid">
        <div v-for="course in enrolledCourses" :key="course.id" class="course-card card">
          <div class="course-card-icon">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
            </svg>
          </div>
          <div class="course-card-body">
            <h3 class="course-card-title">{{ course.title }}</h3>
            <span class="badge badge-success">Matriculado</span>
          </div>
        </div>
      </div>

      <div v-else class="empty-state card">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
        </svg>
        <p class="empty-state-text">Voce ainda nao esta matriculado em nenhum curso.<br>Entre em contato com a administracao.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.student-dashboard {
  animation: fadeIn 0.3s ease;
}

.welcome-card {
  position: relative;
  background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
  border-radius: 16px;
  padding: 2rem;
  margin-bottom: 2rem;
  overflow: hidden;
  box-shadow: 0 8px 32px rgba(99, 102, 241, 0.35);
}

.welcome-bg-blob {
  position: absolute;
  top: -60px;
  right: -60px;
  width: 200px;
  height: 200px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 50%;
  pointer-events: none;
}

.welcome-content {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
}

.welcome-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.welcome-avatar {
  background: rgba(255, 255, 255, 0.2);
  color: #fff;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
  flex-shrink: 0;
}

.welcome-title {
  font-size: 1.5rem;
  font-weight: 800;
  color: #fff;
  margin: 0;
}

.welcome-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.75);
  margin: 0.25rem 0 0;
}

.welcome-stat {
  text-align: center;
  background: rgba(255, 255, 255, 0.15);
  padding: 0.875rem 1.5rem;
  border-radius: 12px;
  flex-shrink: 0;
}

.welcome-stat-value {
  font-size: 2rem;
  font-weight: 800;
  color: #fff;
  line-height: 1;
}

.welcome-stat-label {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.75);
  margin-top: 0.25rem;
}




.section-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.section-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-gray-800);
}

.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1rem;
}

.course-card {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  transition: box-shadow 0.2s, transform 0.2s;
}

.course-card:hover {
  box-shadow: var(--shadow-lg);
  transform: translateY(-2px);
}

.course-card-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  background: var(--color-primary-subtle);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.course-card-body {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
}

.course-card-title {
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--color-gray-800);
  line-height: 1.3;
}

@media (max-width: 640px) {
  .welcome-content {
    flex-direction: column;
    align-items: flex-start;
  }

  .welcome-stat {
    width: 100%;
  }

  .welcome-left {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
