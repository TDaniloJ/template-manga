<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Perfil do Usuário - Manga Reader</title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.10.2/css/all.css">
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light shadow py-2 py-sm-0">
        <a class="navbar-brand" href="index.html">
            <h5>Manga Man</h5>
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Populares</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Novos</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Gêneros</a></li>
                <li class="nav-item"><a class="nav-link active" href="user.html">Meu Perfil</a></li>
            </ul>
        </div>
    </nav>
    
    <!-- Container do Perfil -->
    <div class="container mt-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-4">
                <div class="card p-3">
                    <div class="text-center">
                        <img src="img/avatar.png" class="rounded-circle" width="150">
                        <h4 class="mt-3">Nome do Usuário</h4>
                        <p class="text-muted">usuário@email.com</p>
                        <button class="btn btn-primary btn-block">Editar Perfil</button>
                        <button class="btn btn-danger btn-block">Sair</button>
                    </div>
                </div>
            </div>

            <!-- Informações do Usuário -->
            <div class="col-md-8">
                <div class="card p-4">
                    <h4>Minhas Informações</h4>
                    <table class="table">
                        <tr>
                            <th>Nome:</th>
                            <td>Nome do Usuário</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>usuario@email.com</td>
                        </tr>
                        <tr>
                            <th>Data de Cadastro:</th>
                            <td>01/01/2024</td>
                        </tr>
                    </table>
                </div>

                <!-- Mangás Favoritos -->
                <div class="card p-4 mt-4">
                    <h4>Meus Mangás Favoritos</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <img src="img/manga1.jpg" class="card-img-top">
                                <div class="card-body">
                                    <h5 class="card-title">One Piece</h5>
                                    <p class="card-text">Último capítulo lido: 1100</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <img src="img/manga2.jpg" class="card-img-top">
                                <div class="card-body">
                                    <h5 class="card-title">Attack on Titan</h5>
                                    <p class="card-text">Último capítulo lido: 139</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <img src="img/manga3.jpg" class="card-img-top">
                                <div class="card-body">
                                    <h5 class="card-title">Naruto</h5>
                                    <p class="card-text">Último capítulo lido: 700</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Histórico de Leitura -->
                <div class="card p-4 mt-4">
                    <h4>Histórico de Leitura</h4>
                    <ul class="list-group">
                        <li class="list-group-item">Leu <strong>Capítulo 50</strong> de <strong>Bleach</strong> <span class="text-muted"> - 2 dias atrás</span></li>
                        <li class="list-group-item">Leu <strong>Capítulo 20</strong> de <strong>Solo Leveling</strong> <span class="text-muted"> - 5 dias atrás</span></li>
                        <li class="list-group-item">Leu <strong>Capítulo 100</strong> de <strong>Dragon Ball</strong> <span class="text-muted"> - 1 semana atrás</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 mt-5">
        <p>&copy; 2025 Manga Reader | Desenvolvido por TJDanilo</p>
    </footer>

    <!-- JS Files -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>
</html>
