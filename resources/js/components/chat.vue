<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import Layout from './Layout.vue'

const route = useRoute()
const conversations = ref([])
const contacts = ref([])
const currentContact = ref(null)
const messages = ref([])
const newMessage = ref('')
const loading = ref(false)
const unreadCount = ref(0)
const messagesContainer = ref(null)
const showSidebar = ref(true)
const isMobile = ref(window.innerWidth < 768)
const sendingMessage = ref(false)

// Rendez-vous
const showAppointmentModal = ref(false)
const appointmentData = ref({
  title: '',
  date: '',
  time: '',
  duration: 30,
  location: '',
  notes: '',
  type: 'video'
})
const creatingAppointment = ref(false)

const user = computed(() => JSON.parse(localStorage.getItem('user') || '{}'))

// Format messages with clickable links
const formatMessageWithLinks = (text) => {
    if (!text) return ''
    
    const appointmentPattern = /(\/appointments\/\d+)/g
    let formattedText = text.replace(appointmentPattern, (match) => {
        return `<a href="${match}" class="appointment-link" onclick="event.stopPropagation()">🔗 Voir le rendez-vous</a>`
    })
    
    const urlPattern = /(https?:\/\/[^\s]+)/g
    formattedText = formattedText.replace(urlPattern, (url) => {
        return `<a href="${url}" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation()">${url}</a>`
    })
    
    formattedText = formattedText.replace(/\n/g, '<br>')
    
    return formattedText
}

const pushMessageSmooth = (msg) => {
  messages.value = [...messages.value, msg]
}

const loadData = async () => {
  try {
    const res = await axios.get('/api/messages')
    conversations.value = res.data.conversations || []
    contacts.value = res.data.contacts || []
    unreadCount.value = res.data.unreadCount || 0
  } catch (err) {
    console.error('Error loading messages:', err)
  }
}

const loadConversation = async (contact) => {
  if (currentContact.value?.id === contact.id) return
  
  try {
    loading.value = true
    currentContact.value = contact
    const res = await axios.get(`/api/messages/conversation/${contact.id}`)
    messages.value = res.data.messages || []
    await nextTick()
    scrollToBottom()
    if (isMobile.value) {
      showSidebar.value = false
    }
    await loadUnreadCount()
  } catch (err) {
    console.error('Error loading conversation:', err)
  } finally {
    loading.value = false
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !currentContact.value || sendingMessage.value) return
  
  sendingMessage.value = true
  const messageText = newMessage.value.trim()
  
  const tempMessage = { 
    id: Date.now(), 
    sender_id: user.value.id, 
    receiver_id: currentContact.value.id, 
    content: messageText, 
    is_read: false, 
    created_at: new Date().toISOString(), 
    is_temp: true 
  }
  
  pushMessageSmooth(tempMessage)
  newMessage.value = ''
  await nextTick()
  scrollToBottom()
  
  try {
    const res = await axios.post(`/api/messages/send/${currentContact.value.id}`, { content: messageText })
    
    const index = messages.value.findIndex(m => m.id === tempMessage.id)
    if (index !== -1) {
      messages.value[index] = { ...res.data, is_temp: false }
      messages.value = [...messages.value]
    }
    
    const conv = conversations.value.find(c => c.id === currentContact.value.id)
    if (conv) {
      conv.last_message = messageText
      conv.updated_at = new Date().toISOString()
    }
    
  } catch (err) {
    console.error('Error sending message:', err)
    const index = messages.value.findIndex(m => m.id === tempMessage.id)
    if (index !== -1) {
      messages.value[index].error = true
      messages.value[index].content = `[Échec] ${messageText}`
      messages.value = [...messages.value]
    }
    newMessage.value = messageText
  } finally {
    sendingMessage.value = false
  }
}

// Créer un rendez-vous - MODIFIÉ : plus de double message
const createAppointment = async () => {
  if (!appointmentData.value.date || !appointmentData.value.time) {
    alert('Veuillez remplir la date et l\'heure du rendez-vous')
    return
  }
  
  creatingAppointment.value = true
  
  try {
    const datetime = `${appointmentData.value.date}T${appointmentData.value.time}:00`
    
    const selectedDate = new Date(datetime)
    if (selectedDate < new Date()) {
      alert('La date du rendez-vous ne peut pas être dans le passé')
      creatingAppointment.value = false
      return
    }
    
    // UN SEUL appel API - le message est créé automatiquement dans le contrôleur
    const response = await axios.post('/api/appointments', {
      receiver_id: currentContact.value.id,
      title: appointmentData.value.title || `Rendez-vous avec ${currentContact.value.name}`,
      scheduled_at: datetime,
      duration: parseInt(appointmentData.value.duration),
      location: appointmentData.value.location,
      notes: appointmentData.value.notes,
      type: appointmentData.value.type
    })
    
    showAppointmentModal.value = false
    resetAppointmentForm()
    
    await loadConversation(currentContact.value)
    
    alert('✅ Rendez-vous créé avec succès!')
    
  } catch (err) {
    console.error('Error creating appointment:', err)
    if (err.response) {
      alert(`Erreur: ${err.response.data.error || err.response.data.message || 'Erreur lors de la création du rendez-vous'}`)
    } else {
      alert('Erreur lors de la création du rendez-vous')
    }
  } finally {
    creatingAppointment.value = false
  }
}

const formatAppointmentMessage = (data, datetime) => {
  const dateObj = new Date(datetime)
  const formattedDate = dateObj.toLocaleDateString('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
  const formattedTime = dateObj.toLocaleTimeString('fr-FR', {
    hour: '2-digit',
    minute: '2-digit'
  })
  
  let typeText = ''
  switch(data.type) {
    case 'video': typeText = '📹 Visioconférence'; break
    case 'phone': typeText = '📞 Téléphone'; break
    case 'in_person': typeText = '🏢 En personne'; break
  }
  
  let message = `${typeText} - ${formattedDate} à ${formattedTime}`
  message += `\n⏱️ Durée: ${data.duration} minutes`
  
  if (data.location) message += `\n📍 Lieu: ${data.location}`
  if (data.notes) message += `\n📝 Notes: ${data.notes}`
  
  return message
}

const resetAppointmentForm = () => {
  appointmentData.value = {
    title: '',
    date: '',
    time: '',
    duration: 30,
    location: '',
    notes: '',
    type: 'video'
  }
}

const getCurrentYear = () => {
  return new Date().getFullYear()
}

const getMinDate = () => {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const getMaxDate = () => {
  const max = new Date()
  max.setFullYear(max.getFullYear() + 2)
  const year = max.getFullYear()
  const month = String(max.getMonth() + 1).padStart(2, '0')
  const day = String(max.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

const formatMessageDate = (date) => {
  const msgDate = new Date(date)
  const today = new Date()
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)
  
  if (msgDate.toDateString() === today.toDateString()) {
    return 'Today'
  }
  if (msgDate.toDateString() === yesterday.toDateString()) {
    return 'Yesterday'
  }
  return msgDate.toLocaleDateString()
}

const loadUnreadCount = async () => {
  try {
    const res = await axios.get('/api/messages/unread-count')
    unreadCount.value = res.data.count
  } catch (err) {
    console.error('Error loading unread count:', err)
  }
}

const getRoleIcon = (role) => {
  switch(role) {
    case 'parent': return '👨‍👩‍👧'
    case 'teacher': return '📚'
    case 'psychologist': return '👩‍⚕️'
    default: return '👤'
  }
}

const backToContacts = () => {
  showSidebar.value = true
  currentContact.value = null
}

const handleResize = () => {
  isMobile.value = window.innerWidth < 768
  if (!isMobile.value) {
    showSidebar.value = true
  }
}

const findOrCreateContact = (id, name, role) => {
  let contact = contacts.value.find(c => c.id === parseInt(id))
  if (!contact) {
    contact = { 
      id: parseInt(id), 
      name: name, 
      role: role || 'parent', 
      email: '' 
    }
  }
  return contact
}

const setupRealtimeListener = () => {
  if (window.Echo && user.value.id) {
    try {
      if (window.Echo.connector && window.Echo.channel(`chat.${user.value.id}`)) {
        window.Echo.leave(`chat.${user.value.id}`)
      }
      
      window.Echo.channel(`chat.${user.value.id}`)
        .listen('MessageSent', (e) => {
          console.log('Nouveau message reçu:', e)
          
          const newMsg = { 
            id: e.message?.id || e.id, 
            sender_id: e.message?.sender_id || e.sender_id, 
            receiver_id: e.message?.receiver_id || e.receiver_id, 
            content: e.message?.content || e.content, 
            is_read: e.message?.is_read || e.is_read || false, 
            created_at: e.message?.created_at || e.created_at || new Date().toISOString() 
          }
          
          if (currentContact.value && currentContact.value.id === newMsg.sender_id) {
            const exists = messages.value.some(m => m.id === newMsg.id)
            if (!exists) {
              pushMessageSmooth(newMsg)
              scrollToBottom()
            }
          }
          
          loadUnreadCount()
          
          const conv = conversations.value.find(c => c.id === newMsg.sender_id)
          if (conv && conv.id !== currentContact.value?.id) {
            conv.last_message = newMsg.content
            conv.updated_at = newMsg.created_at
            const index = conversations.value.findIndex(c => c.id === conv.id)
            if (index > 0) {
              conversations.value = [
                conv,
                ...conversations.value.slice(0, index),
                ...conversations.value.slice(index + 1)
              ]
            }
          }
        })
        .error((error) => {
          console.error('Echo error:', error)
        })
      
      console.log('Real-time listener setup for user:', user.value.id)
    } catch (error) {
      console.error('Error setting up realtime listener:', error)
    }
  }
}

onMounted(async () => {
  await loadData()
  await loadUnreadCount()
  window.addEventListener('resize', handleResize)
  setupRealtimeListener()
  
  if (route.query.contactId && route.query.contactName) {
    const contactId = route.query.contactId
    const contactName = route.query.contactName
    const contactRole = route.query.contactRole || 'parent'
    
    setTimeout(async () => {
      const contact = findOrCreateContact(contactId, contactName, contactRole)
      await loadConversation(contact)
    }, 500)
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  if (window.Echo && window.Echo.channel(`chat.${user.value.id}`)) {
    window.Echo.leave(`chat.${user.value.id}`)
  }
})
</script>

<template>
  <Layout>
    <div class="chat-container">
      <div class="chat-sidebar" :class="{ 'sidebar-hidden': !showSidebar && isMobile }">
        <div class="sidebar-header">
          <div class="header-title">
            <span class="header-icon">💬</span>
            <h2>Messages</h2>
            <span v-if="unreadCount > 0" class="unread-badge">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
          </div>
        </div>
        
        <div class="contacts-list">
          <div 
            v-for="contact in contacts" 
            :key="contact.id" 
            class="contact-item" 
            :class="{ active: currentContact?.id === contact.id }" 
            @click="loadConversation(contact)"
          >
            <div class="contact-avatar" :style="{ background: 'linear-gradient(135deg, #1a0a2e, #2d1b4e)' }">
              {{ contact.name?.charAt(0) || '👤' }}
            </div>
            <div class="contact-info">
              <div class="contact-name">{{ contact.name }}</div>
              <div class="contact-role">
                {{ getRoleIcon(contact.role) }} {{ contact.role }}
              </div>
            </div>
            <div v-if="contact.unread_count > 0" class="unread-badge-mini">
              {{ contact.unread_count > 99 ? '99+' : contact.unread_count }}
            </div>
          </div>
          
          <div v-if="contacts.length === 0" class="no-contacts">
            <div class="no-contacts-icon">💬</div>
            <p>No contacts available</p>
            <small>Contacts will appear based on your role</small>
          </div>
        </div>
      </div>

      <div class="chat-main" :class="{ 'chat-full': !showSidebar && isMobile }">
        <div v-if="currentContact" class="chat-header">
          <button v-if="isMobile" @click="backToContacts" class="back-btn">←</button>
          <div class="contact-avatar large" :style="{ background: 'linear-gradient(135deg, #1a0a2e, #2d1b4e)' }">
            {{ currentContact.name?.charAt(0) || '👤' }}
          </div>
          <div class="contact-details">
            <h3>{{ currentContact.name }}</h3>
            <p>{{ getRoleIcon(currentContact.role) }} {{ currentContact.role }}</p>
          </div>
          <button @click="showAppointmentModal = true" class="appointment-btn">
            📅 Rendez-vous
          </button>
        </div>
        
        <div v-else class="chat-placeholder">
          <div class="placeholder-animation">
            <div class="placeholder-icon">💬</div>
            <div class="placeholder-pulse"></div>
          </div>
          <h3>Select a contact</h3>
          <p>Choose someone to start messaging</p>
        </div>

        <div v-if="currentContact" class="messages-area" ref="messagesContainer">
          <div v-if="loading" class="loading-messages">
            <div class="spinner-small"></div>
            <p>Loading messages...</p>
          </div>
          <div v-else>
            <template v-for="(msg, index) in messages" :key="msg.id + '-' + msg.created_at">
              <div 
                v-if="index === 0 || formatMessageDate(msg.created_at) !== formatMessageDate(messages[index-1].created_at)" 
                class="message-date-separator"
              >
                <span>{{ formatMessageDate(msg.created_at) }}</span>
              </div>
              
              <div 
                class="message" 
                :class="{ 
                  'message-sent': msg.sender_id === user.id, 
                  'message-received': msg.sender_id !== user.id, 
                  'message-error': msg.error,
                  'message-appointment': msg.is_appointment
                }"
              >
                <div class="message-bubble">
                  <div class="message-content" v-html="formatMessageWithLinks(msg.content)"></div>
                  <div class="message-time">
                    {{ formatTime(msg.created_at) }}
                    <span v-if="msg.error" class="error-badge">⚠️ Non envoyé</span>
                  </div>
                </div>
              </div>
            </template>
            
            <div v-if="messages.length === 0 && !loading" class="no-messages">
              <div class="no-messages-icon">💭</div>
              <p>No messages yet</p>
              <small>Send a message to start the conversation!</small>
            </div>
          </div>
        </div>

        <div v-if="currentContact" class="chat-input-area">
          <input 
            v-model="newMessage" 
            @keyup.enter="sendMessage" 
            type="text" 
            placeholder="Type your message..." 
            class="chat-input" 
            :disabled="sendingMessage"
          >
          <button @click="sendMessage" class="send-btn" :disabled="!newMessage.trim() || sendingMessage">
            <span>{{ sendingMessage ? '⏳' : '📤' }}</span>
            Send
          </button>
        </div>
      </div>
    </div>

    <div v-if="showAppointmentModal" class="modal-overlay" @click.self="showAppointmentModal = false">
      <div class="modal-content">
        <div class="modal-header">
          <h2>📅 Créer un rendez-vous</h2>
          <button class="modal-close" @click="showAppointmentModal = false">✕</button>
        </div>
        
        <div class="modal-body">
          <div class="info-message">
            💡 Les années disponibles : {{ getCurrentYear() }} - {{ getCurrentYear() + 2 }}
          </div>
          
          <div class="form-group">
            <label>Titre (optionnel)</label>
            <input 
              v-model="appointmentData.title" 
              type="text" 
              placeholder="Ex: Consultation suivi"
              class="form-input"
            >
          </div>
          
          <div class="form-group">
            <label>Type de rendez-vous *</label>
            <div class="appointment-types">
              <label class="type-option">
                <input type="radio" value="video" v-model="appointmentData.type">
                <span>📹 Visio</span>
              </label>
              <label class="type-option">
                <input type="radio" value="phone" v-model="appointmentData.type">
                <span>📞 Téléphone</span>
              </label>
              <label class="type-option">
                <input type="radio" value="in_person" v-model="appointmentData.type">
                <span>🏢 Présentiel</span>
              </label>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label>Date *</label>
              <input 
                v-model="appointmentData.date" 
                type="date" 
                :min="getMinDate()"
                :max="getMaxDate()"
                class="form-input"
                required
              >
              <small class="input-help">Format: JJ/MM/AAAA (année sur 4 chiffres)</small>
            </div>
            
            <div class="form-group">
              <label>Heure *</label>
              <input 
                v-model="appointmentData.time" 
                type="time" 
                class="form-input"
                required
              >
              <small class="input-help">Format: HH:MM (24h)</small>
            </div>
          </div>
          
          <div class="form-group">
            <label>Durée (minutes)</label>
            <select v-model="appointmentData.duration" class="form-input">
              <option value="15">15 minutes</option>
              <option value="30">30 minutes</option>
              <option value="45">45 minutes</option>
              <option value="60">1 heure</option>
              <option value="90">1h30</option>
              <option value="120">2 heures</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Lieu / Lien (optionnel)</label>
            <input 
              v-model="appointmentData.location" 
              type="text" 
              placeholder="Lien visio, adresse, numéro de téléphone..."
              class="form-input"
            >
          </div>
          
          <div class="form-group">
            <label>Notes (optionnel)</label>
            <textarea 
              v-model="appointmentData.notes" 
              rows="3" 
              placeholder="Informations supplémentaires pour le rendez-vous..."
              class="form-textarea"
            ></textarea>
          </div>
        </div>
        
        <div class="modal-footer">
          <button class="btn-cancel" @click="showAppointmentModal = false">
            Annuler
          </button>
          <button class="btn-create" @click="createAppointment" :disabled="creatingAppointment">
            {{ creatingAppointment ? 'Création...' : 'Créer le rendez-vous' }}
          </button>
        </div>
      </div>
    </div>
  </Layout>
</template>

<style scoped>
.chat-container {
  display: flex;
  height: calc(100vh - 120px);
  background: white;
  border-radius: 24px;
  overflow: hidden;
  margin: 20px;
  box-shadow: 0 4px 25px rgba(0,0,0,0.08);
  animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.chat-sidebar {
  width: 320px;
  border-right: 1px solid #e0e6ed;
  display: flex;
  flex-direction: column;
  background: #f9fafb;
  transition: transform 0.3s ease;
}

.sidebar-header {
  padding: 20px;
  border-bottom: 1px solid #e0e6ed;
  background: white;
}

.header-title {
  display: flex;
  align-items: center;
  gap: 10px;
}

.header-icon {
  font-size: 24px;
}

.sidebar-header h2 {
  font-size: 20px;
  margin: 0;
  color: #1f2937;
}

.unread-badge {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border-radius: 20px;
  padding: 2px 8px;
  font-size: 12px;
  font-weight: 600;
  margin-left: auto;
  animation: pulse 0.5s ease;
}

.unread-badge-mini {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border-radius: 50%;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  font-size: 11px;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: auto;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.contacts-list {
  flex: 1;
  overflow-y: auto;
}

.contact-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 20px;
  cursor: pointer;
  transition: all 0.2s ease;
  border-bottom: 1px solid #f0f0f0;
}

.contact-item:hover {
  background: #f3f4f6;
  transform: translateX(4px);
}

.contact-item.active {
  background: linear-gradient(135deg, #eef2ff, #e0e7ff);
  border-left: 3px solid #667eea;
}

.contact-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 18px;
  transition: transform 0.2s;
}

.contact-item:hover .contact-avatar {
  transform: scale(1.05);
}

.contact-avatar.large {
  width: 55px;
  height: 55px;
  font-size: 24px;
}

.contact-info {
  flex: 1;
}

.contact-name {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 4px;
}

.contact-role {
  font-size: 12px;
  color: #6b7280;
}

.chat-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  background: white;
}

.chat-header {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 16px 24px;
  border-bottom: 1px solid #e0e6ed;
  background: white;
}

.back-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #667eea;
  padding: 5px;
  display: none;
}

.contact-details {
  flex: 1;
}

.contact-details h3 {
  margin: 0;
  font-size: 16px;
  color: #1f2937;
}

.contact-details p {
  margin: 4px 0 0;
  font-size: 12px;
  color: #6b7280;
}

.appointment-btn {
  padding: 8px 16px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}

.appointment-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.chat-placeholder {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 16px;
}

.placeholder-animation {
  position: relative;
}

.placeholder-icon {
  font-size: 80px;
  animation: bounce 2s ease infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.placeholder-pulse {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 100px;
  height: 100px;
  background: rgba(102, 126, 234, 0.1);
  border-radius: 50%;
  transform: translate(-50%, -50%);
  animation: pulse-ring 2s ease infinite;
}

@keyframes pulse-ring {
  0% { width: 80px; height: 80px; opacity: 0.5; }
  100% { width: 140px; height: 140px; opacity: 0; }
}

.chat-placeholder h3 {
  font-size: 20px;
  color: #374151;
  margin: 0;
}

.chat-placeholder p {
  color: #9ca3af;
  margin: 0;
}

.messages-area {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f9fafb;
}

.message-date-separator {
  text-align: center;
  margin: 20px 0 16px;
}

.message-date-separator span {
  font-size: 11px;
  background: #e5e7eb;
  padding: 4px 12px;
  border-radius: 20px;
  color: #6b7280;
}

.message {
  display: flex;
  margin-bottom: 16px;
  animation: messageAppear 0.3s ease;
}

@keyframes messageAppear {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.message-sent {
  justify-content: flex-end;
}

.message-received {
  justify-content: flex-start;
}

.message-appointment .message-bubble {
  background: linear-gradient(135deg, #d1fae5, #a7f3d0);
  border: 1px solid #10b981;
}

.message-error .message-bubble {
  background: #fee2e2;
  border: 1px solid #ef4444;
}

.message-bubble {
  max-width: 70%;
  padding: 10px 14px;
  border-radius: 18px;
  position: relative;
  transition: transform 0.1s;
}

.message-sent .message-bubble {
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border-bottom-right-radius: 4px;
}

.message-received .message-bubble {
  background: white;
  color: #1f2937;
  border-bottom-left-radius: 4px;
  box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.message-content {
  font-size: 14px;
  word-wrap: break-word;
  line-height: 1.4;
  white-space: pre-wrap;
}

.message-content :deep(.appointment-link) {
  display: inline-block;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  text-decoration: none;
  font-size: 12px;
  font-weight: 500;
  margin-top: 5px;
}

.message-content :deep(.appointment-link:hover) {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.message-time {
  font-size: 10px;
  margin-top: 4px;
  opacity: 0.7;
  text-align: right;
}

.error-badge {
  margin-left: 8px;
  color: #ef4444;
}

.chat-input-area {
  display: flex;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid #e0e6ed;
  background: white;
}

.chat-input {
  flex: 1;
  padding: 12px 18px;
  border: 2px solid #e0e6ed;
  border-radius: 30px;
  font-size: 14px;
  outline: none;
  transition: all 0.2s;
}

.chat-input:focus {
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.send-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, #667eea, #764ba2);
  color: white;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.send-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.send-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.loading-messages, .no-messages, .no-contacts {
  text-align: center;
  padding: 60px 20px;
  color: #9ca3af;
}

.no-messages-icon, .no-contacts-icon {
  font-size: 48px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.spinner-small {
  width: 30px;
  height: 30px;
  border: 2px solid #e0e6ed;
  border-top-color: #667eea;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 12px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

.modal-content {
  background: white;
  border-radius: 24px;
  width: 90%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 24px;
  border-bottom: 1px solid #e0e6ed;
}

.modal-header h2 {
  margin: 0;
  font-size: 24px;
  color: #1f2937;
}

.modal-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #6b7280;
  transition: color 0.2s;
}

.modal-close:hover {
  color: #1f2937;
}

.modal-body {
  padding: 24px;
}

.info-message {
  background: linear-gradient(135deg, #e0e7ff, #f3e8ff);
  padding: 10px 12px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-size: 13px;
  color: #4c1d95;
  border-left: 3px solid #667eea;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: #374151;
  font-size: 14px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-input,
.form-textarea,
select.form-input {
  width: 100%;
  padding: 10px 12px;
  border: 2px solid #e0e6ed;
  border-radius: 12px;
  font-size: 14px;
  transition: all 0.2s;
  font-family: inherit;
}

.form-input:focus,
.form-textarea:focus,
select.form-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-textarea {
  resize: vertical;
}

.input-help {
  display: block;
  font-size: 11px;
  color: #6b7280;
  margin-top: 5px;
}

.appointment-types {
  display: flex;
  gap: 16px;
}

.type-option {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  padding: 8px 12px;
  border: 2px solid #e0e6ed;
  border-radius: 12px;
  transition: all 0.2s;
}

.type-option:hover {
  border-color: #667eea;
  background: #f9fafb;
}

.type-option input[type="radio"] {
  margin: 0;
  cursor: pointer;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  padding: 20px 24px;
  border-top: 1px solid #e0e6ed;
}

.btn-cancel,
.btn-create {
  padding: 10px 20px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel {
  background: white;
  border: 2px solid #e0e6ed;
  color: #6b7280;
}

.btn-cancel:hover {
  border-color: #9ca3af;
  color: #374151;
}

.btn-create {
  background: linear-gradient(135deg, #10b981, #059669);
  border: none;
  color: white;
}

.btn-create:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-create:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .chat-container {
    margin: 10px;
    height: calc(100vh - 100px);
  }
  
  .chat-sidebar {
    position: absolute;
    z-index: 10;
    height: calc(100vh - 120px);
    background: white;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
  }
  
  .chat-sidebar.sidebar-hidden {
    transform: translateX(-100%);
  }
  
  .chat-main.chat-full {
    width: 100%;
  }
  
  .back-btn {
    display: block;
  }
  
  .message-bubble {
    max-width: 85%;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .appointment-types {
    flex-direction: column;
    gap: 8px;
  }
  
  .modal-content {
    width: 95%;
    margin: 20px;
  }
}
</style>