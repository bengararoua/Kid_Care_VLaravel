<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const email = ref('')
const error = ref('')
const success = ref('')
const loading = ref(false)

const sendResetLink = async () => {
  error.value = ''
  success.value = ''
  loading.value = true

  try {
    const response = await axios.post('/api/password/email', {
      email: email.value
    })
    success.value = '✓ Reset link sent! Check your email.'
    console.log('Reset response:', response.data)
  } catch (err) {
    console.error('Reset error:', err.response?.data)
    error.value = err.response?.data?.error || 'Email not found'
  } finally {
    loading.value = false
  }
}

const backToLogin = () => {
  router.push('/')
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
        <!-- Left Side - Features (même que Login/Register) -->
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

        <!-- Right Side - Forgot Password Form -->
        <div class="auth-form-container">
          <div class="auth-card">
            <div class="brand-section">
              <div class="logo-icon">🔐</div>
              <h2>Reset Password</h2>
              <p>Enter your email to receive a reset link</p>
            </div>

            <div v-if="error" class="message-card error">
              <span>⚠️</span>
              <span>{{ error }}</span>
            </div>

            <div v-if="success" class="message-card success">
              <span>✓</span>
              <span>{{ success }}</span>
            </div>

            <form @submit.prevent="sendResetLink">
              <div class="input-group">
                <div class="input-icon">📧</div>
                <input v-model="email" type="email" placeholder="Email Address" required>
              </div>

              <button type="submit" class="submit-btn" :disabled="loading">
                {{ loading ? 'Sending...' : 'Send Reset Link' }}
              </button>
            </form>

            <div class="back-link">
              <a href="#" @click.prevent="backToLogin">← Back to Login</a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer (identique à Login/Register) -->
    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-logo">👶 KidCare Insight</div>
          <div class="footer-copyright">
            © {{ new Date().getFullYear() }} KidCare Insight. All rights reserved.
          </div>
          <div class="footer-email">
            📧 contact@kidcare-insight.com
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

/* Purple plus foncé - identique à Login/Register */
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

.brand-section {
  text-align: center;
  margin-bottom: 32px;
}

.brand-section .logo-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.brand-section h2 {
  font-size: 28px;
  color: #2d3748;
  margin-bottom: 8px;
}

.brand-section p {
  color: #8b9dc3;
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
  margin-bottom: 24px;
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
  padding: 14px 20px 14px 48px;
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

.back-link {
  text-align: center;
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #e0e6ed;
}

.back-link a {
  color: #667eea;
  text-decoration: none;
  font-size: 14px;
  font-weight: 500;
}

.back-link a:hover {
  color: #764ba2;
}

/* Footer - identique à Login/Register */
.footer {
  background: #1a1a2e;
  color: #cbd5e0;
  padding: 20px 0;
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

.footer-copyright {
  font-size: 0.85rem;
}

.footer-email {
  font-size: 0.85rem;
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

@media (max-width: 480px) {
  .auth-form-container {
    padding: 24px;
  }
  .footer-content {
    flex-direction: column;
    text-align: center;
  }
}
</style>