<?php
// filepath: c:\xampp\htdocs\admin\chapters.php

// Conectar ao banco de dados
require '../config.php';

// Verificar se o usuário está autenticado como administrador
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Adicionar um novo capítulo com imagens
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_chapter'])) {
    $manga_id = intval($_POST['manga_id']);
    $name = trim($_POST['name']);

    try {
        // Inserir o capítulo no banco de dados
        $stmt = $pdo->prepare("INSERT INTO chapter (manga_id, name) 
                               VALUES (:manga_id, :name)");
        $stmt->execute([
            'manga_id' => $manga_id,
            'name' => $name
        ]);
        $chapter_id = $pdo->lastInsertId();

        // Processar o upload de imagens
        if (!empty($_FILES['images']['name'][0])) {
            // Criar a pasta do mangá, se não existir
            $mangaFolder = "../uploads/mangas/{$manga_id}/";
            if (!is_dir($mangaFolder)) {
                mkdir($mangaFolder, 0777, true);
            }

            // Criar a pasta do capítulo
            $chapterFolder = $mangaFolder . "chapter_{$chapter_id}/";
            if (!is_dir($chapterFolder)) {
                mkdir($chapterFolder, 0777, true);
            }

            // Criar um array associativo para ordenar os arquivos
            $files = [];
            foreach ($_FILES['images']['name'] as $key => $name) {
                $files[] = [
                    'tmp_name' => $_FILES['images']['tmp_name'][$key],
                    'name' => $name
                ];
            }

            // Ordenar os arquivos pelo nome (ordem alfabética natural)
            usort($files, function ($a, $b) {
                return strnatcmp($a['name'], $b['name']);
            });

            // Processar os arquivos ordenados
            foreach ($files as $key => $file) {
                $fileName = basename($file['name']);
                $filePath = $chapterFolder . $fileName; // Salvar com o nome original

                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    // Inserir o caminho da imagem na tabela `pages`
                    $stmt = $pdo->prepare("INSERT INTO pages (chapter_id, page_number, image_path) 
                                        VALUES (:chapter_id, :page_number, :image_path)");
                    $stmt->execute([
                        'chapter_id' => $chapter_id,
                        'page_number' => $key + 1,
                        'image_path' => $filePath
                    ]);
                }
            }
        }

        $success = "Capítulo e imagens adicionados com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao adicionar capítulo: " . $e->getMessage();
    }
}

// Excluir um capítulo
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $chapterId = intval($_GET['delete']);
    try {
        // Consultar o mangá e o capítulo para obter o caminho da pasta
        $stmt = $pdo->prepare("SELECT manga_id FROM chapter WHERE id = :id");
        $stmt->execute(['id' => $chapterId]);
        $chapter = $stmt->fetch();

        if ($chapter) {
            $manga_id = $chapter['manga_id'];
            $chapterFolder = "../uploads/mangas/{$manga_id}/chapter_{$chapterId}/";

            // Remover a pasta do capítulo e seus arquivos
            if (is_dir($chapterFolder)) {
                $files = glob($chapterFolder . '*'); // Obter todos os arquivos na pasta
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file); // Excluir cada arquivo
                    }
                }
                rmdir($chapterFolder); // Excluir a pasta do capítulo
            }
        }

        // Excluir o capítulo do banco de dados
        $stmt = $pdo->prepare("DELETE FROM chapter WHERE id = :id");
        $stmt->execute(['id' => $chapterId]);

        // Excluir as imagens associadas ao capítulo da tabela `pages`
        $stmt = $pdo->prepare("DELETE FROM pages WHERE chapter_id = :chapter_id");
        $stmt->execute(['chapter_id' => $chapterId]);

        $success = "Capítulo e imagens excluídos com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao excluir capítulo: " . $e->getMessage();
    }
}

// Editar um capítulo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_chapter'])) {
    $id = intval($_POST['id']);
    $manga_id = intval($_POST['manga_id']);
    $name = trim($_POST['name']);

    try {
        $stmt = $pdo->prepare("UPDATE chapter 
                               SET manga_id = :manga_id, name = :name 
                               WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'manga_id' => $manga_id,
            'name' => $name
        ]);

        // Processar o upload de novas imagens
        if (!empty($_FILES['images']['name'][0])) {
            // Criar a pasta do mangá, se não existir
            $mangaFolder = "../uploads/mangas/{$manga_id}/";
            if (!is_dir($mangaFolder)) {
                mkdir($mangaFolder, 0777, true);
            }

            // Criar a pasta do capítulo
            $chapterFolder = $mangaFolder . "chapter_{$id}/";
            if (!is_dir($chapterFolder)) {
                mkdir($chapterFolder, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $fileName = basename($_FILES['images']['name'][$key]);
                $filePath = $chapterFolder . uniqid() . '-' . $fileName;

                if (move_uploaded_file($tmpName, $filePath)) {
                    // Inserir o caminho da imagem na tabela `pages`
                    $stmt = $pdo->prepare("INSERT INTO pages (chapter_id, page_number, image_path) 
                                           VALUES (:chapter_id, :page_number, :image_path)");
                    $stmt->execute([
                        'chapter_id' => $id,
                        'page_number' => $key + 1,
                        'image_path' => $filePath
                    ]);
                }
            }
        }

        $success = "Capítulo atualizado com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao atualizar capítulo: " . $e->getMessage();
    }
}

// Consultar todos os capítulos
try {
    $stmt = $pdo->query("SELECT c.id, c.name, m.name AS manga_name 
                         FROM chapter c 
                         INNER JOIN manga m ON c.manga_id = m.id 
                         ORDER BY c.created_at DESC");
    $chapters = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar capítulos: " . $e->getMessage());
}

// Consultar dados do capítulo para edição
$editChapter = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $chapterId = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM chapter WHERE id = :id");
        $stmt->execute(['id' => $chapterId]);
        $editChapter = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Erro ao buscar capítulo para edição: " . $e->getMessage();
    }
}

// Consultar todos os mangás para o formulário
try {
    $mangas = $pdo->query("SELECT id, name FROM manga")->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar mangás: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gerenciar Capítulos - Painel Admin</title>

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
        <h1 class="mb-4">Gerenciar Capítulos</h1>

        <!-- Mensagens de Sucesso ou Erro -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Formulário para Adicionar ou Editar Capítulo -->
        <div class="card mb-4">
            <div class="card-header"><?= $editChapter ? 'Editar Capítulo' : 'Adicionar Novo Capítulo' ?></div>
            <div class="card-body">
                <form method="POST" action="chapters.php" enctype="multipart/form-data">
                    <?php if ($editChapter): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($editChapter['id']) ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="manga_id">Mangá</label>
                        <select name="manga_id" id="manga_id" class="form-control" required>
                            <?php foreach ($mangas as $manga): ?>
                                <option value="<?= $manga['id'] ?>" <?= $editChapter && $editChapter['manga_id'] == $manga['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($manga['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="name">Nome do Capítulo</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= $editChapter ? htmlspecialchars($editChapter['name']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="images">Imagens do Capítulo</label>
                        <input type="file" name="images[]" id="images" class="form-control" multiple>
                        <small class="form-text text-muted">Você pode selecionar várias imagens.</small>
                    </div>
                    <button type="submit" name="<?= $editChapter ? 'edit_chapter' : 'add_chapter' ?>" class="btn btn-primary"><?= $editChapter ? 'Salvar Alterações' : 'Adicionar' ?></button>
                </form>
            </div>
        </div>

        <!-- Lista de Capítulos -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Mangá</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($chapters as $chapter): ?>
                    <tr>
                        <td><?= htmlspecialchars($chapter['id']) ?></td>
                        <td><?= htmlspecialchars($chapter['name']) ?></td>
                        <td><?= htmlspecialchars($chapter['manga_name']) ?></td>
                        <td>
                            <a href="chapters.php?edit=<?= $chapter['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="chapters.php?delete=<?= $chapter['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este capítulo?')">Excluir</a>
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