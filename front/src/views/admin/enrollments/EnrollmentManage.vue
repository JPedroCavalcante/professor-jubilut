<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import enrollmentService from '@/services/enrollmentService'
import courseService from '@/services/courseService'
import studentService from '@/services/studentService'

const route = useRoute()
const router = useRouter()
const studentId = route.params.id

const student = ref(null)
const enrolledCourses = ref([])
const allCourses = ref([])
const selectedCourseId = ref('')
const loading = ref(false)
const enrolling = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const availableCourses = computed(() => {
  const enrolledIds = enrolledCourses.value.map((c) => c.id)
  return allCourses.value.filter((c) => !enrolledIds.includes(c.id))
})

async function fetchData() {
  loading.value = true
  try {
    const [studentRes, coursesRes, enrolledRes] = await Promise.all([
      studentService.get(studentId),
      courseService.list(),
      enrollmentService.getStudentCourses(studentId),
    ])
    student.value = studentRes.data
    allCourses.value = coursesRes.data
    enrolledCourses.value = enrolledRes.data
  } catch (error) {
    errorMessage.value = 'Erro ao carregar dados.'
  } finally {
    loading.value = false
  }
}

async function enrollCourse() {
  if (!selectedCourseId.value) return
  enrolling.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    await enrollmentService.enroll(studentId, selectedCourseId.value)
    successMessage.value = 'Matricula realizada com sucesso!'
    selectedCourseId.value = ''
    const res = await enrollmentService.getStudentCourses(studentId)
    enrolledCourses.value = res.data
    setTimeout(() => { successMessage.value = '' }, 3000)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Erro ao matricular.'
  } finally {
    enrolling.value = false
  }
}

async function unenrollCourse(courseId) {
  if (!confirm('Remover esta matricula?')) return
  errorMessage.value = ''
  try {
    await enrollmentService.unenroll(studentId, courseId)
    enrolledCourses.value = enrolledCourses.value.filter((c) => c.id !== courseId)
  } catch (error) {
    errorMessage.value = 'Erro ao remover matricula.'
  }
}

onMounted(fetchData)
</script>

<template>
  <div>

    <div class="page-header">
      <div>
        <h1 class="page-title">Matriculas</h1>
        <p class="page-subtitle" v-if="student">
          Gerenciando matriculas de <strong>{{ student.name }}</strong>
        </p>
      </div>
      <button class="btn btn-ghost" @click="router.push({ name: 'admin-students' })">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
        Voltar para Alunos
      </button>
    </div>


    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <span>Carregando dados...</span>
    </div>

    <template v-else>

      <div v-if="errorMessage" class="alert alert-error">
        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="flex-shrink:0;margin-top:1px">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        {{ errorMessage }}
      </div>
      <div v-if="successMessage" class="alert alert-success">
        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="flex-shrink:0;margin-top:1px">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ successMessage }}
      </div>


      <div v-if="student" class="student-info-card card">
        <div class="avatar avatar-lg">{{ student.name.charAt(0).toUpperCase() }}</div>
        <div class="student-info-body">
          <div class="student-info-name">{{ student.name }}</div>
          <div class="student-info-email">{{ student.email }}</div>
          <div class="student-info-meta">
            <span class="badge badge-primary">{{ enrolledCourses.length }} curso(s) matriculado(s)</span>
          </div>
        </div>
      </div>


      <div class="enroll-section card">
        <h2 class="enroll-title">Adicionar Matricula</h2>

        <div v-if="availableCourses.length === 0" class="alert alert-info">
          <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="flex-shrink:0;margin-top:1px">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
          </svg>
          O aluno ja esta matriculado em todos os cursos disponíveis.
        </div>

        <div v-else class="enroll-form">
          <select v-model="selectedCourseId" class="form-control enroll-select" :disabled="enrolling">
            <option value="" disabled>Selecione um curso para matricular...</option>
            <option v-for="course in availableCourses" :key="course.id" :value="course.id">
              {{ course.title }}
            </option>
          </select>
          <button
            class="btn btn-success"
            :class="{ 'btn-loading': enrolling }"
            @click="enrollCourse"
            :disabled="!selectedCourseId || enrolling"
          >
            <svg v-if="!enrolling" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            {{ enrolling ? '' : 'Matricular' }}
          </button>
        </div>
      </div>


      <div class="enrolled-section card">
        <div class="enrolled-header">
          <h2 class="enrolled-title">Cursos Matriculados</h2>
          <span class="badge badge-gray">{{ enrolledCourses.length }}</span>
        </div>

        <div v-if="enrolledCourses.length > 0" class="chips-container">
          <div v-for="course in enrolledCourses" :key="course.id" class="chip">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
            </svg>
            {{ course.title }}
            <button
              class="chip-remove"
              @click="unenrollCourse(course.id)"
              :aria-label="`Remover matricula em ${course.title}`"
              title="Remover matricula"
            >
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
        </div>

        <div v-else class="empty-state" style="padding:2rem 1rem;">
          <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
          </svg>
          <p class="empty-state-text">Nenhuma matricula encontrada.<br>Adicione um curso acima.</p>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>

.student-info-card {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  margin-bottom: 1.25rem;
}

.student-info-body {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.student-info-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--color-gray-900);
}

.student-info-email {
  font-size: 0.875rem;
  color: var(--color-gray-500);
}

.student-info-meta {
  margin-top: 0.25rem;
}


.enroll-section {
  margin-bottom: 1.25rem;
}

.enroll-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-gray-800);
  margin-bottom: 1rem;
}

.enroll-form {
  display: flex;
  gap: 0.75rem;
  align-items: flex-end;
}

.enroll-select {
  flex: 1;
}




.enrolled-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.enrolled-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-gray-800);
}


.chips-container {
  display: flex;
  flex-wrap: wrap;
  gap: 0.625rem;
}

@media (max-width: 640px) {
  .enroll-form {
    flex-direction: column;
  }

  .enroll-form .btn {
    width: 100%;
  }

  .student-info-card {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
