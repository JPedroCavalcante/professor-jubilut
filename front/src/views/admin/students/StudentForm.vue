<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import studentService from '@/services/studentService'

const router = useRouter()
const route = useRoute()

const isEdit = ref(false)
const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  name: '',
  email: '',
  birth_date: '',
  password: '',
})

onMounted(async () => {
  if (route.params.id) {
    isEdit.value = true
    loading.value = true
    try {
      const response = await studentService.get(route.params.id)
      const student = response.data
      form.value = {
        name: student.name,
        email: student.email,
        birth_date: student.birth_date ? student.birth_date.substring(0, 10) : '',
        password: '',
      }
    } catch (error) {
      errorMessage.value = 'Erro ao carregar aluno.'
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
    const data = { ...form.value }
    if (isEdit.value && !data.password) {
      delete data.password
    }
    if (isEdit.value) {
      await studentService.update(route.params.id, data)
      successMessage.value = 'Aluno atualizado com sucesso!'
    } else {
      await studentService.create(data)
      successMessage.value = 'Aluno criado com sucesso!'
    }
    setTimeout(() => router.push({ name: 'admin-students' }), 1200)
  } catch (error) {
    if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors).flat().join(' ')
    } else {
      errorMessage.value = error.response?.data?.message || 'Erro ao salvar aluno.'
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
        <h1 class="page-title">{{ isEdit ? 'Editar Aluno' : 'Novo Aluno' }}</h1>
        <p class="page-subtitle">{{ isEdit ? 'Atualize os dados do aluno' : 'Cadastre um novo aluno na plataforma' }}</p>
      </div>
      <button class="btn btn-ghost" @click="router.push({ name: 'admin-students' })">
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
        <label for="name" class="form-label">Nome <span style="color:var(--color-danger)">*</span></label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          class="form-control"
          required
          placeholder="Nome completo do aluno"
          :disabled="saving"
        />
      </div>

      <div class="form-group">
        <label for="email" class="form-label">E-mail <span style="color:var(--color-danger)">*</span></label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          class="form-control"
          required
          placeholder="email@aluno.com.br"
          :disabled="saving"
        />
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="birth_date" class="form-label">Data de Nascimento</label>
          <input
            id="birth_date"
            v-model="form.birth_date"
            type="date"
            class="form-control"
            :disabled="saving"
          />
        </div>

        <div class="form-group">
          <label for="password" class="form-label">
            Senha
            <span v-if="!isEdit" style="color:var(--color-danger)">*</span>
            <span v-else style="color:var(--color-gray-400);font-weight:400;font-size:0.75rem;"> (deixe em branco para manter)</span>
          </label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-control"
            :required="!isEdit"
            minlength="6"
            placeholder="Minimo 6 caracteres"
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
          {{ saving ? '' : (isEdit ? 'Atualizar Aluno' : 'Criar Aluno') }}
        </button>
        <button
          type="button"
          class="btn btn-ghost"
          @click="router.push({ name: 'admin-students' })"
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
