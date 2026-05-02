<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const isLogin = ref(true)
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const name = ref('')
const role = ref('parent')
const error = ref('')
const success = ref('')
const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const toggleConfirmPasswordVisibility = () => {
  showConfirmPassword.value = !showConfirmPassword.value
}

const goTo = (path) => {
  router.push(path)
}

const submit = async () => {
  error.value = ''
  success.value = ''
  
  if (!isLogin.value) {
    if (!name.value.trim()) {
      error.value = 'Please enter your full name'
      return
    }
    
    if (password.value !== password_confirmation.value) {
      error.value = 'Passwords do not match'
      return
    }
    
    if (password.value.length < 6) {
      error.value = 'Password must be at least 6 characters'
      return
    }
  }
  
  if (!email.value) {
    error.value = 'Please enter your email address'
    return
  }
  
  loading.value = true
  
  try {
    if (isLogin.value) {
      const response = await axios.post('/api/login', {
        email: email.value,
        password: password.value
      })
      
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      console.log('Stored user:', response.data.user)
      console.log('Stored user role:', response.data.user.role)  
      axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`
      
      router.push('/dashboard')
    } else {
      const registerData = {
        name: name.value.trim(),
        email: email.value.toLowerCase().trim(),
        password: password.value,
        password_confirmation: password_confirmation.value,
        role: role.value
      }
      
      const response = await axios.post('/api/register', registerData)
      
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`
      
      success.value = 'Account created successfully! Redirecting...'
      setTimeout(() => {
        router.push('/dashboard')
      }, 1500)
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else if (err.response?.data?.error) {
      error.value = err.response.data.error
    } else {
      error.value = 'An error occurred. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const switchMode = () => {
  isLogin.value = !isLogin.value
  error.value = ''
  success.value = ''
  name.value = ''
  email.value = ''
  password.value = ''
  password_confirmation.value = ''
}

const goToForgotPassword = () => {
  router.push('/reset-password')
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-container">
      <div class="background-animation">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
        <div class="circle circle-3"></div>
        <div class="circle circle-4"></div>
      </div>

      <div class="auth-grid">
        <!-- Left Side - Features -->
        <div class="auth-features">
          <div class="features-content">
            <div class="logo-large">
              <span class="logo-icon">🧠</span>
              <h1>KidCare <span>Insight</span></h1>
              <p>Supporting children's behavioral development</p>
            </div>

            <div class="features-list">
              <div class="feature-item">
                <div class="feature-icon">📊</div>
                <div>
                  <h3>Behavioral Tracking</h3>
                  <p>Monitor focus, mood, sleep, and social interaction with insightful charts.</p>
                </div>
              </div>
              <div class="feature-item">
                <div class="feature-icon">🤝</div>
                <div>
                  <h3>Team Collaboration</h3>
                  <p>Parents, teachers, and psychologists work together for each child.</p>
                </div>
              </div>
              <div class="feature-item">
                <div class="feature-icon">🧠</div>
                <div>
                  <h3>AI-Powered Insights</h3>
                  <p>Automatic risk assessment and personalized recommendations.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Form -->
        <div class="auth-form-container">
          <div class="auth-card">
            <div class="tab-switcher">
              <button @click="switchMode" class="tab-btn" :class="{ active: isLogin }">
                <span>🔐</span> Sign In
              </button>
              <button @click="switchMode" class="tab-btn" :class="{ active: !isLogin }">
                <span>✨</span> Create Account
              </button>
            </div>

            <div v-if="error" class="message-card error">
              <span>⚠️</span>
              <span>{{ error }}</span>
            </div>

            <div v-if="success" class="message-card success">
              <span>✓</span>
              <span>{{ success }}</span>
            </div>

            <form @submit.prevent="submit">
              <div v-if="!isLogin" class="input-group">
                <div class="input-icon">👤</div>
                <input v-model="name" type="text" placeholder="Full Name" required>
              </div>

              <div class="input-group">
                <div class="input-icon">📧</div>
                <input v-model="email" type="email" placeholder="Email Address" required>
              </div>

              <div class="input-group">
                <div class="input-icon">🔒</div>
                <input v-model="password" :type="showPassword ? 'text' : 'password'" placeholder="Password" required>
                <button type="button" class="password-toggle" @click="togglePasswordVisibility">
                  {{ showPassword ? '🙈' : '👁️' }}
                </button>
              </div>

              <div v-if="!isLogin" class="input-group">
                <div class="input-icon">✓</div>
                <input v-model="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" placeholder="Confirm Password" required>
                <button type="button" class="password-toggle" @click="toggleConfirmPasswordVisibility">
                  {{ showConfirmPassword ? '🙈' : '👁️' }}
                </button>
              </div>

              <div v-if="!isLogin" class="role-section">
                <label>I am a...</label>
                <div class="role-cards">
                  <div @click="role = 'parent'" class="role-card" :class="{ active: role === 'parent' }">
                    <div class="role-emoji">👨‍👩‍👧</div>
                    <div>Parent</div>
                  </div>
                  <div @click="role = 'teacher'" class="role-card" :class="{ active: role === 'teacher' }">
                    <div class="role-emoji">📚</div>
                    <div>Teacher</div>
                  </div>
                  <div @click="role = 'psychologist'" class="role-card" :class="{ active: role === 'psychologist' }">
                    <div class="role-emoji">👩‍⚕️</div>
                    <div>Psychologist</div>
                  </div>
                </div>
              </div>

              <div v-if="isLogin" class="forgot-link">
                <a href="#" @click.prevent="goToForgotPassword">Forgot Password?</a>
              </div>

              <button type="submit" class="submit-btn" :disabled="loading">
                {{ loading ? 'Please wait...' : (isLogin ? 'Sign In' : 'Create Account') }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-logo">👶 KidCare Insight</div>
          <div class="footer-links">
            <a href="#" @click.prevent="goTo('/about')">À propos</a>
            <a href="#" @click.prevent="goTo('/privacy')">Confidentialité</a>
            <a href="#" @click.prevent="goTo('/terms')">Conditions</a>
            <a href="#" @click.prevent="goTo('/contact')">Contact</a>
          </div>
          <div class="footer-email">
            📧 <a href="mailto:contact@kidcare.tn">contact@kidcare.tn</a>
          </div>
          <div class="footer-copyright">
            © {{ new Date().getFullYear() }} KidCare Insight. Tous droits réservés.
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.auth-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #e8f4f8 0%, #f0e6f5 100%);
  padding: 40px 20px;
}

.background-animation {
  position: absolute;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.circle {
  position: absolute;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(79, 142, 247, 0.1), rgba(107, 203, 119, 0.1));
  animation: float 20s infinite ease-in-out;
}

.circle-1 { width: 300px; height: 300px; top: -100px; left: -100px; }
.circle-2 { width: 400px; height: 400px; bottom: -150px; right: -150px; animation-delay: 5s; }
.circle-3 { width: 200px; height: 200px; top: 50%; left: 10%; animation-delay: 2s; }
.circle-4 { width: 250px; height: 250px; bottom: 20%; right: 5%; animation-delay: 8s; }

@keyframes float {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(5deg); }
}

.auth-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  max-width: 1200px;
  width: 100%;
  background: white;
  border-radius: 32px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  z-index: 1;
}

.auth-features {
  background: linear-gradient(135deg, #1a0a2e 0%, #2d1b4e 50%, #1a0a2e 100%);
  padding: 48px;
  color: white;
}

.features-content {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.logo-large {
  margin-bottom: 48px;
}

.logo-icon {
  font-size: 48px;
}

.logo-large h1 {
  font-size: 32px;
  margin: 16px 0 8px 0;
}

.logo-large h1 span {
  color: #a855f7;
}

.logo-large p {
  opacity: 0.8;
  font-size: 14px;
}

.features-list {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 32px;
}

.feature-item {
  display: flex;
  gap: 16px;
  align-items: flex-start;
}

.feature-icon {
  font-size: 32px;
}

.feature-item h3 {
  margin: 0 0 8px 0;
  font-size: 18px;
}

.feature-item p {
  margin: 0;
  opacity: 0.8;
  font-size: 14px;
  line-height: 1.5;
}

.auth-form-container {
  padding: 48px;
  background: white;
}

.auth-card {
  max-width: 400px;
  margin: 0 auto;
}

.tab-switcher {
  display: flex;
  gap: 12px;
  background: #f0f2f5;
  padding: 6px;
  border-radius: 60px;
  margin-bottom: 28px;
}

.tab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px;
  border: none;
  background: transparent;
  border-radius: 50px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  color: #6c7a8e;
}

.tab-btn.active {
  background: white;
  color: #667eea;
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.message-card {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 18px;
  border-radius: 16px;
  margin-bottom: 20px;
  font-size: 14px;
}

.message-card.error {
  background: #fee;
  color: #c62828;
  border-left: 4px solid #c62828;
}

.message-card.success {
  background: #e8f5e9;
  color: #2e7d32;
  border-left: 4px solid #2e7d32;
}

.input-group {
  position: relative;
  margin-bottom: 18px;
}

.input-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 18px;
}

.input-group input {
  width: 100%;
  padding: 14px 48px 14px 48px;
  border: 2px solid #e0e6ed;
  border-radius: 16px;
  font-size: 15px;
  transition: all 0.3s;
}

.input-group input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.password-toggle {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
}

.role-section {
  margin-bottom: 24px;
}

.role-section label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #4a5568;
  margin-bottom: 12px;
}

.role-cards {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
}

.role-card {
  text-align: center;
  padding: 12px;
  border: 2px solid #e0e6ed;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.3s;
  font-size: 12px;
  font-weight: 600;
}

.role-card.active {
  border-color: #667eea;
  background: rgba(102, 126, 234, 0.1);
}

.role-emoji {
  font-size: 28px;
  margin-bottom: 6px;
}

.forgot-link {
  text-align: right;
  margin-bottom: 20px;
}

.forgot-link a {
  color: #8b9dc3;
  font-size: 13px;
  text-decoration: none;
}

.forgot-link a:hover {
  color: #667eea;
}

.submit-btn {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border: none;
  border-radius: 60px;
  font-size: 16px;
  font-weight: 600;
  color: white;
  cursor: pointer;
  transition: all 0.3s;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* Footer Styles */
.footer {
  background: #1a1a2e;
  color: #cbd5e0;
  padding: 20px 0;
  margin-top: auto;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
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

.footer-email a {
  color: #cbd5e0;
  text-decoration: none;
  transition: color 0.3s;
  font-size: 13px;
}

.footer-email a:hover {
  color: white;
}

.footer-copyright {
  font-size: 0.8rem;
}

@media (max-width: 900px) {
  .auth-grid {
    grid-template-columns: 1fr;
  }
  .auth-features {
    padding: 32px;
  }
  .auth-form-container {
    padding: 32px;
  }
}

@media (max-width: 768px) {
  .footer-content {
    flex-direction: column;
    text-align: center;
  }
  .footer-links {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .auth-form-container {
    padding: 24px;
  }
  .role-cards {
    gap: 8px;
  }
  .role-card {
    padding: 8px;
  }
  .role-emoji {
    font-size: 24px;
  }
}
</style>