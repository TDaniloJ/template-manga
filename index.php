<?php
// Conectar ao banco de dados
require 'config.php';

session_start();

// Consultar os mangás no banco de dados
try {
    $stmt = $pdo->prepare("SELECT m.id, m.name AS nome, m.image AS capa, m.type AS tipo 
                           FROM manga m 
                           ORDER BY m.created_at DESC");
    $stmt->execute();
    $mangasData = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Manga Reader</title>

    <!-- css files -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.10.2/css/all.css">
    <link rel="stylesheet" href="css/main.css">
</head>

<body>
    <!-- start navbar -->
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
    <!-- end navbar-->

    <!-- start slider -->
    <div id="mangaslider" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="d-block w-100" src="img/slider1.jpg" alt="First slide">
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="img/slider2.jpg" alt="Second slide">
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="img/slider3.jpg" alt="Third slide">
            </div>
        </div>
        <a class="carousel-control-prev" href="#mangaslider" role="button" data-slide="prev">
            <div><span class="carousel-control-prev-icon" aria-hidden="true"></span></div>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#mangaslider" role="button" data-slide="next">
            <div><span class="carousel-control-next-icon" aria-hidden="true"></span></div>
            <span class="sr-only">Next</span>
        </a>
    </div>
    <!-- end slider -->

    <!-- start lastest -->
    <div class="lastest container mt-4 mt-sm-5">
        <div class="row">
            <div class="col-lg-6">

            </div>
        </div>

        <div class="posts row">
            <div class="filters text-center my-4">
                <button class="btn btn-primary" onclick="filterContent('Mangas')">Mangás</button>
                <button class="btn btn-success" onclick="filterContent('Manhwas')">Manhwas</button>
                <button class="btn btn-warning" onclick="filterContent('Comics')">Comics</button>
                <button class="btn btn-secondary" onclick="filterContent('')">Todos</button>
            </div>
            <div class="lastest container mt-4 mt-sm-5">
                <div class="row">
                    <div class="col-lg-6">
                        <h2 class="font-weight-bolder float-left">Obras Populares</h2>
                    </div>
                </div>

                <div class="posts row">
                    <?php foreach ($mangasData as $manga): ?>
                    <div class="col-lg-2 col-md-3 col-sm-4">
                        <div class="card mb-3">
                            <!-- Link para a página de detalhes -->
                            <a href="details.php?manga=<?= urlencode($manga['id']) ?>">
                                <img src="<?= htmlspecialchars($manga['capa']) ?>" class="card-img-top" alt="Capa do <?= htmlspecialchars($manga['tipo']) ?> <?= htmlspecialchars($manga['nome']) ?>">
                            </a>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="details.php?manga=<?= urlencode($manga['id']) ?>">
                                        <?= htmlspecialchars($manga['nome']) ?>
                                    </a>
                                </h5>
                                <p class="card-text"><?= htmlspecialchars($manga['tipo']) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>



        <div class="posts row">
        <div class="lastest container mt-4 mt-sm-5">
            <div class="row">
                <div class="col-lg-6">
                    <h2 class="font-weight-bolder float-left">Últimos updates</h2>
                </div>
            </div>

            <div class="posts row">
                <?php foreach ($mangasData as $manga): ?>
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <div class="card mb-3">
                        <!-- Link para a página de detalhes -->
                        <a href="details.php?manga=<?= urlencode($manga['id']) ?>">
                            <img src="<?= htmlspecialchars($manga['capa']) ?>" class="card-img-top" alt="Capa do <?= htmlspecialchars($manga['tipo']) ?> <?= htmlspecialchars($manga['nome']) ?>">
                        </a>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="details.php?manga=<?= urlencode($manga['id']) ?>">
                                    <?= htmlspecialchars($manga['nome']) ?>
                                </a>
                            </h5>
                            <p class="card-text"><?= htmlspecialchars($manga['tipo']) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        </div>
    </div>
    <!-- end lastest -->

    <!-- start footer -->
    <footer>
        <div class="container py-4">
            <span class="copyright">&copy; 2025 - Manga Reader | Desenvolvido por TJDanilo</span>
            <span class="design float-right">Designed by TJDanilo</span>
        </div>
    </footer>
    <!-- end footer -->

    <!-- js files -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/main.js"></script>
</body>

</html>