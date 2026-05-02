<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import NotificationToast from './NotificationToast.vue'
import ConfirmDialog from './ConfirmDialog.vue'

const props = defineProps({
    childId: {
        type: [Number, String],
        required: true
    }
})

const routines = ref([])
const loading = ref(false)
const showModal = ref(false)
const editingRoutine = ref(null)
const selectedDay = ref('monday')

// Références aux composants
const toast = ref(null)
const confirmDialog = ref(null)

// Formulaire
const form = ref({
    day_of_week: 'monday',
    time: '08:00',
    activity: '',
    duration: null
})

// Notifications
const showSuccess = (message) => {
    toast.value?.addNotification('success', 'Succès', message, 4000)
}

const showError = (message) => {
    toast.value?.addNotification('error', 'Erreur', message, 5000)
}

// Jours de la semaine
const daysOfWeek = [
    { value: 'monday', label: 'Lundi', icon: '📅' },
    { value: 'tuesday', label: 'Mardi', icon: '📅' },
    { value: 'wednesday', label: 'Mercredi', icon: '📅' },
    { value: 'thursday', label: 'Jeudi', icon: '📅' },
    { value: 'friday', label: 'Vendredi', icon: '📅' },
    { value: 'saturday', label: 'Samedi', icon: '🎉' },
    { value: 'sunday', label: 'Dimanche', icon: '🎉' }
]

const routinesByDay = computed(() => {
    return routines.value.filter(r => r.day_of_week === selectedDay.value)
})

// Charger les routines
const loadRoutines = async () => {
    if (!props.childId) return
    
    loading.value = true
    try {
        const res = await axios.get(`/api/routines/${props.childId}`)
        routines.value = res.data || []
    } catch (err) {
        console.error('Error loading routines:', err)
        showError('Impossible de charger les routines')
    } finally {
        loading.value = false
    }
}

// Ouvrir modal d'ajout
const openAddModal = () => {
    editingRoutine.value = null
    form.value = {
        day_of_week: selectedDay.value,
        time: '08:00',
        activity: '',
        duration: null
    }
    showModal.value = true
}

// Ouvrir modal d'édition
const openEditModal = (routine) => {
    editingRoutine.value = routine
    form.value = {
        day_of_week: routine.day_of_week,
        time: routine.time.substring(0, 5),
        activity: routine.activity,
        duration: routine.duration
    }
    showModal.value = true
}

// Fermer modal
const closeModal = () => {
    showModal.value = false
    editingRoutine.value = null
}

// Sauvegarder routine
const saveRoutine = async () => {
    if (!form.value.activity.trim()) {
        showError('Veuillez saisir une activité')
        return
    }

    loading.value = true
    try {
        if (editingRoutine.value) {
            await axios.put(`/api/routines/${editingRoutine.value.id}`, form.value)
            showSuccess('Routine modifiée avec succès !')
        } else {
            await axios.post(`/api/routines/${props.childId}`, form.value)
            showSuccess('Routine ajoutée avec succès !')
        }
        
        closeModal()
        await loadRoutines()
    } catch (err) {
        console.error('Error saving routine:', err)
        showError(err.response?.data?.error || 'Erreur lors de l\'enregistrement')
    } finally {
        loading.value = false
    }
}

// Supprimer routine avec confirmation
const deleteRoutine = async (routine) => {
    try {
        const confirmed = await confirmDialog.value?.show({
            title: 'Supprimer une routine',
            message: `Êtes-vous sûr de vouloir supprimer l'activité "${routine.activity}" ?`,
            type: 'danger',
            confirmText: 'Supprimer'
        })
        
        if (confirmed) {
            await axios.delete(`/api/routines/${routine.id}`)
            showSuccess('Routine supprimée avec succès !')
            await loadRoutines()
        }
    } catch (err) {
        console.error('Error deleting routine:', err)
        showError('Erreur lors de la suppression')
    }
}

// Basculer complété
const toggleComplete = async (routine) => {
    try {
        const res = await axios.patch(`/api/routines/${routine.id}/toggle`)
        routine.completed = res.data.completed
        showSuccess(routine.completed ? '✅ Activité complétée !' : '🔄 Activité réouverte')
    } catch (err) {
        console.error('Error toggling routine:', err)
        showError('Erreur lors de la mise à jour')
    }
}

// Changer de jour
const changeDay = (day) => {
    selectedDay.value = day
}

onMounted(() => {
    loadRoutines()
})
</script>

<template>
    <div class="routine-planner">
        <!-- Notifications -->
        <NotificationToast ref="toast" />
        <ConfirmDialog ref="confirmDialog" />

        <div class="planner-header">
            <div class="header-title">
                <h2>📋 Planificateur de routine</h2>
                <p>Organisez les activités quotidiennes de votre enfant</p>
            </div>
            <button @click="openAddModal" class="btn-add-routine">
                ➕ Ajouter une activité
            </button>
        </div>

        <!-- Sélecteur de jours -->
        <div class="days-selector">
            <button 
                v-for="day in daysOfWeek" 
                :key="day.value"
                @click="changeDay(day.value)"
                class="day-btn"
                :class="{ active: selectedDay === day.value }"
            >
                <span class="day-icon">{{ day.icon }}</span>
                <span class="day-label">{{ day.label }}</span>
            </button>
        </div>

        <!-- Liste des routines -->
        <div class="routines-list">
            <div v-if="loading" class="loading-state">
                <div class="spinner-small"></div>
                <p>Chargement...</p>
            </div>

            <div v-else-if="!loading && routinesByDay.length === 0" class="empty-state">
                <div class="empty-icon">📭</div>
                <p>Aucune activité programmée</p>
                <p class="empty-hint">Cliquez sur "Ajouter une activité" pour commencer</p>
            </div>

            <div v-else class="routines-items">
                <div 
                    v-for="routine in routinesByDay" 
                    :key="routine.id" 
                    class="routine-card"
                    :class="{ completed: routine.completed }"
                >
                    <div class="routine-time">
                        <span class="time-icon">⏰</span>
                        <span class="time-value">{{ routine.time?.substring(0,5) }}</span>
                    </div>
                    <div class="routine-content">
                        <div class="routine-activity">{{ routine.activity }}</div>
                        <div v-if="routine.duration" class="routine-duration">
                            ⏱️ {{ routine.duration }} minutes
                        </div>
                    </div>
                    <div class="routine-actions">
                        <button @click="toggleComplete(routine)" class="btn-complete" :class="{ completed: routine.completed }">
                            {{ routine.completed ? '✓ Fait' : '○ À faire' }}
                        </button>
                        <button @click="openEditModal(routine)" class="btn-edit" title="Modifier">
                            ✏️
                        </button>
                        <button @click="deleteRoutine(routine)" class="btn-delete" title="Supprimer">
                            🗑️
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Ajout/Modification -->
        <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
            <div class="modal">
                <div class="modal-header">
                    <h2>{{ editingRoutine ? '✏️ Modifier' : '➕ Ajouter une activité' }}</h2>
                    <button @click="closeModal" class="modal-close">✕</button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jour de la semaine</label>
                        <select v-model="form.day_of_week" class="form-select">
                            <option value="monday">Lundi</option>
                            <option value="tuesday">Mardi</option>
                            <option value="wednesday">Mercredi</option>
                            <option value="thursday">Jeudi</option>
                            <option value="friday">Vendredi</option>
                            <option value="saturday">Samedi</option>
                            <option value="sunday">Dimanche</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Heure</label>
                            <input v-model="form.time" type="time" class="form-input">
                        </div>
                        <div class="form-group">
                            <label>Durée (minutes)</label>
                            <input v-model="form.duration" type="number" min="1" max="180" placeholder="Optionnel" class="form-input">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Activité</label>
                        <textarea v-model="form.activity" rows="2" placeholder="Décrivez l'activité..." class="form-textarea"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button @click="closeModal" class="btn-cancel">Annuler</button>
                    <button @click="saveRoutine" class="btn-save" :disabled="loading">
                        {{ loading ? 'Enregistrement...' : (editingRoutine ? 'Modifier' : 'Ajouter') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Tous vos styles existants - inchangés */
.routine-planner {
    background: white;
    border-radius: 24px;
    padding: 24px;
    margin-top: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.planner-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.header-title h2 {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.header-title p {
    font-size: 13px;
    color: #6b7280;
}

.btn-add-routine {
    background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 40px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-add-routine:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 10, 46, 0.3);
}

.days-selector {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.day-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    background: #f3f4f6;
    border: none;
    border-radius: 16px;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 70px;
}

.day-btn:hover {
    background: #e5e7eb;
    transform: translateY(-2px);
}

.day-btn.active {
    background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
    color: white;
}

.day-icon {
    font-size: 20px;
}

.day-label {
    font-size: 12px;
    font-weight: 600;
}

.routines-list {
    min-height: 200px;
}

.routines-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.routine-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 16px;
    transition: all 0.2s;
    border: 1px solid #e5e7eb;
}

.routine-card:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.routine-card.completed {
    opacity: 0.6;
    background: #f3f4f6;
}

.routine-time {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 70px;
    padding: 8px;
    background: white;
    border-radius: 12px;
}

.time-icon {
    font-size: 16px;
}

.time-value {
    font-size: 14px;
    font-weight: 700;
    color: #1f2937;
}

.routine-content {
    flex: 1;
}

.routine-activity {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 4px;
}

.routine-duration {
    font-size: 11px;
    color: #6b7280;
}

.routine-actions {
    display: flex;
    gap: 8px;
}

.btn-complete {
    padding: 6px 14px;
    border: none;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    background: #e5e7eb;
    color: #6b7280;
    transition: all 0.2s;
}

.btn-complete.completed {
    background: #d1fae5;
    color: #065f46;
}

.btn-complete:hover {
    transform: scale(1.02);
}

.btn-edit, .btn-delete {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    padding: 6px;
    border-radius: 8px;
    transition: all 0.2s;
}

.btn-edit:hover {
    background: #e0e7ff;
    transform: scale(1.05);
}

.btn-delete:hover {
    background: #fee2e2;
    transform: scale(1.05);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.loading-state, .empty-state {
    text-align: center;
    padding: 40px;
    color: #9ca3af;
}

.spinner-small {
    width: 30px;
    height: 30px;
    border: 2px solid #e0e6ed;
    border-top-color: #1a0a2e;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 12px;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.empty-icon {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-hint {
    font-size: 12px;
    color: #cbd5e1;
    margin-top: 8px;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal {
    background: white;
    border-radius: 24px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #e0e6ed;
}

.modal-header h2 {
    font-size: 20px;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
}

.modal-body {
    padding: 24px;
}

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
    padding: 10px 14px;
    border: 2px solid #e0e6ed;
    border-radius: 12px;
    font-size: 14px;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #1a0a2e;
}

.modal-footer {
    display: flex;
    gap: 12px;
    padding: 16px 24px;
    border-top: 1px solid #e0e6ed;
}

.btn-cancel, .btn-save {
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 40px;
    font-weight: 600;
    cursor: pointer;
}

.btn-cancel {
    background: #f3f4f6;
    color: #6b7280;
}

.btn-save {
    background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
    color: white;
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .routine-card {
        flex-direction: column;
        text-align: center;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .days-selector {
        justify-content: center;
    }
    
    .day-btn {
        min-width: 55px;
        padding: 8px 12px;
    }
}
</style>