<?php
// Conectar ao banco de dados
require 'config.php';

session_start();

// Capturar os parâmetros da URL
$mangaId = isset($_GET['manga']) ? intval($_GET['manga']) : null;
$capituloId = isset($_GET['capitulo']) ? intval($_GET['capitulo']) : null;

// Verificar se os parâmetros foram fornecidos
if (!$mangaId || !$capituloId) {
    die('Mangá ou capítulo não especificado.');
}

try {
    // Consultar informações do capítulo
    $stmt = $pdo->prepare("SELECT c.id, c.name AS capitulo_nome, m.name AS manga_nome
                           FROM chapter c
                           INNER JOIN manga m ON c.manga_id = m.id
                           WHERE c.id = :capituloId AND m.id = :mangaId");
    $stmt->execute(['capituloId' => $capituloId, 'mangaId' => $mangaId]);
    $capitulo = $stmt->fetch();

    if (!$capitulo) {
        die('Capítulo não encontrado.');
    }

    // Consultar as páginas do capítulo
    $stmt = $pdo->prepare("SELECT page_number, image_path FROM pages WHERE chapter_id = :capituloId ORDER BY page_number ASC");
    $stmt->execute(['capituloId' => $capituloId]);
    $paginas = $stmt->fetchAll();

    if (!$paginas) {
        die('Nenhuma página encontrada para este capítulo.');
    }
} catch (PDOException $e) {
    die("Erro ao buscar dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Leitor - <?= htmlspecialchars($capitulo['manga_nome']) ?> - <?= htmlspecialchars($capitulo['capitulo_nome']) ?></title>

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

    <!-- Manga Reader -->
    <div class="container my-5">
        <div class="reader-container">
            <div class="row">
                <div class="col-12 text-center">
                    <h2><?= htmlspecialchars($capitulo['manga_nome']) ?> - <?= htmlspecialchars($capitulo['capitulo_nome']) ?></h2>
                </div>

                <div class="reading-options text-center my-4">
                    <label for="readingMode">Modo de leitura:</label>
                    <select id="readingMode" class="form-control d-inline-block w-auto">
                        <option value="single">Página por Página</option>
                        <option value="all">Todas as Páginas</option>
                    </select>
                </div>
            </div>

            <!-- Start Manga Pages -->
            <div class="manga-pages text-center">
                <div id="page-container" class="manga-page">
                    <img id="manga-page" src="<?= htmlspecialchars($paginas[0]['image_path']) ?>" alt="Página 1" class="img-fluid" onclick="nextPage()">
                </div>
            </div>
            <!-- End Manga Pages -->

            <div class="navigation-buttons text-center my-3 auto-navigation">
                <button class="btn btn-primary" id="prevPageBtn">Página Anterior</button>
                <button class="btn btn-primary" id="nextPageBtn">Próxima Página</button>
            </div>

            <div class="go-to-page text-center auto-navigation">
                <label for="pageNumberInput">Ir para a página:</label>
                <input type="number" id="pageNumberInput" min="1" value="1">
                <button class="btn btn-warning" id="goToPageBtn">Ir</button>
            </div>
        </div>
    </div>

    <!-- Modal de Fim do Capítulo -->
    <div class="modal fade" id="endChapterModal" tabindex="-1" role="dialog" aria-labelledby="endChapterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="endChapterModalLabel" style="color: black;">Fim do Capítulo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="color: black;">
                    Você chegou ao fim deste capítulo.
                </div>
                <div class="modal-footer">
                    <a href="details.php?manga=<?= urlencode($mangaId) ?>" class="btn btn-primary">Voltar para Detalhes</a>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container py-4">
            <span class="copyright">&copy; 2025 Manga Reader</span>
        </div>
    </footer>

    <!-- JS Files -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script>
        // Carregar as páginas do PHP para o JavaScript
        const pages = <?= json_encode(array_column($paginas, 'image_path')); ?>;

        let currentPage = 0;

        // Função para mudar para a próxima página
        function nextPage() {
            if (currentPage < pages.length - 1) {
                currentPage++;
                updatePage();
            } else {
                $('#endChapterModal').modal('show');
            }
        }

        // Função para voltar para a página anterior
        function prevPage() {
            if (currentPage > 0) {
                currentPage--;
                updatePage();
            }
        }

        // Função para ir para uma página específica
        function goToPage() {
            const pageNumber = parseInt(document.getElementById('pageNumberInput').value, 10);
            if (pageNumber >= 1 && pageNumber <= pages.length) {
                currentPage = pageNumber - 1;
                updatePage();
            }
        }

        // Atualiza a imagem da página com base no índice atual
        function updatePage() {
            document.getElementById('manga-page').src = pages[currentPage];
            document.getElementById('pageNumberInput').value = currentPage + 1;

            // Voltar para o topo da página
            window.scrollTo(0, 0);
        }

        // Função para alternar o modo de leitura
        function changeReadingMode() {
            const mode = document.getElementById('readingMode').value;

            if (mode === 'single') {
                // Modo "Página por Página"
                document.getElementById('page-container').innerHTML = `
                    <img id="manga-page" src="${pages[currentPage]}" alt="Página ${currentPage + 1}" class="img-fluid">
                `;
                document.querySelector('.navigation-buttons').style.display = 'block';
                document.querySelector('.go-to-page').style.display = 'block';
            } else if (mode === 'all') {
                // Modo "Todas as Páginas"
                let allPagesHtml = '';
                pages.forEach((page, index) => {
                    allPagesHtml += `<img src="${page}" alt="Página ${index + 1}" class="img-fluid mb-3">`;
                });
                document.getElementById('page-container').innerHTML = allPagesHtml;
                document.querySelector('.navigation-buttons').style.display = 'none';
                document.querySelector('.go-to-page').style.display = 'none';
            }
        }

        // Adicionar evento para o seletor de modo de leitura
        document.getElementById('readingMode').addEventListener('change', changeReadingMode);

        // Iniciar no modo "Página por Página"
        changeReadingMode();

        // Eventos para os botões
        document.getElementById('nextPageBtn').addEventListener('click', nextPage);
        document.getElementById('prevPageBtn').addEventListener('click', prevPage);
        document.getElementById('goToPageBtn').addEventListener('click', goToPage);

        // Iniciar na primeira página
        updatePage();
    </script>
</body>

</html>