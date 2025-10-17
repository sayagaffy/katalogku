import { createRouter, createWebHistory } from 'vue-router'
import { authService } from '@/services/auth.service'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      redirect: () => {
        return authService.isAuthenticated() ? '/dashboard' : '/login'
      },
    },
    // Authentication routes
    {
      path: '/login',
      name: 'login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/views/auth/RegisterView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/verify-otp',
      name: 'verify-otp',
      component: () => import('@/views/auth/VerifyOTPView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/forgot-pin',
      name: 'forgot-pin',
      component: () => import('@/views/auth/ResetPinView.vue'),
      meta: { requiresGuest: true },
    },
    // Dashboard routes (protected)
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('@/views/dashboard/DashboardView.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'dashboard-home',
          component: () => import('@/views/dashboard/HomeView.vue'),
        },
        {
          path: 'products',
          name: 'dashboard-products',
          component: () => import('@/views/dashboard/ProductsView.vue'),
        },
        {
          path: 'links',
          name: 'dashboard-links',
          component: () => import('@/views/dashboard/LinksView.vue'),
        },
        {
          path: 'catalog',
          name: 'dashboard-catalog',
          component: () => import('@/views/dashboard/CatalogView.vue'),
        },
        {
          path: 'analytics',
          name: 'dashboard-analytics',
          component: () => import('@/views/dashboard/AnalyticsView.vue'),
        },
        {
          path: 'settings',
          name: 'dashboard-settings',
          component: () => import('@/views/dashboard/SettingsView.vue'),
        },
      ],
    },
    // Public catalog view
    {
      path: '/c/:username',
      name: 'public-catalog',
      component: () => import('@/views/public/PublicCatalogView.vue'),
    },
    // Public product detail
    {
      path: '/c/:username/p/:productId',
      name: 'public-product-detail',
      component: () => import('@/views/public/PublicProductDetailView.vue'),
    },
    // 404 Not Found
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/NotFoundView.vue'),
    },
  ],
})

// Navigation guards
router.beforeEach((to, from, next) => {
  const isAuthenticated = authService.isAuthenticated()

  // Redirect authenticated users away from guest pages
  if (to.meta.requiresGuest && isAuthenticated) {
    next('/dashboard')
    return
  }

  // Redirect unauthenticated users to login
  if (to.meta.requiresAuth && !isAuthenticated) {
    next('/login')
    return
  }

  next()
})

export default router
