<template>
  <Layout>
    <div class="page-contact">
      <div class="hero-section-contact">
        <div class="hero-content">
          <div class="hero-icon">📧</div>
          <h1 class="hero-title">Nous contacter</h1>
          <p class="hero-subtitle">Une question ? Une suggestion ? Écrivez-nous !</p>
        </div>
      </div>

      <div class="container">
        <div class="contact-grid">
          <div class="contact-info-card">
            <div class="info-header">
              <span class="info-icon">📍</span>
              <h3>Adresse</h3>
            </div>
            <p>Pôle Technologique El Ghazala<br>2088 Ariana, Tunis, Tunisie</p>
          </div>

          <div class="contact-info-card">
            <div class="info-header">
              <span class="info-icon">📞</span>
              <h3>Téléphone</h3>
            </div>
            <p>+216 70 000 000<br><small>Du lundi au vendredi, 9h‑17h</small></p>
          </div>

          <div class="contact-info-card">
            <div class="info-header">
              <span class="info-icon">✉️</span>
              <h3>Email</h3>
            </div>
            <p><a href="mailto:contact@kidcare.tn">contact@kidcare.tn</a><br><a href="mailto:support@kidcare.tn">support@kidcare.tn</a></p>
          </div>
        </div>

        <div class="contact-form-card">
          <div class="form-header">
            <div class="form-icon">💬</div>
            <h2>Envoyez-nous un message</h2>
            <p>Nous vous répondrons dans les plus brefs délais</p>
          </div>

          <form @submit.prevent="sendMessage" class="contact-form">
            <div class="form-row">
              <div class="form-group">
                <label>Nom complet</label>
                <input type="text" v-model="form.name" placeholder="Votre nom" required>
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="email" v-model="form.email" placeholder="votre@email.com" required>
              </div>
            </div>
            <div class="form-group">
              <label>Sujet</label>
              <select v-model="form.subject">
                <option value="general">Question générale</option>
                <option value="technical">Problème technique</option>
                <option value="collaboration">Collaboration / Partenariat</option>
                <option value="other">Autre</option>
              </select>
            </div>
            <div class="form-group">
              <label>Message</label>
              <textarea v-model="form.message" rows="5" placeholder="Votre message..." required></textarea>
            </div>
            <button type="submit" class="submit-btn" :disabled="isSubmitting">
              {{ isSubmitting ? 'Envoi en cours...' : '✉️ Envoyer le message' }}
            </button>
          </form>
          <p class="form-note">Vous pouvez également utiliser le <strong>système de messagerie intégré</strong> de l'application pour contacter directement notre équipe support.</p>
        </div>

        <div class="map-card">
          <div class="map-placeholder">
            <div class="map-icon">🗺️</div>
            <p>Pôle Technologique El Ghazala, Tunis</p>
            <small>Plan d'accès disponible sur demande</small>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import { ref } from 'vue'
import Layout from '../Layout.vue'

const form = ref({
  name: '',
  email: '',
  subject: 'general',
  message: ''
})
const isSubmitting = ref(false)

const sendMessage = async () => {
  isSubmitting.value = true
  // Simulation d'envoi (à remplacer par un vrai appel API)
  setTimeout(() => {
    alert('Merci pour votre message ! Nous vous répondrons rapidement.')
    form.value = { name: '', email: '', subject: 'general', message: '' }
    isSubmitting.value = false
  }, 1000)
}
</script>

<style scoped>
.page-contact {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fb 0%, #e8f4f8 100%);
}

.hero-section-contact {
  background: linear-gradient(135deg, #1a0a2e 0%, #2d1b4e 100%);
  padding: 60px 0;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero-section-contact::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.05) 50%, transparent 70%);
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}

.hero-content {
  position: relative;
  z-index: 1;
  animation: fadeInUp 0.6s ease;
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

.hero-icon {
  font-size: 64px;
  margin-bottom: 20px;
  animation: wiggle 1s ease infinite;
}

@keyframes wiggle {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(-15deg); }
  75% { transform: rotate(15deg); }
}

.hero-title {
  font-size: 36px;
  font-weight: 700;
  color: white;
  margin-bottom: 16px;
}

.hero-subtitle {
  font-size: 18px;
  color: rgba(255,255,255,0.8);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 40px 20px;
}

.contact-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 24px;
  margin-bottom: 40px;
}

.contact-info-card {
  background: white;
  border-radius: 20px;
  padding: 24px;
  text-align: center;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0,0,0,0.08);
  animation: fadeInUp 0.4s ease both;
}

.contact-info-card:nth-child(1) { animation-delay: 0.1s; }
.contact-info-card:nth-child(2) { animation-delay: 0.2s; }
.contact-info-card:nth-child(3) { animation-delay: 0.3s; }

.contact-info-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.info-header {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-bottom: 16px;
}

.info-icon {
  font-size: 28px;
}

.info-header h3 {
  font-size: 18px;
  color: #1a0a2e;
}

.contact-info-card p {
  font-size: 14px;
  color: #6b7280;
  line-height: 1.5;
}

.contact-info-card a {
  color: #667eea;
  text-decoration: none;
}

.contact-info-card a:hover {
  text-decoration: underline;
}

.contact-form-card {
  background: white;
  border-radius: 24px;
  padding: 40px;
  margin-bottom: 40px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  animation: fadeInUp 0.6s ease 0.4s both;
}

.form-header {
  text-align: center;
  margin-bottom: 32px;
}

.form-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.form-header h2 {
  font-size: 24px;
  color: #1a0a2e;
  margin-bottom: 8px;
}

.form-header p {
  color: #6b7280;
}

.contact-form {
  max-width: 600px;
  margin: 0 auto;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 8px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e0e6ed;
  border-radius: 12px;
  font-size: 14px;
  transition: all 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #1a0a2e;
  box-shadow: 0 0 0 3px rgba(26, 10, 46, 0.1);
}

.submit-btn {
  width: 100%;
  padding: 14px;
  background: linear-gradient(135deg, #1a0a2e, #2d1b4e);
  color: white;
  border: none;
  border-radius: 40px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(26, 10, 46, 0.3);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.form-note {
  text-align: center;
  font-size: 13px;
  color: #9ca3af;
  margin-top: 20px;
}

.map-card {
  background: white;
  border-radius: 24px;
  padding: 40px;
  text-align: center;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  animation: fadeInUp 0.6s ease 0.5s both;
}

.map-placeholder {
  background: linear-gradient(135deg, #f5f3ff, #ede9fe);
  border-radius: 20px;
  padding: 40px;
}

.map-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.map-placeholder p {
  font-weight: 500;
  color: #1a0a2e;
  margin-bottom: 8px;
}

.map-placeholder small {
  color: #6b7280;
}

@media (max-width: 900px) {
  .contact-grid { grid-template-columns: repeat(2, 1fr); }
  .form-row { grid-template-columns: 1fr; }
}

@media (max-width: 600px) {
  .contact-grid { grid-template-columns: 1fr; }
  .hero-title { font-size: 28px; }
  .contact-form-card { padding: 24px; }
}
</style>