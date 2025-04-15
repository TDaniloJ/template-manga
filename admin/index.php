<?php
// filepath: c:\xampp\htdocs\admin\index.php

// Conectar ao banco de dados
require '../config.php';

// Verificar se o usuário está autenticado como administrador
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Consultar estatísticas básicas
try {
    $totalMangas = $pdo->query("SELECT COUNT(*) FROM manga")->fetchColumn();
    $totalCapitulos = $pdo->query("SELECT COUNT(*) FROM chapter")->fetchColumn();
    $totalUsuarios = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $totalGeneros = $pdo->query("SELECT COUNT(*) FROM genres")->fetchColumn();
} catch (PDOException $e) {
    die("Erro ao buscar dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Painel Admin - Manga Reader</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.10.2/css/all.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">Painel Admin</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="mangas.php">Gerenciar Mangás</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="chapters.php">Gerenciar Capítulos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">Gerenciar Usuários</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="genres.php">Gerenciar Gêneros</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="logout.php">Sair</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Dashboard -->
    <div class="container mt-5">
        <h1 class="mb-4">Bem-vindo ao Painel Admin</h1>

        <!-- Estatísticas -->
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Total de Mangás</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $totalMangas ?></h5>
                        <a href="mangas.php" class="btn btn-light btn-sm">Ver Mangás</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Total de Capítulos</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $totalCapitulos ?></h5>
                        <a href="chapters.php" class="btn btn-light btn-sm">Ver Capítulos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Total de Usuários</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $totalUsuarios ?></h5>
                        <a href="users.php" class="btn btn-light btn-sm">Ver Usuários</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Total de Gêneros</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $totalGeneros ?></h5>
                        <a href="genres.php" class="btn btn-light btn-sm">Ver Gêneros</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Links Rápidos -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h3>Links Rápidos</h3>
                <div class="list-group">
                    <a href="mangas.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-book"></i> Gerenciar Mangás
                    </a>
                    <a href="chapters.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-alt"></i> Gerenciar Capítulos
                    </a>
                    <a href="users.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users"></i> Gerenciar Usuários
                    </a>
                    <a href="genres.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-tags"></i> Gerenciar Gêneros
                    </a>
                </div>
            </div>
        </div>

        <!-- Gráficos Placeholder -->
        <div class="row mt-5">
            <div class="col-md-6">
                <h3>Gráfico de Mangás por Gênero</h3>
                <div class="card">
                    <div class="card-body">
                        <canvas id="mangasPorGenero"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h3>Gráfico de Usuários por Papel</h3>
                <div class="card">
                    <div class="card-body">
                        <canvas id="usuariosPorPapel"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 bg-dark text-white">
        <span>&copy; 2025 Manga Reader - Painel Admin</span>
    </footer>

    <!-- JS Files -->
    <script src="../js/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Gráfico de Mangás por Gênero (Placeholder)
        const ctx1 = document.getElementById('mangasPorGenero').getContext('2d');
        const mangasPorGenero = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Ação', 'Aventura', 'Comédia', 'Drama'], // Substituir pelos gêneros reais
                datasets: [{
                    label: 'Mangás',
                    data: [12, 19, 3, 5], // Substituir pelos dados reais
                    backgroundColor: ['#007bff', '#28a745', '#ffc107', '#17a2b8']
                }]
            }
        });

        // Gráfico de Usuários por Papel (Placeholder)
        const ctx2 = document.getElementById('usuariosPorPapel').getContext('2d');
        const usuariosPorPapel = new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Admin', 'Usuário'], // Substituir pelos papéis reais
                datasets: [{
                    data: [5, 95], // Substituir pelos dados reais
                    backgroundColor: ['#dc3545', '#007bff']
                }]
            }
        });
    </script>
</body>

</html>