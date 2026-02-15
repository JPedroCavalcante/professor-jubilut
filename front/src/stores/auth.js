import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('auth_token') || null)
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.role === 'admin')
  const isStudent = computed(() => user.value?.role === 'student')

  async function login(email, password) {
    const response = await api.post('/login', { email, password })
    token.value = response.data.access_token
    user.value = response.data.user
    localStorage.setItem('auth_token', token.value)
    localStorage.setItem('auth_user', JSON.stringify(user.value))
    return response.data.user
  }

  async function logout() {
    try {
      await api.post('/logout')
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  async function fetchMe() {
    const response = await api.get('/me')
    user.value = response.data
    localStorage.setItem('auth_user', JSON.stringify(user.value))
    return response.data
  }

  return { token, user, isAuthenticated, isAdmin, isStudent, login, logout, fetchMe }
})
