<?php
// filepath: c:\xampp\htdocs\cadastro.php

// Conectar ao banco de dados
require 'config.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Verificar se as senhas coincidem
    if ($password !== $confirmPassword) {
        $error = "As senhas não coincidem.";
    } else {
        try {
            // Verificar se o e-mail já está registrado
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetchColumn() > 0) {
                $error = "O e-mail já está registrado.";
            } else {
                // Inserir o novo usuário no banco de dados
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");
                $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'password' => $hashedPassword
                ]);
                $success = "Cadastro realizado com sucesso! Você pode fazer login.";
            }
        } catch (PDOException $e) {
            die("Erro ao cadastrar: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cadastro - Manga Reader</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Cadastro</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="cadastro.php">
                            <div class="form-group">
                                <label for="name">Nome</label>
                                <input type="text" name="name" id="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Senha</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Senha</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Cadastrar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JS Files -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>

</html>