import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    component: () => import('@/layouts/AuthLayout.vue'),
    children: [
      {
        path: '',
        name: 'login',
        component: () => import('@/views/auth/LoginView.vue'),
      },
    ],
    meta: { guestOnly: true },
  },
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: () => import('@/views/admin/DashboardView.vue'),
      },
      {
        path: 'courses',
        name: 'admin-courses',
        component: () => import('@/views/admin/courses/CoursesIndex.vue'),
      },
      {
        path: 'courses/create',
        name: 'admin-courses-create',
        component: () => import('@/views/admin/courses/CourseForm.vue'),
      },
      {
        path: 'courses/:id/edit',
        name: 'admin-courses-edit',
        component: () => import('@/views/admin/courses/CourseForm.vue'),
      },
      {
        path: 'professors',
        name: 'admin-professors',
        component: () => import('@/views/admin/professors/ProfessorsIndex.vue'),
      },
      {
        path: 'professors/create',
        name: 'admin-professors-create',
        component: () => import('@/views/admin/professors/ProfessorForm.vue'),
      },
      {
        path: 'professors/:id/edit',
        name: 'admin-professors-edit',
        component: () => import('@/views/admin/professors/ProfessorForm.vue'),
      },
      {
        path: 'subjects',
        name: 'admin-subjects',
        component: () => import('@/views/admin/subjects/SubjectsIndex.vue'),
      },
      {
        path: 'subjects/create',
        name: 'admin-subjects-create',
        component: () => import('@/views/admin/subjects/SubjectForm.vue'),
      },
      {
        path: 'subjects/:id/edit',
        name: 'admin-subjects-edit',
        component: () => import('@/views/admin/subjects/SubjectForm.vue'),
      },
      {
        path: 'students',
        name: 'admin-students',
        component: () => import('@/views/admin/students/StudentsIndex.vue'),
      },
      {
        path: 'students/create',
        name: 'admin-students-create',
        component: () => import('@/views/admin/students/StudentForm.vue'),
      },
      {
        path: 'students/:id/edit',
        name: 'admin-students-edit',
        component: () => import('@/views/admin/students/StudentForm.vue'),
      },
      {
        path: 'students/:id/enrollments',
        name: 'admin-enrollments',
        component: () => import('@/views/admin/enrollments/EnrollmentManage.vue'),
      },
    ],
  },
  {
    path: '/student',
    component: () => import('@/layouts/StudentLayout.vue'),
    meta: { requiresAuth: true, role: 'student' },
    children: [
      {
        path: 'dashboard',
        name: 'student-dashboard',
        component: () => import('@/views/student/DashboardView.vue'),
      },
      {
        path: 'profile',
        name: 'student-profile',
        component: () => import('@/views/student/ProfileView.vue'),
      },
    ],
  },
  {
    path: '/',
    redirect: '/login',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.guestOnly && auth.isAuthenticated) {
    return next(auth.isAdmin ? '/admin/dashboard' : '/student/dashboard')
  }

  if (to.meta.requiresAuth && !auth.isAuthenticated) {
    return next('/login')
  }

  if (to.meta.role && auth.user?.role !== to.meta.role) {
    return next(auth.isAdmin ? '/admin/dashboard' : '/student/dashboard')
  }

  next()
})

export default router
