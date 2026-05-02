<script setup>
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler } from 'chart.js'
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler)

const props = defineProps({
  childId: {
    type: [Number, String],
    required: true
  }
})

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

const hasData = ref(false)
const isLoading = ref(true)

const options = {
  responsive: true,
  maintainAspectRatio: true,
  plugins: {
    legend: {
      position: 'top',
      labels: {
        usePointStyle: true,
        boxWidth: 8
      }
    },
    tooltip: {
      mode: 'index',
      intersect: false,
      backgroundColor: 'rgba(0,0,0,0.8)',
      titleColor: 'white',
      bodyColor: '#e5e7eb',
      padding: 10,
      cornerRadius: 8
    }
  },
  scales: {
    y: {
      min: 0,
      max: 5,
      title: {
        display: true,
        text: 'Focus Level',
        color: '#6b7280'
      },
      grid: {
        color: '#e5e7eb'
      },
      ticks: {
        stepSize: 1
      }
    },
    x: {
      title: {
        display: true,
        text: 'Date',
        color: '#6b7280'
      },
      grid: {
        display: false
      }
    }
  },
  elements: {
    line: {
      borderWidth: 2
    }
  }
}

const loadChartData = async () => {
  if (!props.childId) {
    console.warn('No childId provided to ChartComponent')
    isLoading.value = false
    return
  }
  
  isLoading.value = true
  
  try {
    console.log('ChartComponent loading data for child:', props.childId)
    const res = await axios.get(`/api/logs/${props.childId}`)
    const logs = res.data
    console.log('Logs received:', logs)
    
    if (logs && logs.length > 0) {
      hasData.value = true
      // Trier par date croissante pour le graphique
      const sortedLogs = [...logs].reverse()
      
      chartData.value.labels = sortedLogs.map(log => {
        const date = new Date(log.log_date)
        return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
      })
      chartData.value.datasets[0].data = sortedLogs.map(log => log.focus_level)
      
      console.log('Chart data loaded successfully')
    } else {
      hasData.value = false
      chartData.value.labels = []
      chartData.value.datasets[0].data = []
    }
  } catch (error) {
    console.error('Error loading chart data:', error)
    hasData.value = false
    chartData.value.labels = []
    chartData.value.datasets[0].data = []
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadChartData()
})

// Reload when childId changes
watch(() => props.childId, (newId) => {
  if (newId) {
    loadChartData()
  }
})
</script>

<template>
  <div class="chart-container" v-if="childId">
    <div class="chart-header">
      <h3>📊 Focus Level Over Time</h3>
      <p class="chart-subtitle">Track your child's focus progression</p>
    </div>
    <div class="chart-wrapper">
      <div v-if="isLoading" class="chart-loading">
        <div class="spinner-small"></div>
        <p>Loading chart data...</p>
      </div>
      <div v-else-if="!hasData" class="chart-empty">
        <span class="empty-chart-icon">📭</span>
        <p>No behavior logs yet</p>
        <p class="empty-hint">Click "Log Today" to add your first observation and see the chart!</p>
      </div>
      <Line v-else :data="chartData" :options="options" />
    </div>
  </div>
  <div v-else class="chart-placeholder">
    <span class="placeholder-icon">👶</span>
    <p>Select a child to view the focus chart</p>
  </div>
</template>

<style scoped>
.chart-container {
  background: white;
  border-radius: 24px;
  padding: 24px;
  margin-bottom: 32px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.chart-header {
  margin-bottom: 20px;
}

.chart-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 4px;
}

.chart-subtitle {
  font-size: 13px;
  color: #9ca3af;
}

.chart-wrapper {
  min-height: 320px;
  position: relative;
}

.chart-loading, .chart-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  text-align: center;
}

.spinner-small {
  width: 40px;
  height: 40px;
  border: 3px solid #e0e6ed;
  border-top-color: #1a0a2e;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-chart-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.chart-empty p {
  color: #9ca3af;
  margin: 4px 0;
}

.empty-hint {
  font-size: 12px;
  color: #cbd5e1;
}

.chart-placeholder {
  background: white;
  border-radius: 24px;
  padding: 48px;
  text-align: center;
  margin-bottom: 32px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.placeholder-icon {
  font-size: 48px;
  display: block;
  margin-bottom: 12px;
}

.chart-placeholder p {
  color: #9ca3af;
}
</style>