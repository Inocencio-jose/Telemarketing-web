<?php
include_once(__DIR__ . "/../config/db.php");

$sql = "SELECT id, titulo, SUBSTRING(conteudo, 1, 300) AS resumo, criado_em 
        FROM tb_roteiro ORDER BY criado_em DESC";
$result = $strcon->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Reportagens - Teleprompter</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <style>
    body {
      background: #0d1117;
      color: #f0f6fc;
      min-height: 100vh;
    }
    
    .navbar-custom {
      background: #161b22;
      border-bottom: 2px solid #30363d;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    
    .card-roteiro {
      background: #161b22;
      border: 1px solid #30363d;
      transition: all 0.3s ease;
      height: 100%;
    }
    
    .card-roteiro:hover {
      transform: translateY(-8px);
      border-color: #238636;
      box-shadow: 0 8px 24px rgba(35, 134, 54, 0.3);
    }
    
    .btn-ver {
      background: #238636;
      border: none;
      color: white;
      transition: all 0.3s ease;
    }
    
    .btn-ver:hover {
      background: #2ea043;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(35, 134, 54, 0.4);
    }
    
    .badge-data {
      background: rgba(35, 134, 54, 0.2);
      color: #238636;
      border: 1px solid #238636;
    }
    
    .resumo-texto {
      color: #8b949e;
      font-size: 0.95rem;
      line-height: 1.6;
    }
    
    .hero-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    }
    
    .empty-state {
      background: #161b22;
      border: 2px dashed #30363d;
      border-radius: 12px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-custom navbar-dark mb-4">
  <div class="container">
    <span class="navbar-brand mb-0 h1">
      <i class="bi bi-camera-video-fill me-2"></i>
      Teleprompter/Telemarketing web
    </span>
  </div>
</nav>

<div class="container py-4">
  
  <div class="hero-section p-5 mb-5 text-white text-center">
    <h1 class="display-4 fw-bold mb-3">
      <i class="bi bi-newspaper me-3"></i>
      Reportagens Disponíveis
    </h1>
  </div>

  <?php if ($result->num_rows > 0): ?>
    <div class="row g-4">
      <?php while ($r = $result->fetch_assoc()): ?>
        <div class="col-sm-6 col-lg-4">
          <div class="card card-roteiro shadow-sm">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start mb-3">
                <h5 class="card-title mb-0 flex-grow-1">
                  <i class="bi bi-file-text text-success me-2"></i>
                  <?= htmlspecialchars($r['titulo']) ?>
                </h5>
              </div>
              
              <div class="mb-3">
                <span class="badge badge-data">
                  <i class="bi bi-calendar3 me-1"></i>
                  <?= htmlspecialchars(date("d/m/Y", strtotime($r['criado_em']))) ?>
                </span>
                <span class="badge badge-data ms-2">
                  <i class="bi bi-clock me-1"></i>
                  <?= htmlspecialchars(date("H:i", strtotime($r['criado_em']))) ?>
                </span>
              </div>
              
              <p class="resumo-texto flex-grow-1 mb-3">
                <?= nl2br(htmlspecialchars($r['resumo'])) ?>...
              </p>
              
              <div class="mt-auto d-grid">
                <a href="ver_roteiro.php?id=<?= $r['id'] ?>" class="btn btn-ver">
                  <i class="bi bi-play-circle me-2"></i>
                  Iniciar Apresentação
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="empty-state p-5 text-center">
      <i class="bi bi-inbox" style="font-size: 4rem; color: #30363d;"></i>
      <h3 class="mt-4 mb-3">Nenhuma reportagem encontrada</h3>
      <p class="text-muted mb-4">Comece criando seu primeiro roteiro</p>
      <a href="criar_roteiro.php" class="btn btn-ver btn-lg">
        <i class="bi bi-plus-circle me-2"></i>
        Criar Primeiro Roteiro
      </a>
    </div>
  <?php endif; ?>

</div>

<footer class="mt-5 py-4 text-center text-muted border-top border-secondary">
  <div class="container">
    <p class="mb-0">
      <i class="bi bi-code-slash me-2"></i>
      Orion Technologies &copy; <?= date('Y') ?>
    </p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>