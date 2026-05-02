<template>
  <div class="toast-notifications">
    <transition-group name="toast" tag="div">
      <div 
        v-for="notification in notifications" 
        :key="notification.id"
        class="toast"
        :class="notification.type"
      >
        <div class="toast-icon">
          <span v-if="notification.type === 'success'">✓</span>
          <span v-else-if="notification.type === 'error'">⚠️</span>
          <span v-else-if="notification.type === 'warning'">⚠️</span>
          <span v-else>ℹ️</span>
        </div>
        <div class="toast-content">
          <div class="toast-title">{{ notification.title }}</div>
          <div class="toast-message">{{ notification.message }}</div>
        </div>
        <button @click="removeNotification(notification.id)" class="toast-close">✕</button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const notifications = ref([])

let nextId = 0

const addNotification = (type, title, message, duration = 4000) => {
  const id = nextId++
  notifications.value.push({ id, type, title, message })
  
  if (duration > 0) {
    setTimeout(() => {
      removeNotification(id)
    }, duration)
  }
}

const removeNotification = (id) => {
  const index = notifications.value.findIndex(n => n.id === id)
  if (index !== -1) {
    notifications.value.splice(index, 1)
  }
}

// Exposer les méthodes
defineExpose({ addNotification })
</script>

<style scoped>
.toast-notifications {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.toast {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 14px 18px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  min-width: 320px;
  max-width: 400px;
  animation: slideInRight 0.3s ease;
  border-left: 4px solid;
}

.toast.success {
  border-left-color: #10b981;
}
.toast.success .toast-icon {
  background: #d1fae5;
  color: #065f46;
}

.toast.error {
  border-left-color: #ef4444;
}
.toast.error .toast-icon {
  background: #fee2e2;
  color: #991b1b;
}

.toast.warning {
  border-left-color: #f59e0b;
}
.toast.warning .toast-icon {
  background: #fef3c7;
  color: #92400e;
}

.toast.info {
  border-left-color: #3b82f6;
}
.toast.info .toast-icon {
  background: #dbeafe;
  color: #1e40af;
}

.toast-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
  font-weight: bold;
  flex-shrink: 0;
}

.toast-content {
  flex: 1;
}

.toast-title {
  font-size: 14px;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 2px;
}

.toast-message {
  font-size: 12px;
  color: #6b7280;
  line-height: 1.4;
}

.toast-close {
  background: none;
  border: none;
  font-size: 16px;
  cursor: pointer;
  color: #9ca3af;
  padding: 4px;
  transition: all 0.2s;
}

.toast-close:hover {
  color: #6b7280;
  transform: scale(1.1);
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(100%);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.toast-enter-active, .toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}

@media (max-width: 500px) {
  .toast {
    min-width: 280px;
    max-width: 350px;
    padding: 12px 16px;
  }
}
</style>