<?php
// filepath: c:\xampp\htdocs\login.php

// Iniciar a sessão
session_start();

// Conectar ao banco de dados
require 'config.php';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        // Consultar o usuário no banco de dados
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login bem-sucedido
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];

            // Redirecionar com base no papel do usuário
            if ($user['role'] === 'admin') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['user_name'] = $user['name'];
                header('Location: admin/index.php');
            } else {
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_name'] = $user['name'];
                header('Location: index.php');
            }
            exit;
        } else {
            $error = "E-mail ou senha inválidos.";
        }
    } catch (PDOException $e) {
        die("Erro ao verificar login: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login - Manga Reader</title>

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
                        <h3 style="color: black">Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <form method="POST" action="login.php">
                            <div class="form-group">
                                <label for="email" style="color: black">E-mail</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="password" style="color: black">Senha</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
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