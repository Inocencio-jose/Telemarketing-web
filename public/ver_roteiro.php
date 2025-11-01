<?php
include_once(__DIR__ . "/../config/db.php");

$id = intval($_GET['id'] ?? 0);
$sql = "SELECT * FROM tb_roteiro WHERE id = $id";
$result = $strcon->query($sql);

if (!$result || $result->num_rows == 0) {
  die("Roteiro não encontrado.");
}
$r = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($r['titulo']) ?></title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  
  <style>
    :root {
      --bg-primary: #0d1117;
      --bg-secondary: #161b22;
      --border-color: #30363d;
      --text-primary: #f0f6fc;
      --accent-green: #238636;
      --accent-green-hover: #2ea043;
    }

    body {
      background: var(--bg-primary);
      color: var(--text-primary);
      min-height: 100vh;
    }

    .header-custom {
      background: var(--bg-secondary);
      border-bottom: 2px solid var(--border-color);
      padding: 1.5rem 0;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }

    .video-container {
      position: relative;
      border-radius: 12px;
      overflow: hidden;
      background: #000;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
      height: 70vh;
    }

    .video-container video {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .texto-container {
      background: var(--bg-secondary);
      border: 1px solid var(--border-color);
      border-radius: 12px;
      height: 70vh;
      overflow: hidden;
      position: relative;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    }

    .texto-scroll {
      position: absolute;
      width: 100%;
      padding: 2rem;
      white-space: pre-wrap;
      font-size: 1.25rem;
      line-height: 2rem;
      animation: scrollText linear;
      animation-duration: var(--scroll-time, 60s);
    }

    @keyframes scrollText {
      from { 
        transform: translateY(100%);
      }
      to { 
        transform: translateY(-100%);
      }
    }

    .btn-custom {
      background: var(--accent-green);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-custom:hover {
      background: var(--accent-green-hover);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(35, 134, 54, 0.4);
    }

    .controls-section {
      padding: 2rem 0;
    }

    /* Responsividade aprimorada */
    @media (max-width: 991px) {
      .video-container,
      .texto-container {
        height: 50vh;
      }
      
      .texto-scroll {
        font-size: 1.1rem;
        line-height: 1.8rem;
        padding: 1.5rem;
      }
    }

    @media (max-width: 576px) {
      .video-container,
      .texto-container {
        height: 40vh;
      }
      
      .texto-scroll {
        font-size: 1rem;
        line-height: 1.6rem;
        padding: 1rem;
      }

      .header-custom h1 {
        font-size: 1.5rem;
      }
    }

    /* Fade in na entrada */
    .fade-in {
      animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
  </style>
</head>
<body>

<!-- Header -->
<header class="header-custom">
  <div class="container">
    <h1 class="text-center mb-0">
      <i class="bi bi-play-circle-fill me-2"></i>
      <?= htmlspecialchars($r['titulo']) ?>
    </h1>
  </div>
</header>

<!-- Conteúdo Principal -->
<main class="container my-4 fade-in">
  <div class="row g-4">
    <!-- Vídeo do Apresentador -->
    <div class="col-lg-6">
      <div class="video-container">
        <video id="videoApresentador" muted loop playsinline preload="auto">
          <source src="videos/apresentaor.mp4" type="video/mp4">
          Seu navegador não suporta a reprodução de vídeo.
        </video>
      </div>
    </div>

    <!-- Texto do Roteiro -->
    <div class="col-lg-6">
      <div class="texto-container">
        <div class="texto-scroll" id="textoScroll">
          <?= nl2br(htmlspecialchars($r['conteudo'])) ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Controles -->
  <div class="controls-section text-center">
    <button class="btn btn-custom btn-lg me-2" onclick="reiniciarApresentacao()">
      <i class="bi bi-arrow-clockwise me-2"></i>Reiniciar
    </button>
    <a href="index.php" class="btn btn-custom btn-lg">
      <i class="bi bi-arrow-left me-2"></i>Voltar
    </a>
  </div>
</main>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
let utterance = null;
let video = null;
let textoScroll = null;
let textoConteudo = '';

// Inicialização quando a página carregar
window.addEventListener('DOMContentLoaded', () => {
  video = document.getElementById('videoApresentador');
  textoScroll = document.getElementById('textoScroll');
  textoConteudo = textoScroll.innerText.trim();

  // Configurar duração da animação baseada no vídeo
  video.addEventListener('loadedmetadata', () => {
    const duracao = video.duration > 0 ? video.duration : 60;
    textoScroll.style.setProperty('--scroll-time', `${duracao}s`);
    
    // Iniciar apresentação imediatamente após carregar metadados
    iniciarApresentacao();
  });

  // Fallback: se os metadados não carregarem rapidamente, inicia após 1 segundo
  setTimeout(() => {
    if (video.paused) {
      iniciarApresentacao();
    }
  }, 1000);
});

function iniciarApresentacao() {
  // Cancelar qualquer leitura anterior
  if (speechSynthesis.speaking) {
    speechSynthesis.cancel();
  }

  // Iniciar vídeo
  video.play().catch(err => {
    console.log('Erro ao reproduzir vídeo:', err);
    // Em alguns navegadores, é necessário interação do usuário
    // Mas tentamos iniciar automaticamente mesmo assim
  });

  // Iniciar animação do texto
  textoScroll.style.animationPlayState = 'running';

  // Iniciar síntese de voz
  utterance = new SpeechSynthesisUtterance(textoConteudo);
  utterance.lang = 'pt-BR';
  utterance.rate = 1.0;
  utterance.pitch = 1.0;
  utterance.volume = 1.0;

  // Aguardar um pouco para garantir que as vozes estão carregadas
  setTimeout(() => {
    speechSynthesis.speak(utterance);
  }, 100);
}

function reiniciarApresentacao() {
  // Parar animação e vídeo
  speechSynthesis.cancel();
  video.pause();
  video.currentTime = 0;

  // Reiniciar animação do texto
  textoScroll.style.animation = 'none';
  void textoScroll.offsetHeight; // Forçar reflow
  textoScroll.style.animation = '';

  // Reiniciar tudo
  setTimeout(() => {
    iniciarApresentacao();
  }, 50);
}

// Limpar ao sair da página
window.addEventListener('beforeunload', () => {
  speechSynthesis.cancel();
  if (video) {
    video.pause();
  }
});
</script>

</body>
</html>