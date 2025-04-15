<?php
// filepath: c:\xampp\htdocs\admin\users.php

// Conectar ao banco de dados
require '../config.php';

// Verificar se o usuário está autenticado como administrador
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Adicionar um novo usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $role = trim($_POST['role']);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) 
                               VALUES (:name, :email, :password, :role)");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);
        $success = "Usuário adicionado com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao adicionar usuário: " . $e->getMessage();
    }
}

// Editar um usuário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = !empty($_POST['password']) ? password_hash(trim($_POST['password']), PASSWORD_DEFAULT) : null;

    try {
        if ($password) {
            $stmt = $pdo->prepare("UPDATE users 
                                   SET name = :name, email = :email, password = :password, role = :role 
                                   WHERE id = :id");
            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users 
                                   SET name = :name, email = :email, role = :role 
                                   WHERE id = :id");
            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'email' => $email,
                'role' => $role
            ]);
        }
        $success = "Usuário atualizado com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao atualizar usuário: " . $e->getMessage();
    }
}

// Excluir um usuário
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $success = "Usuário excluído com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao excluir usuário: " . $e->getMessage();
    }
}

// Consultar todos os usuários
try {
    $stmt = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar usuários: " . $e->getMessage());
}

// Consultar dados do usuário para edição
$editUser = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $userId = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $editUser = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Erro ao buscar usuário para edição: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gerenciar Usuários - Painel Admin</title>

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
        <h1 class="mb-4">Gerenciar Usuários</h1>

        <!-- Mensagens de Sucesso ou Erro -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Formulário para Adicionar ou Editar Usuário -->
        <div class="card mb-4">
            <div class="card-header"><?= $editUser ? 'Editar Usuário' : 'Adicionar Novo Usuário' ?></div>
            <div class="card-body">
                <form method="POST" action="users.php">
                    <?php if ($editUser): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($editUser['id']) ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="name">Nome</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= $editUser ? htmlspecialchars($editUser['name']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= $editUser ? htmlspecialchars($editUser['email']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Senha</label>
                        <input type="password" name="password" id="password" class="form-control" <?= $editUser ? '' : 'required' ?>>
                        <?php if ($editUser): ?>
                            <small class="form-text text-muted">Deixe em branco para manter a senha atual.</small>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="role">Papel</label>
                        <select name="role" id="role" class="form-control" required>
                            <option value="user" <?= $editUser && $editUser['role'] === 'user' ? 'selected' : '' ?>>Usuário</option>
                            <option value="admin" <?= $editUser && $editUser['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>
                    <button type="submit" name="<?= $editUser ? 'edit_user' : 'add_user' ?>" class="btn btn-primary"><?= $editUser ? 'Salvar Alterações' : 'Adicionar' ?></button>
                </form>
            </div>
        </div>

        <!-- Lista de Usuários -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Papel</th>
                    <th>Data de Criação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td><?= htmlspecialchars($user['created_at']) ?></td>
                        <td>
                            <a href="users.php?edit=<?= $user['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="users.php?delete=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</a>
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