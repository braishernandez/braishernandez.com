<section class="home-hero">
  <div class="home-hero-inner">
    <p class="home-greeting" id="home-greeting">Hola 👋</p>
    <h1 class="home-question">¿Qué necesitas?</h1>
    <p class="home-sub">Elige una sección o explora las herramientas disponibles.</p>

    <div class="home-cards">
      <a href="index.php?page=sobremi" class="home-card">
        <div class="home-card-icon">👤</div>
        <div class="home-card-label">Sobre mí</div>
        <div class="home-card-desc">CV y experiencia profesional</div>
      </a>
      <a href="index.php?page=videos" class="home-card">
        <div class="home-card-icon">📥</div>
        <div class="home-card-label">Descargar vídeos</div>
        <div class="home-card-desc">YouTube, Instagram, TikTok...</div>
      </a>
      <a href="index.php?page=monitor" class="home-card">
        <div class="home-card-icon">🔍</div>
        <div class="home-card-label">Monitor de URLs</div>
        <div class="home-card-desc">Detecta cambios en páginas web</div>
      </a>
      <a href="index.php?page=pdf" class="home-card">
        <div class="home-card-icon">📄</div>
        <div class="home-card-label">Herramientas PDF</div>
        <div class="home-card-desc">Unir y convertir documentos</div>
      </a>
      <a href="index.php?page=curiosidades" class="home-card">
        <div class="home-card-icon">✨</div>
        <div class="home-card-label">Curiosidades</div>
        <div class="home-card-desc">Productos seleccionados</div>
      </a>
    </div>
  </div>
</section>

<style>
.home-hero {
  min-height: calc(100vh - 60px);
  display: flex; align-items: center; justify-content: center;
  padding: 2rem;
}
.home-hero-inner {
  text-align: center; max-width: 700px; width: 100%;
}
.home-greeting {
  font-size: 1rem; color: var(--muted); margin-bottom: 0.5rem;
  opacity: 0; animation: fadeUp 0.5s ease forwards;
}
.home-question {
  font-family: 'DM Serif Display', serif;
  font-size: clamp(2.4rem, 6vw, 3.8rem);
  line-height: 1.1; color: var(--text);
  margin-bottom: 0.75rem;
  opacity: 0; animation: fadeUp 0.5s ease 0.1s forwards;
}
.home-sub {
  color: var(--muted); font-size: 0.95rem; margin-bottom: 2.5rem;
  opacity: 0; animation: fadeUp 0.5s ease 0.2s forwards;
}
.home-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
  opacity: 0; animation: fadeUp 0.5s ease 0.3s forwards;
}
.home-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.4rem 1rem;
  text-decoration: none; color: var(--text);
  transition: all 0.2s ease;
  display: flex; flex-direction: column; align-items: center; gap: 0.4rem;
  box-shadow: var(--shadow);
}
.home-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  border-color: var(--accent);
}
.home-card-icon  { font-size: 1.8rem; }
.home-card-label { font-weight: 600; font-size: 0.9rem; }
.home-card-desc  { font-size: 0.75rem; color: var(--muted); text-align: center; }

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

@media (max-width: 480px) {
  .home-cards { grid-template-columns: repeat(2, 1fr); }
}
</style>

<script>
// Saludo según hora del día
const h = new Date().getHours();
const greet = h < 12 ? 'Buenos días 👋' : h < 20 ? 'Buenas tardes 👋' : 'Buenas noches 👋';
document.getElementById('home-greeting').textContent = greet;
</script>
