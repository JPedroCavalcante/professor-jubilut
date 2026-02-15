import api from './api'

export default {
  list() {
    return api.get('/admin/courses')
  },
  get(id) {
    return api.get(`/admin/courses/${id}`)
  },
  create(data) {
    return api.post('/admin/courses', data)
  },
  update(id, data) {
    return api.put(`/admin/courses/${id}`, data)
  },
  delete(id) {
    return api.delete(`/admin/courses/${id}`)
  },
}
