<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'
import Layout from './Layout.vue'

const route = useRoute()
const router = useRouter()
const appointment = ref(null)
const loading = ref(true)
const error = ref(null)
const updating = ref(false)
const currentUser = ref(null)

// Statuts possibles
const statusOptions = [
    { value: 'pending', label: '⏳ En attente', color: 'orange', icon: '⏳' },
    { value: 'confirmed', label: '✓ Confirmé', color: 'green', icon: '✓' },
    { value: 'cancelled', label: '✗ Annulé', color: 'red', icon: '✗' },
    { value: 'completed', label: '✓ Terminé', color: 'blue', icon: '✓' }
]

// Types de rendez-vous
const typeOptions = [
    { value: 'video', label: '📹 Visioconférence', icon: '📹' },
    { value: 'phone', label: '📞 Téléphone', icon: '📞' },
    { value: 'in_person', label: '🏢 En personne', icon: '🏢' }
]

// Computed properties pour les permissions
const isCreator = computed(() => {
    return currentUser.value && appointment.value && currentUser.value.id === appointment.value.sender_id
})

const isInvited = computed(() => {
    return currentUser.value && appointment.value && currentUser.value.id === appointment.value.receiver_id
})

// Actions disponibles selon l'utilisateur
const availableActions = computed(() => {
    if (!appointment.value) return []
    
    const status = appointment.value.status
    const actions = []
    
    if (isCreator.value) {
        // Le créateur (David) peut annuler ou supprimer
        if (status === 'pending' || status === 'confirmed') {
            actions.push({ type: 'cancel', label: '❌ Annuler le rendez-vous', class: 'btn-cancel' })
        }
        if (status !== 'cancelled' && status !== 'completed') {
            actions.push({ type: 'delete', label: '🗑️ Supprimer le rendez-vous', class: 'btn-delete' })
        }
    } else if (isInvited.value) {
        // L'invité (Sarah) peut confirmer ou refuser
        if (status === 'pending') {
            actions.push({ type: 'confirm', label: '✓ Confirmer le rendez-vous', class: 'btn-confirm' })
            actions.push({ type: 'refuse', label: '✗ Refuser le rendez-vous', class: 'btn-refuse' })
        }
    }
    
    return actions
})

// Vérifier si le rendez-vous est passé et le marquer comme terminé automatiquement
const checkAndUpdateExpiredAppointment = async () => {
    if (!appointment.value) return
    
    const now = new Date()
    const appointmentDate = new Date(appointment.value.scheduled_at)
    
    // Si la date est passée et le statut n'est pas déjà terminé ou annulé
    if (appointmentDate < now && 
        appointment.value.status !== 'completed' && 
        appointment.value.status !== 'cancelled') {
        
        try {
            console.log('Rendez-vous expiré, mise à jour automatique du statut...')
            await axios.patch(`/api/appointments/${appointment.value.id}/status`, {
                status: 'completed'
            })
            appointment.value.status = 'completed'
            
            // Afficher une notification discrète
            const notification = document.createElement('div')
            notification.className = 'auto-update-notification'
            notification.innerHTML = '✅ Ce rendez-vous a été automatiquement marqué comme terminé car la date est passée.'
            document.body.appendChild(notification)
            setTimeout(() => notification.remove(), 5000)
            
        } catch (err) {
            console.error('Erreur lors de la mise à jour automatique:', err)
        }
    }
}

// Charger les détails du rendez-vous et l'utilisateur courant
const loadAppointment = async () => {
    try {
        loading.value = true
        const id = route.params.id
        
        // Récupérer l'utilisateur courant
        const userResponse = await axios.get('/api/me')
        currentUser.value = userResponse.data
        
        // Récupérer le rendez-vous
        const response = await axios.get(`/api/appointments/${id}`)
        appointment.value = response.data
        
        error.value = null
        
        // Vérifier si le rendez-vous est expiré
        await checkAndUpdateExpiredAppointment()
        
    } catch (err) {
        console.error('Error loading appointment:', err)
        error.value = 'Impossible de charger les détails du rendez-vous'
    } finally {
        loading.value = false
    }
}

// Mettre à jour le statut
const updateStatus = async (newStatus) => {
    let confirmMessage = ''
    let actionName = ''
    
    switch(newStatus) {
        case 'confirmed':
            confirmMessage = 'Êtes-vous sûr de vouloir confirmer ce rendez-vous ?'
            actionName = 'confirmé'
            break
        case 'cancelled':
            if (isCreator.value) {
                confirmMessage = 'Êtes-vous sûr de vouloir annuler ce rendez-vous ?'
                actionName = 'annulé'
            } else {
                confirmMessage = 'Êtes-vous sûr de vouloir refuser ce rendez-vous ?'
                actionName = 'refusé'
            }
            break
        case 'completed':
            confirmMessage = 'Êtes-vous sûr de vouloir marquer ce rendez-vous comme terminé ?'
            actionName = 'terminé'
            break
        default:
            confirmMessage = `Êtes-vous sûr de vouloir ${actionName} ce rendez-vous ?`
    }
    
    if (!confirm(confirmMessage)) return
    
    try {
        updating.value = true
        await axios.patch(`/api/appointments/${appointment.value.id}/status`, {
            status: newStatus
        })
        
        // Recharger les données
        await loadAppointment()
        
        alert(`✅ Rendez-vous ${actionName} avec succès !`)
        
        // Rediriger vers le chat après annulation/suppression
        if (newStatus === 'cancelled' || newStatus === 'delete') {
            setTimeout(() => {
                router.push('/messages')
            }, 1500)
        }
        
    } catch (err) {
        console.error('Error updating status:', err)
        alert('Erreur lors de la mise à jour du statut')
    } finally {
        updating.value = false
    }
}

// Supprimer le rendez-vous (uniquement pour le créateur)
const deleteAppointment = async () => {
    if (!confirm('Êtes-vous sûr de vouloir supprimer définitivement ce rendez-vous ? Cette action est irréversible.')) {
        return
    }
    
    try {
        updating.value = true
        await axios.delete(`/api/appointments/${appointment.value.id}`)
        alert('🗑️ Rendez-vous supprimé avec succès !')
        router.push('/messages')
    } catch (err) {
        console.error('Error deleting appointment:', err)
        alert('Erreur lors de la suppression du rendez-vous')
    } finally {
        updating.value = false
    }
}

// Formater la date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

// Formater l'heure
const formatTime = (date) => {
    return new Date(date).toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Obtenir le statut affichage
const getStatusDisplay = (status) => {
    const statusObj = statusOptions.find(s => s.value === status)
    return statusObj || { label: status, color: 'gray', icon: '📅' }
}

// Obtenir le type affichage
const getTypeDisplay = (type) => {
    const typeObj = typeOptions.find(t => t.value === type)
    return typeObj || { label: type, icon: '📅' }
}

// Retourner au chat
const goBack = () => {
    router.push('/messages')
}

// Savoir si la date est passée
const isExpired = computed(() => {
    if (!appointment.value) return false
    return new Date(appointment.value.scheduled_at) < new Date()
})

// Message d'information selon le rôle
const infoMessage = computed(() => {
    if (!appointment.value) return ''
    
    if (isCreator.value) {
        if (appointment.value.status === 'pending') {
            return 'ℹ️ En attente de confirmation de la part de l\'invité(e).'
        } else if (appointment.value.status === 'confirmed') {
            return '✅ Ce rendez-vous a été confirmé par l\'invité(e).'
        }
    } else if (isInvited.value) {
        if (appointment.value.status === 'pending') {
            return 'ℹ️ Veuillez confirmer ou refuser ce rendez-vous.'
        } else if (appointment.value.status === 'confirmed') {
            return '✅ Vous avez confirmé ce rendez-vous.'
        }
    }
    return ''
})

onMounted(() => {
    loadAppointment()
})
</script>

<template>
    <Layout>
        <div class="appointment-details-container">
            <div class="details-card">
                <!-- Header -->
                <div class="details-header">
                    <button @click="goBack" class="back-button">
                        ← Retour au chat
                    </button>
                    <div class="header-icon">📅</div>
                    <h1>Détails du rendez-vous</h1>
                </div>

                <!-- Loading -->
                <div v-if="loading" class="loading-state">
                    <div class="spinner"></div>
                    <p>Chargement des détails...</p>
                </div>

                <!-- Error -->
                <div v-else-if="error" class="error-state">
                    <div class="error-icon">⚠️</div>
                    <p>{{ error }}</p>
                    <button @click="goBack" class="btn-primary">Retour</button>
                </div>

                <!-- Details -->
                <div v-else-if="appointment" class="details-content">
                    <!-- Badge expiré -->
                    <div v-if="isExpired && appointment.status !== 'cancelled'" class="expired-badge">
                        ⏰ Rendez-vous passé
                    </div>
                    
                    <!-- Statut Badge -->
                    <div class="status-badge" :style="{ backgroundColor: getStatusDisplay(appointment.status).color === 'orange' ? '#f59e0b' : getStatusDisplay(appointment.status).color === 'green' ? '#10b981' : getStatusDisplay(appointment.status).color === 'red' ? '#ef4444' : '#3b82f6' }">
                        {{ getStatusDisplay(appointment.status).icon }} {{ getStatusDisplay(appointment.status).label }}
                    </div>

                    <!-- Message d'information -->
                    <div v-if="infoMessage" class="info-message">
                        {{ infoMessage }}
                    </div>

                    <!-- Titre -->
                    <div class="detail-section">
                        <h2>{{ appointment.title }}</h2>
                    </div>

                    <!-- Type -->
                    <div class="detail-row">
                        <div class="detail-icon">{{ getTypeDisplay(appointment.type).icon }}</div>
                        <div class="detail-info">
                            <span class="detail-label">Type de rendez-vous</span>
                            <span class="detail-value">{{ getTypeDisplay(appointment.type).label }}</span>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="detail-row">
                        <div class="detail-icon">📅</div>
                        <div class="detail-info">
                            <span class="detail-label">Date</span>
                            <span class="detail-value">{{ formatDate(appointment.scheduled_at) }}</span>
                        </div>
                    </div>

                    <!-- Heure -->
                    <div class="detail-row">
                        <div class="detail-icon">⏰</div>
                        <div class="detail-info">
                            <span class="detail-label">Heure</span>
                            <span class="detail-value">{{ formatTime(appointment.scheduled_at) }}</span>
                        </div>
                    </div>

                    <!-- Durée -->
                    <div class="detail-row">
                        <div class="detail-icon">⏱️</div>
                        <div class="detail-info">
                            <span class="detail-label">Durée</span>
                            <span class="detail-value">{{ appointment.duration }} minutes</span>
                        </div>
                    </div>

                    <!-- Lieu -->
                    <div v-if="appointment.location" class="detail-row">
                        <div class="detail-icon">📍</div>
                        <div class="detail-info">
                            <span class="detail-label">Lieu</span>
                            <span class="detail-value">{{ appointment.location }}</span>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="appointment.notes" class="detail-section notes-section">
                        <h3>📝 Notes</h3>
                        <p class="notes-content">{{ appointment.notes }}</p>
                    </div>

                    <!-- Participants -->
                    <div class="detail-section participants-section">
                        <h3>👥 Participants</h3>
                        <div class="participant" :class="{ 'current-user': currentUser?.id === appointment.sender?.id }">
                            <div class="participant-avatar" :style="{ background: 'linear-gradient(135deg, #667eea, #764ba2)' }">
                                {{ appointment.sender?.name?.charAt(0) || '?' }}
                            </div>
                            <div class="participant-info">
                                <span class="participant-name">{{ appointment.sender?.name }}</span>
                                <span class="participant-role">
                                    Créateur
                                    <span v-if="currentUser?.id === appointment.sender?.id" class="you-badge">(Vous)</span>
                                </span>
                            </div>
                        </div>
                        <div class="participant" :class="{ 'current-user': currentUser?.id === appointment.receiver?.id }">
                            <div class="participant-avatar" :style="{ background: 'linear-gradient(135deg, #10b981, #059669)' }">
                                {{ appointment.receiver?.name?.charAt(0) || '?' }}
                            </div>
                            <div class="participant-info">
                                <span class="participant-name">{{ appointment.receiver?.name }}</span>
                                <span class="participant-role">
                                    Invité(e)
                                    <span v-if="currentUser?.id === appointment.receiver?.id" class="you-badge">(Vous)</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions (selon le rôle) -->
                    <div class="actions-section">
                        <div class="action-buttons">
                            <button 
                                v-for="action in availableActions"
                                :key="action.type"
                                @click="action.type === 'delete' ? deleteAppointment() : updateStatus(action.type === 'confirm' ? 'confirmed' : 'cancelled')"
                                :class="action.class"
                                :disabled="updating"
                            >
                                {{ action.label }}
                            </button>
                        </div>
                        
                        <!-- Message explicatif si aucune action disponible -->
                        <div v-if="availableActions.length === 0 && appointment.status !== 'cancelled' && appointment.status !== 'completed'" class="no-actions-message">
                            <p>ℹ️ Aucune action disponible pour ce rendez-vous.</p>
                        </div>
                        
                        <div v-if="appointment.status === 'completed'" class="completed-message">
                            ✅ Ce rendez-vous est terminé.
                        </div>
                        
                        <div v-if="appointment.status === 'cancelled'" class="cancelled-message">
                            ❌ Ce rendez-vous a été annulé.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.appointment-details-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
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

.details-card {
    background: white;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.details-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    padding: 30px;
    text-align: center;
    position: relative;
}

.back-button {
    position: absolute;
    top: 20px;
    left: 20px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.back-button:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateX(-2px);
}

.header-icon {
    font-size: 60px;
    margin-bottom: 15px;
}

.details-header h1 {
    color: white;
    margin: 0;
    font-size: 28px;
}

.loading-state, .error-state {
    text-align: center;
    padding: 60px 20px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e0e6ed;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.error-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.error-state p {
    color: #ef4444;
    margin-bottom: 20px;
}

.details-content {
    padding: 30px;
}

.expired-badge {
    background: #6b7280;
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    display: inline-block;
    margin-bottom: 15px;
}

.status-badge {
    display: inline-block;
    padding: 6px 16px;
    border-radius: 20px;
    color: white;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 20px;
}

.info-message {
    background: #e0e7ff;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 20px;
    font-size: 13px;
    color: #4c1d95;
    border-left: 3px solid #667eea;
}

.detail-section {
    margin-bottom: 25px;
}

.detail-section h2 {
    font-size: 24px;
    color: #1f2937;
    margin: 0 0 10px 0;
}

.detail-section h3 {
    font-size: 18px;
    color: #374151;
    margin: 0 0 15px 0;
}

.detail-row {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.detail-icon {
    font-size: 24px;
    width: 45px;
}

.detail-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 16px;
    color: #1f2937;
    font-weight: 500;
}

.notes-section {
    background: #f9fafb;
    padding: 20px;
    border-radius: 16px;
    margin: 20px 0;
}

.notes-content {
    color: #374151;
    line-height: 1.6;
    margin: 0;
    white-space: pre-wrap;
}

.participants-section {
    margin: 25px 0;
}

.participant {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 12px;
    margin-bottom: 10px;
    transition: all 0.2s;
}

.participant.current-user {
    background: #e0e7ff;
    border-left: 3px solid #667eea;
}

.participant-avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 18px;
}

.participant-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.participant-name {
    font-weight: 600;
    color: #1f2937;
}

.participant-role {
    font-size: 12px;
    color: #6b7280;
}

.you-badge {
    font-size: 10px;
    background: #667eea;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    margin-left: 5px;
}

.actions-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e0e6ed;
}

.action-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-confirm, .btn-cancel, .btn-refuse, .btn-delete, .btn-primary {
    padding: 12px 24px;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    flex: 1;
    min-width: 200px;
}

.btn-confirm {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-confirm:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-refuse, .btn-cancel {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.btn-refuse:hover:not(:disabled), .btn-cancel:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.btn-delete {
    background: white;
    border: 2px solid #ef4444;
    color: #ef4444;
}

.btn-delete:hover:not(:disabled) {
    background: #ef4444;
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    display: inline-block;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.no-actions-message, .completed-message, .cancelled-message {
    text-align: center;
    padding: 20px;
    background: #f9fafb;
    border-radius: 12px;
    color: #6b7280;
}

.completed-message {
    background: #d1fae5;
    color: #065f46;
}

.cancelled-message {
    background: #fee2e2;
    color: #991b1b;
}

.auto-update-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #10b981;
    color: white;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 14px;
    z-index: 1000;
    animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .appointment-details-container {
        margin: 20px;
        padding: 0;
    }
    
    .details-content {
        padding: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-confirm, .btn-cancel, .btn-refuse, .btn-delete {
        width: 100%;
    }
}
</style>