import { createRouter, createWebHistory } from 'vue-router'
import AuthPage from '../components/AuthPage.vue'
import Dashboard from '../components/Dashboard.vue'
import ForgotPassword from '../components/ForgotPassword.vue'
import Placeholder from '../components/Placeholder.vue'
import ChildrenManagement from '../components/ChildrenManagement.vue'
import Chat from '../components/Chat.vue'
import Profile from '../components/Profile.vue'
import ChildDetails from '../components/ChildDetails.vue'
import ResetPassword from '../components/ResetPassword.vue'
import About from '../components/FooterPages/About.vue'
import Privacy from '../components/FooterPages/Privacy.vue'
import Terms from '../components/FooterPages/Terms.vue'
import Contact from '../components/FooterPages/Contact.vue'
import AppointmentDetails from '../components/AppointmentDetails.vue'

const routes = [
  { path: '/', name: 'home', component: AuthPage },
  { path: '/dashboard', name: 'dashboard', component: Dashboard, meta: { requiresAuth: true } },
  { path: '/forgot-password', name: 'forgot-password', component: ForgotPassword },
  
  // Pages avec composants réels (pas Placeholder)
  { path: '/children', name: 'children', component: ChildrenManagement, meta: { requiresAuth: true } },
  { path: '/messages', name: 'messages', component: Chat, meta: { requiresAuth: true } },
  { path: '/profile', name: 'profile', component: Profile, meta: { requiresAuth: true } },
  
  // Rendez-vous
  { path: '/appointments/:id', name: 'appointment-details', component: AppointmentDetails, meta: { requiresAuth: true } },
  
  // Pages information (sans authentification requise)
  { path: '/about', name: 'about', component: About },
  { path: '/privacy', name: 'privacy', component: Privacy },
  { path: '/terms', name: 'terms', component: Terms },
  { path: '/contact', name: 'contact', component: Contact },
  { path: '/settings', name: 'settings', component: Placeholder, meta: { requiresAuth: true }, props: { title: '⚙️ Settings', message: 'Configure your preferences. Coming soon!' } },
  { path: '/child/:id', name: 'child-details', component: ChildDetails, meta: { requiresAuth: true } },
  { path: '/reset-password', name: 'reset-password', component: ResetPassword },
  
  // Redirection 404
  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  
  // Rediriger vers login si besoin d'authentification
  if (to.meta.requiresAuth && !token) {
    next('/')
  } 
  // Rediriger vers dashboard si déjà connecté sur la page login
  else if (to.path === '/' && token) {
    next('/dashboard')
  } 
  else {
    next()
  }
})

export default router