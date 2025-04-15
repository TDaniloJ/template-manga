<?php
session_start(); // Iniciar sessão para salvar última página lida

// Configuração: diretório onde as imagens do mangá estão armazenadas
$mangaDir = "manga/";
$pages = glob($mangaDir . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
sort($pages); // Ordena as páginas numericamente

// Obtém a página atual do parâmetro GET ou da sessão
$pageIndex = isset($_GET['page']) ? intval($_GET['page']) : ($_SESSION['last_page'] ?? 0);
$rtlMode = isset($_GET['rtl']) ? boolval($_GET['rtl']) : false;
$darkMode = isset($_GET['dark']) ? boolval($_GET['dark']) : false;
$zoom = isset($_GET['zoom']) ? intval($_GET['zoom']) : 100;
$autoScroll = isset($_GET['autoScroll']) ? boolval($_GET['autoScroll']) : false;
$scrollMode = isset($_GET['scrollMode']) ? boolval($_GET['scrollMode']) : false;

// Salva a última página lida na sessão
$_SESSION['last_page'] = $pageIndex;

// Garante que a página está dentro dos limites
if ($pageIndex < 0) $pageIndex = 0;
if ($pageIndex >= count($pages)) $pageIndex = count($pages) - 1;

// Página atual
$currentImage = $pages[$pageIndex];

// Define os links das páginas anterior e próxima
$prevPage = "?page=" . ($rtlMode ? $pageIndex + 1 : $pageIndex - 1) . "&rtl=$rtlMode&dark=$darkMode&zoom=$zoom&autoScroll=$autoScroll&scrollMode=$scrollMode";
$nextPage = "?page=" . ($rtlMode ? $pageIndex - 1 : $pageIndex + 1) . "&rtl=$rtlMode&dark=$darkMode&zoom=$zoom&autoScroll=$autoScroll&scrollMode=$scrollMode";

// Inverte a navegação no modo RTL
if ($rtlMode) {
    $prevPage = $pageIndex < count($pages) - 1 ? $prevPage : "#";
    $nextPage = $pageIndex > 0 ? $nextPage : "#";
} else {
    $prevPage = $pageIndex > 0 ? $prevPage : "#";
    $nextPage = $pageIndex < count($pages) - 1 ? $nextPage : "#";
}

// Caminho do arquivo de comentários
$comentariosArquivo = "comentarios.txt";

// Se o formulário foi enviado, salva o comentário
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['nome'], $_POST['comentario'])) {
    $nome = htmlspecialchars(trim($_POST['nome']));
    $comentario = htmlspecialchars(trim($_POST['comentario']));
    
    if (!empty($nome) && !empty($comentario)) {
        $data = date("d/m/Y H:i");
        $novoComentario = "$data | $nome: $comentario\n";
        file_put_contents($comentariosArquivo, $novoComentario, FILE_APPEND);
    }
}

// Carrega os comentários existentes
$comentarios = file_exists($comentariosArquivo) ? file($comentariosArquivo) : [];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leitor de Mangá</title>
    <style>
        body {
            text-align: center;
            font-family: Arial, sans-serif;
            background-color: <?= $darkMode ? "#121212" : "#ffffff" ?>;
            color: <?= $darkMode ? "#ffffff" : "#000000" ?>;
        }
        .image-container {
            text-align: center;
            margin: 10px 0;
        }
        img {
            width: 800px;
            height: auto;
            transition: transform 0.3s;
            cursor: pointer;
        }
        .scroll-mode img {
            width: 80%;
            margin: 0 auto 10px;
            display: block;
        }
        .nav {
            margin: 20px;
        }
        .btn {
            padding: 10px 20px;
            text-decoration: none;
            border: 1px solid #000;
            margin: 5px;
            display: inline-block;
            cursor: pointer;
            background-color: <?= $darkMode ? "#333" : "#f0f0f0" ?>;
            color: <?= $darkMode ? "#fff" : "#000" ?>;
        }
        .comentarios {
            width: 60%;
            margin: 20px auto;
            text-align: left;
        }
        .comentario {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .formulario {
            margin: 20px auto;
            width: 60%;
            padding: 10px;
            background: #f9f9f9;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
        }
        button {
            padding: 10px 20px;
            cursor: pointer;
            background: #333;
            color: white;
            border: none;
        }
    </style>
</head>
<body>
    <h1>Leitor de Mangá</h1>

    <?php if ($scrollMode): ?>
        <div class="scroll-mode">
            <?php foreach ($pages as $index => $page): ?>
                <img src="<?= $page ?>" alt="Página <?= $index + 1 ?>" onclick="window.location.href='?page=<?= $index ?>&rtl=<?= $rtlMode ?>&dark=<?= $darkMode ?>&zoom=<?= $zoom ?>&autoScroll=<?= $autoScroll ?>&scrollMode=<?= $scrollMode ?>'">
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="image-container">
            <img src="<?= $currentImage ?>" alt="Página <?= $pageIndex + 1 ?>" onclick="window.location.href='<?= $nextPage ?>'">
        </div>
    <?php endif; ?>

    <div class="nav">
        <a href="<?= $prevPage ?>" class="btn">Anterior</a>
        <a href="<?= $nextPage ?>" class="btn">Próxima</a>
    </div>

    <div class="comentarios">
        <h2>Comentários</h2>
        
        <div class="formulario">
            <h3>Deixe seu comentário:</h3>
            <form method="POST">
                <input type="text" name="nome" placeholder="Seu nome" required>
                <textarea name="comentario" rows="4" placeholder="Seu comentário" required></textarea>
                <button type="submit">Enviar Comentário</button>
            </form>
        </div>

        <?php if (!empty($comentarios)): ?>
            <?php foreach (array_reverse($comentarios) as $linha): ?>
                <div class="comentario"><?= nl2br(htmlspecialchars($linha)) ?></div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Seja o primeiro a comentar!</p>
        <?php endif; ?>
    </div>

</body>
</html>
