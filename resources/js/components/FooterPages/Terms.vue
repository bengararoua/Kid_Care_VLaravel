<template>
  <Layout>
    <div class="page-terms">
      <div class="hero-section-terms">
        <div class="hero-content">
          <div class="hero-icon">📜</div>
          <h1 class="hero-title">Conditions générales d'utilisation</h1>
          <p class="hero-subtitle">Veuillez lire attentivement ces conditions</p>
        </div>
      </div>

      <div class="container">
        <div class="content-card">
          <div class="last-update">
            📅 Dernière mise à jour : 25 avril 2025
          </div>

          <div class="card-section">
            <div class="section-icon">✅</div>
            <h2>Acceptation des conditions</h2>
            <p>En utilisant KidCare Insight, vous acceptez pleinement et sans réserve les présentes conditions générales d'utilisation. Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser notre application.</p>
          </div>

          <div class="card-section">
            <div class="section-icon">👨‍👩‍👧</div>
            <h2>Responsabilité des parents</h2>
            <p>Les parents déclarent être légalement responsables des données concernant leurs enfants. Ils s'engagent à fournir des informations exactes et à jour. Toute fausse déclaration engage leur responsabilité.</p>
          </div>

          <div class="card-section">
            <div class="section-icon">⚖️</div>
            <h2>Propriété intellectuelle</h2>
            <p>Tous les contenus de l'application (textes, graphismes, logos, icônes) sont la propriété exclusive de KidCare Insight Tunisie. Toute reproduction est interdite sans autorisation préalable.</p>
          </div>

          <div class="card-section">
            <div class="section-icon">🛡️</div>
            <h2>Limitation de responsabilité</h2>
            <p>KidCare Insight est un outil d'aide à la décision et ne remplace en aucun cas l'avis d'un professionnel de santé (médecin, psychologue). L'application est fournie "en l'état" – nous nous efforçons d'assurer une disponibilité maximale, mais des interruptions temporaires peuvent survenir.</p>
          </div>

          <div class="card-section">
            <div class="section-icon">💳</div>
            <h2>Tarifs et paiement</h2>
            <p>L'utilisation de l'application est gratuite pour la version de base. Des fonctionnalités premium pourront être proposées ultérieurement. Vous serez informé préalablement de tout changement tarifaire.</p>
          </div>

          <div class="card-section">
            <div class="section-icon">⚡</div>
            <h2>Modification des conditions</h2>
            <p>Nous nous réservons le droit de modifier ces conditions à tout moment. Les modifications seront notifiées par email ou via l'application. L'utilisation continue de l'application après modification vaut acceptation.</p>
          </div>

          <div class="card-section">
            <div class="section-icon">⚖️</div>
            <h2>Droit applicable</h2>
            <p>En cas de litige, la loi tunisienne est applicable et les tribunaux de Tunis sont seuls compétents.</p>
          </div>

          <div class="card-section-highlight">
            <div class="section-icon">📧</div>
            <h3>Contact</h3>
            <p>Pour toute question relative aux conditions générales :</p>
            <p><strong>Email :</strong> <a href="mailto:legal@kidcare.tn">legal@kidcare.tn</a></p>
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
.page-terms {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fb 0%, #fef3c7 100%);
}

.hero-section-terms {
  background: linear-gradient(135deg, #1a0a2e 0%, #2d1b4e 100%);
  padding: 60px 0;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero-section-terms::after {
  content: '';
  position: absolute;
  bottom: -50%;
  right: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
  animation: spin 30s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.hero-content {
  position: relative;
  z-index: 1;
  animation: fadeInScale 0.6s ease;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.hero-icon {
  font-size: 64px;
  margin-bottom: 20px;
  animation: float 3s ease infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
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
  animation: slideInRight 0.6s ease 0.2s both;
}

@keyframes slideInRight {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.last-update {
  background: #d1fae5;
  padding: 12px 20px;
  border-radius: 12px;
  margin-bottom: 30px;
  font-size: 14px;
  color: #065f46;
  text-align: center;
}

.card-section {
  margin-bottom: 30px;
  padding: 20px;
  border-radius: 16px;
  transition: all 0.3s ease;
}

.card-section:hover {
  background: #f9fafb;
  transform: translateY(-3px);
}

.card-section-highlight {
  margin-top: 30px;
  padding: 24px;
  background: linear-gradient(135deg, #eef2ff, #e0e7ff);
  border-radius: 20px;
  text-align: center;
}

.section-icon {
  font-size: 36px;
  margin-bottom: 12px;
}

.card-section h2 {
  font-size: 20px;
  color: #1a0a2e;
  margin-bottom: 12px;
}

.card-section h3 {
  font-size: 18px;
  color: #1a0a2e;
  margin-bottom: 12px;
}

.card-section p {
  font-size: 15px;
  line-height: 1.6;
  color: #4b5563;
}

.card-section-highlight p {
  color: #374151;
}

.card-section-highlight a {
  color: #667eea;
  text-decoration: none;
}

.card-section-highlight a:hover {
  text-decoration: underline;
}

@media (max-width: 768px) {
  .hero-title { font-size: 28px; }
  .content-card { padding: 20px; }
}
</style>