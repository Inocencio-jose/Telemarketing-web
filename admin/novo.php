<?php require '../config/db.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Novo Roteiro</title>
  <style>
    body { font-family: Arial; padding: 20px; background: #f4f4f4; }
    textarea { width: 100%; height: 300px; font-size: 1.1em; }
    input, button { padding: 10px; font-size: 1em; margin-top: 10px; }
  </style>
</head>
<body>
  <h1>Novo Roteiro</h1>
  <form action="salvar.php" method="post">
    <input type="text" name="titulo" placeholder="Título da notícia" required style="width:100%;">
    <br><br>
    <textarea name="conteudo" placeholder="Escreva o roteiro completo aqui..." required></textarea>
    <br>
    <button type="submit">Salvar Roteiro</button>
    <a href="index.php">Voltar</a>
  </form>
</body>
</html>