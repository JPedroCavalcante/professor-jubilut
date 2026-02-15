<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import studentService from '@/services/studentService'

const router = useRouter()
const students = ref([])
const pagination = ref({})
const loading = ref(false)
const errorMessage = ref('')
const filterName = ref('')
const filterEmail = ref('')

async function fetchStudents(page = 1) {
  loading.value = true
  errorMessage.value = ''
  try {
    const response = await studentService.list({
      name: filterName.value,
      email: filterEmail.value,
      page: page
    })
    students.value = response.data.data
    pagination.value = response.data.meta
  } catch (error) {
    errorMessage.value = 'Erro ao carregar alunos.'
  } finally {
    loading.value = false
  }
}

function changePage(page) {
  if (page < 1 || page > pagination.value.last_page) return
  fetchStudents(page)
}

async function deleteStudent(id) {
  if (!confirm('Tem certeza que deseja excluir este aluno?')) return
  try {
    await studentService.delete(id)
    fetchStudents(pagination.value.current_page) // Refresh current page
  } catch (error) {
    errorMessage.value = 'Erro ao excluir aluno.'
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleDateString('pt-BR')
}

onMounted(() => fetchStudents(1))
</script>

<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">Alunos</h1>
        <p class="page-subtitle">Gerencie o cadastro de alunos</p>
      </div>
      <button class="btn btn-primary" @click="router.push({ name: 'admin-students-create' })">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Novo Aluno
      </button>
    </div>

    <div class="filter-bar">
      <input
        v-model="filterName"
        type="text"
        class="form-control"
        placeholder="Filtrar por nome..."
        @keyup.enter="fetchStudents(1)"
      />
      <input
        v-model="filterEmail"
        type="text"
        class="form-control"
        placeholder="Filtrar por e-mail..."
        @keyup.enter="fetchStudents(1)"
      />
      <button class="btn btn-primary" @click="fetchStudents(1)">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        Buscar
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
      <span>Carregando alunos...</span>
    </div>

    <div v-else class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Aluno</th>
            <th>E-mail</th>
            <th>Nascimento</th>
            <th>Acoes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="student in students" :key="student.id">
            <td style="color:var(--color-gray-400);font-size:0.75rem;">{{ student.id }}</td>
            <td>
              <div style="display:flex;align-items:center;gap:0.625rem;">
                <div class="avatar" style="width:32px;height:32px;font-size:0.75rem;flex-shrink:0;">
                  {{ student.name.charAt(0).toUpperCase() }}
                </div>
                <span style="font-weight:600;color:var(--color-gray-800);">{{ student.name }}</span>
              </div>
            </td>
            <td style="color:var(--color-gray-600);">{{ student.email }}</td>
            <td>
              <span class="badge badge-gray">{{ formatDate(student.birth_date) }}</span>
            </td>
            <td class="actions">
              <button
                class="btn btn-sm"
                style="background:var(--color-success-light);color:var(--color-success-dark);border:none;"
                @click="router.push({ name: 'admin-enrollments', params: { id: student.id } })"
                title="Gerenciar matriculas"
              >
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
                Matriculas
              </button>

              <button
                class="btn btn-sm"
                style="background:var(--color-info-light);color:var(--color-info-dark);border:none;"
                @click="router.push({ name: 'admin-students-edit', params: { id: student.id } })"
              >
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Editar
              </button>

              <button
                class="btn btn-sm"
                style="background:var(--color-danger-light);color:var(--color-danger-dark);border:none;"
                @click="deleteStudent(student.id)"
              >
                 <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                </svg>
                Excluir
              </button>
            </td>
          </tr>
          <tr v-if="students.length === 0">
            <td colspan="5">
              <div class="empty-state">
                <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
                <p class="empty-state-text">Nenhum aluno encontrado.</p>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination Controls -->
    <div v-if="pagination.last_page > 1" class="pagination-controls" style="display:flex;justify-content:center;gap:10px;margin-top:20px;">
        <button 
            class="btn btn-sm" 
            :disabled="pagination.current_page === 1"
            @click="changePage(pagination.current_page - 1)"
        >
            Anterior
        </button>
        <span style="display:flex;align-items:center;">
            Página {{ pagination.current_page }} de {{ pagination.last_page }}
        </span>
        <button 
            class="btn btn-sm" 
            :disabled="pagination.current_page === pagination.last_page"
            @click="changePage(pagination.current_page + 1)"
        >
            Próxima
        </button>
    </div>
  </div>
</template>
