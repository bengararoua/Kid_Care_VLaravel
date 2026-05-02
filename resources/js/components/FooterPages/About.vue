<template>
  <Layout>
    <div class="page-about">
      <div class="hero-section-about">
        <div class="hero-content">
          <div class="hero-icon">🧠</div>
          <h1 class="hero-title">À propos de KidCare Insight</h1>
          <p class="hero-subtitle">Une plateforme tunisienne dédiée au bien-être des enfants</p>
        </div>
      </div>

      <div class="container">
        <div class="content-card">
          <div class="card-section">
            <div class="section-icon">🎯</div>
            <h2>Notre Mission</h2>
            <p>KidCare Insight est une application tunisienne innovante qui aide les parents, enseignants et psychologues à mieux comprendre et accompagner le développement comportemental des enfants. Notre mission est de fournir des outils simples et efficaces pour détecter précocement les difficultés (baisse d'attention, troubles de l'humeur, fatigue chronique) afin de permettre une intervention rapide et adaptée.</p>
          </div>

          <div class="card-section">
            <div class="section-icon">💡</div>
            <h2>Notre Vision</h2>
            <p>Nous croyons qu'une détection précoce des difficultés comportementales permet une intervention plus efficace. Notre plateforme facilite la collaboration entre parents, enseignants et psychologues pour offrir un accompagnement personnalisé à chaque enfant.</p>
          </div>

          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-number">500+</div>
              <div class="stat-label">Enfants suivis</div>
            </div>
            <div class="stat-card">
              <div class="stat-number">50+</div>
              <div class="stat-label">Psychologues partenaires</div>
            </div>
            <div class="stat-card">
              <div class="stat-number">1000+</div>
              <div class="stat-label">Logs comportementaux</div>
            </div>
          </div>

          <div class="card-section">
            <div class="section-icon">🌍</div>
            <h2>Version & Informations</h2>
            <p><strong>Version :</strong> 1.0.0</p>
            <p><strong>© 2026 KidCare Insight Tunisie</strong> - Tous droits réservés</p>
            <p><strong>Développé avec ❤️ en Tunisie</strong></p>
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
.page-about {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fb 0%, #f0e6f5 100%);
}

.hero-section-about {
  background: linear-gradient(135deg, #1a0a2e 0%, #2d1b4e 100%);
  padding: 60px 0;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.hero-section-about::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
  animation: rotate 20s linear infinite;
}

@keyframes rotate {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.hero-content {
  position: relative;
  z-index: 1;
  animation: fadeInUp 0.6s ease;
}

.hero-icon {
  font-size: 64px;
  margin-bottom: 20px;
  animation: bounce 2s ease infinite;
}

@keyframes bounce {
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

.card-section {
  margin-bottom: 40px;
  padding: 20px;
  border-radius: 16px;
  transition: all 0.3s ease;
}

.card-section:hover {
  background: #f8f9fc;
  transform: translateX(10px);
}

.section-icon {
  font-size: 48px;
  margin-bottom: 16px;
}

.card-section h2 {
  font-size: 24px;
  color: #1a0a2e;
  margin-bottom: 16px;
}

.card-section p {
  font-size: 16px;
  line-height: 1.6;
  color: #4b5563;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 30px;
  margin: 40px 0;
}

.stat-card {
  text-align: center;
  padding: 30px;
  background: linear-gradient(135deg, #f5f3ff, #ede9fe);
  border-radius: 20px;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.stat-number {
  font-size: 36px;
  font-weight: 800;
  color: #1a0a2e;
  margin-bottom: 8px;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
}

@media (max-width: 768px) {
  .hero-title { font-size: 28px; }
  .stats-grid { grid-template-columns: 1fr; gap: 20px; }
  .content-card { padding: 20px; }
}
</style>