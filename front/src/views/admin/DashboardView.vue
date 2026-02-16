<script setup>
import { ref, onMounted, computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js'
import api from '@/services/api'
import courseService from '@/services/courseService'
import professorService from '@/services/professorService'
import studentService from '@/services/studentService'
import subjectService from '@/services/subjectService'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const report = ref([])
const loading = ref(false)
const errorMessage = ref('')

const totalCourses = ref(null)
const totalProfessors = ref(null)
const totalStudents = ref(null)
const totalSubjects = ref(null)

const chartData = computed(() => ({
  labels: report.value.map((r) => r.course_title),
  datasets: [
    {
      label: 'Media de Idade',
      backgroundColor: (ctx) => {
        const chart = ctx.chart
        const { ctx: c, chartArea } = chart
        if (!chartArea) return '#6366f1'
        const gradient = c.createLinearGradient(0, chartArea.bottom, 0, chartArea.top)
        gradient.addColorStop(0, 'rgba(99,102,241,0.6)')
        gradient.addColorStop(1, 'rgba(139,92,246,0.9)')
        return gradient
      },
      borderRadius: 6,
      borderSkipped: false,
      data: report.value.map((r) => r.avg_age),
    },
  ],
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    title: { display: false },
    tooltip: {
      backgroundColor: '#1e1b4b',
      padding: 10,
      titleColor: '#c7d2fe',
      bodyColor: '#e0e7ff',
      callbacks: {
        label: (ctx) => ` ${ctx.parsed.y} anos (media)`,
      },
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: {
        color: '#6b7280',
        font: { size: 12, family: 'Inter, sans-serif' },
      },
    },
    y: {
      beginAtZero: true,
      grid: {
        color: 'rgba(107, 114, 128, 0.12)',
        drawBorder: false,
      },
      ticks: {
        color: '#6b7280',
        font: { size: 12, family: 'Inter, sans-serif' },
        callback: (val) => `${val} anos`,
      },
    },
  },
}

const coursesWithStudents = computed(() =>
  report.value.filter((r) => r.total_students > 0)
)

async function fetchAll() {
  loading.value = true
  errorMessage.value = ''
  try {
    const [reportRes, coursesRes, professorsRes, studentsRes, subjectsRes] = await Promise.all([
      api.get('/admin/reports/intelligence'),
      courseService.list(),
      professorService.list(),
      studentService.list(),
      subjectService.list(),
    ])
    report.value = reportRes.data
    totalCourses.value = coursesRes.data.meta?.total ?? coursesRes.data.data?.length ?? 0
    totalProfessors.value = Array.isArray(professorsRes.data) ? professorsRes.data.length : 0
    totalStudents.value = studentsRes.data.meta?.total ?? studentsRes.data.data?.length ?? 0
    totalSubjects.value = Array.isArray(subjectsRes.data) ? subjectsRes.data.length : 0
  } catch (error) {
    errorMessage.value = 'Erro ao carregar dados do dashboard.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchAll)
</script>

<template>
  <div class="dashboard">

    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard</h1>
        <p class="page-subtitle">Visao geral da plataforma</p>
      </div>
    </div>


    <div v-if="errorMessage" class="alert alert-error">
      <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" style="flex-shrink:0;margin-top:1px">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      {{ errorMessage }}
    </div>


    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <span>Carregando dados...</span>
    </div>

    <template v-if="!loading">

      <div class="stats-grid">

        <div class="stat-card">
          <div class="stat-icon stat-icon-primary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ totalCourses ?? '—' }}</div>
            <div class="stat-label">Cursos</div>
          </div>
        </div>


        <div class="stat-card">
          <div class="stat-icon stat-icon-secondary">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ totalProfessors ?? '—' }}</div>
            <div class="stat-label">Professores</div>
          </div>
        </div>


        <div class="stat-card">
          <div class="stat-icon stat-icon-success">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ totalStudents ?? '—' }}</div>
            <div class="stat-label">Alunos</div>
          </div>
        </div>


        <div class="stat-card">
          <div class="stat-icon stat-icon-warning">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
          </div>
          <div class="stat-body">
            <div class="stat-value">{{ totalSubjects ?? '—' }}</div>
            <div class="stat-label">Disciplinas</div>
          </div>
        </div>
      </div>


      <template v-if="report.length > 0">
        <div class="chart-section card">
          <div class="chart-header">
            <h2 class="chart-title">Media de Idade por Curso</h2>
            <span class="badge badge-primary">{{ report.length }} curso(s)</span>
          </div>
          <div class="chart-wrapper">
            <Bar :data="chartData" :options="chartOptions" />
          </div>
        </div>


        <div v-if="coursesWithStudents.length > 0">
          <h2 class="section-title">Detalhes por Curso</h2>
          <div class="courses-grid">
            <div
              v-for="item in coursesWithStudents"
              :key="item.course_id"
              class="course-report-card card"
            >
              <div class="course-report-header">
                <h3 class="course-report-title">{{ item.course_title }}</h3>
                <span class="badge badge-primary">{{ item.total_students }} aluno(s)</span>
              </div>

              <div class="course-report-stat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Media de idade: <strong>{{ item.avg_age }} anos</strong>
              </div>

              <hr class="divider" style="margin: 0.75rem 0;" />

              <div v-if="item.youngest" class="age-badge age-badge--youngest">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <polyline points="18 15 12 9 6 15"/>
                </svg>
                Mais novo: <strong>{{ item.youngest.name }}</strong> — {{ item.youngest.age }} anos
              </div>

              <div v-if="item.oldest" class="age-badge age-badge--oldest">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
                Mais velho: <strong>{{ item.oldest.name }}</strong> — {{ item.oldest.age }} anos
              </div>
            </div>
          </div>
        </div>

        <div v-else class="empty-state">
          <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
          </svg>
          <p class="empty-state-text">Nenhum curso possui alunos matriculados ainda.</p>
        </div>
      </template>

      <div v-else-if="!loading" class="empty-state">
        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
        </svg>
        <p class="empty-state-text">Nenhum dado disponivel para o relatorio.</p>
      </div>
    </template>
  </div>
</template>

<style scoped>
.dashboard {
  animation: fadeIn 0.3s ease;
}


.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.75rem;
}

@media (max-width: 1024px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}


.chart-section {
  margin-bottom: 1.75rem;
}

.chart-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.chart-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--color-gray-800);
}

.chart-wrapper {
  height: 280px;
  position: relative;
}


.section-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: var(--color-gray-800);
  margin-bottom: 1rem;
}


.courses-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.course-report-card {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.course-report-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.5rem;
}

.course-report-title {
  font-size: 0.95rem;
  font-weight: 700;
  color: var(--color-gray-800);
  line-height: 1.3;
}

.course-report-stat {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 0.8rem;
  color: var(--color-gray-600);
}


.age-badge {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  font-size: 0.8rem;
}

.age-badge--youngest {
  background: var(--color-success-light);
  color: var(--color-success-dark);
}

.age-badge--oldest {
  background: var(--color-warning-light);
  color: var(--color-warning-dark);
}
</style>
