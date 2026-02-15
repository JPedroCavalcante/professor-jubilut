import api from './api'

export default {
  list(filters = {}) {
    const params = new URLSearchParams()
    if (filters.name) params.append('name', filters.name)
    if (filters.email) params.append('email', filters.email)
    return api.get('/admin/students?' + params.toString())
  },
  get(id) {
    return api.get(`/admin/students/${id}`)
  },
  create(data) {
    return api.post('/admin/students', data)
  },
  update(id, data) {
    return api.put(`/admin/students/${id}`, data)
  },
  delete(id) {
    return api.delete(`/admin/students/${id}`)
  },
  getProfile() {
    return api.get('/student/profile')
  },
  updateProfile(data) {
    return api.put('/student/profile', data)
  },
}
