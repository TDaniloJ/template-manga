<?php
// filepath: c:\xampp\htdocs\admin\genres.php

// Conectar ao banco de dados
require '../config.php';

// Verificar se o usuário está autenticado como administrador
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Adicionar um novo gênero
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_genre'])) {
    $name = trim($_POST['name']);

    try {
        $stmt = $pdo->prepare("INSERT INTO genres (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);
        $success = "Gênero adicionado com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao adicionar gênero: " . $e->getMessage();
    }
}

// Editar um gênero
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_genre'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);

    try {
        $stmt = $pdo->prepare("UPDATE genres SET name = :name WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'name' => $name
        ]);
        $success = "Gênero atualizado com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao atualizar gênero: " . $e->getMessage();
    }
}

// Excluir um gênero
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $genreId = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM genres WHERE id = :id");
        $stmt->execute(['id' => $genreId]);
        $success = "Gênero excluído com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao excluir gênero: " . $e->getMessage();
    }
}

// Consultar todos os gêneros
try {
    $stmt = $pdo->query("SELECT id, name FROM genres ORDER BY name ASC");
    $genres = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar gêneros: " . $e->getMessage());
}

// Consultar dados do gênero para edição
$editGenre = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $genreId = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM genres WHERE id = :id");
        $stmt->execute(['id' => $genreId]);
        $editGenre = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Erro ao buscar gênero para edição: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gerenciar Gêneros - Painel Admin</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">Painel Admin</a>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Sair</a>
            </li>
        </ul>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4">Gerenciar Gêneros</h1>

        <!-- Mensagens de Sucesso ou Erro -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Formulário para Adicionar ou Editar Gênero -->
        <div class="card mb-4">
            <div class="card-header"><?= $editGenre ? 'Editar Gênero' : 'Adicionar Novo Gênero' ?></div>
            <div class="card-body">
                <form method="POST" action="genres.php">
                    <?php if ($editGenre): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($editGenre['id']) ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="name">Nome do Gênero</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= $editGenre ? htmlspecialchars($editGenre['name']) : '' ?>" required>
                    </div>
                    <button type="submit" name="<?= $editGenre ? 'edit_genre' : 'add_genre' ?>" class="btn btn-primary"><?= $editGenre ? 'Salvar Alterações' : 'Adicionar' ?></button>
                </form>
            </div>
        </div>

        <!-- Lista de Gêneros -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($genres as $genre): ?>
                    <tr>
                        <td><?= htmlspecialchars($genre['id']) ?></td>
                        <td><?= htmlspecialchars($genre['name']) ?></td>
                        <td>
                            <a href="genres.php?edit=<?= $genre['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="genres.php?delete=<?= $genre['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este gênero?')">Excluir</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- JS Files -->
    <script src="../js/jquery-3.4.1.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</body>

</html>