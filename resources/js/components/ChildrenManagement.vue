<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'  
import axios from 'axios'
import Layout from './Layout.vue'
import ConfirmDialog from './ConfirmDialog.vue'
import NotificationToast from './NotificationToast.vue'

const router = useRouter()  

const children = ref([])
const psychologists = ref([])
const teachers = ref([])
const parents = ref([])
const showModal = ref(false)
const editingChild = ref(null)
const loading = ref(false)
const error = ref(null)
const success = ref(null)
const selectedPsychologist = ref(null)
const showPsychologistModal = ref(false)

// Références pour les notifications
const toast = ref(null)
const confirmDialog = ref(null)

// Formulaire
const form = ref({
  name: '',
  age: '',
  parent_id: '',
  psychologist_id: '',
  teacher_id: '',
  notes: ''
})

// Utilisateur connecté
const user = computed(() => JSON.parse(localStorage.getItem('user') || '{}'))
const isParent = computed(() => user.value?.role === 'parent')
const isPsychologist = computed(() => user.value?.role === 'psychologist')
const isTeacher = computed(() => user.value?.role === 'teacher')

// Notifications
const showNotification = (message, type = 'success', title = '') => {
  const titles = {
    success: 'Succès',
    error: 'Erreur',
    warning: 'Attention',
    info: 'Information'
  }
  toast.value?.addNotification(type, title || titles[type] || 'Notification', message, 4000)
}

// Export PDF
const exportPDF = async (childId) => {
  try {
    const response = await axios.get(`/api/export/child/${childId}`, {
      responseType: 'blob',
      headers: {
        'Accept': 'application/pdf',
        'Authorization': `Bearer ${localStorage.getItem('token')}`
      }
    })
    
    const blob = new Blob([response.data], { type: 'application/pdf' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `rapport_enfant_${childId}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    showNotification('PDF exporté avec succès !', 'success')
  } catch (err) {
    console.error('Export error:', err)
    showNotification('Erreur lors de l\'export du PDF', 'error')
  }
}

// Charger les enfants
const loadChildren = async () => {
  try {
    loading.value = true
    const res = await axios.get('/api/children')
    children.value = res.data
  } catch (err) {
    console.error('Error loading children:', err)
    showNotification('Impossible de charger les enfants', 'error')
  } finally {
    loading.value = false
  }
}

// Charger les psychologues
const loadPsychologists = async () => {
  try {
    const res = await axios.get('/api/users/psychologists')
    psychologists.value = res.data
  } catch (err) {
    console.error('Error loading psychologists:', err)
  }
}

// Charger les teachers
const loadTeachers = async () => {
  try {
    const res = await axios.get('/api/users/teachers')
    teachers.value = res.data
  } catch (err) {
    console.error('Error loading teachers:', err)
  }
}

// Charger les parents (pour le teacher)
const loadParents = async () => {
  try {
    const res = await axios.get('/api/users/parents')
    parents.value = res.data
  } catch (err) {
    console.error('Error loading parents:', err)
  }
}

// Voir les infos du psychologue
const viewPsychologistInfo = async (child) => {
  try {
    const res = await axios.get(`/api/children/${child.id}/psychologist`)
    selectedPsychologist.value = res.data
    showPsychologistModal.value = true
  } catch (err) {
    showNotification('Aucun psychologue assigné', 'error')
  }
}

// Envoyer un message au parent (pour psychologue)
const messageParent = (child) => {
  if (child.parent && child.parent.id) {
    router.push({
      path: '/messages',
      query: {
        contactId: child.parent.id,
        contactName: child.parent.name,
        contactRole: 'parent'
      }
    })
  } else {
    showNotification('Aucun parent assigné à cet enfant', 'error')
  }
}

// Ouvrir modal d'ajout
const openAddModal = () => {
  if (!isParent.value && !isTeacher.value) return
  editingChild.value = null
  form.value = {
    name: '',
    age: '',
    parent_id: '',
    psychologist_id: '',
    teacher_id: '',
    notes: ''
  }
  showModal.value = true
}

// Ouvrir modal d'édition
const openEditModal = (child) => {
  if (!isParent.value && !isTeacher.value) return
  editingChild.value = child
  form.value = {
    name: child.name,
    age: child.age,
    parent_id: child.parent_id || '',
    psychologist_id: child.psychologist_id || '',
    teacher_id: child.teacher_id || '',
    notes: child.notes || ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  showPsychologistModal.value = false
  editingChild.value = null
  selectedPsychologist.value = null
}

// Sauvegarder
const saveChild = async () => {
  if (!form.value.name || !form.value.age) {
    showNotification('Le nom et l\'âge sont requis', 'error')
    return
  }

  try {
    loading.value = true

    const dataToSend = {
      name: form.value.name,
      age: parseInt(form.value.age),
      notes: form.value.notes || null
    }
    
    if (isParent.value) {
      dataToSend.psychologist_id = form.value.psychologist_id || null
      dataToSend.teacher_id = form.value.teacher_id || null
    }
    
    if (isTeacher.value) {
      dataToSend.parent_id = form.value.parent_id || null
      dataToSend.psychologist_id = form.value.psychologist_id || null
      dataToSend.teacher_id = user.value.id
    }

    console.log('Sending data:', dataToSend)

    if (editingChild.value) {
      const response = await axios.put(`/api/children/${editingChild.value.id}`, dataToSend)
      console.log('Update response:', response.data)
      showNotification('Enfant modifié avec succès !', 'success')
    } else {
      const response = await axios.post('/api/children', dataToSend)
      console.log('Create response:', response.data)
      showNotification('Enfant ajouté avec succès !', 'success')
    }

    closeModal()
    await loadChildren()
  } catch (err) {
    console.error('Error saving child:', err)
    console.error('Error response:', err.response?.data)
    showNotification(err.response?.data?.error || 'Erreur lors de l\'enregistrement', 'error')
  } finally {
    loading.value = false
  }
}

// Supprimer avec confirmation animée
const deleteChild = async (child) => {
  if (!isParent.value && !isTeacher.value) return
  
  const confirmed = await confirmDialog.value?.show({
    title: 'Supprimer un enfant',
    message: `Êtes-vous sûr de vouloir supprimer "${child.name}" ? Toutes les données associées seront supprimées définitivement.`,
    type: 'danger',
    confirmText: 'Supprimer',
    cancelText: 'Annuler'
  })
  
  if (confirmed) {
    try {
      loading.value = true
      await axios.delete(`/api/children/${child.id}`)
      showNotification(`"${child.name}" a été supprimé avec succès`, 'success')
      await loadChildren()
    } catch (err) {
      console.error('Error deleting child:', err)
      showNotification(err.response?.data?.error || 'Erreur lors de la suppression', 'error')
    } finally {
      loading.value = false
    }
  }
}

// Voir les détails
const viewDetails = (child) => {
  router.push(`/child/${child.id}`)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' })
}

// Animation d'entrée pour les cartes
const getCardDelay = (index) => `${index * 0.05}s`

onMounted(() => {
  loadChildren()
  loadPsychologists()
  
  if (isParent.value) {
    loadTeachers()
  }
  
  if (isTeacher.value) {
    loadParents()
  }
})
</script>

<template>
  <Layout>
    <div class="children-container">
      <!-- Notifications -->
      <NotificationToast ref="toast" />
      <ConfirmDialog ref="confirmDialog" />

      <div class="page-header">
        <div>
          <h1 class="page-title">👶 Gestion des enfants</h1>
          <p class="page-subtitle">
            <span v-if="isParent">Gérez vos enfants et leurs professionnels assignés</span>
            <span v-else-if="isTeacher">Gérez les enfants que vous avez ajoutés</span>
            <span v-else-if="isPsychologist">Consultez les enfants qui vous sont assignés</span>
          </p>
        </div>
        <button v-if="isParent || isTeacher" @click="openAddModal" class="btn-add">
          <span class="btn-icon">➕</span>
          Ajouter un enfant
        </button>
      </div>

      <div v-if="loading && !children.length" class="loading-state">
        <div class="spinner"></div>
        <p>Chargement des enfants...</p>
      </div>

      <div v-else class="children-grid">
        <div 
          v-for="(child, index) in children" 
          :key="child.id" 
          class="child-card"
          :style="{ animationDelay: getCardDelay(index) }"
        >
          <div class="card-header">
            <div class="child-avatar" :style="{ background: `linear-gradient(135deg, #${Math.floor(Math.random()*16777215).toString(16)}, #${Math.floor(Math.random()*16777215).toString(16)})` }">
              {{ child.name?.charAt(0) || '👶' }}
            </div>
            <div class="child-info">
              <h3 class="child-name">{{ child.name }}</h3>
              <p class="child-age">{{ child.age }} ans</p>
            </div>
            <div class="card-actions">
              <button v-if="isParent || isTeacher" @click="openEditModal(child)" class="btn-icon" title="Modifier">
                ✏️
              </button>
              <button v-if="isParent || isTeacher" @click="deleteChild(child)" class="btn-icon delete" title="Supprimer">
                🗑️
              </button>
              <button @click="exportPDF(child.id)" class="btn-pdf" title="Exporter PDF">
                📄
              </button>
            </div>
          </div>
          
          <div class="card-body">
            <div class="info-row">
              <span class="info-label">👨‍👩‍👧 Parent :</span>
              <span class="info-value">{{ child.parent?.name || 'Non assigné' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">👩‍⚕️ Psychologue :</span>
              <span class="info-value">
                <span class="psychologist-name">{{ child.psychologist?.name || 'Non assigné' }}</span>
                <button v-if="child.psychologist && (isParent || isTeacher)" 
                        @click="viewPsychologistInfo(child)" 
                        class="btn-contact">
                  📞 Contacter
                </button>
              </span>
            </div>
            <div class="info-row">
              <span class="info-label">📚 Enseignant :</span>
              <span class="info-value">{{ child.teacher?.name || 'Non assigné' }}</span>
            </div>
            <div v-if="child.notes && (isParent || isPsychologist || isTeacher)" class="info-row">
              <span class="info-label">📝 Notes :</span>
              <span class="info-value notes-value">{{ child.notes }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">📅 Créé le :</span>
              <span class="info-value">{{ formatDate(child.created_at) }}</span>
            </div>
          </div>
          
          <div class="card-footer">
            <button @click="viewDetails(child)" class="btn-view">
              📊 Voir détails
            </button>
            <button v-if="isPsychologist && child.parent" @click="messageParent(child)" class="btn-message">
              💬 Message Parent
            </button>
          </div>
        </div>

        <div v-if="children.length === 0 && !loading" class="empty-state">
          <div class="empty-icon">👶</div>
          <h3>Aucun enfant trouvé</h3>
          <p v-if="isParent">Cliquez sur "Ajouter un enfant" pour commencer</p>
          <p v-else-if="isTeacher">Cliquez sur "Ajouter un enfant" pour ajouter des enfants</p>
          <p v-else-if="isPsychologist">Aucun enfant ne vous est encore assigné</p>
        </div>
      </div>

      <!-- Modal Add/Edit -->
      <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal">
          <div class="modal-header">
            <h2>{{ editingChild ? '✏️ Modifier' : '➕ Ajouter un enfant' }}</h2>
            <button @click="closeModal" class="modal-close">✕</button>
          </div>
          
          <div class="modal-body">
            <div class="form-group">
              <label>Nom de l'enfant *</label>
              <input v-model="form.name" type="text" placeholder="Entrez le nom" class="form-input">
            </div>
            
            <div class="form-group">
              <label>Âge *</label>
              <input v-model="form.age" type="number" min="1" max="18" placeholder="Âge" class="form-input">
            </div>
            
            <div v-if="isTeacher" class="form-group">
              <label>👨‍👩‍👧 Assigner un parent (optionnel)</label>
              <select v-model="form.parent_id" class="form-select">
                <option :value="null">— Aucun —</option>
                <option v-for="parent in parents" :key="parent.id" :value="parent.id">
                  {{ parent.name }} ({{ parent.email }})
                </option>
              </select>
            </div>
            
            <div v-if="isTeacher || isParent" class="form-group">
              <label>👩‍⚕️ Assigner un psychologue (optionnel)</label>
              <select v-model="form.psychologist_id" class="form-select">
                <option :value="null">— Aucun —</option>
                <option v-for="psych in psychologists" :key="psych.id" :value="psych.id">
                  {{ psych.name }} ({{ psych.email }})
                </option>
              </select>
            </div>
            
            <div v-if="isParent" class="form-group">
              <label>📚 Assigner un enseignant (optionnel)</label>
              <select v-model="form.teacher_id" class="form-select">
                <option :value="null">— Aucun —</option>
                <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                  {{ teacher.name }} ({{ teacher.email }})
                </option>
              </select>
            </div>
            
            <div class="form-group">
              <label>Notes (optionnel)</label>
              <textarea v-model="form.notes" rows="3" placeholder="Notes sur l'enfant..." class="form-textarea"></textarea>
            </div>
          </div>
          
          <div class="modal-footer">
            <button @click="closeModal" class="btn-cancel">Annuler</button>
            <button @click="saveChild" class="btn-save" :disabled="loading">
              {{ loading ? 'Enregistrement...' : (editingChild ? 'Modifier' : 'Ajouter') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Modal Psychologist Info -->
      <div v-if="showPsychologistModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal modal-small">
          <div class="modal-header">
            <h2>👩‍⚕️ Contact du psychologue</h2>
            <button @click="closeModal" class="modal-close">✕</button>
          </div>
          <div class="modal-body" v-if="selectedPsychologist">
            <div class="psychologist-info">
              <div class="psychologist-avatar">
                {{ selectedPsychologist.name?.charAt(0) || '👩‍⚕️' }}
              </div>
              <h3>{{ selectedPsychologist.name }}</h3>
              <p class="psychologist-email">📧 {{ selectedPsychologist.email }}</p>
              <div class="psychologist-actions">
                <button @click="window.location.href = `mailto:${selectedPsychologist.email}`" class="btn-email">
                  ✉️ Envoyer un email
                </button>
                <button @click="closeModal" class="btn-close-modal">Fermer</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<style scoped>
/* Vos styles existants - inchangés */
.children-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 20px;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 8px;
}

.page-subtitle {
  color: #6b7280;
  font-size: 14px;
}

.btn-add {
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 40px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 8px;
}

.btn-add:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(26, 10, 46, 0.3);
}

.loading-state {
  text-align: center;
  padding: 60px;
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

.children-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
  gap: 24px;
}

.child-card {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  animation: fadeInUp 0.5s ease both;
}

.child-card:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 20px;
  background: linear-gradient(135deg, #f5f3ff, #ede9fe);
  border-bottom: 1px solid #e0e6ed;
}

.child-avatar {
  width: 55px;
  height: 55px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: 700;
  color: white;
  transition: transform 0.3s;
}

.child-card:hover .child-avatar {
  transform: scale(1.05);
}

.child-info {
  flex: 1;
}

.child-name {
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 4px;
}

.child-age {
  font-size: 13px;
  color: #6b7280;
}

.card-actions {
  display: flex;
  gap: 8px;
}

.btn-icon, .btn-pdf {
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  padding: 8px;
  border-radius: 10px;
  transition: all 0.2s;
}

.btn-icon:hover {
  background: rgba(0, 0, 0, 0.05);
  transform: scale(1.1);
}

.btn-icon.delete:hover {
  background: #fee2e2;
  color: #ef4444;
}

.btn-pdf {
  color: #ef4444;
}

.btn-pdf:hover {
  background: #fee2e2;
  transform: scale(1.1);
}

.card-body {
  padding: 20px;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #f3f4f6;
  transition: background 0.2s;
}

.info-row:hover {
  background: #f9fafb;
  padding-left: 8px;
}

.info-row:last-child {
  border-bottom: none;
}

.info-label {
  font-size: 13px;
  font-weight: 600;
  color: #6b7280;
  min-width: 120px;
}

.info-value {
  font-size: 13px;
  color: #374151;
  text-align: right;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.notes-value {
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.psychologist-name {
  font-weight: 500;
  color: #4f46e5;
}

.btn-contact {
  background: none;
  border: none;
  color: #4f46e5;
  cursor: pointer;
  font-size: 11px;
  padding: 4px 10px;
  border-radius: 20px;
  background: #eef2ff;
  transition: all 0.2s;
}

.btn-contact:hover {
  background: #e0e7ff;
  transform: scale(1.02);
}

.card-footer {
  display: flex;
  gap: 12px;
  padding: 16px 20px;
  background: #f9fafb;
  border-top: 1px solid #e0e6ed;
}

.btn-view, .btn-message {
  flex: 1;
  padding: 10px;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-view {
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
}

.btn-view:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 10, 46, 0.3);
}

.btn-message {
  background: #eef2ff;
  color: #4f46e5;
}

.btn-message:hover {
  background: #e0e7ff;
  transform: translateY(-2px);
}

.empty-state {
  text-align: center;
  padding: 60px;
  background: white;
  border-radius: 20px;
  animation: fadeInUp 0.5s ease;
}

.empty-icon {
  font-size: 64px;
  margin-bottom: 16px;
  opacity: 0.5;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.modal {
  background: white;
  border-radius: 24px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-small {
  max-width: 400px;
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
  color: #9ca3af;
  transition: all 0.2s;
}

.modal-close:hover {
  transform: scale(1.1);
  color: #ef4444;
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
  padding: 12px 14px;
  border: 2px solid #e0e6ed;
  border-radius: 12px;
  font-size: 14px;
  transition: all 0.2s;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: #1a0a2e;
  box-shadow: 0 0 0 3px rgba(26, 10, 46, 0.1);
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
  transition: all 0.2s;
}

.btn-cancel {
  background: #f3f4f6;
  color: #6b7280;
}

.btn-cancel:hover {
  background: #e5e7eb;
}

.btn-save {
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 10, 46, 0.3);
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.psychologist-info {
  text-align: center;
  padding: 20px;
}

.psychologist-avatar {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 40px;
  margin: 0 auto 16px;
  color: white;
  animation: scaleIn 0.3s ease;
}

@keyframes scaleIn {
  from { transform: scale(0); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.psychologist-info h3 {
  font-size: 20px;
  margin-bottom: 8px;
}

.psychologist-email {
  color: #6b7280;
  margin-bottom: 24px;
}

.psychologist-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
}

.btn-email, .btn-close-modal {
  padding: 10px 20px;
  border: none;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-email {
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
}

.btn-email:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(26, 10, 46, 0.3);
}

.btn-close-modal {
  background: #f3f4f6;
  color: #6b7280;
}

.btn-close-modal:hover {
  background: #e5e7eb;
}

@media (max-width: 900px) {
  .children-grid {
    grid-template-columns: 1fr;
  }
  
  .page-header {
    flex-direction: column;
    text-align: center;
  }
  
  .info-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 5px;
  }
  
  .info-value {
    text-align: left;
    justify-content: flex-start;
  }
}
</style>