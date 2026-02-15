<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import courseService from '@/services/courseService'

const router = useRouter()
const courses = ref([])
const loading = ref(false)
const errorMessage = ref('')

async function fetchCourses() {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await courseService.list()
    courses.value = response.data
  } catch (error) {
    errorMessage.value = 'Erro ao carregar cursos.'
  } finally {
    loading.value = false
  }
}

async function deleteCourse(id) {
  if (!confirm('Tem certeza que deseja excluir este curso?')) return
  try {
    await courseService.delete(id)
    courses.value = courses.value.filter((c) => c.id !== id)
  } catch (error) {
    errorMessage.value = 'Erro ao excluir curso.'
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('pt-BR')
}

onMounted(fetchCourses)
</script>

<template>
  <div>

    <div class="page-header">
      <div>
        <h1 class="page-title">Cursos</h1>
        <p class="page-subtitle">Gerencie os cursos da plataforma</p>
      </div>
      <button class="btn btn-primary" @click="router.push({ name: 'admin-courses-create' })">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Novo Curso
      </button>
    </div>


    <div v-if="errorMessage" class="alert alert-error">
      <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="flex-shrink:0;margin-top:1px">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      {{ errorMessage }}
    </div>


    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <span>Carregando cursos...</span>
    </div>


    <div v-else class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Titulo</th>
            <th>Data Inicio</th>
            <th>Data Fim</th>
            <th>Acoes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="course in courses" :key="course.id">
            <td style="color:var(--color-gray-400);font-size:0.75rem;">{{ course.id }}</td>
            <td style="font-weight:600;color:var(--color-gray-800);">{{ course.title }}</td>
            <td>
              <span class="badge badge-gray">{{ formatDate(course.start_date) }}</span>
            </td>
            <td>
              <span class="badge badge-gray">{{ formatDate(course.end_date) }}</span>
            </td>
            <td class="actions">
              <button
                class="btn btn-sm"
                style="background:var(--color-info-light);color:var(--color-info-dark);border:none;"
                @click="router.push({ name: 'admin-courses-edit', params: { id: course.id } })"
              >
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Editar
              </button>
              <button
                class="btn btn-sm"
                style="background:var(--color-danger-light);color:var(--color-danger-dark);border:none;"
                @click="deleteCourse(course.id)"
              >
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
                </svg>
                Excluir
              </button>
            </td>
          </tr>
          <tr v-if="courses.length === 0">
            <td colspan="5">
              <div class="empty-state">
                <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
                </svg>
                <p class="empty-state-text">Nenhum curso encontrado.</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
