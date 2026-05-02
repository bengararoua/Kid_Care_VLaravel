<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import axios from 'axios'
import ConfirmDialog from './ConfirmDialog.vue'

const notifications = ref([])
const unreadCount = ref(0)
const showDropdown = ref(false)
const loading = ref(false)

// Référence pour le dialogue de confirmation
const confirmDialog = ref(null)

// Charger les notifications
const loadNotifications = async () => {
  try {
    const res = await axios.get('/api/notifications')
    notifications.value = res.data.notifications
    unreadCount.value = res.data.unreadCount
  } catch (err) {
    console.error('Error loading notifications:', err)
  }
}

// Marquer comme lu
const markAsRead = async (id) => {
  try {
    await axios.put(`/api/notifications/${id}/read`)
    const notification = notifications.value.find(n => n.id === id)
    if (notification && !notification.is_read) {
      notification.is_read = true
      unreadCount.value--
    }
  } catch (err) {
    console.error('Error marking as read:', err)
  }
}

// Marquer tout comme lu
const markAllAsRead = async () => {
  try {
    await axios.put('/api/notifications/read-all')
    notifications.value.forEach(n => n.is_read = true)
    unreadCount.value = 0
  } catch (err) {
    console.error('Error marking all as read:', err)
  }
}

// Supprimer une notification
const deleteNotification = async (id) => {
  try {
    await axios.delete(`/api/notifications/${id}`)
    const deletedNotif = notifications.value.find(n => n.id === id)
    notifications.value = notifications.value.filter(n => n.id !== id)
    if (deletedNotif && !deletedNotif.is_read) {
      unreadCount.value--
    }
  } catch (err) {
    console.error('Error deleting notification:', err)
  }
}

// Supprimer toutes les notifications avec confirmation personnalisée
const deleteAllNotifications = async () => {
  const confirmed = await confirmDialog.value?.show({
    title: 'Supprimer toutes les notifications',
    message: 'Êtes-vous sûr de vouloir supprimer toutes les notifications ? Cette action est irréversible.',
    type: 'danger',
    confirmText: 'Supprimer tout',
    cancelText: 'Annuler'
  })
  
  if (confirmed) {
    try {
      await axios.delete('/api/notifications')
      notifications.value = []
      unreadCount.value = 0
    } catch (err) {
      console.error('Error deleting all notifications:', err)
    }
  }
}

// Obtenir l'icône selon le type
const getNotificationIcon = (type) => {
  const icons = {
    behavior_alert: '⚠️',
    message: '💬',
    reminder: '🔔',
    recommendation: '🎯',
    system: 'ℹ️',
    new_behavior_log: '📝',
    milestone: '🏆'
  }
  return icons[type] || '📢'
}

// Obtenir la couleur selon le type
const getNotificationColor = (type) => {
  const colors = {
    behavior_alert: {
      bg: '#fee2e2',
      border: '#ef4444',
      text: '#991b1b'
    },
    message: {
      bg: '#e0e7ff',
      border: '#667eea',
      text: '#1e3a8a'
    },
    reminder: {
      bg: '#fed7aa',
      border: '#f59e0b',
      text: '#92400e'
    },
    recommendation: {
      bg: '#d1fae5',
      border: '#10b981',
      text: '#065f46'
    },
    system: {
      bg: '#e5e7eb',
      border: '#6b7280',
      text: '#374151'
    },
    new_behavior_log: {
      bg: '#fce7f3',
      border: '#ec4899',
      text: '#9d174d'
    },
    milestone: {
      bg: '#dbeafe',
      border: '#3b82f6',
      text: '#1e40af'
    }
  }
  return colors[type] || colors.system
}

// Formater la date relative
const formatTime = (date) => {
  const d = new Date(date)
  const now = new Date()
  const diff = Math.floor((now - d) / 1000)
  
  if (diff < 60) return 'À l\'instant'
  if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`
  if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} heure${Math.floor(diff / 3600) > 1 ? 's' : ''}`
  if (diff < 604800) return `Il y a ${Math.floor(diff / 86400)} jour${Math.floor(diff / 86400) > 1 ? 's' : ''}`
  return d.toLocaleDateString('fr-FR')
}

// Fermer le dropdown en cliquant ailleurs (CORRIGÉ)
const handleClickOutside = (event) => {
  const container = document.querySelector('.notifications-container')
  // Vérifier si le clic est à l'intérieur du container
  if (container && !container.contains(event.target)) {
    showDropdown.value = false
  }
}

// Toggle dropdown avec arrêt de propagation (CORRIGÉ)
const toggleDropdown = (event) => {
  event.stopPropagation() // Empêche l'événement de se propager
  showDropdown.value = !showDropdown.value
}

let interval
onMounted(() => {
  loadNotifications()
  interval = setInterval(loadNotifications, 30000)
  // Ajouter un délai avant d'attacher l'écouteur pour éviter les conflits
  setTimeout(() => {
    document.addEventListener('click', handleClickOutside)
  }, 100)
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <div class="notifications-container">
    <!-- Dialogue de confirmation -->
    <ConfirmDialog ref="confirmDialog" />

    <button @click="toggleDropdown" class="notification-btn" :class="{ active: showDropdown }">
      <span class="bell-icon">🔔</span>
      <span v-if="unreadCount > 0" class="notification-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
    </button>

    <Transition name="dropdown">
      <div v-if="showDropdown" class="notifications-dropdown" @click.stop>
        <div class="dropdown-header">
          <h3>
            Notifications
            <span v-if="unreadCount > 0" class="unread-count-badge">{{ unreadCount }}</span>
          </h3>
          <div class="dropdown-actions">
            <button 
              v-if="unreadCount > 0" 
              @click="markAllAsRead" 
              class="action-btn mark-all-btn"
              title="Marquer tout comme lu"
            >
              ✓ Tout lire
            </button>
            <button 
              v-if="notifications.length > 0" 
              @click="deleteAllNotifications" 
              class="action-btn delete-all-btn"
              title="Supprimer tout"
            >
              🗑️ Tout supprimer
            </button>
          </div>
        </div>

        <div class="notifications-list">
          <div v-if="loading" class="loading-notifications">
            <div class="spinner-small"></div>
            <p>Chargement...</p>
          </div>

          <div v-else-if="notifications.length === 0" class="empty-notifications">
            <div class="empty-icon">📭</div>
            <p>Aucune notification</p>
            <p class="empty-subtitle">Les nouvelles notifications apparaîtront ici</p>
          </div>

          <TransitionGroup name="notification-item" tag="div" class="notifications-items">
            <div 
              v-for="notif in notifications" 
              :key="notif.id" 
              class="notification-item"
              :class="{ unread: !notif.is_read }"
            >
              <div 
                class="notification-icon" 
                :style="{ backgroundColor: getNotificationColor(notif.type).bg }"
              >
                <span class="icon-emoji">{{ getNotificationIcon(notif.type) }}</span>
              </div>
              <div class="notification-content" @click="markAsRead(notif.id)">
                <div class="notification-title">{{ notif.title }}</div>
                <div class="notification-message">{{ notif.message }}</div>
                <div class="notification-time">{{ formatTime(notif.created_at) }}</div>
              </div>
              <button @click.stop="deleteNotification(notif.id)" class="delete-notif-btn" title="Supprimer">
                ✕
              </button>
            </div>
          </TransitionGroup>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
.notifications-container {
  position: relative;
}

/* Bouton de notification */
.notification-btn {
  position: relative;
  background: rgba(255, 255, 255, 0.15);
  border: none;
  width: 42px;
  height: 42px;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: scale(1.05);
}

.notification-btn.active {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(0.98);
}

.bell-icon {
  font-size: 20px;
  color: white;
  transition: transform 0.3s ease;
}

.notification-btn:hover .bell-icon {
  transform: rotate(15deg);
}

/* Badge de notification */
.notification-badge {
  position: absolute;
  top: -4px;
  right: -4px;
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border-radius: 50%;
  min-width: 18px;
  height: 18px;
  font-size: 10px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 5px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  animation: pulse 0.6s ease;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.2); }
}

/* Dropdown animations */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.95);
}

.dropdown-enter-to,
.dropdown-leave-from {
  opacity: 1;
  transform: translateY(0) scale(1);
}

.notifications-dropdown {
  position: absolute;
  top: 50px;
  right: 0;
  width: 400px;
  max-height: 550px;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.05);
  z-index: 1000;
  overflow: hidden;
}

/* Header */
.dropdown-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  border-bottom: 1px solid #e2e8f0;
}

.dropdown-header h3 {
  font-size: 16px;
  font-weight: 700;
  margin: 0;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 8px;
}

.unread-count-badge {
  background: #ef4444;
  color: white;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 20px;
  font-weight: 600;
}

.dropdown-actions {
  display: flex;
  gap: 12px;
}

.action-btn {
  background: none;
  border: none;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  padding: 6px 12px;
  border-radius: 20px;
  transition: all 0.2s;
}

.mark-all-btn {
  color: #3b82f6;
  background: #eff6ff;
}

.mark-all-btn:hover {
  background: #dbeafe;
  transform: translateY(-1px);
}

.delete-all-btn {
  color: #ef4444;
  background: #fef2f2;
}

.delete-all-btn:hover {
  background: #fee2e2;
  transform: translateY(-1px);
}

/* Liste des notifications */
.notifications-list {
  max-height: 480px;
  overflow-y: auto;
}

.notifications-list::-webkit-scrollbar {
  width: 6px;
}

.notifications-list::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.notifications-list::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.notifications-list::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* États vides/chargement */
.loading-notifications,
.empty-notifications {
  text-align: center;
  padding: 50px 20px;
  color: #94a3b8;
}

.spinner-small {
  width: 32px;
  height: 32px;
  border: 3px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 12px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-icon {
  font-size: 56px;
  display: block;
  margin-bottom: 12px;
  opacity: 0.5;
}

.empty-subtitle {
  font-size: 12px;
  margin-top: 8px;
  color: #cbd5e1;
}

/* Items de notification */
.notifications-items {
  display: flex;
  flex-direction: column;
}

/* Animation des items */
.notification-item-enter-active,
.notification-item-leave-active {
  transition: all 0.3s ease;
}

.notification-item-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.notification-item-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

.notification-item-move {
  transition: transform 0.3s ease;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 16px 20px;
  border-bottom: 1px solid #f1f5f9;
  transition: all 0.2s;
  position: relative;
}

.notification-item:hover {
  background: #f8fafc;
}

/* Notification non lue */
.notification-item.unread {
  background: linear-gradient(90deg, #f0f9ff 0%, white 100%);
  animation: slideIn 0.3s ease;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(-10px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.notification-item.unread::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 3px;
  background: #3b82f6;
  animation: heightGrow 0.3s ease;
}

@keyframes heightGrow {
  from { height: 0; }
  to { height: 100%; }
}

/* Icône de notification */
.notification-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.2s;
}

.notification-item:hover .notification-icon {
  transform: scale(1.05);
}

.icon-emoji {
  font-size: 22px;
}

/* Contenu */
.notification-content {
  flex: 1;
  cursor: pointer;
}

.notification-title {
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 4px;
}

.notification-message {
  font-size: 12px;
  color: #475569;
  margin-bottom: 6px;
  line-height: 1.4;
}

.notification-time {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 500;
}

/* Bouton supprimer */
.delete-notif-btn {
  background: none;
  border: none;
  font-size: 16px;
  cursor: pointer;
  color: #cbd5e1;
  padding: 6px;
  border-radius: 8px;
  transition: all 0.2s;
  opacity: 0;
  flex-shrink: 0;
}

.notification-item:hover .delete-notif-btn {
  opacity: 1;
}

.delete-notif-btn:hover {
  background: #fee2e2;
  color: #ef4444;
  transform: scale(1.1);
}

/* Responsive */
@media (max-width: 500px) {
  .notifications-dropdown {
    width: 360px;
    right: -60px;
  }
  
  .dropdown-header {
    padding: 12px 16px;
  }
  
  .notification-item {
    padding: 12px 16px;
  }
}
</style>