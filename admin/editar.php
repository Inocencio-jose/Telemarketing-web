<?php 
include_once(__DIR__ . "/../config/db.php");

$errors = [];
$id = intval($_GET['id'] ?? 0);
$titulo = '';
$conteudo = '';
$criado_em = '';

// --- Salvamento (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['conteudo'] ?? '');

    if ($id <= 0) $errors[] = 'ID inválido.';
    if ($titulo === '') $errors[] = 'Título obrigatório.';
    if ($conteudo === '') $errors[] = 'Conteúdo obrigatório.';

    if (empty($errors)) {
        if (!isset($strcon) || !($strcon instanceof mysqli)) {
            $errors[] = 'Conexão mysqli não encontrada. Verifique config/db.php';
        } else {
            $sql = "UPDATE tb_roteiro SET titulo = ?, conteudo = ?, atualizado_em = NOW() WHERE id = ?";
            $stmt = $strcon->prepare($sql);
            if (!$stmt) {
                $errors[] = 'Erro na preparação da consulta: ' . $strcon->error;
            } else {
                $stmt->bind_param('ssi', $titulo, $conteudo, $id);
                if ($stmt->execute()) {
                    $stmt->close();
                    header('Location: index.php?updated=1');
                    exit;
                } else {
                    $errors[] = 'Erro ao atualizar: ' . $stmt->error;
                    $stmt->close();
                }
            }
        }
    }
}

// --- Carregar dados existentes ---
if ($id > 0 && ($_SERVER['REQUEST_METHOD'] !== 'POST' || !empty($errors))) {
    if (!isset($strcon) || !($strcon instanceof mysqli)) {
        $errors[] = 'Conexão mysqli não encontrada. Verifique config/db.php';
    } else {
        $sql = "SELECT titulo, conteudo, criado_em FROM tb_roteiro WHERE id = ?";
        $stmt = $strcon->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $stmt->bind_result($db_titulo, $db_conteudo, $db_criado);
                if ($stmt->fetch()) {
                    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($titulo)) $titulo = $db_titulo;
                    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($conteudo)) $conteudo = $db_conteudo;
                    $criado_em = $db_criado;
                } else {
                    $errors[] = 'Roteiro não encontrado.';
                }
            } else {
                $errors[] = 'Erro ao buscar roteiro: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $errors[] = 'Erro na preparação da consulta: ' . $strcon->error;
        }
    }
} elseif ($id <= 0) {
    $errors[] = 'ID do roteiro não informado.';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Roteiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Editar Roteiro</h5>
        </div>
        <div class="card-body">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                            <li><?= htmlspecialchars($err, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="needs-validation" novalidate method="post" action="editar.php">
                <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

                <div class="mb-3">
                    <label for="titulo" class="form-label">Título</label>
                    <input type="text" id="titulo" name="titulo" class="form-control" required
                           value="<?= htmlspecialchars($titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <div class="invalid-feedback">Informe o título do roteiro.</div>
                </div>

                <div class="mb-3">
                    <label for="conteudo" class="form-label">Conteúdo</label>
                    <textarea id="conteudo" name="conteudo" rows="10" class="form-control" required><?= htmlspecialchars($conteudo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                    <div class="invalid-feedback">O conteúdo não pode ficar vazio.</div>
                </div>

                <?php if (!empty($criado_em)): ?>
                    <p class="text-muted mb-3">
                        <small><strong>Criado em:</strong> <?= htmlspecialchars($criado_em, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small>
                    </p>
                <?php endif; ?>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    <a href="index.php" class="btn btn-secondary">Voltar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()
</script>
</body>
</html>
