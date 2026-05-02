<script setup>
import { ref } from 'vue'
import axios from 'axios'

const isLogin = ref(true)
const email = ref('')
const password = ref('')
const password_confirmation = ref('')
const name = ref('')
const role = ref('parent')
const error = ref('')
const success = ref('')
const loading = ref(false)

const submit = async () => {
  error.value = ''
  success.value = ''
  loading.value = true
  
  try {
    if (isLogin.value) {
      // LOGIN
      const response = await axios.post('/api/login', {
        email: email.value,
        password: password.value
      })
      
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`
      
      window.location.href = '/'
    } else {
      // REGISTER - Make sure passwords match
      if (password.value !== password_confirmation.value) {
        error.value = 'Passwords do not match!'
        loading.value = false
        return
      }
      
      if (password.value.length < 6) {
        error.value = 'Password must be at least 6 characters!'
        loading.value = false
        return
      }
      
      const registerData = {
        name: name.value,
        email: email.value,
        password: password.value,
        password_confirmation: password_confirmation.value,
        role: role.value
      }
      
      console.log('Sending registration data:', registerData)
      
      const response = await axios.post('/api/register', registerData)
      
      console.log('Registration response:', response.data)
      
      localStorage.setItem('token', response.data.token)
      localStorage.setItem('user', JSON.stringify(response.data.user))
      axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`
      
      success.value = 'Registration successful! Redirecting...'
      setTimeout(() => {
        window.location.href = '/'
      }, 1500)
    }
  } catch (err) {
    console.error('Full error:', err)
    console.error('Error response:', err.response)
    
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else if (err.response?.data?.error) {
      error.value = err.response.data.error
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'An error occurred. Please try again.'
    }
  } finally {
    loading.value = false
  }
}

const demoLogin = async (demoEmail) => {
  email.value = demoEmail
  password.value = 'password'
  await submit()
}

const toggleMode = () => {
  isLogin.value = !isLogin.value
  error.value = ''
  success.value = ''
  name.value = ''
  email.value = ''
  password.value = ''
  password_confirmation.value = ''
  role.value = 'parent'
}
</script>

<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-5">
          <div class="text-center mb-4">
            <span class="display-1">👶</span>
            <h1 class="text-white mb-2" style="font-weight: 700;">KidCare Insight</h1>
            <p class="text-white-50">Supporting children's behavioral development</p>
          </div>
          
          <div class="card border-0 shadow-lg" style="border-radius: 20px;">
            <div class="card-body p-5">
              <div class="d-flex gap-2 mb-4">
                <button @click="toggleMode" class="flex-grow-1 btn py-2 rounded-pill fw-semibold" :class="isLogin ? 'btn-primary' : 'btn-outline-secondary'">Sign In</button>
                <button @click="toggleMode" class="flex-grow-1 btn py-2 rounded-pill fw-semibold" :class="!isLogin ? 'btn-primary' : 'btn-outline-secondary'">Create Account</button>
              </div>
              
              <div v-if="error" class="alert alert-danger">
                <strong>Error:</strong> {{ error }}
              </div>
              
              <div v-if="success" class="alert alert-success">
                {{ success }}
              </div>
              
              <form @submit.prevent="submit">
                <div v-if="!isLogin" class="mb-3">
                  <label class="form-label fw-semibold">Full Name *</label>
                  <input 
                    v-model="name" 
                    type="text" 
                    class="form-control" 
                    placeholder="Enter your full name"
                    required
                  >
                </div>
                
                <div class="mb-3">
                  <label class="form-label fw-semibold">Email Address *</label>
                  <input 
                    v-model="email" 
                    type="email" 
                    class="form-control" 
                    placeholder="your@email.com"
                    required
                  >
                </div>
                
                <div class="mb-3">
                  <label class="form-label fw-semibold">Password *</label>
                  <input 
                    v-model="password" 
                    type="password" 
                    class="form-control" 
                    placeholder="minimum 6 characters"
                    required
                  >
                  <small class="text-muted" v-if="!isLogin">Password must be at least 6 characters</small>
                </div>
                
                <div v-if="!isLogin" class="mb-3">
                  <label class="form-label fw-semibold">Confirm Password *</label>
                  <input 
                    v-model="password_confirmation" 
                    type="password" 
                    class="form-control" 
                    placeholder="Confirm your password"
                    required
                  >
                </div>
                
                <div v-if="!isLogin" class="mb-4">
                  <label class="form-label fw-semibold">I am a... *</label>
                  <div class="row g-2">
                    <div class="col-4">
                      <div 
                        @click="role = 'parent'" 
                        class="card text-center p-2 cursor-pointer" 
                        :class="role === 'parent' ? 'border-primary bg-primary bg-opacity-10' : 'border'"
                      >
                        <div class="fs-2">👨‍👩‍👧</div>
                        <small class="fw-semibold">Parent</small>
                      </div>
                    </div>
                    <div class="col-4">
                      <div 
                        @click="role = 'teacher'" 
                        class="card text-center p-2 cursor-pointer" 
                        :class="role === 'teacher' ? 'border-primary bg-primary bg-opacity-10' : 'border'"
                      >
                        <div class="fs-2">📚</div>
                        <small class="fw-semibold">Teacher</small>
                      </div>
                    </div>
                    <div class="col-4">
                      <div 
                        @click="role = 'psychologist'" 
                        class="card text-center p-2 cursor-pointer" 
                        :class="role === 'psychologist' ? 'border-primary bg-primary bg-opacity-10' : 'border'"
                      >
                        <div class="fs-2">👩‍⚕️</div>
                        <small class="fw-semibold">Psychologist</small>
                      </div>
                    </div>
                  </div>
                </div>
                
                <button 
                  type="submit" 
                  class="btn btn-primary w-100 py-2 rounded-pill fw-semibold mb-3"
                  :disabled="loading"
                >
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  {{ loading ? 'Please wait...' : (isLogin ? 'Sign In' : 'Create Account') }}
                </button>
              </form>
            </div>
          </div>
          
          <div class="row mt-4 g-3">
            <div class="col-md-4">
              <div class="card border-0 shadow-sm text-center cursor-pointer" @click="demoLogin('parent@example.com')">
                <div class="card-body p-3">
                  <div class="fs-3">👨‍👩‍👧</div>
                  <div class="fw-semibold">Demo Parent</div>
                  <small>parent@example.com</small>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border-0 shadow-sm text-center cursor-pointer" @click="demoLogin('teacher@example.com')">
                <div class="card-body p-3">
                  <div class="fs-3">📚</div>
                  <div class="fw-semibold">Demo Teacher</div>
                  <small>teacher@example.com</small>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card border-0 shadow-sm text-center cursor-pointer" @click="demoLogin('psychologist@example.com')">
                <div class="card-body p-3">
                  <div class="fs-3">👩‍⚕️</div>
                  <div class="fw-semibold">Demo Psychologist</div>
                  <small>psychologist@example.com</small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.cursor-pointer { 
  cursor: pointer; 
}
.card { 
  transition: all 0.3s ease; 
}
.card:hover { 
  transform: translateY(-5px); 
  box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
}
</style>