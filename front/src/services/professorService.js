import api from './api'

export default {
  list() {
    return api.get('/admin/professors')
  },
  get(id) {
    return api.get(`/admin/professors/${id}`)
  },
  create(data) {
    return api.post('/admin/professors', data)
  },
  update(id, data) {
    return api.put(`/admin/professors/${id}`, data)
  },
  delete(id) {
    return api.delete(`/admin/professors/${id}`)
  },
}
