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
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  
  <style>
    body {
      background: #0d1117;
      color: #f0f6fc;
      min-height: 100vh;
    }

    .header-custom {
      background: #161b22;
      border-bottom: 2px solid #30363d;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }

    .video-box {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
      height: 70vh;
    }

    .texto-box {
      background: #161b22;
      border: 1px solid #30363d;
      border-radius: 12px;
      height: 70vh;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
    }

    .texto-scroll {
      padding: 2rem;
      font-size: 1.25rem;
      line-height: 2rem;
      white-space: pre-wrap;
      transform: translateY(100%);
      transition: transform linear;
    }

    .btn-green {
      background: #238636;
      border: none;
      transition: all 0.3s ease;
    }

    .btn-green:hover {
      background: #2ea043;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(35, 134, 54, 0.4);
    }

    .btn-green:disabled {
      background: #238636;
      opacity: 0.6;
    }

    .status-box {
      background: rgba(35, 134, 54, 0.2);
      border: 1px solid #238636;
      border-radius: 8px;
    }

    .visualizer-bar {
      width: 4px;
      background: rgba(255, 255, 255, 0.8);
      border-radius: 2px;
      transition: height 0.1s ease;
    }

    @media (max-width: 991px) {
      .video-box, .texto-box {
        height: 50vh;
      }
      .texto-scroll {
        font-size: 1.1rem;
        line-height: 1.8rem;
      }
    }

    @media (max-width: 576px) {
      .video-box, .texto-box {
        height: 40vh;
      }
      .texto-scroll {
        font-size: 1rem;
        line-height: 1.6rem;
        padding: 1rem;
      }
    }
  </style>
</head>
<body>

<header class="header-custom py-4">
  <div class="container">
    <h1 class="text-center mb-0">
      <i class="bi bi-play-circle-fill me-2"></i>
      <?= htmlspecialchars($r['titulo']) ?>
    </h1>
  </div>
</header>

<main class="container my-4">
  <div id="statusMessage" class="status-box p-3 mb-3 text-center">
    <i class="bi bi-info-circle me-2"></i>
    <span id="statusText">Clique em "Iniciar" para começar a apresentação</span>
  </div>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="video-box d-flex align-items-center justify-content-center position-relative">
        <canvas id="avatarCanvas" class="w-100 h-100"></canvas>
        <div class="position-absolute bottom-0 start-50 translate-middle-x d-flex gap-1 align-items-end mb-3" 
             style="height: 40px;" id="audioVisualizer"></div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="texto-box overflow-hidden position-relative">
        <div class="texto-scroll position-absolute w-100" id="textoScroll">
          <?= nl2br(htmlspecialchars($r['conteudo'])) ?>
        </div>
      </div>
    </div>
  </div>

  <div class="text-center py-4">
    <button class="btn btn-green btn-lg text-white me-2" id="btnIniciar" onclick="iniciarApresentacao()">
      <i class="bi bi-play-fill me-2"></i>Iniciar
    </button>
    <button class="btn btn-green btn-lg text-white me-2" id="btnPausar" onclick="pausarApresentacao()" disabled>
      <i class="bi bi-pause-fill me-2"></i>Pausar
    </button>
    <button class="btn btn-green btn-lg text-white me-2" id="btnReiniciar" onclick="reiniciarApresentacao()" disabled>
      <i class="bi bi-arrow-clockwise me-2"></i>Reiniciar
    </button>
    <a href="index.php" class="btn btn-green btn-lg text-white">
      <i class="bi bi-arrow-left me-2"></i>Voltar
    </a>
  </div>

  <div class="row mt-3">
    <div class="col-md-6 mx-auto">
      <div class="card bg-dark border-secondary">
        <div class="card-body">
          <label class="form-label">
            <i class="bi bi-mic-fill me-2"></i>Selecione a Voz:
          </label>
          <select id="voiceSelect" class="form-select bg-dark text-light border-secondary">
            <option value="">Carregando vozes...</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
var canvas, ctx;
var textoScroll, textoConteudo;
var utterance = null;
var audioContext = null;
var analyser = null;
var apresentacaoAtiva = false;
var apresentacaoPausada = false;
var scrollDuration = 0;
var scrollStartTime = 0;
var pausedProgress = 0;
var availableVoices = [];

var avatarX, avatarY, avatarRadius;
var mouthOpen = 0;
var blinkTimer = 0;
var eyesOpen = true;

window.addEventListener('DOMContentLoaded', function() {
  canvas = document.getElementById('avatarCanvas');
  ctx = canvas.getContext('2d');
  textoScroll = document.getElementById('textoScroll');
  textoConteudo = textoScroll.innerText.trim();

  resizeCanvas();
  window.addEventListener('resize', resizeCanvas);

  carregarVozes();
  animarAvatar();
  criarVisualizador();
});

function resizeCanvas() {
  var container = canvas.parentElement;
  canvas.width = container.offsetWidth;
  canvas.height = container.offsetHeight;
  
  avatarX = canvas.width / 2;
  avatarY = canvas.height / 2;
  avatarRadius = Math.min(canvas.width, canvas.height) / 4;
}

function carregarVozes() {
  var loadVoices = function() {
    availableVoices = speechSynthesis.getVoices();
    var portugueseVoices = availableVoices.filter(function(voice) {
      return voice.lang.startsWith('pt');
    });
    
    var select = document.getElementById('voiceSelect');
    select.innerHTML = '';
    
    if (portugueseVoices.length === 0) {
      select.innerHTML = '<option value="">Nenhuma voz em português encontrada</option>';
      portugueseVoices = availableVoices;
    }
    
    portugueseVoices.forEach(function(voice, index) {
      var option = document.createElement('option');
      option.value = index;
      option.textContent = voice.name + ' (' + voice.lang + ')';
      if (voice.default) {
        option.selected = true;
      }
      select.appendChild(option);
    });
    
    var brVoice = portugueseVoices.find(function(v) {
      return v.lang === 'pt-BR';
    });
    if (brVoice) {
      var brIndex = portugueseVoices.indexOf(brVoice);
      select.selectedIndex = brIndex;
    }
  };

  loadVoices();
  if (speechSynthesis.onvoiceschanged !== undefined) {
    speechSynthesis.onvoiceschanged = loadVoices;
  }
}

function animarAvatar() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  
  var gradient = ctx.createRadialGradient(avatarX, avatarY, 0, avatarX, avatarY, avatarRadius * 2);
  gradient.addColorStop(0, 'rgba(102, 126, 234, 0.3)');
  gradient.addColorStop(1, 'rgba(118, 75, 162, 0.1)');
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  
  ctx.fillStyle = '#FFD4A3';
  ctx.beginPath();
  ctx.arc(avatarX, avatarY, avatarRadius, 0, Math.PI * 2);
  ctx.fill();
  
  var eyeY = avatarY - avatarRadius * 0.2;
  var eyeSize = eyesOpen ? avatarRadius * 0.15 : avatarRadius * 0.02;
  
  ctx.fillStyle = '#000';
  ctx.beginPath();
  ctx.ellipse(avatarX - avatarRadius * 0.3, eyeY, avatarRadius * 0.1, eyeSize, 0, 0, Math.PI * 2);
  ctx.fill();
  
  ctx.beginPath();
  ctx.ellipse(avatarX + avatarRadius * 0.3, eyeY, avatarRadius * 0.1, eyeSize, 0, 0, Math.PI * 2);
  ctx.fill();
  
  var mouthY = avatarY + avatarRadius * 0.3;
  ctx.strokeStyle = '#000';
  ctx.lineWidth = 3;
  ctx.beginPath();
  
  if (mouthOpen > 0.3) {
    ctx.ellipse(avatarX, mouthY, avatarRadius * 0.3, avatarRadius * 0.2 * mouthOpen, 0, 0, Math.PI * 2);
    ctx.fillStyle = '#8B4513';
    ctx.fill();
  } else {
    ctx.arc(avatarX, mouthY - avatarRadius * 0.1, avatarRadius * 0.3, 0.2, Math.PI - 0.2);
  }
  ctx.stroke();
  
  blinkTimer++;
  if (blinkTimer > 150) {
    eyesOpen = !eyesOpen;
    blinkTimer = 0;
    if (!eyesOpen) {
      setTimeout(function() {
        eyesOpen = true;
      }, 100);
    }
  }
  
  if (apresentacaoAtiva && !apresentacaoPausada) {
    mouthOpen = 0.5 + Math.sin(Date.now() / 100) * 0.5;
  } else {
    mouthOpen *= 0.9;
  }
  
  requestAnimationFrame(animarAvatar);
}

function criarVisualizador() {
  var visualizer = document.getElementById('audioVisualizer');
  for (var i = 0; i < 20; i++) {
    var bar = document.createElement('div');
    bar.className = 'visualizer-bar';
    bar.style.height = '5px';
    visualizer.appendChild(bar);
  }
}

function atualizarVisualizador() {
  if (!analyser || !apresentacaoAtiva || apresentacaoPausada) {
    document.querySelectorAll('.visualizer-bar').forEach(function(bar) {
      bar.style.height = '5px';
    });
    return;
  }
  
  var dataArray = new Uint8Array(analyser.frequencyBinCount);
  analyser.getByteFrequencyData(dataArray);
  
  var bars = document.querySelectorAll('.visualizer-bar');
  var step = Math.floor(dataArray.length / bars.length);
  
  bars.forEach(function(bar, index) {
    var value = dataArray[index * step];
    var height = Math.max(5, (value / 255) * 40);
    bar.style.height = height + 'px';
  });
  
  requestAnimationFrame(atualizarVisualizador);
}

function iniciarApresentacao() {
  if (apresentacaoAtiva) return;
  
  apresentacaoAtiva = true;
  apresentacaoPausada = false;
  
  document.getElementById('btnIniciar').disabled = true;
  document.getElementById('btnPausar').disabled = false;
  document.getElementById('btnReiniciar').disabled = false;
  document.getElementById('statusText').textContent = 'Apresentação em andamento...';
  
  speechSynthesis.cancel();
  
  utterance = new SpeechSynthesisUtterance(textoConteudo);
  
  var voiceSelect = document.getElementById('voiceSelect');
  var selectedIndex = voiceSelect.value;
  if (selectedIndex) {
    var portugueseVoices = availableVoices.filter(function(v) {
      return v.lang.startsWith('pt');
    });
    utterance.voice = portugueseVoices[selectedIndex] || availableVoices[selectedIndex];
  }
  
  utterance.lang = 'pt-BR';
  utterance.rate = 0.95;
  utterance.pitch = 1.0;
  utterance.volume = 1.0;
  
  var palavras = textoConteudo.split(/\s+/).length;
  var palavrasPorMinuto = 150 * utterance.rate;
  scrollDuration = (palavras / palavrasPorMinuto) * 60 * 1000;
  
  utterance.onend = function() {
    apresentacaoAtiva = false;
    document.getElementById('statusText').textContent = 'Apresentação finalizada!';
    document.getElementById('btnIniciar').disabled = false;
    document.getElementById('btnPausar').disabled = true;
  };
  
  utterance.onerror = function(event) {
    console.error('Erro na síntese de voz:', event);
    document.getElementById('statusText').textContent = 'Erro na narração. Tente novamente.';
    apresentacaoAtiva = false;
  };
  
  try {
    if (!audioContext) {
      audioContext = new (window.AudioContext || window.webkitAudioContext)();
      analyser = audioContext.createAnalyser();
      analyser.fftSize = 256;
      
      var destination = audioContext.createMediaStreamDestination();
      analyser.connect(destination);
    }
    atualizarVisualizador();
  } catch (e) {
    console.log('AudioContext não disponível:', e);
  }
  
  speechSynthesis.speak(utterance);
  
  scrollStartTime = Date.now();
  iniciarRolagemTexto();
}

function iniciarRolagemTexto() {
  var containerHeight = document.querySelector('.texto-box').offsetHeight;
  var contentHeight = textoScroll.offsetHeight;
  
  var remainingDuration = pausedProgress > 0 
    ? scrollDuration * (1 - pausedProgress) 
    : scrollDuration;
  
  textoScroll.style.transition = 'transform ' + remainingDuration + 'ms linear';
  textoScroll.style.transform = 'translateY(-' + contentHeight + 'px)';
}

function pausarApresentacao() {
  if (!apresentacaoAtiva) return;
  
  if (apresentacaoPausada) {
    speechSynthesis.resume();
    apresentacaoPausada = false;
    
    var elapsed = Date.now() - scrollStartTime;
    pausedProgress = elapsed / scrollDuration;
    iniciarRolagemTexto();
    
    document.getElementById('btnPausar').innerHTML = '<i class="bi bi-pause-fill me-2"></i>Pausar';
    document.getElementById('statusText').textContent = 'Apresentação em andamento...';
  } else {
    speechSynthesis.pause();
    apresentacaoPausada = true;
    
    var computedStyle = window.getComputedStyle(textoScroll);
    var matrix = new WebKitCSSMatrix(computedStyle.transform);
    textoScroll.style.transition = 'none';
    textoScroll.style.transform = 'translateY(' + matrix.m42 + 'px)';
    
    document.getElementById('btnPausar').innerHTML = '<i class="bi bi-play-fill me-2"></i>Continuar';
    document.getElementById('statusText').textContent = 'Apresentação pausada';
  }
}

function reiniciarApresentacao() {
  speechSynthesis.cancel();
  apresentacaoAtiva = false;
  apresentacaoPausada = false;
  pausedProgress = 0;
  
  textoScroll.style.transition = 'none';
  textoScroll.style.transform = 'translateY(100%)';
  
  document.getElementById('btnIniciar').disabled = false;
  document.getElementById('btnPausar').disabled = true;
  document.getElementById('btnPausar').innerHTML = '<i class="bi bi-pause-fill me-2"></i>Pausar';
  document.getElementById('statusText').textContent = 'Clique em "Iniciar" para começar novamente';
  
  setTimeout(function() {
    iniciarApresentacao();
  }, 100);
}

window.addEventListener('beforeunload', function() {
  speechSynthesis.cancel();
  if (audioContext) {
    audioContext.close();
  }
});
</script>

</body>
</html>