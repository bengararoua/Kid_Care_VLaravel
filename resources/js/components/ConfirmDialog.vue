<template>
  <div v-if="visible" class="confirm-overlay" @click.self="cancel">
    <div class="confirm-dialog">
      <div class="confirm-header" :class="type">
        <div class="confirm-icon">
          <span v-if="type === 'danger'">⚠️</span>
          <span v-else-if="type === 'warning'">⚠️</span>
          <span v-else-if="type === 'info'">ℹ️</span>
          <span v-else>❓</span>
        </div>
        <h3>{{ title }}</h3>
      </div>
      <div class="confirm-body">
        <p>{{ message }}</p>
        <div v-if="showPasswordInput" class="password-input-group">
          <label>Mot de passe</label>
          <input 
            ref="passwordInputRef"
            v-model="passwordValue" 
            type="password" 
            class="password-input"
            placeholder="Entrez votre mot de passe"
            @keyup.enter="confirm"
          >
        </div>
      </div>
      <div class="confirm-footer">
        <button @click="cancel" class="btn-cancel">
          <span>✕</span> {{ cancelText }}
        </button>
        <button @click="confirm" class="btn-confirm" :class="type">
          <span>✓</span> {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue'

const visible = ref(false)
let resolvePromise = null

const title = ref('Confirmation')
const message = ref('Êtes-vous sûr de vouloir effectuer cette action ?')
const type = ref('warning')
const confirmText = ref('Confirmer')
const cancelText = ref('Annuler')
const showPasswordInput = ref(false)
const passwordValue = ref('')
const passwordInputRef = ref(null)

/**
 * Affiche le dialogue de confirmation
 * @param {Object} options - Options de configuration
 * @param {string} options.title - Titre du dialogue
 * @param {string} options.message - Message du dialogue
 * @param {string} options.type - Type de dialogue (danger, warning, info)
 * @param {string} options.confirmText - Texte du bouton de confirmation
 * @param {string} options.cancelText - Texte du bouton d'annulation
 * @param {boolean} options.showPasswordInput - Afficher un champ de mot de passe
 * @returns {Promise<boolean|Object>} - Retourne true/false ou un objet avec password
 */
const show = (options) => {
  return new Promise((resolve) => {
    title.value = options.title || 'Confirmation'
    message.value = options.message || 'Êtes-vous sûr ?'
    type.value = options.type || 'warning'
    confirmText.value = options.confirmText || 'Confirmer'
    cancelText.value = options.cancelText || 'Annuler'
    showPasswordInput.value = options.showPasswordInput || false
    passwordValue.value = ''
    
    visible.value = true
    
    // Focus sur le champ mot de passe si présent
    if (showPasswordInput.value) {
      nextTick(() => {
        if (passwordInputRef.value) {
          passwordInputRef.value.focus()
        }
      })
    }
    
    resolvePromise = resolve
  })
}

const confirm = () => {
  visible.value = false
  
  if (showPasswordInput.value) {
    // Vérifier que le mot de passe n'est pas vide
    if (!passwordValue.value) {
      // Si mot de passe vide, ne pas fermer et montrer une erreur
      // Mais pour l'instant, on retourne false
      if (resolvePromise) {
        resolvePromise({ confirmed: false, password: null, error: 'Mot de passe requis' })
      }
      return
    }
    // Retourner un objet avec le mot de passe
    if (resolvePromise) {
      resolvePromise({ confirmed: true, password: passwordValue.value })
    }
  } else {
    if (resolvePromise) {
      resolvePromise(true)
    }
  }
}

const cancel = () => {
  visible.value = false
  if (showPasswordInput.value) {
    if (resolvePromise) {
      resolvePromise({ confirmed: false, password: null })
    }
  } else {
    if (resolvePromise) {
      resolvePromise(false)
    }
  }
}

defineExpose({ show })
</script>

<style scoped>
.confirm-overlay {
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
  z-index: 1100;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.confirm-dialog {
  background: white;
  border-radius: 20px;
  width: 90%;
  max-width: 450px;
  overflow: hidden;
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

.confirm-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 24px;
  border-bottom: 1px solid #e5e7eb;
}

.confirm-header.danger {
  background: linear-gradient(135deg, #fef2f2, #fee2e2);
}

.confirm-header.warning {
  background: linear-gradient(135deg, #fffbeb, #fef3c7);
}

.confirm-header.info {
  background: linear-gradient(135deg, #eff6ff, #dbeafe);
}

.confirm-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  background: rgba(0, 0, 0, 0.05);
  animation: shakeIcon 0.5s ease-in-out;
}

@keyframes shakeIcon {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-3px); }
  75% { transform: translateX(3px); }
}

.confirm-header h3 {
  font-size: 18px;
  font-weight: 600;
  color: #1f2937;
  margin: 0;
}

.confirm-body {
  padding: 24px;
}

.confirm-body p {
  font-size: 14px;
  color: #4b5563;
  line-height: 1.5;
  margin: 0 0 16px 0;
}

.password-input-group {
  margin-top: 16px;
  animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.password-input-group label {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 8px;
}

.password-input {
  width: 100%;
  padding: 12px 14px;
  border: 2px solid #e0e6ed;
  border-radius: 12px;
  font-size: 14px;
  transition: all 0.2s;
}

.password-input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.confirm-footer {
  display: flex;
  gap: 12px;
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

.btn-cancel, .btn-confirm {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 16px;
  border: none;
  border-radius: 40px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel {
  background: white;
  color: #6b7280;
  border: 1px solid #e5e7eb;
}

.btn-cancel:hover {
  background: #f3f4f6;
  transform: translateY(-2px);
}

.btn-confirm {
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
}

.btn-confirm.danger {
  background: linear-gradient(135deg, #dc2626, #ef4444);
}

.btn-confirm.warning {
  background: linear-gradient(135deg, #f59e0b, #d97706);
}

.btn-confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Responsive */
@media (max-width: 500px) {
  .confirm-dialog {
    max-width: 350px;
  }
  
  .confirm-header {
    padding: 16px 20px;
  }
  
  .confirm-body {
    padding: 20px;
  }
  
  .confirm-footer {
    padding: 12px 20px;
  }
  
  .btn-cancel, .btn-confirm {
    padding: 8px 12px;
    font-size: 13px;
  }
}
</style>