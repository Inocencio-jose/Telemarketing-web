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
  
  <!-- bootstrap -->
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
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .video-container video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border: none;
    }

    .loading-spinner {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 10;
    }

    .texto-container {
      background: #161b22;
      border: 1px solid #30363d;
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
      transform: translateY(100%);
      transition: transform linear;
    }

    .btn-custom {
      background: #238636;
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .btn-custom:hover {
      background: #2ea043;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(35, 134, 54, 0.4);
    }

    .btn-custom:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .controls-section {
      padding: 2rem 0;
    }

    .status-message {
      background: rgba(35, 134, 54, 0.2);
      border: 1px solid #238636;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
      text-align: center;
    }

    /* mobile */
    @media (max-width: 991px) {
      .video-container, .texto-container {
        height: 50vh;
      }
      .texto-scroll {
        font-size: 1.1rem;
        line-height: 1.8rem;
        padding: 1.5rem;
      }
    }

    @media (max-width: 576px) {
      .video-container, .texto-container {
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

<header class="header-custom">
  <div class="container">
    <h1 class="text-center mb-0">
      <i class="bi bi-play-circle-fill me-2"></i>
      <?= htmlspecialchars($r['titulo']) ?>
    </h1>
  </div>
</header>

<main class="container my-4 fade-in">
  <!-- status -->
  <div id="statusMessage" class="status-message" style="display: none;">
    <div class="spinner-border spinner-border-sm me-2" role="status">
      <span class="visually-hidden">Carregando...</span>
    </div>
    <span id="statusText">Preparando apresentação...</span>
  </div>

  <div class="row g-4">
    <!-- video -->
    <div class="col-lg-6">
      <div class="video-container" id="videoContainer">
        <div class="loading-spinner">
          <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Carregando...</span>
          </div>
        </div>
        <video id="videoApresentador" style="display: none;" preload="auto"></video>
      </div>
    </div>

    <!-- texto -->
    <div class="col-lg-6">
      <div class="texto-container">
        <div class="texto-scroll" id="textoScroll">
          <?= nl2br(htmlspecialchars($r['conteudo'])) ?>
        </div>
      </div>
    </div>
  </div>

  <!-- botoes -->
  <div class="controls-section text-center">
    <button class="btn btn-custom btn-lg me-2" id="btnReiniciar" onclick="reiniciarApresentacao()" disabled>
      <i class="bi bi-arrow-clockwise me-2"></i>Reiniciar
    </button>
    <button class="btn btn-custom btn-lg me-2" id="btnPausar" onclick="pausarApresentacao()" disabled>
      <i class="bi bi-pause-fill me-2"></i>Pausar
    </button>
    <a href="index.php" class="btn btn-custom btn-lg">
      <i class="bi bi-arrow-left me-2"></i>Voltar
    </a>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// configs da api
var CONFIG = {
  DID_API_KEY: 'SEU_DID_API_KEY_AQUI',
  DID_API_URL: 'https://api.d-id.com/talks',
  ELEVENLABS_API_KEY: 'SEU_ELEVENLABS_API_KEY_AQUI',
  ELEVENLABS_API_URL: 'https://api.elevenlabs.io/v1/text-to-speech',
  ELEVENLABS_VOICE_ID: '21m00Tcm4TlvDq8ikWAM',
  AZURE_API_KEY: 'SEU_AZURE_API_KEY_AQUI',
  AZURE_REGION: 'brazilsouth',
  AZURE_VOICE: 'pt-BR-FranciscaNeural'
};

var video = null;
var textoScroll = null;
var textoConteudo = '';
var audioElement = null;
var apresentacaoIniciada = false;
var apresentacaoPausada = false;
var animationStartTime = 0;
var scrollDuration = 0;
var pausedAt = 0;

// quando carregar a pagina
window.addEventListener('DOMContentLoaded', function() {
  video = document.getElementById('videoApresentador');
  textoScroll = document.getElementById('textoScroll');
  textoConteudo = textoScroll.innerText.trim();

  inicializarApresentacao();
});

// inicializar tudo
function inicializarApresentacao() {
  mostrarStatus('Gerando narração de áudio...', true);
  
  gerarAudioNarracao(textoConteudo).then(function(audioUrl) {
    mostrarStatus('Criando vídeo do apresentador...', true);
    
    return gerarVideoApresentador(audioUrl);
  }).then(function(videoUrl) {
    mostrarStatus('Sincronizando mídia...', true);
    
    return Promise.all([
      carregarVideo(videoUrl),
      carregarAudio(videoUrl)
    ]);
  }).then(function() {
    esconderStatus();
    habilitarControles();
    iniciarApresentacao();
  }).catch(function(error) {
    console.error('Erro:', error);
    mostrarStatus('Erro ao carregar. Usando modo fallback...', false);
    usarModoFallback();
  });
}

// gerar audio com elevenlabs
function gerarAudioNarracao(texto) {
  return fetch(CONFIG.ELEVENLABS_API_URL + '/' + CONFIG.ELEVENLABS_VOICE_ID, {
    method: 'POST',
    headers: {
      'Accept': 'audio/mpeg',
      'Content-Type': 'application/json',
      'xi-api-key': CONFIG.ELEVENLABS_API_KEY
    },
    body: JSON.stringify({
      text: texto,
      model_id: 'eleven_multilingual_v2',
      voice_settings: {
        stability: 0.5,
        similarity_boost: 0.75
      }
    })
  }).then(function(response) {
    if (!response.ok) {
      throw new Error('ElevenLabs API error');
    }
    return response.blob();
  }).then(function(audioBlob) {
    return URL.createObjectURL(audioBlob);
  }).catch(function(error) {
    console.warn('ElevenLabs falhou, tentando Azure...', error);
    return gerarAudioAzure(texto);
  });
}

// fallback azure
function gerarAudioAzure(texto) {
  var ssml = '<speak version="1.0" xml:lang="pt-BR"><voice name="' + CONFIG.AZURE_VOICE + '"><prosody rate="1.0" pitch="0%">' + texto + '</prosody></voice></speak>';
  
  return fetch('https://' + CONFIG.AZURE_REGION + '.tts.speech.microsoft.com/cognitiveservices/v1', {
    method: 'POST',
    headers: {
      'Ocp-Apim-Subscription-Key': CONFIG.AZURE_API_KEY,
      'Content-Type': 'application/ssml+xml',
      'X-Microsoft-OutputFormat': 'audio-24khz-48kbitrate-mono-mp3'
    },
    body: ssml
  }).then(function(response) {
    if (!response.ok) {
      throw new Error('Azure TTS error');
    }
    return response.blob();
  }).then(function(audioBlob) {
    return URL.createObjectURL(audioBlob);
  });
}

// criar video no d-id
function gerarVideoApresentador(audioUrl) {
  return fetch(CONFIG.DID_API_URL, {
    method: 'POST',
    headers: {
      'Authorization': 'Basic ' + CONFIG.DID_API_KEY,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      source_url: 'https://create-images-results.d-id.com/default-presenter-image.jpg',
      script: {
        type: 'audio',
        audio_url: audioUrl
      },
      config: {
        fluent: true,
        pad_audio: 0
      }
    })
  }).then(function(response) {
    if (!response.ok) {
      throw new Error('D-ID API error');
    }
    return response.json();
  }).then(function(data) {
    return aguardarVideoProcessado(data.id);
  });
}

// esperar o video ficar pronto
function aguardarVideoProcessado(talkId) {
  var maxTentativas = 60;
  var tentativas = 0;

  function verificar() {
    return fetch(CONFIG.DID_API_URL + '/' + talkId, {
      headers: {
        'Authorization': 'Basic ' + CONFIG.DID_API_KEY
      }
    }).then(function(response) {
      return response.json();
    }).then(function(data) {
      if (data.status === 'done') {
        return data.result_url;
      } else if (data.status === 'error') {
        throw new Error('Erro ao processar vídeo');
      }

      tentativas++;
      if (tentativas >= maxTentativas) {
        throw new Error('Timeout');
      }

      return new Promise(function(resolve) {
        setTimeout(function() {
          resolve(verificar());
        }, 2000);
      });
    });
  }

  return verificar();
}

// carregar o video
function carregarVideo(url) {
  return new Promise(function(resolve, reject) {
    video.src = url;
    video.onloadedmetadata = function() {
      document.querySelector('.loading-spinner').style.display = 'none';
      video.style.display = 'block';
      resolve();
    };
    video.onerror = reject;
  });
}

// carregar audio
function carregarAudio(url) {
  return new Promise(function(resolve, reject) {
    audioElement = new Audio(url);
    audioElement.onloadedmetadata = function() {
      scrollDuration = audioElement.duration * 1000;
      resolve();
    };
    audioElement.onerror = reject;
  });
}

// iniciar a apresentacao
function iniciarApresentacao() {
  if (apresentacaoIniciada && !apresentacaoPausada) {
    return;
  }

  apresentacaoIniciada = true;
  apresentacaoPausada = false;

  video.play();
  audioElement.play();

  animationStartTime = Date.now();
  iniciarRolagemTexto();

  document.getElementById('btnPausar').innerHTML = '<i class="bi bi-pause-fill me-2"></i>Pausar';
}

// rolar o texto
function iniciarRolagemTexto() {
  var containerHeight = document.querySelector('.texto-container').offsetHeight;
  var contentHeight = textoScroll.offsetHeight;

  textoScroll.style.transition = 'transform ' + scrollDuration + 'ms linear';
  textoScroll.style.transform = 'translateY(-' + contentHeight + 'px)';
}

// pausar/despausar
function pausarApresentacao() {
  if (!apresentacaoIniciada) {
    return;
  }

  if (apresentacaoPausada) {
    video.play();
    audioElement.play();
    apresentacaoPausada = false;
    document.getElementById('btnPausar').innerHTML = '<i class="bi bi-pause-fill me-2"></i>Pausar';
  } else {
    video.pause();
    audioElement.pause();
    apresentacaoPausada = true;
    document.getElementById('btnPausar').innerHTML = '<i class="bi bi-play-fill me-2"></i>Continuar';
  }
}

// reiniciar
function reiniciarApresentacao() {
  video.pause();
  video.currentTime = 0;
  audioElement.pause();
  audioElement.currentTime = 0;

  textoScroll.style.transition = 'none';
  textoScroll.style.transform = 'translateY(100%)';
  
  setTimeout(function() {
    textoScroll.style.transition = '';
    apresentacaoIniciada = false;
    apresentacaoPausada = false;
    iniciarApresentacao();
  }, 50);
}

// modo fallback se der erro
function usarModoFallback() {
  video.src = 'videos/apresentaor.mp4';
  document.querySelector('.loading-spinner').style.display = 'none';
  video.style.display = 'block';
  
  video.onloadedmetadata = function() {
    habilitarControles();
    iniciarApresentacaoFallback();
  };
}

function iniciarApresentacaoFallback() {
  video.play();
  
  var utterance = new SpeechSynthesisUtterance(textoConteudo);
  utterance.lang = 'pt-BR';
  utterance.rate = 1.0;
  
  speechSynthesis.speak(utterance);
  
  scrollDuration = (textoConteudo.length / 15) * 1000;
  iniciarRolagemTexto();
}

// mostrar mensagem de status
function mostrarStatus(mensagem, loading) {
  var statusDiv = document.getElementById('statusMessage');
  var statusText = document.getElementById('statusText');
  var spinner = statusDiv.querySelector('.spinner-border');
  
  statusText.textContent = mensagem;
  
  if (loading) {
    spinner.style.display = 'inline-block';
  } else {
    spinner.style.display = 'none';
  }
  
  statusDiv.style.display = 'block';
}

function esconderStatus() {
  document.getElementById('statusMessage').style.display = 'none';
}

// habilitar botoes
function habilitarControles() {
  document.getElementById('btnReiniciar').disabled = false;
  document.getElementById('btnPausar').disabled = false;
}

// limpar quando sair
window.addEventListener('beforeunload', function() {
  if (audioElement) {
    audioElement.pause();
    audioElement = null;
  }
  if (video) {
    video.pause();
  }
  speechSynthesis.cancel();
});
</script>

</body>
</html> 