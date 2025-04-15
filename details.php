<?php
// Conectar ao banco de dados
require 'config.php';

session_start();

// Capturar os parâmetros da URL
$mangaId = isset($_GET['manga']) ? intval($_GET['manga']) : null;

// Verificar se o parâmetro foi fornecido e é válido
if (!$mangaId || $mangaId <= 0) {
    die('Mangá não especificado ou inválido.');
}

try {
    // Consultar informações do mangá
    $stmt = $pdo->prepare("SELECT m.id, m.name AS nome, m.image AS capa, m.description, m.genre, m.author, m.status, m.type, m.created_at
                           FROM manga m
                           WHERE m.id = :mangaId");
    $stmt->execute(['mangaId' => $mangaId]);
    $manga = $stmt->fetch();

    if (!$manga) {
        die('Mangá não encontrado.');
    }

    // Consultar capítulos do mangá
    $stmt = $pdo->prepare("SELECT id, name FROM chapter WHERE manga_id = :mangaId ORDER BY created_at ASC");
    $stmt->execute(['mangaId' => $mangaId]);
    $capitulos = $stmt->fetchAll();

    // Consultar mangás relacionados (baseado no gênero)
    $stmt = $pdo->prepare("SELECT id, name AS nome, image AS capa FROM manga WHERE genre = :genre AND id != :mangaId LIMIT 4");
    $stmt->execute(['genre' => $manga['genre'], 'mangaId' => $mangaId]);
    $related = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Detalhes - <?= htmlspecialchars($manga['nome']) ?></title>

    <!-- css files -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.10.2/css/all.css">
    <link rel="stylesheet" href="css/main.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light shadow py-2 py-sm-0">
        <a class="navbar-brand" href="index.php">
            <h5>Manga Man</h5>
        </a>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <div class="container-fluid">
                <div class="row py-3">
                    <div class="col-lg-6 col-sm-12 mb-3 mb-sm-0">
                        <ul class="navbar-nav mr-auto">
                            <!-- always use single word for li -->
                            <li class="nav-item active">
                                <a class="nav-link" href="#">Inicio<span class="sr-only">(current)</span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Aleatório</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Populares</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">+ 18</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col">
                        <form class="form-inline search">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Search"
                                    aria-label="Type Title, auther or genre">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button"><i
                                            class="fa fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="profile float-right">
            <div class="saved">
                <button class="btn" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                    <i class="fa fa-moon fa-2x"></i>
                </button>
            </div>

            <div class="account">
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                    <!-- Exibir informações da conta se o usuário estiver logado -->
                    <button class="btn" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false">
                        <i class="fa fa-user-circle fa-2x"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                        <caption class="dropdown-header"><?= htmlspecialchars($_SESSION['user_name']) ?></caption>
                        <a class="dropdown-item" href="user.php">Meu Perfil</a>
                        <a class="dropdown-item" href="#">Biblioteca</a>
                        <a class="dropdown-item" href="logout.php">Sair</a>
                    </div>
                <?php else: ?>
                    <!-- Exibir botões de login e cadastro se o usuário não estiver logado -->
                    <a href="login.php" class="btn btn-primary">Login</a>
                    <a href="cadastro.php" class="btn btn-secondary">Cadastro</a>
                <?php endif; ?>
            </div>
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </nav>

    <!-- Manga Details -->
    <div class="container my-5">
        <div class="row">
            <div class="col-md-4">
                <img src="<?= htmlspecialchars($manga['capa']) ?>" class="img-fluid" alt="Capa de <?= htmlspecialchars($manga['nome']) ?>">
            </div>
            <div class="info col-md-8">
                <h1><?= htmlspecialchars($manga['nome']) ?></h1>
                <p><strong>Tipo:</strong> <?= htmlspecialchars($manga['type']) ?></p>
                <p><?= nl2br(htmlspecialchars($manga['description'])) ?></p>
                <table class="table table-borderless">
                    <tbody>
                        <tr>
                            <th scope="row">Gênero:</th>
                            <td><?= htmlspecialchars($manga['genre']) ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Autor:</th>
                            <td><?= htmlspecialchars($manga['author']) ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Status:</th>
                            <td><?= htmlspecialchars($manga['status']) ?></td>
                        </tr>
                        <tr>
                            <th scope="row">Lançamento:</th>
                            <td><?= date('d/m/Y', strtotime($manga['created_at'])) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lista de Capítulos -->
        <div class="mt-5">
            <h3>Capítulos</h3>
            <ul class="list-group">
                <?php foreach ($capitulos as $capitulo): ?>
                <li class="list-group-item">
                    <a href="leitor.php?manga=<?= $manga['id'] ?>&capitulo=<?= $capitulo['id'] ?>">
                        <?= htmlspecialchars($capitulo['name']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Mangás Relacionados -->
        <div class="mt-5">
            <h3>Relacionados</h3>
            <div class="row">
                <?php foreach ($related as $rel): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <a href="details.php?manga=<?= $rel['id'] ?>">
                        <div class="card mb-3">
                            <img src="<?= htmlspecialchars($rel['capa']) ?>" class="card-img-top" alt="Capa de <?= htmlspecialchars($rel['nome']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($rel['nome']) ?></h5>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4">
        <span>&copy; 2025 Manga Reader</span>
    </footer>

    <!-- JS Files -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</body>

</html>