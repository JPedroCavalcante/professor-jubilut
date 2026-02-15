import api from './api'

export default {
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
