<template>
  <Layout>
    <div class="page-privacy">
      <div class="hero-section-privacy">
        <div class="hero-content">
          <div class="hero-icon">🔒</div>
          <h1 class="hero-title">Politique de confidentialité</h1>
          <p class="hero-subtitle">La protection de vos données est notre priorité</p>
        </div>
      </div>

      <div class="container">
        <div class="content-card">
          <div class="last-update">
            📅 Dernière mise à jour : 25 avril 2025
          </div>

          <div class="card-section">
            <div class="section-icon">📋</div>
            <h2>Collecte des données</h2>
            <p>Chez KidCare Insight (Tunisie), nous collectons uniquement les données nécessaires au suivi comportemental de votre enfant : nom, âge, logs quotidiens (focus, humeur, sommeil, interactions sociales). Ces données sont hébergées sur des serveurs sécurisés situés en Tunisie, conformément à la loi tunisienne n° 2004‑63 relative à la protection des données à caractère personnel.</p>
          </div>

          <div class="card-section">
            <div class="section-icon">🔐</div>
            <h2>Protection des données</h2>
            <p>Nous utilisons un chiffrement de bout en bout pour toutes les communications (messageries entre parents, psychologues et enseignants). Les logs comportementaux sont anonymisés pour les analyses statistiques internes et ne sont jamais revendus à des tiers.</p>
          </div>

          <div class="card-section">
            <div class="section-icon">👨‍👩‍👧</div>
            <h2>Vos droits</h2>
            <p>Vous avez le droit d'accéder, de modifier ou de supprimer toutes vos données à tout moment depuis votre espace paramètres. Vous pouvez également exporter vos données au format JSON ou PDF.</p>
          </div>

          <div class="card-section">
            <div class="section-icon">📧</div>
            <h2>Contact DPO</h2>
            <p>Pour toute question relative à vos données personnelles, vous pouvez contacter notre Délégué à la Protection des Données :</p>
            <div class="contact-info">
              <p><strong>Email :</strong> <a href="mailto:dpo@kidcare.tn">dpo@kidcare.tn</a></p>
              <p><strong>Téléphone :</strong> +216 70 000 000</p>
              <p><strong>Adresse :</strong> Pôle Technologique El Ghazala, 2088 Ariana, Tunis, Tunisie</p>
            </div>
          </div>

          <div class="card-section">
            <div class="section-icon">🍪</div>
            <h2>Cookies</h2>
            <p>Nous utilisons uniquement des cookies techniques nécessaires au fonctionnement de l'application (authentification, session). Aucun cookie publicitaire ou de traçage n'est utilisé.</p>
          </div>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script setup>
import Layout from '../Layout.vue'
</script>

<style scoped>
.page-privacy {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fb 0%, #e8f4f8 100%);
}

.hero-section-privacy {
  background: linear-gradient(135deg, #1a0a2e 0%, #2d1b4e 100%);
  padding: 60px 0;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero-section-privacy::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  animation: pulse 4s ease infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.05); }
}

.hero-content {
  position: relative;
  z-index: 1;
  animation: fadeInDown 0.6s ease;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.hero-icon {
  font-size: 64px;
  margin-bottom: 20px;
  animation: shake 0.5s ease;
}

@keyframes shake {
  0%, 100% { transform: rotate(0deg); }
  25% { transform: rotate(-10deg); }
  75% { transform: rotate(10deg); }
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

.content-card {
  background: white;
  border-radius: 24px;
  padding: 40px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  animation: fadeInUp 0.6s ease 0.2s both;
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

.last-update {
  background: #fef3c7;
  padding: 12px 20px;
  border-radius: 12px;
  margin-bottom: 30px;
  font-size: 14px;
  color: #92400e;
  text-align: center;
}

.card-section {
  margin-bottom: 40px;
  padding: 20px;
  border-radius: 16px;
  transition: all 0.3s ease;
  border-left: 4px solid transparent;
}

.card-section:hover {
  background: #f8f9fc;
  border-left-color: #1a0a2e;
  transform: translateX(10px);
}

.section-icon {
  font-size: 40px;
  margin-bottom: 16px;
}

.card-section h2 {
  font-size: 22px;
  color: #1a0a2e;
  margin-bottom: 16px;
}

.card-section p {
  font-size: 15px;
  line-height: 1.6;
  color: #4b5563;
  margin-bottom: 12px;
}

.contact-info {
  background: #f3f4f6;
  padding: 16px;
  border-radius: 12px;
  margin-top: 16px;
}

.contact-info p {
  margin-bottom: 8px;
}

.contact-info a {
  color: #667eea;
  text-decoration: none;
}

.contact-info a:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .hero-title { font-size: 28px; }
  .content-card { padding: 20px; }
  .card-section:hover { transform: translateX(0); }
}
</style>