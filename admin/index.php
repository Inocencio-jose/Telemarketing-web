<?php
include_once(__DIR__ . "/../config/db.php");

$errors = [];
$messages = [];

// Verifica conexão MySQLi
if (!isset($strcon) || !($strcon instanceof mysqli)) {
    $errors[] = 'Conexão mysqli não encontrada. Verifique config/db.php';
}

// Mensagens de retorno via GET
if (isset($_GET['deleted'])) {
    $messages[] = 'Roteiro excluído com sucesso.';
}
if (isset($_GET['updated'])) {
    $messages[] = 'Roteiro atualizado com sucesso.';
}
if (isset($_GET['created'])) {
    $messages[] = 'Roteiro criado com sucesso.';
}

// Consulta de roteiros
$roteiros = [];
if (empty($errors)) {
    $sql = "SELECT id, titulo, conteudo, criado_em FROM tb_roteiro ORDER BY criado_em DESC";
    if ($result = $strcon->query($sql)) {
        while ($row = $result->fetch_assoc()) {
            $roteiros[] = $row;
        }
        $result->free();
    } else {
        $errors[] = 'Erro ao buscar roteiros: ' . $strcon->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Roteiros — Listagem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Roteiros cadastrados</h3>
        <a href="salvar.php" class="btn btn-success">Novo Roteiro</a>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($messages)): ?>
        <div class="alert alert-success">
            <ul class="mb-0">
                <?php foreach ($messages as $m): ?>
                    <li><?= htmlspecialchars($m, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Título</th>
                    <th>Conteúdo</th>
                    <th>Criado em</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($roteiros)): ?>
                <tr><td colspan="4" class="text-center text-muted">Nenhum roteiro cadastrado.</td></tr>
            <?php else: ?>
                <?php foreach ($roteiros as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= nl2br(htmlspecialchars($r['conteudo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?></td>
                        <td><?= htmlspecialchars($r['criado_em'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="text-end">
                            <a href="editar.php?id=<?= (int)$r['id'] ?>" class="btn btn-sm btn-primary">
                                Editar
                            </a>
                            <form method="post" action="excluir.php" class="d-inline-block"
                                  onsubmit="return confirm('Confirma exclusão do roteiro &quot;<?= htmlspecialchars($r['titulo'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>&quot;?');">
                                <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
