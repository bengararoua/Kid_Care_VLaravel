<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import Notifications from './Notifications.vue'

const router = useRouter()
const route = useRoute()
const mobileMenuOpen = ref(false)

// Unread messages count
const unreadCount = ref(0)

// User
const user = computed(() => {
  const userData = localStorage.getItem('user')
  if (userData) {
    return JSON.parse(userData)
  }
  return {}
})

const loadUnreadCount = async () => {
  try {
    const res = await axios.get('/api/messages/unread-count')
    unreadCount.value = res.data.count || 0
  } catch (err) {
    console.error('Error loading unread count:', err)
    unreadCount.value = 0
  }
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  delete axios.defaults.headers.common['Authorization']
  router.push('/')
}

const goTo = (path) => {
  router.push(path)
  mobileMenuOpen.value = false // Fermer le menu après navigation
}

const isActive = (path) => {
  return route.path === path
}

const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

onMounted(() => {
  loadUnreadCount()
  const interval = setInterval(loadUnreadCount, 10000)
  
  onUnmounted(() => {
    clearInterval(interval)
  })
})
</script>

<template>
  <div class="app-wrapper">
    <nav class="navbar">
      <div class="container nav-container">
        <div class="nav-brand" @click="goTo('/dashboard')">
          <img src="http://localhost:8000/logo.png" alt="KidCare Insight" class="brand-logo">
          <span class="brand-text">KidCare Insight</span>
        </div>
        
        <!-- Menu Hamburger pour mobile -->
        <button class="mobile-menu-btn" @click="toggleMobileMenu">
          <span class="menu-icon">☰</span>
        </button>
        
        <div class="nav-menu" :class="{ 'mobile-open': mobileMenuOpen }">
          <button @click="goTo('/dashboard')" class="nav-link" :class="{ active: isActive('/dashboard') }">
            🏠 Dashboard
          </button>
          <button @click="goTo('/children')" class="nav-link" :class="{ active: isActive('/children') }">
            👶 Children
          </button>
          <button @click="goTo('/messages')" class="nav-link" :class="{ active: isActive('/messages') }">
            💬 Messages
            <span v-if="unreadCount > 0" class="nav-unread-badge">{{ unreadCount }}</span>
          </button>
          <button @click="goTo('/profile')" class="nav-link" :class="{ active: isActive('/profile') }">
            👤 Profile
          </button>
          
          <div class="nav-right-mobile">
            <Notifications />
            <div class="nav-user">
              <div class="user-dropdown">
                <button class="user-btn">
                  <span class="user-avatar">{{ user.name?.charAt(0) || 'U' }}</span>
                  <span class="user-name">{{ user.name }}</span>
                  <span class="dropdown-icon">▼</span>
                </button>
                <div class="dropdown-menu">
                  <div class="dropdown-item" @click="goTo('/profile')">
                    <span>👤</span> My Profile
                  </div>
                  <div class="dropdown-divider"></div>
                  <div class="dropdown-item logout" @click="logout">
                    <span>🚪</span> Logout
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="nav-right">
          <Notifications />
          <div class="nav-user">
            <div class="user-dropdown">
              <button class="user-btn">
                <span class="user-avatar">{{ user.name?.charAt(0) || 'U' }}</span>
                <span class="user-name">{{ user.name }}</span>
                <span class="dropdown-icon">▼</span>
              </button>
              <div class="dropdown-menu">
                <div class="dropdown-item" @click="goTo('/profile')">
                  <span>👤</span> My Profile
                </div>
                <div class="dropdown-divider"></div>
                <div class="dropdown-item logout" @click="logout">
                  <span>🚪</span> Logout
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <slot />
    </main>

    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-logo">👶 KidCare Insight</div>
          <div class="footer-links">
            <a href="#" @click.prevent="goTo('/about')">About</a>
            <a href="#" @click.prevent="goTo('/privacy')">Privacy</a>
            <a href="#" @click.prevent="goTo('/terms')">Terms</a>
            <a href="#" @click.prevent="goTo('/contact')">Contact</a>
          </div>
          <div class="footer-copyright">
            &copy; {{ new Date().getFullYear() }} KidCare Insight. All rights reserved.
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.app-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.navbar {
  background: linear-gradient(135deg, #1a0a2e 0%, #2d1b4e 100%); 
  padding: 0.8rem 0;
  position: sticky;
  top: 0;
  z-index: 100;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.nav-container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 10px; 
  font-size: 1.3rem;
  font-weight: 700;
  color: white;
  cursor: pointer;
}

.brand-logo {
  height: 55px;  
  width: auto;
  max-height: 55px;
  object-fit: contain;
}

.brand-text {
  font-size: 1.2rem;
  font-weight: 700;
  color: white;
}

/* Menu Hamburger */
.mobile-menu-btn {
  display: none;
  background: none;
  border: none;
  color: white;
  font-size: 28px;
  cursor: pointer;
  padding: 8px;
}

.nav-menu {
  display: flex;
  gap: 0.3rem;
  flex-wrap: wrap;
  align-items: center;
}

.nav-right-mobile {
  display: none;
}

.nav-link {
  background: none;
  border: none;
  color: rgba(255,255,255,0.8);
  padding: 0.5rem 1.2rem;
  border-radius: 50px;
  transition: all 0.3s;
  font-weight: 500;
  cursor: pointer;
  font-size: 0.95rem;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.nav-link:hover {
  background: rgba(255,255,255,0.15);
  color: white;
}

.nav-link.active {
  background: rgba(255,255,255,0.25);
  color: white;
}

.nav-unread-badge {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border-radius: 20px;
  padding: 2px 8px;
  font-size: 11px;
  font-weight: 600;
  margin-left: 5px;
  animation: pulse 0.5s ease;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 16px;
}

.nav-user {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(255,255,255,0.15);
  border: none;
  padding: 0.4rem 1rem 0.4rem 0.4rem;
  border-radius: 50px;
  cursor: pointer;
  transition: all 0.3s;
}

.user-btn:hover {
  background: rgba(255,255,255,0.25);
}

.user-avatar {
  width: 32px;
  height: 32px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #4F8EF7;
  font-size: 14px;
}

.user-name {
  color: white;
  font-weight: 500;
  font-size: 14px;
}

.dropdown-icon {
  color: rgba(255,255,255,0.7);
  font-size: 10px;
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  right: 0;
  margin-top: 8px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
  min-width: 180px;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s;
  z-index: 100;
}

.nav-user:hover .dropdown-menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 16px;
  cursor: pointer;
  transition: background 0.2s;
  font-size: 14px;
  color: #374151;
}

.dropdown-item:hover {
  background: #f3f4f6;
}

.dropdown-item.logout {
  color: #ef4444;
}

.dropdown-item.logout:hover {
  background: #fee2e2;
}

.dropdown-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 4px 0;
}

.main-content {
  flex: 1;
  padding: 0;
  background: #f5f7fb;
}

.footer {
  background: #1a1a2e;
  color: #cbd5e0;
  padding: 1.5rem 0;
  margin-top: auto;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.footer-logo {
  font-size: 1rem;
  font-weight: 600;
  color: white;
}

.footer-links {
  display: flex;
  gap: 1.5rem;
}

.footer-links a {
  color: #cbd5e0;
  text-decoration: none;
  transition: color 0.3s;
  cursor: pointer;
  font-size: 13px;
}

.footer-links a:hover {
  color: white;
}

.footer-copyright {
  font-size: 0.8rem;
}

/* Responsive Styles */
@media (max-width: 768px) {
  .nav-container {
    flex-wrap: wrap;
  }
  
  .mobile-menu-btn {
    display: block;
  }
  
  .nav-menu {
    display: none;
    width: 100%;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 0;
  }
  
  .nav-menu.mobile-open {
    display: flex;
  }
  
  .nav-right {
    display: none;
  }
  
  .nav-right-mobile {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
  }
  
  .nav-link {
    width: 100%;
    justify-content: center;
  }
  
  .footer-content {
    flex-direction: column;
    text-align: center;
  }
  
  .footer-links {
    justify-content: center;
    flex-wrap: wrap;
  }
}
</style>