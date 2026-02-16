import api from './api'

export default {
  getMyCourses() {
    return api.get('/student/courses')
  },
  getStudentCourses(studentId) {
    return api.get(`/admin/students/${studentId}/courses`)
  },
  enroll(studentId, courseId) {
    return api.post(`/admin/students/${studentId}/courses`, { course_id: courseId })
  },
  unenroll(studentId, courseId) {
    return api.delete(`/admin/students/${studentId}/courses/${courseId}`)
  },
}
