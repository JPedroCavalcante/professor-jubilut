import api from './api'

export default {
  list() {
    return api.get('/admin/subjects')
  },
  get(id) {
    return api.get(`/admin/subjects/${id}`)
  },
  create(data) {
    return api.post('/admin/subjects', data)
  },
  update(id, data) {
    return api.put(`/admin/subjects/${id}`, data)
  },
  delete(id) {
    return api.delete(`/admin/subjects/${id}`)
  },
}
