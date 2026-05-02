<script setup>
import Layout from './Layout.vue'   
import { ref, onMounted, watch, computed } from 'vue' 
import axios from 'axios'
import ChartComponent from './ChartComponent.vue'
import NotificationToast from './NotificationToast.vue'
import ConfirmDialog from './ConfirmDialog.vue'

const children = ref([])
const selectedChild = ref(null)
const insights = ref(null)
const logs = ref([])
const focus = ref(3)
const mood = ref('neutral')
const sleep = ref(8)
const social = ref(3)
const note = ref('')
const showLogForm = ref(false)
const loading = ref(false)
const error = ref(null)
const userPoints = ref(0)

// Références aux composants de notification
const toast = ref(null)
const confirmDialog = ref(null)

// Notifications helper
const showSuccess = (message, title = 'Succès') => {
  toast.value?.addNotification('success', title, message, 4000)
}

const showError = (message, title = 'Erreur') => {
  toast.value?.addNotification('error', title, message, 5000)
}

const showInfo = (message, title = 'Information') => {
  toast.value?.addNotification('info', title, message, 3000)
}

const showWarning = (message, title = 'Attention') => {
  toast.value?.addNotification('warning', title, message, 4000)
}

const user = computed(() => {
  const userData = localStorage.getItem('user')
  if (userData) {
    return JSON.parse(userData)
  }
  return {}
})

// Charger les points depuis l'API
const loadUserPoints = async () => {
  try {
    const res = await axios.get('/api/me')
    userPoints.value = res.data.points || 0
    console.log('Points chargés:', userPoints.value)
  } catch (err) {
    console.error('Erreur chargement points:', err)
  }
}

// Load children on mount
onMounted(async () => {
  await loadChildren()
  await loadUserPoints()
  showInfo('Bienvenue sur votre tableau de bord !')
})

// Watch for selected child changes
watch(selectedChild, (newChild) => {
  if (newChild && newChild.id) {
    loadData()
    showInfo(`Chargement des données pour ${newChild.name}`, 'Enfant sélectionné')
  }
})

const loadChildren = async () => {
  try {
    loading.value = true
    error.value = null
    const res = await axios.get('/api/children')
    children.value = res.data
    
    if (children.value.length > 0) {
      selectedChild.value = children.value[0]
      showSuccess(`${children.value.length} enfant(s) chargé(s)`, 'Chargement réussi')
    } else {
      showInfo('Aucun enfant enregistré. Ajoutez votre premier enfant !', 'Bienvenue')
    }
  } catch (err) {
    console.error('Error loading children:', err)
    error.value = 'Failed to load children'
    showError('Impossible de charger la liste des enfants', 'Erreur de chargement')
  } finally {
    loading.value = false
  }
}

const loadData = async () => {
  if (!selectedChild.value || !selectedChild.value.id) return
  
  try {
    await Promise.all([
      loadInsights(),
      loadLogs()
    ])
  } catch (err) {
    console.error('Error loading data:', err)
    showError('Erreur lors du chargement des données', 'Erreur')
  }
}

const loadInsights = async () => {
  const childId = selectedChild.value.id
  if (!childId) return
  
  try {
    const res = await axios.get(`/api/insights/${childId}`)
    insights.value = res.data
  } catch (err) {
    console.error('Error loading insights:', err)
    insights.value = null
  }
}

const loadLogs = async () => {
  const childId = selectedChild.value.id
  if (!childId) return
  
  try {
    const res = await axios.get(`/api/logs/${childId}`)
    logs.value = res.data
    
    const today = new Date().toDateString()
    const loggedToday = logs.value.some(log => new Date(log.log_date).toDateString() === today)
    if (!loggedToday && logs.value.length > 0) {
      showInfo('Aucun log enregistré aujourd\'hui. Pensez à ajouter une observation !', 'Rappel')
    }
  } catch (err) {
    console.error('Error loading logs:', err)
    logs.value = []
    showError('Impossible de charger l\'historique', 'Erreur')
  }
}

const addLog = async () => {
  if (!selectedChild.value || !selectedChild.value.id) {
    showWarning('Veuillez sélectionner un enfant d\'abord', 'Action requise')
    return
  }
  
  try {
    const logData = {
      child_id: selectedChild.value.id,
      focus_level: focus.value,
      mood: mood.value,
      sleep_hours: sleep.value,
      social_interaction: social.value,
      note: note.value,
      log_date: new Date().toISOString().split('T')[0]
    }
    
    await axios.post('/api/logs', logData)
    
    const moodEmoji = getMoodEmoji(mood.value)
    showSuccess(
      `${moodEmoji} Log enregistré pour ${selectedChild.value.name}\nFocus: ${focus.value}/5 | Sommeil: ${sleep.value}h | Social: ${social.value}/5`,
      '✓ Comportement enregistré'
    )
    
    focus.value = 3
    mood.value = 'neutral'
    sleep.value = 8
    social.value = 3
    note.value = ''
    showLogForm.value = false
    
    await loadData()
    await loadUserPoints()
    
  } catch (err) {
    console.error('Error adding log:', err)
    showError(
      err.response?.data?.message || 'Impossible d\'enregistrer le log',
      'Erreur d\'enregistrement'
    )
  }
}

const getRiskBg = (risk) => {
  switch(risk) {
    case 'high': return 'linear-gradient(135deg, #ef4444, #dc2626)'
    case 'medium': return 'linear-gradient(135deg, #f59e0b, #d97706)'
    case 'low': return 'linear-gradient(135deg, #10b981, #059669)'
    default: return 'linear-gradient(135deg, #6b7280, #4b5563)'
  }
}

const getMoodEmoji = (mood) => {
  const emojis = {
    'happy': '😊',
    'sad': '😢',
    'angry': '😡',
    'neutral': '😐',
    'excited': '🤩',
    'anxious': '😰'
  }
  return emojis[mood] || '😊'
}

const getMoodColor = (mood) => {
  const colors = {
    'happy': '#10b981',
    'sad': '#3b82f6',
    'angry': '#ef4444',
    'neutral': '#6b7280',
    'excited': '#f59e0b',
    'anxious': '#8b5cf6'
  }
  return colors[mood] || '#6b7280'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
}

const getRecommendations = () => {
  const recommendations = []
  
  if (insights.value?.avg_focus < 3) {
    recommendations.push({
      icon: '🧘',
      title: 'Focus Exercise',
      description: 'Try 5 minutes of breathing exercises before homework'
    })
  }
  
  if (insights.value?.avg_sleep < 7) {
    recommendations.push({
      icon: '😴',
      title: 'Sleep Routine',
      description: 'Establish a consistent bedtime routine'
    })
  }
  
  if (insights.value?.avg_social < 3) {
    recommendations.push({
      icon: '👥',
      title: 'Social Activity',
      description: 'Encourage playdates or group activities'
    })
  }
  
  if (recommendations.length === 0) {
    recommendations.push({
      icon: '🎉',
      title: 'Keep Going!',
      description: 'Great progress! Continue with daily tracking'
    })
  }
  
  return recommendations.slice(0, 3)
}

const getMostCommonMood = () => {
  if (!logs.value.length) return '😐 Neutral'
  const moodCount = {}
  logs.value.forEach(log => {
    moodCount[log.mood] = (moodCount[log.mood] || 0) + 1
  })
  const mostCommon = Object.entries(moodCount).sort((a,b) => b[1] - a[1])[0]
  return mostCommon ? `${getMoodEmoji(mostCommon[0])} ${mostCommon[0]}` : '😐 Neutral'
}

const getMoodStats = () => {
  if (!logs.value.length) return {}
  const moodCount = {}
  logs.value.slice(0, 10).forEach(log => {
    moodCount[log.mood] = (moodCount[log.mood] || 0) + 1
  })
  return moodCount
}

const getActiveAlerts = () => {
  const alerts = []
  
  if (insights.value?.risk_level === 'high') {
    alerts.push({
      type: 'alert-high',
      icon: '🔴',
      message: 'High risk pattern detected',
      suggestion: 'Consider consulting with a psychologist'
    })
    if (insights.value?.risk_level === 'high') {
      showWarning('⚠️ Niveau de risque élevé détecté. Consultez les insights pour plus d\'informations.', 'Alerte')
    }
  } else if (insights.value?.risk_level === 'medium') {
    alerts.push({
      type: 'alert-medium',
      icon: '🟡',
      message: 'Moderate risk pattern detected',
      suggestion: 'Monitor closely and maintain daily logs'
    })
  }
  
  if (insights.value?.weekly_comparison?.change < -0.5) {
    alerts.push({
      type: 'alert-warning',
      icon: '⚠️',
      message: 'Focus level decreased this week',
      suggestion: 'Check sleep patterns and daily routine'
    })
  }
  
  const today = new Date().toDateString()
  const loggedToday = logs.value.some(log => new Date(log.log_date).toDateString() === today)
  if (!loggedToday && logs.value.length > 0) {
    alerts.push({
      type: 'alert-info',
      icon: '📝',
      message: 'No log recorded today',
      suggestion: 'Click "Log Today" to keep tracking'
    })
  }
  
  return alerts
}
</script>

<template>
  <Layout>
    <div class="dashboard-container">
      <NotificationToast ref="toast" />
      <ConfirmDialog ref="confirmDialog" />

      <div class="hero-section">
        <div class="hero-content">
          <h1 class="hero-title">👋 Welcome back, {{ user?.name?.split(' ')[0] || user?.name || 'User' }} 
            <span style="font-size: 0.7em; opacity: 0.8;">
              ({{ user?.role === 'parent' ? 'Parent' : (user?.role === 'teacher' ? 'Teacher' : (user?.role === 'psychologist' ? 'Psychologist' : 'User')) }})
            </span>
          </h1>
          <p class="hero-subtitle">Track and support your child's behavioral development journey</p>
        </div>
      </div>

      <div class="container">
        <div v-if="loading" class="loading-state">
          <div class="spinner"></div>
          <p>Loading your dashboard...</p>
        </div>

        <div v-else-if="error" class="error-state">
          <span class="error-icon">⚠️</span>
          <h3>Oops! Something went wrong</h3>
          <p>{{ error }}</p>
          <button @click="loadChildren" class="retry-btn">Try Again</button>
        </div>

        <div v-else>
          <div class="child-selector-card">
            <div class="selector-label">
              <span class="selector-icon">👶</span>
              <span>Select Child</span>
            </div>
            <div class="selector-wrapper">
              <select v-model="selectedChild" class="child-select">
                <option v-for="child in children" :key="child.id" :value="child">
                  {{ child.name }} · {{ child.age }} years old
                </option>
              </select>
            </div>
          </div>

          <div class="stats-grid">
            <div class="stat-card risk-card" :style="{ background: getRiskBg(insights?.risk_level) }">
              <div class="stat-icon">🎯</div>
              <div class="stat-info">
                <span class="stat-label">Risk Level</span>
                <span class="stat-value">{{ insights?.risk_level?.toUpperCase() || 'LOW' }}</span>
              </div>
              <div class="stat-trend">{{ insights?.total_logs || 0 }} total logs</div>
            </div>

            <div class="stat-card focus-card">
              <div class="stat-icon">🧠</div>
              <div class="stat-info">
                <span class="stat-label">Avg Focus</span>
                <span class="stat-value">{{ insights?.avg_focus || '-' }}/5</span>
              </div>
              <div class="stat-trend">
                <span v-if="insights?.weekly_comparison?.change > 0" class="trend-up">↑</span>
                <span v-else-if="insights?.weekly_comparison?.change < 0" class="trend-down">↓</span>
                {{ insights?.weekly_comparison?.change > 0 ? '+' : '' }}{{ insights?.weekly_comparison?.change || 0 }}
              </div>
            </div>

            <div class="stat-card sleep-card">
              <div class="stat-icon">😴</div>
              <div class="stat-info">
                <span class="stat-label">Avg Sleep</span>
                <span class="stat-value">{{ insights?.avg_sleep || '-' }}h</span>
              </div>
              <div class="stat-trend">recommended 8-10h</div>
            </div>

            <div class="stat-card social-card">
              <div class="stat-icon">👥</div>
              <div class="stat-info">
                <span class="stat-label">Social Interaction</span>
                <span class="stat-value">{{ insights?.avg_social || '-' }}/5</span>
              </div>
              <div class="stat-trend">weekly average</div>
            </div>

            <div class="stat-card points-card">
              <div class="stat-icon">⭐</div>
              <div class="stat-info">
                <span class="stat-label">Mes points</span>
                <span class="stat-value">{{ userPoints }}</span>
              </div>
              <div class="stat-trend">+10 par jour</div>
            </div>
          </div>

          <!-- 🌤️ Météo des émotions - NOUVEAU BONUS -->
          <div class="emotion-weather-card" v-if="logs.length > 0">
            <h3>🌤️ Météo des émotions</h3>
            <div class="emotion-grid">
              <div class="emotion-item" v-for="(count, moodName) in getMoodStats()" :key="moodName">
                <div class="emotion-emoji">{{ getMoodEmoji(moodName) }}</div>
                <div class="emotion-name">{{ moodName }}</div>
                <div class="emotion-count">{{ count }}x</div>
                <div class="emotion-bar">
                  <div class="emotion-fill" :style="{ 
                    width: (count / Math.max(...Object.values(getMoodStats()), 1)) * 100 + '%', 
                    background: getMoodColor(moodName) 
                  }"></div>
                </div>
              </div>
            </div>
            <div class="emotion-message">
              <span v-if="getMostCommonMood().includes('happy')">🌟 Super journée ! Continuez ainsi !</span>
              <span v-else-if="getMostCommonMood().includes('sad')">💪 Un petit coup de mou, c'est normal</span>
              <span v-else-if="getMostCommonMood().includes('angry')">😤 Besoin de calme ? Essayez un jeu relaxant</span>
              <span v-else-if="getMostCommonMood().includes('anxious')">😰 Rassurez-le avec des câlins et de la douceur</span>
              <span v-else>🎯 Gardez le rythme !</span>
            </div>
          </div>

          <div v-if="children.length === 0" class="empty-state">
            <div class="empty-icon">👶</div>
            <h3>No children added yet</h3>
            <p>Add your first child to start tracking their development</p>
          </div>

          <div v-if="selectedChild" class="quick-log-section">
            <div class="section-header">
              <h2>📝 Quick Log</h2>
              <button @click="showLogForm = !showLogForm" class="toggle-btn">
                {{ showLogForm ? '− Cancel' : '+ Log Today' }}
              </button>
            </div>

            <transition name="slide-fade">
              <div v-if="showLogForm" class="log-form-card">
                <div class="form-grid">
                  <div class="form-field">
                    <label>Focus Level</label>
                    <div class="slider-container">
                      <input v-model="focus" type="range" min="1" max="5" step="1" class="focus-slider">
                      <div class="stars-preview">
                        <span v-for="i in 5" :key="i" class="star" :class="{ filled: i <= focus }">★</span>
                      </div>
                    </div>
                  </div>

                  <div class="form-field">
                    <label>Mood</label>
                    <div class="mood-buttons">
                      <button v-for="m in ['happy', 'neutral', 'sad', 'excited', 'anxious']" :key="m"
                        @click="mood = m" class="mood-btn" :class="{ active: mood === m }">
                        {{ getMoodEmoji(m) }} {{ m }}
                      </button>
                    </div>
                  </div>

                  <div class="form-field">
                    <label>Sleep Hours</label>
                    <input v-model="sleep" type="number" step="0.5" min="0" max="24" class="form-input">
                  </div>

                  <div class="form-field">
                    <label>Social Interaction</label>
                    <div class="social-buttons">
                      <button v-for="i in 5" :key="i" @click="social = i" class="social-btn" :class="{ active: social >= i }">
                        {{ i }}
                      </button>
                    </div>
                  </div>

                  <div class="form-field full-width">
                    <label>Notes (optional)</label>
                    <textarea v-model="note" class="form-textarea" rows="2" placeholder="Any observations..."></textarea>
                  </div>
                </div>
                <button @click="addLog" class="save-btn">💾 Save Log</button>
              </div>
            </transition>
          </div>

          <div v-if="getActiveAlerts().length > 0" class="alerts-section">
            <div class="section-header">
              <h2>🔔 Smart Alerts</h2>
              <span class="alert-count">{{ getActiveAlerts().length }} alerts</span>
            </div>
            <div class="alerts-list">
              <div v-for="alert in getActiveAlerts()" :key="alert.message" class="alert-card" :class="alert.type">
                <span class="alert-icon">{{ alert.icon }}</span>
                <div class="alert-content">
                  <p>{{ alert.message }}</p>
                  <small>{{ alert.suggestion }}</small>
                </div>
              </div>
            </div>
          </div>

          <div v-if="insights && insights.pattern && insights.pattern.length" class="insights-section">
            <div class="section-header">
              <h2>🧠 Smart Insights</h2>
              <span class="insights-badge">AI Powered</span>
            </div>
            <div class="insights-card">
              <ul class="insights-list">
                <li v-for="pattern in insights.pattern" :key="pattern" class="insight-item">
                  <span class="insight-icon">💡</span>
                  <span>{{ pattern }}</span>
                </li>
              </ul>
              <div class="weekly-compare">
                <div class="compare-item">
                  <span class="compare-label">This week</span>
                  <span class="compare-value">{{ insights.weekly_comparison?.current_week_focus || 0 }}/5</span>
                </div>
                <div class="compare-arrow">→</div>
                <div class="compare-item">
                  <span class="compare-label">Last week</span>
                  <span class="compare-value">{{ insights.weekly_comparison?.previous_week_focus || 0 }}/5</span>
                </div>
                <div class="compare-change" :class="insights.weekly_comparison?.change > 0 ? 'positive' : 'negative'">
                  {{ insights.weekly_comparison?.change > 0 ? '+' : '' }}{{ insights.weekly_comparison?.change || 0 }}
                </div>
              </div>
            </div>
          </div>

          <div class="recommendations-section">
            <div class="section-header">
              <h2>🎯 Activity Recommendations</h2>
              <span class="recommendations-badge">Based on behavior</span>
            </div>
            <div class="recommendations-grid">
              <div v-for="rec in getRecommendations()" :key="rec.title" class="recommendation-card">
                <div class="rec-icon">{{ rec.icon }}</div>
                <div class="rec-content">
                  <h4>{{ rec.title }}</h4>
                  <p>{{ rec.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <ChartComponent v-if="selectedChild && selectedChild.id" :child-id="selectedChild.id" />

          <div class="additional-charts">
            <div class="charts-grid">
              <div class="mini-chart-card">
                <div class="mini-chart-header">
                  <span>😴 Sleep Pattern</span>
                  <span class="mini-chart-value">{{ insights?.avg_sleep || '-' }}h</span>
                </div>
                <div class="progress-bar">
                  <div class="progress-fill sleep-fill" :style="{ width: ((insights?.avg_sleep || 0) / 10 * 100) + '%' }"></div>
                </div>
              </div>
              <div class="mini-chart-card">
                <div class="mini-chart-header">
                  <span>👥 Social Interaction</span>
                  <span class="mini-chart-value">{{ insights?.avg_social || '-' }}/5</span>
                </div>
                <div class="stars-display">
                  <span v-for="i in 5" :key="i" class="star" :class="{ filled: i <= (insights?.avg_social || 0) }">★</span>
                </div>
              </div>
              <div class="mini-chart-card">
                <div class="mini-chart-header">
                  <span>😊 Mood Distribution</span>
                  <span class="mini-chart-value">{{ getMostCommonMood() }}</span>
                </div>
                <div class="mood-tags">
                  <span v-for="(count, moodName) in getMoodStats()" :key="moodName" class="mood-tag">
                    {{ getMoodEmoji(moodName) }} {{ count }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div v-if="logs.length > 0" class="logs-section">
            <div class="section-header">
              <h2>📋 Recent Activity</h2>
              <span class="logs-count">{{ logs.length }} total logs</span>
            </div>
            <div class="logs-table-wrapper">
              <table class="logs-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Focus</th>
                    <th>Mood</th>
                    <th>Sleep</th>
                    <th>Social</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="log in logs.slice(0, 8)" :key="log.id">
                    <td class="log-date">{{ formatDate(log.log_date) }}</td>
                    <td>
                      <div class="focus-indicator">
                        <div class="focus-bar" :style="{ width: (log.focus_level/5)*100 + '%' }"></div>
                        <span class="focus-value">{{ log.focus_level }}/5</span>
                      </div>
                    </td>
                    <td>
                      <span class="mood-badge" :style="{ background: getMoodColor(log.mood) + '20', color: getMoodColor(log.mood) }">
                        {{ getMoodEmoji(log.mood) }} {{ log.mood }}
                      </span>
                    </td>
                    <td><span class="sleep-value">{{ log.sleep_hours }}h</span></td>
                    <td>
                      <div class="social-stars">
                        <span v-for="i in 5" :key="i" class="small-star" :class="{ filled: i <= log.social_interaction }">★</span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div v-else-if="selectedChild && logs.length === 0" class="empty-logs">
            <div class="empty-icon">📭</div>
            <p>No behavior logs yet</p>
            <p class="empty-hint">Click "Log Today" to add your first observation!</p>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<style scoped>
/* Tous vos styles existants restent inchangés */
.dashboard-container {
  min-height: 100vh;
  background: #f5f7fb;
}

.hero-section {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 40px 0 60px 0;
  margin-bottom: -30px;
}

.hero-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.hero-title {
  font-size: 28px;
  font-weight: 700;
  color: white;
  margin-bottom: 8px;
}

.hero-subtitle {
  font-size: 16px;
  color: rgba(255,255,255,0.8);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px 40px 20px;
}

.loading-state {
  text-align: center;
  padding: 60px 20px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 3px solid #e0e6ed;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-state {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 24px;
}

.error-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 16px;
}

.retry-btn {
  margin-top: 20px;
  padding: 10px 24px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 30px;
  cursor: pointer;
}

.child-selector-card {
  background: white;
  border-radius: 20px;
  padding: 20px 24px;
  margin-bottom: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.selector-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
  color: #374151;
}

.selector-icon {
  font-size: 24px;
}

.child-select {
  padding: 10px 16px;
  border: 2px solid #e0e6ed;
  border-radius: 40px;
  font-size: 14px;
  background: white;
  cursor: pointer;
  min-width: 200px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
  margin-bottom: 32px;
}

.stat-card {
  background: white;
  border-radius: 20px;
  padding: 20px;
  position: relative;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  transition: transform 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
}

.risk-card {
  color: white;
}

.focus-card {
  border-left: 4px solid #667eea;
}

.sleep-card {
  border-left: 4px solid #10b981;
}

.social-card {
  border-left: 4px solid #f59e0b;
}

.points-card {
  border-left: 4px solid #f59e0b;
  background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.points-card .stat-icon {
  color: #f59e0b;
}

.stat-icon {
  font-size: 32px;
  margin-bottom: 12px;
}

.stat-info {
  margin-bottom: 8px;
}

.stat-label {
  display: block;
  font-size: 13px;
  opacity: 0.7;
  margin-bottom: 4px;
}

.stat-value {
  display: block;
  font-size: 28px;
  font-weight: 700;
}

.stat-trend {
  font-size: 12px;
  opacity: 0.8;
}

.trend-up { color: #10b981; }
.trend-down { color: #ef4444; }

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h2 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
}

.toggle-btn, .insights-badge, .logs-count, .recommendations-badge, .alert-count {
  padding: 6px 16px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  border: none;
}

.toggle-btn, .insights-badge, .logs-count {
  background: #eef2ff;
  color: #667eea;
}

.recommendations-badge {
  background: #fef3c7;
  color: #d97706;
}

.alert-count {
  background: #fee2e2;
  color: #dc2626;
}

.log-form-card {
  background: white;
  border-radius: 24px;
  padding: 24px;
  margin-bottom: 32px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
}

.form-field.full-width {
  grid-column: span 2;
}

.form-field label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 8px;
}

.form-input {
  width: 100%;
  padding: 10px 14px;
  border: 2px solid #e0e6ed;
  border-radius: 12px;
  font-size: 14px;
}

.form-textarea {
  width: 100%;
  padding: 10px 14px;
  border: 2px solid #e0e6ed;
  border-radius: 12px;
  font-size: 14px;
  resize: vertical;
}

.focus-slider {
  width: 100%;
  margin-bottom: 8px;
}

.stars-preview .star {
  font-size: 20px;
  color: #e5e7eb;
}

.stars-preview .star.filled {
  color: #f59e0b;
}

.mood-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.mood-btn {
  padding: 6px 12px;
  border: 2px solid #e0e6ed;
  border-radius: 30px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  font-size: 13px;
}

.mood-btn.active {
  border-color: #667eea;
  background: #eef2ff;
  color: #667eea;
}

.social-buttons {
  display: flex;
  gap: 8px;
}

.social-btn {
  width: 40px;
  height: 40px;
  border: 2px solid #e0e6ed;
  border-radius: 12px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  font-weight: 600;
}

.social-btn.active {
  background: #667eea;
  border-color: #667eea;
  color: white;
}

.save-btn {
  margin-top: 20px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  border-radius: 40px;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
}

.alerts-section {
  margin-bottom: 32px;
}

.alerts-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.alert-card {
  display: flex;
  gap: 12px;
  padding: 16px;
  border-radius: 16px;
  background: white;
  border-left: 4px solid;
}

.alert-card.alert-high {
  border-left-color: #dc2626;
  background: #fef2f2;
}

.alert-card.alert-medium {
  border-left-color: #f59e0b;
  background: #fffbeb;
}

.alert-card.alert-warning {
  border-left-color: #f59e0b;
  background: #fffbeb;
}

.alert-card.alert-info {
  border-left-color: #3b82f6;
  background: #eff6ff;
}

.alert-icon {
  font-size: 20px;
}

.alert-content p {
  font-size: 14px;
  font-weight: 500;
  margin-bottom: 4px;
}

.alert-content small {
  font-size: 11px;
  color: #6b7280;
}

.insights-section {
  margin-bottom: 32px;
}

.insights-card {
  background: linear-gradient(135deg, #eef2ff, #f3e8ff);
  border-radius: 24px;
  padding: 24px;
}

.insights-list {
  list-style: none;
  margin-bottom: 20px;
}

.insight-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 0;
  border-bottom: 1px solid rgba(0,0,0,0.05);
}

.insight-icon {
  font-size: 20px;
}

.weekly-compare {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 20px;
  padding-top: 16px;
  border-top: 1px solid rgba(0,0,0,0.1);
}

.compare-item {
  text-align: center;
}

.compare-label {
  display: block;
  font-size: 12px;
  color: #6b7280;
}

.compare-value {
  display: block;
  font-size: 20px;
  font-weight: 700;
  color: #374151;
}

.compare-arrow {
  font-size: 20px;
  color: #9ca3af;
}

.compare-change {
  font-size: 18px;
  font-weight: 700;
}

.compare-change.positive {
  color: #10b981;
}

.compare-change.negative {
  color: #ef4444;
}

.recommendations-section {
  margin-bottom: 32px;
}

.recommendations-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
}

.recommendation-card {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  border-radius: 16px;
  padding: 16px;
  display: flex;
  gap: 12px;
  transition: transform 0.2s;
}

.recommendation-card:hover {
  transform: translateY(-2px);
}

.rec-icon {
  font-size: 32px;
}

.rec-content h4 {
  font-size: 14px;
  font-weight: 700;
  margin-bottom: 4px;
  color: #92400e;
}

.rec-content p {
  font-size: 12px;
  color: #b45309;
}

.additional-charts {
  margin-bottom: 32px;
}

.charts-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
}

.mini-chart-card {
  background: white;
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.mini-chart-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 12px;
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
}

.mini-chart-value {
  color: #1a0a2e;
  font-weight: 700;
}

.progress-bar {
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  border-radius: 4px;
  transition: width 0.3s;
}

.sleep-fill {
  background: #8b5cf6;
}

.stars-display {
  display: flex;
  gap: 4px;
}

.stars-display .star {
  font-size: 18px;
  color: #e5e7eb;
}

.stars-display .star.filled {
  color: #f59e0b;
}

.mood-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.mood-tag {
  font-size: 12px;
  padding: 4px 8px;
  background: #f3f4f6;
  border-radius: 20px;
}

.logs-section {
  margin-top: 32px;
}

.logs-table-wrapper {
  background: white;
  border-radius: 20px;
  overflow-x: auto;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.logs-table {
  width: 100%;
  border-collapse: collapse;
}

.logs-table th {
  text-align: left;
  padding: 16px;
  background: #f9fafb;
  font-weight: 600;
  font-size: 13px;
  color: #6b7280;
}

.logs-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f3f4f6;
}

.log-date {
  font-weight: 500;
  color: #374151;
}

.focus-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
}

.focus-bar {
  width: 60px;
  height: 6px;
  background: #667eea;
  border-radius: 3px;
}

.focus-value {
  font-size: 13px;
}

.mood-badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 500;
}

.sleep-value {
  font-weight: 500;
}

.social-stars {
  display: flex;
  gap: 3px;
}

.small-star {
  font-size: 12px;
  color: #e5e7eb;
}

.small-star.filled {
  color: #f59e0b;
}

.empty-state, .empty-logs {
  text-align: center;
  padding: 60px 20px;
  background: white;
  border-radius: 24px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
}

.empty-hint {
  font-size: 13px;
  color: #9ca3af;
  margin-top: 8px;
}

.slide-fade-enter-active {
  transition: all 0.3s ease-out;
}

.slide-fade-leave-active {
  transition: all 0.2s ease-in;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}

/* 🌤️ Météo des émotions - NOUVEAU STYLE */
.emotion-weather-card {
  background: white;
  border-radius: 24px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.emotion-weather-card h3 {
  font-size: 18px;
  font-weight: 600;
  margin-bottom: 20px;
  color: #1f2937;
}

.emotion-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
  gap: 15px;
  margin-bottom: 20px;
}

.emotion-item {
  text-align: center;
  padding: 12px;
  background: #f9fafb;
  border-radius: 16px;
  transition: all 0.3s ease;
}

.emotion-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.emotion-emoji {
  font-size: 36px;
  margin-bottom: 8px;
}

.emotion-name {
  font-size: 12px;
  font-weight: 600;
  color: #4b5563;
  text-transform: capitalize;
  margin-bottom: 4px;
}

.emotion-count {
  font-size: 14px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.emotion-bar {
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.emotion-fill {
  height: 100%;
  border-radius: 3px;
  transition: width 0.5s ease;
}

.emotion-message {
  text-align: center;
  padding: 16px;
  background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
  border-radius: 16px;
  font-size: 14px;
  font-weight: 500;
  color: #374151;
}

/* Responsive pour météo des émotions */
@media (max-width: 600px) {
  .emotion-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }
  
  .emotion-item {
    padding: 8px;
  }
  
  .emotion-emoji {
    font-size: 28px;
  }
}

@media (max-width: 900px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .charts-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
  }
  
  .stat-card {
    padding: 12px;
  }
  
  .stat-icon {
    font-size: 24px;
    margin-bottom: 8px;
  }
  
  .stat-value {
    font-size: 20px;
  }
  
  .stat-label {
    font-size: 11px;
  }
  
  .stat-trend {
    font-size: 10px;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .charts-grid {
    grid-template-columns: 1fr;
  }
  
  .recommendations-grid {
    grid-template-columns: 1fr;
  }
  
  .logs-table th,
  .logs-table td {
    padding: 8px;
    font-size: 12px;
  }
  
  .focus-bar {
    width: 40px;
  }
}

@media (max-width: 600px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .form-field.full-width {
    grid-column: span 1;
  }
  
  .child-selector-card {
    flex-direction: column;
    text-align: center;
  }
  
  .weekly-compare {
    flex-wrap: wrap;
  }
  
  .recommendations-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .hero-title {
    font-size: 20px;
  }
  
  .hero-subtitle {
    font-size: 14px;
  }
}
</style>