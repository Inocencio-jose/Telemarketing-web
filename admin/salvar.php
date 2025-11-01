<?php
include_once(__DIR__ . "/../config/db.php");

$errors = [];
$titulo = '';
$roteiro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $roteiro = trim($_POST['roteiro'] ?? '');

    if ($titulo === '') {
        $errors[] = 'Título obrigatório.';
    }
    if ($roteiro === '') {
        $errors[] = 'Roteiro obrigatório.';
    }

    if (empty($errors)) {
        // garante que $conn exista e seja mysqli
        if (!isset($strcon) || !($strcon instanceof mysqli)) {
            $errors[] = 'Conexão mysqli não encontrada. Verifique config/db.php';
        } else {
            // prepara e executa statement mysqli
            $sql = "INSERT INTO tb_roteiro (titulo, conteudo, criado_em) VALUES (?, ?, NOW())";
            $stmt = $strcon->prepare($sql);
            if (!$stmt) {
                $errors[] = 'Erro na preparação da consulta: ' . $strcon->error;
            } else {
                $stmt->bind_param('ss', $titulo, $roteiro);
                if (!$stmt->execute()) {
                    $errors[] = 'Erro ao executar a consulta: ' . $stmt->error;
                } else {
                    $stmt->close();
                    header('Location: index.php?success=1');
                    exit;
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Roteiros</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Cadastro de Roteiro</h5>
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

            <form class="needs-validation" novalidate method="post" action="salvar.php" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="titulo" name="titulo" required
                               value="<?= htmlspecialchars($titulo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <div class="invalid-feedback">Informe o título do roteiro.</div>
                    </div>

                    <div class="col-12">
                        <label for="roteiro" class="form-label">Roteiro (texto)</label>
                        <textarea class="form-control" id="roteiro" name="roteiro" rows="8" required><?= htmlspecialchars($roteiro, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
                        <div class="invalid-feedback">O roteiro não pode ficar vazio.</div>
                    </div>

                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Salvar Roteiro</button>
                    <a href="index.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS e validação -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Validação padrão do Bootstrap
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