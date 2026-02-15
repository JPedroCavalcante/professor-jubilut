<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import subjectService from '@/services/subjectService'
import courseService from '@/services/courseService'
import professorService from '@/services/professorService'

const router = useRouter()
const route = useRoute()

const isEdit = ref(false)
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const courses = ref([])
const professors = ref([])

const form = ref({
  title: '',
  description: '',
  course_id: '',
  professor_id: '',
})

onMounted(async () => {
  loading.value = true
  try {
    const [coursesRes, professorsRes] = await Promise.all([
      courseService.list(),
      professorService.list(),
    ])
    courses.value = coursesRes.data
    professors.value = professorsRes.data

    if (route.params.id) {
      isEdit.value = true
      const response = await subjectService.get(route.params.id)
      const subject = response.data
      form.value = {
        title: subject.title,
        description: subject.description || '',
        course_id: subject.course_id,
        professor_id: subject.professor_id,
      }
    }
  } catch (error) {
    errorMessage.value = 'Erro ao carregar dados.'
  } finally {
    loading.value = false
  }
})

async function handleSubmit() {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    if (isEdit.value) {
      await subjectService.update(route.params.id, form.value)
      successMessage.value = 'Disciplina atualizada com sucesso!'
    } else {
      await subjectService.create(form.value)
      successMessage.value = 'Disciplina criada com sucesso!'
    }
    setTimeout(() => router.push({ name: 'admin-subjects' }), 1200)
  } catch (error) {
    if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors).flat().join(' ')
    } else {
      errorMessage.value = error.response?.data?.message || 'Erro ao salvar disciplina.'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div>
    <div class="page-header">
      <div>
        <h1 class="page-title">{{ isEdit ? 'Editar Disciplina' : 'Nova Disciplina' }}</h1>
        <p class="page-subtitle">{{ isEdit ? 'Atualize os dados da disciplina' : 'Cadastre uma nova disciplina' }}</p>
      </div>
      <button class="btn btn-ghost" @click="router.push({ name: 'admin-subjects' })">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
        Voltar
      </button>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <span>Carregando...</span>
    </div>

    <form v-else class="form-card" @submit.prevent="handleSubmit" novalidate>
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

      <div class="form-group">
        <label for="title" class="form-label">Titulo <span style="color:var(--color-danger)">*</span></label>
        <input
          id="title"
          v-model="form.title"
          type="text"
          class="form-control"
          required
          placeholder="Ex: Anatomia Humana"
          :disabled="saving"
        />
      </div>

      <div class="form-group">
        <label for="description" class="form-label">Descricao</label>
        <textarea
          id="description"
          v-model="form.description"
          class="form-control"
          rows="3"
          placeholder="Descricao opcional..."
          :disabled="saving"
        ></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="course_id" class="form-label">Curso <span style="color:var(--color-danger)">*</span></label>
          <select
            id="course_id"
            v-model="form.course_id"
            class="form-control"
            required
            :disabled="saving"
          >
            <option value="" disabled>Selecione um curso</option>
            <option v-for="course in courses" :key="course.id" :value="course.id">
              {{ course.title }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label for="professor_id" class="form-label">Professor <span style="color:var(--color-danger)">*</span></label>
          <select
            id="professor_id"
            v-model="form.professor_id"
            class="form-control"
            required
            :disabled="saving"
          >
            <option value="" disabled>Selecione um professor</option>
            <option v-for="professor in professors" :key="professor.id" :value="professor.id">
              {{ professor.name }}
            </option>
          </select>
        </div>
      </div>

      <div class="form-actions">
        <button
          type="submit"
          class="btn btn-primary"
          :class="{ 'btn-loading': saving }"
          :disabled="saving"
        >
          {{ saving ? '' : (isEdit ? 'Atualizar Disciplina' : 'Criar Disciplina') }}
        </button>
        <button
          type="button"
          class="btn btn-ghost"
          @click="router.push({ name: 'admin-subjects' })"
          :disabled="saving"
        >
          Cancelar
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 0.5rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--color-gray-200);
}
</style>
