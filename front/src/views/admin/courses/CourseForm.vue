<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import courseService from '@/services/courseService'

const router = useRouter()
const route = useRoute()

const isEdit = ref(false)
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  title: '',
  description: '',
  start_date: '',
  end_date: '',
})

onMounted(async () => {
  if (route.params.id) {
    isEdit.value = true
    loading.value = true
    try {
      const response = await courseService.get(route.params.id)
      const course = response.data
      form.value = {
        title: course.title,
        description: course.description || '',
        start_date: course.start_date ? course.start_date.substring(0, 10) : '',
        end_date: course.end_date ? course.end_date.substring(0, 10) : '',
      }
    } catch (error) {
      errorMessage.value = 'Erro ao carregar curso.'
    } finally {
      loading.value = false
    }
  }
})

async function handleSubmit() {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    if (isEdit.value) {
      await courseService.update(route.params.id, form.value)
      successMessage.value = 'Curso atualizado com sucesso!'
    } else {
      await courseService.create(form.value)
      successMessage.value = 'Curso criado com sucesso!'
    }
    setTimeout(() => router.push({ name: 'admin-courses' }), 1200)
  } catch (error) {
    if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors).flat().join(' ')
    } else {
      errorMessage.value = error.response?.data?.message || 'Erro ao salvar curso.'
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
        <h1 class="page-title">{{ isEdit ? 'Editar Curso' : 'Novo Curso' }}</h1>
        <p class="page-subtitle">{{ isEdit ? 'Atualize os dados do curso' : 'Preencha as informacoes do novo curso' }}</p>
      </div>
      <button class="btn btn-ghost" @click="router.push({ name: 'admin-courses' })">
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
          placeholder="Ex: Medicina 2024"
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
          placeholder="Descricao opcional do curso..."
          :disabled="saving"
        ></textarea>
      </div>


      <div class="form-row">
        <div class="form-group">
          <label for="start_date" class="form-label">Data de Inicio <span style="color:var(--color-danger)">*</span></label>
          <input
            id="start_date"
            v-model="form.start_date"
            type="date"
            class="form-control"
            required
            :disabled="saving"
          />
        </div>
        <div class="form-group">
          <label for="end_date" class="form-label">Data de Fim <span style="color:var(--color-danger)">*</span></label>
          <input
            id="end_date"
            v-model="form.end_date"
            type="date"
            class="form-control"
            required
            :disabled="saving"
          />
        </div>
      </div>

      <div class="form-actions">
        <button
          type="submit"
          class="btn btn-primary"
          :class="{ 'btn-loading': saving }"
          :disabled="saving"
        >
          {{ saving ? '' : (isEdit ? 'Atualizar Curso' : 'Criar Curso') }}
        </button>
        <button
          type="button"
          class="btn btn-ghost"
          @click="router.push({ name: 'admin-courses' })"
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
