<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';
$nav_items = [
  'inicio'       => 'Inicio',
  'sobremi'      => 'Sobre mí',
  'monitor'      => 'Monitor',
  'videos'       => 'Vídeos',
  'pdf'          => 'PDF',
  'curiosidades' => 'Curiosidades',
];
?>
<nav>
  <a href="index.php" class="nav-logo">BHF</a>
  <div class="nav-links">
    <?php foreach ($nav_items as $key => $label): ?>
      <a href="index.php?page=<?= $key ?>"
         class="<?= $page === $key ? 'active' : '' ?>">
        <?= $label ?>
      </a>
    <?php endforeach; ?>
  </div>
  <button class="nav-hamburger" onclick="toggleMobileMenu()" aria-label="Menú">☰</button>
</nav>
<div class="mobile-menu" id="mobile-menu">
  <?php foreach ($nav_items as $key => $label): ?>
    <a href="index.php?page=<?= $key ?>"
       class="<?= $page === $key ? 'active' : '' ?>">
      <?= $label ?>
    </a>
  <?php endforeach; ?>
</div>
