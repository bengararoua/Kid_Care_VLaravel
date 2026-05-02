<script setup>
import Layout from './Layout.vue'

const props = defineProps({
  title: String,
  message: String
})
</script>

<template>
  <Layout>
    <div class="placeholder-container">
      <div class="placeholder-card">
        <div class="placeholder-icon">🚧</div>
        <h2>{{ title || 'Coming Soon' }}</h2>
        <p>{{ message || 'This page is under construction.' }}</p>
        <button @click="$router.push('/dashboard')" class="back-btn">
          ← Back to Dashboard
        </button>
      </div>
    </div>
  </Layout>
</template>

<style scoped>
.placeholder-container {
  min-height: 60vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
}

.placeholder-card {
  text-align: center;
  background: white;
  border-radius: 24px;
  padding: 48px;
  max-width: 500px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.placeholder-icon {
  font-size: 64px;
  margin-bottom: 20px;
}

.placeholder-card h2 {
  font-size: 24px;
  color: #1f2937;
  margin-bottom: 12px;
}

.placeholder-card p {
  color: #6b7280;
  margin-bottom: 24px;
}

.back-btn {
  padding: 10px 24px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 30px;
  cursor: pointer;
}
</style>