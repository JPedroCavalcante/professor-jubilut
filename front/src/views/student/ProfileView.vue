<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import studentService from '@/services/studentService'

const auth = useAuthStore()

const loading = ref(false)
const saving = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  name: '',
  email: '',
  birth_date: '',
})

const userInitial = computed(() => {
  const name = form.value.name || auth.user?.email || 'A'
  return name.charAt(0).toUpperCase()
})

onMounted(async () => {
  loading.value = true
  try {
    const response = await studentService.getProfile()
    const student = response.data
    form.value = {
      name: student.name,
      email: student.email,
      birth_date: student.birth_date ? student.birth_date.substring(0, 10) : '',
    }
  } catch (error) {
    errorMessage.value = 'Erro ao carregar perfil.'
  } finally {
    loading.value = false
  }
})

async function handleSubmit() {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''
  try {
    await studentService.updateProfile(form.value)
    successMessage.value = 'Perfil atualizado com sucesso!'
    setTimeout(() => { successMessage.value = '' }, 4000)
  } catch (error) {
    if (error.response?.data?.errors) {
      errorMessage.value = Object.values(error.response.data.errors).flat().join(' ')
    } else {
      errorMessage.value = error.response?.data?.message || 'Erro ao salvar perfil.'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="profile-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Meu Perfil</h1>
        <p class="page-subtitle">Mantenha seus dados atualizados</p>
      </div>
    </div>

    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <span>Carregando perfil...</span>
    </div>

    <div v-else class="profile-layout">
      <div class="avatar-card card">
        <div class="profile-avatar avatar avatar-lg">{{ userInitial }}</div>
        <div class="profile-name">{{ form.name || 'Aluno' }}</div>
        <div class="profile-email">{{ form.email }}</div>
        <span class="badge badge-success" style="margin-top:0.5rem;">Aluno</span>
      </div>

      <form class="form-card profile-form-card" @submit.prevent="handleSubmit" novalidate>
        <h2 class="form-section-title">Informacoes Pessoais</h2>

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
            placeholder="Seu nome completo"
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
            :disabled="saving"
          />
        </div>

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

        <div class="form-actions">
          <button
            type="submit"
            class="btn btn-primary"
            :class="{ 'btn-loading': saving }"
            :disabled="saving"
          >
            {{ saving ? '' : 'Salvar Alteracoes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.profile-page {
  animation: fadeIn 0.3s ease;
}

.profile-layout {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 1.5rem;
  align-items: start;
}

.avatar-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 0.5rem;
  padding: 2rem 1.5rem;
}

.profile-avatar {
  width: 72px;
  height: 72px;
  font-size: 1.75rem;
  margin-bottom: 0.5rem;
}

.profile-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-gray-800);
}

.profile-email {
  font-size: 0.8rem;
  color: var(--color-gray-500);
  word-break: break-all;
}

.profile-form-card {
  max-width: 100%;
}

.form-section-title {
  font-size: 1rem;
  font-weight: 700;
  color: var(--color-gray-800);
  margin-bottom: 1.5rem;
  padding-bottom: 0.875rem;
  border-bottom: 1px solid var(--color-gray-200);
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 0.5rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--color-gray-200);
}

@media (max-width: 640px) {
  .profile-layout {
    grid-template-columns: 1fr;
  }

  .avatar-card {
    flex-direction: row;
    text-align: left;
    padding: 1.25rem 1.5rem;
  }

  .profile-avatar {
    margin-bottom: 0;
  }
}
</style>
