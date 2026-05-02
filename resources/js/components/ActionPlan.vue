<template>
  <div class="action-plan" v-if="plan">
    <!-- Entête -->
    <div class="plan-header" :class="riskClass">
      <div class="risk-icon">
        <span v-if="plan.risk_level === 'high'">🔴</span>
        <span v-else-if="plan.risk_level === 'medium'">🟡</span>
        <span v-else>🟢</span>
      </div>
      <div class="risk-info">
        <h3>Niveau de risque : {{ getRiskLabel(plan.risk_level) }}</h3>
        <p>Plan généré le {{ formatDate(plan.generated_date) }}</p>
      </div>
    </div>

    <!-- Journée type -->
    <div class="plan-schedule">
      <h3>📅 Programme de la journée</h3>
      
      <div class="time-block morning">
        <div class="time-icon">🌅</div>
        <div class="time-content">
          <h4>Matin</h4>
          <ul>
            <li v-for="activity in plan.morning_activities" :key="activity">
              {{ activity }}
            </li>
          </ul>
        </div>
      </div>

      <div class="time-block afternoon">
        <div class="time-icon">☀️</div>
        <div class="time-content">
          <h4>Après-midi</h4>
          <ul>
            <li v-for="activity in plan.afternoon_activities" :key="activity">
              {{ activity }}
            </li>
          </ul>
        </div>
      </div>

      <div class="time-block evening">
        <div class="time-icon">🌙</div>
        <div class="time-content">
          <h4>Soirée</h4>
          <ul>
            <li v-for="activity in plan.evening_activities" :key="activity">
              {{ activity }}
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Conseils de communication -->
    <div class="plan-tips">
      <h3>💬 Conseils de communication</h3>
      <ul>
        <li v-for="tip in plan.communication_tips" :key="tip">
          {{ tip }}
        </li>
      </ul>
    </div>

    <!-- Activités recommandées -->
    <div class="plan-games">
      <h3>🎮 Activités recommandées</h3>
      <div class="games-grid">
        <div v-for="game in plan.games_activities" :key="game" class="game-card">
          {{ game }}
        </div>
      </div>
    </div>

    <!-- Bouton d'impression -->
    <button @click="printPlan" class="btn-print">
      🖨️ Imprimer le plan
    </button>
  </div>

  <div v-else-if="loading" class="loading-plan">
    <div class="spinner"></div>
    <p>Génération du plan personnalisé...</p>
  </div>

  <div v-else-if="!plan && !loading" class="no-plan">
    <div class="no-plan-icon">📋</div>
    <p>Cliquez sur "Générer mon plan" pour obtenir un programme personnalisé</p>
    <button @click="generatePlan" class="btn-generate">
      ✨ Générer mon plan
    </button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

const props = defineProps({
  childId: {
    type: [Number, String],
    required: true
  }
})

const plan = ref(null)
const loading = ref(false)

const riskClass = computed(() => {
  if (!plan.value) return ''
  return {
    'high': 'risk-high',
    'medium': 'risk-medium',
    'low': 'risk-low'
  }[plan.value.risk_level]
})

const getRiskLabel = (risk) => {
  return {
    'high': 'Élevé - Intervention recommandée',
    'medium': 'Modéré - Surveillance active',
    'low': 'Faible - Maintenir les bonnes habitudes'
  }[risk]
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const generatePlan = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/action-plan/${props.childId}/generate`)
    plan.value = res.data
  } catch (err) {
    console.error('Error generating plan:', err)
  } finally {
    loading.value = false
  }
}

const loadLatestPlan = async () => {
  try {
    const res = await axios.get(`/api/action-plan/${props.childId}/latest`)
    plan.value = res.data
  } catch (err) {
    console.log('No plan yet')
  }
}

const printPlan = () => {
  const printContent = document.querySelector('.action-plan').cloneNode(true)
  const originalContent = document.body.innerHTML
  document.body.innerHTML = printContent.outerHTML
  window.print()
  document.body.innerHTML = originalContent
  window.location.reload()
}

// Charger le dernier plan au montage
loadLatestPlan()

defineExpose({ generatePlan })
</script>

<style scoped>
.action-plan {
  background: white;
  border-radius: 24px;
  padding: 24px;
  margin-top: 24px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.plan-header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  border-radius: 16px;
  margin-bottom: 24px;
}

.risk-high {
  background: linear-gradient(135deg, #fef2f2, #fee2e2);
  border-left: 4px solid #ef4444;
}

.risk-medium {
  background: linear-gradient(135deg, #fffbeb, #fef3c7);
  border-left: 4px solid #f59e0b;
}

.risk-low {
  background: linear-gradient(135deg, #ecfdf5, #d1fae5);
  border-left: 4px solid #10b981;
}

.risk-icon {
  font-size: 48px;
}

.risk-info h3 {
  font-size: 18px;
  margin-bottom: 4px;
}

.risk-info p {
  font-size: 12px;
  color: #6b7280;
}

.plan-schedule h3,
.plan-tips h3,
.plan-games h3 {
  font-size: 18px;
  margin-bottom: 16px;
  color: #1f2937;
}

.time-block {
  display: flex;
  gap: 16px;
  padding: 16px;
  margin-bottom: 12px;
  background: #f9fafb;
  border-radius: 16px;
  transition: transform 0.2s;
}

.time-block:hover {
  transform: translateX(5px);
}

.time-icon {
  font-size: 32px;
}

.time-content h4 {
  font-size: 16px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #374151;
}

.time-content ul {
  margin: 0;
  padding-left: 20px;
}

.time-content li {
  font-size: 14px;
  color: #4b5563;
  margin-bottom: 4px;
}

.plan-tips ul {
  list-style: none;
  padding: 0;
}

.plan-tips li {
  padding: 12px;
  margin-bottom: 8px;
  background: #f3f4f6;
  border-radius: 12px;
  font-size: 14px;
  color: #374151;
}

.games-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px;
}

.game-card {
  padding: 12px;
  background: linear-gradient(135deg, #eef2ff, #e0e7ff);
  border-radius: 12px;
  text-align: center;
  font-size: 14px;
  font-weight: 500;
  color: #1e40af;
}

.btn-print, .btn-generate {
  margin-top: 24px;
  padding: 12px 24px;
  border: none;
  border-radius: 40px;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  transition: all 0.3s;
}

.btn-generate {
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
}

.btn-print {
  background: #f3f4f6;
  color: #374151;
}

.btn-generate:hover, .btn-print:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.loading-plan, .no-plan {
  text-align: center;
  padding: 60px;
  background: white;
  border-radius: 24px;
  margin-top: 24px;
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

@keyframes spin {
  to { transform: rotate(360deg); }
}

.no-plan-icon {
  font-size: 64px;
  margin-bottom: 16px;
  opacity: 0.5;
}

@media (max-width: 768px) {
  .games-grid {
    grid-template-columns: 1fr;
  }
  
  .time-block {
    flex-direction: column;
    text-align: center;
  }
}
</style>