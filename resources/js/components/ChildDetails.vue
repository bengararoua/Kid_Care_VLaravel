<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Layout from './Layout.vue'
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler } from 'chart.js'
import RoutinePlanner from './RoutinePlanner.vue'
import ActionPlan from './ActionPlan.vue'

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler)

const route = useRoute()
const router = useRouter()
const childId = route.params.id

// Données
const child = ref(null)
const behaviors = ref([])
const notes = ref([])
const recommendations = ref([])
const loading = ref(false)
const error = ref(null)
const success = ref(null)
const activeTab = ref('timeline')

// Référence au composant ActionPlan
const actionPlan = ref(null)

// Formulaire pour ajouter une note (psychologue uniquement)
const showNoteForm = ref(false)
const noteForm = ref({
  note: '',
  session_date: new Date().toISOString().split('T')[0]
})

// Formulaire pour ajouter une recommandation (psychologue uniquement)
const showRecommendationForm = ref(false)
const recommendationForm = ref({
  title: '',
  description: '',
  category: 'focus'
})

// Utilisateur connecté
const user = computed(() => JSON.parse(localStorage.getItem('user') || '{}'))
const isPsychologist = computed(() => user.value?.role === 'psychologist')
const isParent = computed(() => user.value?.role === 'parent')
const isTeacher = computed(() => user.value?.role === 'teacher')

// Données pour le graphique
const chartData = ref({
  labels: [],
  datasets: [{
    label: 'Focus Level',
    data: [],
    borderColor: '#4F8EF7',
    backgroundColor: 'rgba(79, 142, 247, 0.1)',
    tension: 0.3,
    fill: true,
    pointBackgroundColor: '#4F8EF7',
    pointBorderColor: 'white',
    pointRadius: 4,
    pointHoverRadius: 6
  }]
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: { position: 'top' },
    tooltip: { mode: 'index', intersect: false }
  },
  scales: {
    y: { min: 0, max: 5, title: { display: true, text: 'Focus Level' }, ticks: { stepSize: 1 } },
    x: { title: { display: true, text: 'Date' }, grid: { display: false } }
  }
}

// Fonctions de calcul des moyennes
const calculateAverageSleep = () => {
  if (!behaviors.value.length) return '0.0'
  let total = 0
  let count = 0
  behaviors.value.forEach(log => {
    const sleep = parseFloat(log.sleep_hours)
    if (!isNaN(sleep) && sleep > 0) {
      total += sleep
      count++
    }
  })
  return count > 0 ? (total / count).toFixed(1) : '0.0'
}

const calculateAverageFocus = () => {
  if (!behaviors.value.length) return '0.0'
  let total = 0
  let count = 0
  behaviors.value.forEach(log => {
    const focus = parseInt(log.focus_level)
    if (!isNaN(focus)) {
      total += focus
      count++
    }
  })
  return count > 0 ? (total / count).toFixed(1) : '0.0'
}

// Charger les données
const loadData = async () => {
  if (!childId) return
  
  loading.value = true
  error.value = null
  
  try {
    const [childRes, behaviorsRes, notesRes, recommendationsRes] = await Promise.all([
      axios.get(`/api/children/${childId}`),
      axios.get(`/api/logs/${childId}`),
      axios.get(`/api/children/${childId}/notes`).catch(() => ({ data: [] })),
      axios.get(`/api/children/${childId}/recommendations`).catch(() => ({ data: [] }))
    ])
    
    child.value = childRes.data
    behaviors.value = behaviorsRes.data || []
    notes.value = notesRes.data || []
    recommendations.value = recommendationsRes.data || []
    
    // Préparer les données du graphique
    if (behaviors.value.length > 0) {
      const sortedLogs = [...behaviors.value].reverse()
      chartData.value.labels = sortedLogs.map(log => {
        const date = new Date(log.log_date)
        return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
      })
      chartData.value.datasets[0].data = sortedLogs.map(log => log.focus_level)
    }
  } catch (err) {
    console.error('Error loading child data:', err)
    error.value = 'Failed to load child data'
  } finally {
    loading.value = false
  }
}

// Ajouter une note (psychologue)
const addNote = async () => {
  if (!noteForm.value.note.trim()) {
    error.value = 'Please enter a note'
    return
  }
  
  loading.value = true
  try {
    await axios.post(`/api/children/${childId}/notes`, noteForm.value)
    success.value = 'Note added successfully!'
    setTimeout(() => { success.value = null }, 3000)
    
    noteForm.value = { note: '', session_date: new Date().toISOString().split('T')[0] }
    showNoteForm.value = false
    await loadData()
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to add note'
    setTimeout(() => { error.value = null }, 3000)
  } finally {
    loading.value = false
  }
}

// Ajouter une recommandation (psychologue)
const addRecommendation = async () => {
  if (!recommendationForm.value.title.trim() || !recommendationForm.value.description.trim()) {
    error.value = 'Please fill all fields'
    return
  }
  
  loading.value = true
  try {
    await axios.post(`/api/children/${childId}/recommendations`, recommendationForm.value)
    success.value = 'Recommendation added successfully!'
    setTimeout(() => { success.value = null }, 3000)
    
    recommendationForm.value = { title: '', description: '', category: 'focus' }
    showRecommendationForm.value = false
    await loadData()
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to add recommendation'
    setTimeout(() => { error.value = null }, 3000)
  } finally {
    loading.value = false
  }
}

// Marquer une recommandation comme complétée
const toggleRecommendation = async (rec) => {
  try {
    await axios.put(`/api/recommendations/${rec.id}/toggle`)
    rec.is_completed = !rec.is_completed
    success.value = rec.is_completed ? 'Recommendation completed!' : 'Recommendation reopened'
    setTimeout(() => { success.value = null }, 3000)
  } catch (err) {
    error.value = 'Failed to update recommendation'
    setTimeout(() => { error.value = null }, 3000)
  }
}

// Supprimer une recommendation (psychologue)
const deleteRecommendation = async (rec) => {
  if (!confirm('Are you sure you want to delete this recommendation?')) return
  
  try {
    await axios.delete(`/api/recommendations/${rec.id}`)
    recommendations.value = recommendations.value.filter(r => r.id !== rec.id)
    success.value = 'Recommendation deleted!'
    setTimeout(() => { success.value = null }, 3000)
  } catch (err) {
    error.value = 'Failed to delete recommendation'
    setTimeout(() => { error.value = null }, 3000)
  }
}

// Régénérer le plan d'action
const regeneratePlan = () => {
  if (actionPlan.value) {
    actionPlan.value.generatePlan()
    success.value = 'Plan d\'action régénéré avec succès!'
    setTimeout(() => { success.value = null }, 3000)
  }
}

// Envoyer un message au parent (pour les psychologues)
const messageParent = () => {
  if (child.value && child.value.parent) {
    router.push({
      path: '/messages',
      query: { contactId: child.value.parent.id, contactName: child.value.parent.name, contactRole: 'parent' }
    })
  } else {
    error.value = 'No parent assigned to this child'
    setTimeout(() => { error.value = null }, 3000)
  }
}

// Retour au dashboard
const goBack = () => {
  router.push('/dashboard')
}

// Formater la date
const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

// Obtenir l'icône de la catégorie
const getCategoryIcon = (category) => {
  const icons = {
    focus: '🧠', social: '👥', relaxation: '🧘', routine: '📅', sleep: '😴', nutrition: '🍎'
  }
  return icons[category] || '📌'
}

// Obtenir la couleur de la catégorie
const getCategoryColor = (category) => {
  const colors = {
    focus: '#667eea', social: '#10b981', relaxation: '#8b5cf6', routine: '#f59e0b', sleep: '#3b82f6', nutrition: '#ef4444'
  }
  return colors[category] || '#6b7280'
}

// Obtenir l'emoji du mood
const getMoodEmoji = (mood) => {
  const emojis = { 'happy': '😊', 'sad': '😢', 'angry': '😡', 'neutral': '😐', 'excited': '🤩', 'anxious': '😰' }
  return emojis[mood] || '😊'
}

// Obtenir la couleur du mood
const getMoodColor = (mood) => {
  const colors = { 'happy': '#10b981', 'sad': '#3b82f6', 'angry': '#ef4444', 'neutral': '#6b7280', 'excited': '#f59e0b', 'anxious': '#8b5cf6' }
  return colors[mood] || '#6b7280'
}

onMounted(() => {
  loadData()
})
</script>

<template>
  <Layout>
    <div class="child-details-container">
      <!-- Header -->
      <div class="details-header">
        <button @click="goBack" class="back-btn">← Back to Dashboard</button>
        <div class="header-info">
          <div class="child-avatar">{{ child?.name?.charAt(0) || '👶' }}</div>
          <div>
            <h1 class="child-name">{{ child?.name }}</h1>
            <p class="child-meta">{{ child?.age }} years old · Created {{ formatDate(child?.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading child data...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-state">
        <span class="error-icon">⚠️</span>
        <p>{{ error }}</p>
        <button @click="loadData" class="retry-btn">Try Again</button>
      </div>

      <div v-else class="details-content">
        <!-- Tabs -->
        <div class="tabs">
          <button @click="activeTab = 'timeline'" class="tab-btn" :class="{ active: activeTab === 'timeline' }">
            📋 Timeline
          </button>
          <button @click="activeTab = 'chart'" class="tab-btn" :class="{ active: activeTab === 'chart' }">
            📊 Progress Chart
          </button>
          <button v-if="isPsychologist" @click="activeTab = 'notes'" class="tab-btn" :class="{ active: activeTab === 'notes' }">
            📝 Psychologist Notes
          </button>
          <button @click="activeTab = 'recommendations'" class="tab-btn" :class="{ active: activeTab === 'recommendations' }">
            🎯 Recommendations
          </button>
        </div>

        <!-- Success Message -->
        <div v-if="success" class="alert-success">
          <span>✓</span> {{ success }}
        </div>

        <!-- Timeline Tab -->
        <div v-if="activeTab === 'timeline'" class="tab-content timeline-tab">
          <div v-if="behaviors.length === 0" class="empty-state">
            <div class="empty-icon">📭</div>
            <p>No behavior logs yet</p>
            <p class="empty-hint">Add logs from the dashboard to track progress</p>
          </div>
          
          <div v-else class="timeline">
            <div v-for="log in behaviors.slice(0, 30)" :key="log.id" class="timeline-item">
              <div class="timeline-marker" :style="{ background: getMoodColor(log.mood) }"></div>
              <div class="timeline-content">
                <div class="timeline-date">{{ formatDate(log.log_date) }}</div>
                <div class="timeline-stats">
                  <div class="stat-badge">
                    <span>🧠 Focus</span>
                    <strong>{{ log.focus_level }}/5</strong>
                  </div>
                  <div class="stat-badge">
                    <span>{{ getMoodEmoji(log.mood) }} Mood</span>
                    <strong>{{ log.mood }}</strong>
                  </div>
                  <div class="stat-badge">
                    <span>😴 Sleep</span>
                    <strong>{{ log.sleep_hours }}h</strong>
                  </div>
                  <div class="stat-badge">
                    <span>👥 Social</span>
                    <strong>{{ log.social_interaction }}/5</strong>
                  </div>
                </div>
                <p v-if="log.note" class="timeline-note">📝 {{ log.note }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart Tab -->
        <div v-if="activeTab === 'chart'" class="tab-content chart-tab">
          <div class="chart-card">
            <h3>📊 Focus Level Progression</h3>
            <p class="chart-subtitle">Track focus improvement over time</p>
            <div class="chart-wrapper">
              <div v-if="behaviors.length === 0" class="chart-empty">
                <span>📭</span>
                <p>No data available yet</p>
              </div>
              <Line v-else :data="chartData" :options="chartOptions" />
            </div>
          </div>

          <!-- Stats Summary -->
          <div class="stats-summary">
            <div class="stat-summary-card">
              <div class="stat-icon">📊</div>
              <div class="stat-info">
                <div class="stat-label">Total Logs</div>
                <div class="stat-number">{{ behaviors.length }}</div>
              </div>
            </div>
            <div class="stat-summary-card">
              <div class="stat-icon">🧠</div>
              <div class="stat-info">
                <div class="stat-label">Average Focus</div>
                <div class="stat-number">{{ calculateAverageFocus() }}/5</div>
              </div>
            </div>
            <div class="stat-summary-card">
              <div class="stat-icon">😴</div>
              <div class="stat-info">
                <div class="stat-label">Average Sleep</div>
                <div class="stat-number">{{ calculateAverageSleep() }}h</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Notes Tab (Psychologist only) -->
        <div v-if="activeTab === 'notes'" class="tab-content notes-tab">
          <div class="add-note-section">
            <button @click="showNoteForm = !showNoteForm" class="btn-add-note">
              {{ showNoteForm ? '− Cancel' : '+ Add Note' }}
            </button>
            
            <div v-if="showNoteForm" class="note-form">
              <div class="form-group">
                <label>Session Date</label>
                <input v-model="noteForm.session_date" type="date" class="form-input">
              </div>
              <div class="form-group">
                <label>Note</label>
                <textarea v-model="noteForm.note" rows="4" placeholder="Write your observations and recommendations..." class="form-textarea"></textarea>
              </div>
              <button @click="addNote" class="btn-save" :disabled="loading">💾 Save Note</button>
            </div>
          </div>

          <div v-if="notes.length === 0" class="empty-state">
            <div class="empty-icon">📝</div>
            <p>No notes yet</p>
            <p class="empty-hint">Add a note to document your observations</p>
          </div>

          <div v-else class="notes-list">
            <div v-for="note in notes" :key="note.id" class="note-card">
              <div class="note-header">
                <span class="note-icon">📋</span>
                <div>
                  <div class="note-date">{{ formatDate(note.session_date) }}</div>
                  <div class="note-author">Added by {{ note.psychologist?.name || 'Psychologist' }}</div>
                </div>
              </div>
              <div class="note-content">{{ note.note }}</div>
            </div>
          </div>
        </div>

        <!-- Recommendations Tab -->
        <div v-if="activeTab === 'recommendations'" class="tab-content recommendations-tab">
          <div v-if="isPsychologist" class="add-recommendation-section">
            <button @click="showRecommendationForm = !showRecommendationForm" class="btn-add-recommendation">
              {{ showRecommendationForm ? '− Cancel' : '+ Add Recommendation' }}
            </button>
            
            <div v-if="showRecommendationForm" class="recommendation-form">
              <div class="form-group">
                <label>Title</label>
                <input v-model="recommendationForm.title" type="text" placeholder="e.g., Morning Mindfulness" class="form-input">
              </div>
              <div class="form-group">
                <label>Category</label>
                <select v-model="recommendationForm.category" class="form-select">
                  <option value="focus">🧠 Focus</option>
                  <option value="social">👥 Social</option>
                  <option value="relaxation">🧘 Relaxation</option>
                  <option value="routine">📅 Routine</option>
                  <option value="sleep">😴 Sleep</option>
                  <option value="nutrition">🍎 Nutrition</option>
                </select>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea v-model="recommendationForm.description" rows="3" placeholder="Describe the recommended activity..." class="form-textarea"></textarea>
              </div>
              <button @click="addRecommendation" class="btn-save" :disabled="loading">💾 Save Recommendation</button>
            </div>
          </div>

          <div v-if="recommendations.length === 0" class="empty-state">
            <div class="empty-icon">🎯</div>
            <p>No recommendations yet</p>
            <p v-if="isPsychologist" class="empty-hint">Add recommendations to help guide the child's development</p>
          </div>

          <div v-else class="recommendations-list">
            <div v-for="rec in recommendations" :key="rec.id" class="recommendation-card" :class="{ completed: rec.is_completed }">
              <div class="rec-header">
                <div class="rec-icon" :style="{ background: getCategoryColor(rec.category) }">
                  {{ getCategoryIcon(rec.category) }}
                </div>
                <div class="rec-info">
                  <h4>{{ rec.title }}</h4>
                  <span class="rec-category">{{ rec.category }}</span>
                </div>
                <div class="rec-actions">
                  <button v-if="isParent || isTeacher" @click="toggleRecommendation(rec)" class="rec-toggle-btn" :class="{ completed: rec.is_completed }">
                    {{ rec.is_completed ? '✓ Completed' : '○ Mark Complete' }}
                  </button>
                  <button v-if="isPsychologist" @click="deleteRecommendation(rec)" class="rec-delete-btn" title="Delete">🗑️</button>
                </div>
              </div>
              <div class="rec-description">{{ rec.description }}</div>
              <div class="rec-date">Added {{ formatDate(rec.created_at) }}</div>
            </div>
          </div>
        </div>

        <!-- Action Plan Section -->
        <div class="action-plan-section">
          <div class="section-header">
            <h2>📋 Plan d'action personnalisé</h2>
            <button @click="regeneratePlan" class="btn-regenerate">
              🔄 Régénérer le plan
            </button>
          </div>
          <ActionPlan ref="actionPlan" :child-id="childId" />
        </div>

        <!-- Routine Planner -->
        <RoutinePlanner :child-id="childId" />
      </div>
    </div>
  </Layout>
</template>

<style scoped>
/* Vos styles existants - inchangés */
.child-details-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px;
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.details-header {
  margin-bottom: 30px;
}

.back-btn {
  background: none;
  border: none;
  color: #667eea;
  font-size: 14px;
  cursor: pointer;
  padding: 8px 0;
  margin-bottom: 20px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  transition: all 0.2s;
}

.back-btn:hover {
  transform: translateX(-3px);
  color: #764ba2;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 20px;
}

.child-avatar {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  font-weight: 700;
  color: white;
  animation: scaleIn 0.3s ease;
}

@keyframes scaleIn {
  from { transform: scale(0); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.child-name {
  font-size: 28px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 8px;
}

.child-meta {
  color: #6b7280;
  font-size: 14px;
}

/* Tabs */
.tabs {
  display: flex;
  gap: 12px;
  border-bottom: 2px solid #e0e6ed;
  margin-bottom: 24px;
  padding-bottom: 12px;
  flex-wrap: wrap;
}

.tab-btn {
  background: none;
  border: none;
  padding: 10px 20px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  border-radius: 30px;
  color: #6b7280;
}

.tab-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.tab-btn.active {
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
}

.tab-content {
  animation: fadeIn 0.3s ease;
}

/* Timeline */
.timeline {
  position: relative;
  padding-left: 30px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 10px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: linear-gradient(180deg, #667eea, #764ba2);
}

.timeline-item {
  position: relative;
  margin-bottom: 24px;
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from { opacity: 0; transform: translateX(-10px); }
  to { opacity: 1; transform: translateX(0); }
}

.timeline-marker {
  position: absolute;
  left: -26px;
  top: 5px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 2px solid white;
  box-shadow: 0 0 0 2px #e0e6ed;
}

.timeline-content {
  background: white;
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  transition: transform 0.2s;
}

.timeline-content:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.timeline-date {
  font-size: 12px;
  color: #9ca3af;
  margin-bottom: 12px;
}

.timeline-stats {
  display: flex;
  gap: 16px;
  flex-wrap: wrap;
}

.stat-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  padding: 4px 10px;
  background: #f3f4f6;
  border-radius: 20px;
}

.stat-badge strong {
  color: #1f2937;
}

.timeline-note {
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #f0f0f0;
  font-size: 13px;
  color: #6b7280;
}

/* Chart Tab */
.chart-card {
  background: white;
  border-radius: 20px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.chart-card h3 {
  font-size: 18px;
  margin-bottom: 8px;
}

.chart-subtitle {
  font-size: 13px;
  color: #9ca3af;
  margin-bottom: 20px;
}

.chart-wrapper {
  min-height: 350px;
}

.chart-empty {
  text-align: center;
  padding: 60px;
  color: #9ca3af;
}

.chart-empty span {
  font-size: 48px;
  display: block;
  margin-bottom: 16px;
}

/* Stats Summary */
.stats-summary {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 20px;
  margin-top: 20px;
}

.stat-summary-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  transition: transform 0.2s;
}

.stat-summary-card:hover {
  transform: translateY(-3px);
}

.stat-icon {
  font-size: 40px;
}

.stat-info {
  flex: 1;
}

.stat-label {
  font-size: 12px;
  color: #9ca3af;
  text-transform: uppercase;
}

.stat-number {
  font-size: 24px;
  font-weight: 700;
  color: #1f2937;
}

/* Notes Tab */
.add-note-section, .add-recommendation-section {
  margin-bottom: 24px;
}

.btn-add-note, .btn-add-recommendation {
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 40px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-add-note:hover, .btn-add-recommendation:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 10, 46, 0.3);
}

.note-form, .recommendation-form {
  background: white;
  border-radius: 20px;
  padding: 24px;
  margin-top: 16px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  animation: fadeIn 0.3s ease;
}

.notes-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.note-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  transition: transform 0.2s;
}

.note-card:hover {
  transform: translateY(-2px);
}

.note-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.note-icon {
  font-size: 32px;
}

.note-date {
  font-weight: 600;
  color: #1f2937;
  font-size: 14px;
}

.note-author {
  font-size: 11px;
  color: #9ca3af;
}

.note-content {
  font-size: 14px;
  color: #4b5563;
  line-height: 1.5;
}

/* Recommendations Tab */
.recommendations-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.recommendation-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  transition: all 0.2s;
  animation: fadeIn 0.3s ease;
}

.recommendation-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.recommendation-card.completed {
  opacity: 0.7;
  background: #f9fafb;
}

.rec-header {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.rec-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
}

.rec-info {
  flex: 1;
}

.rec-info h4 {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 4px;
}

.rec-category {
  font-size: 11px;
  background: #f3f4f6;
  padding: 2px 8px;
  border-radius: 20px;
  color: #6b7280;
}

.rec-actions {
  display: flex;
  gap: 8px;
}

.rec-toggle-btn {
  padding: 6px 14px;
  border: none;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  background: #f3f4f6;
  color: #6b7280;
}

.rec-toggle-btn.completed {
  background: #d1fae5;
  color: #065f46;
}

.rec-toggle-btn:hover {
  transform: scale(1.02);
}

.rec-delete-btn {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  opacity: 0.5;
  transition: all 0.2s;
}

.rec-delete-btn:hover {
  opacity: 1;
  color: #ef4444;
}

.rec-description {
  font-size: 14px;
  color: #4b5563;
  margin-bottom: 12px;
  line-height: 1.5;
}

.rec-date {
  font-size: 11px;
  color: #9ca3af;
}

/* Action Plan Section */
.action-plan-section {
  margin-top: 32px;
  margin-bottom: 32px;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 12px;
}

.section-header h2 {
  font-size: 20px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.btn-regenerate {
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
  padding: 8px 20px;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
}

.btn-regenerate:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 10, 46, 0.3);
}

/* Form Styles */
.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 8px;
}

.form-input, .form-select, .form-textarea {
  width: 100%;
  padding: 12px 14px;
  border: 2px solid #e0e6ed;
  border-radius: 12px;
  font-size: 14px;
  transition: all 0.2s;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: #1a0a2e;
}

.btn-save {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 40px;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: all 0.3s;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* Alert Messages */
.alert-success {
  padding: 12px 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
  background: #d1fae5;
  color: #065f46;
  border-left: 4px solid #10b981;
  animation: slideIn 0.3s ease;
}

/* Loading & Error States */
.loading-state, .error-state, .empty-state {
  text-align: center;
  padding: 60px;
  background: white;
  border-radius: 24px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 3px solid #e0e6ed;
  border-top-color: #1a0a2e;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-hint {
  font-size: 13px;
  color: #9ca3af;
  margin-top: 8px;
}

/* Bouton Message Parent */
.card-footer {
  display: flex;
  gap: 12px;
  padding: 16px 20px;
  background: #f9fafb;
  border-top: 1px solid #e0e6ed;
}

.btn-view {
  flex: 1;
  padding: 10px;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
}

.btn-view:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 10, 46, 0.3);
}

.btn-message {
  flex: 1;
  padding: 10px;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  background: #eef2ff;
  color: #4f46e5;
}

.btn-message:hover {
  background: #e0e7ff;
  transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
  .stats-summary {
    grid-template-columns: 1fr;
  }
  
  .timeline-stats {
    flex-direction: column;
    gap: 8px;
  }
  
  .header-info {
    flex-direction: column;
    text-align: center;
  }
  
  .tabs {
    justify-content: center;
  }
  
  .rec-header {
    flex-direction: column;
    text-align: center;
  }
  
  .card-footer {
    flex-direction: column;
  }
  
  .action-plan-section .section-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>