<?php
// filepath: c:\xampp\htdocs\admin\mangas.php

// Conectar ao banco de dados
require '../config.php';

// Verificar se o usuário está autenticado como administrador
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Adicionar um novo mangá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_manga'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $author = trim($_POST['author']);
    $status = trim($_POST['status']);
    $type = trim($_POST['type']);
    $language_id = intval($_POST['language_id']);
    $genres = $_POST['genres'] ?? []; // Gêneros selecionados
    $image = null;

    // Processar o upload da imagem de capa
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "../uploads/covers/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imageName = uniqid() . '-' . basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $image = $imagePath;
        } else {
            $error = "Erro ao fazer upload da imagem de capa.";
        }
    } else {
        $image = trim($_POST['image_url']); // Usar URL se fornecida
    }

    try {
        // Inserir o mangá
        $stmt = $pdo->prepare("INSERT INTO manga (name, image, description, author, status, type, language_id) 
                               VALUES (:name, :image, :description, :author, :status, :type, :language_id)");
        $stmt->execute([
            'name' => $name,
            'image' => $image,
            'description' => $description,
            'author' => $author,
            'status' => $status,
            'type' => $type,
            'language_id' => $language_id
        ]);
        $mangaId = $pdo->lastInsertId();

        // Inserir os gêneros na tabela intermediária
        $stmt = $pdo->prepare("INSERT INTO manga_genres (manga_id, genre_id) VALUES (:manga_id, :genre_id)");
        foreach ($genres as $genreId) {
            $stmt->execute([
                'manga_id' => $mangaId,
                'genre_id' => $genreId
            ]);
        }

        $success = "Mangá adicionado com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao adicionar mangá: " . $e->getMessage();
    }
}

// Excluir um mangá
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $mangaId = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM manga WHERE id = :id");
        $stmt->execute(['id' => $mangaId]);
        $success = "Mangá excluído com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao excluir mangá: " . $e->getMessage();
    }
}

// Editar um mangá
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_manga'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $genre = trim($_POST['genre']);
    $author = trim($_POST['author']);
    $status = trim($_POST['status']);
    $type = trim($_POST['type']);
    $language_id = intval($_POST['language_id']);
    $image = null;

    // Processar o upload da imagem de capa
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = "../uploads/covers/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imageName = uniqid() . '-' . basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $image = $imagePath;
        } else {
            $error = "Erro ao fazer upload da imagem de capa.";
        }
    } else {
        $image = trim($_POST['image_url']); // Usar URL se fornecida
    }

    try {
        $stmt = $pdo->prepare("UPDATE manga 
                               SET name = :name, image = :image, description = :description, genre = :genre, 
                                   author = :author, status = :status, type = :type, language_id = :language_id 
                               WHERE id = :id");
        $stmt->execute([
            'id' => $id,
            'name' => $name,
            'image' => $image,
            'description' => $description,
            'genre' => $genre,
            'author' => $author,
            'status' => $status,
            'type' => $type,
            'language_id' => $language_id
        ]);
        $success = "Mangá atualizado com sucesso!";
    } catch (PDOException $e) {
        $error = "Erro ao atualizar mangá: " . $e->getMessage();
    }
}

// Consultar todos os mangás
try {
    $stmt = $pdo->query("SELECT m.id, m.name, m.image, m.genre, m.author, m.status, m.type, l.name AS language 
                         FROM manga m 
                         INNER JOIN languages l ON m.language_id = l.id 
                         ORDER BY m.created_at DESC");
    $mangas = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar mangás: " . $e->getMessage());
}

// Consultar dados do mangá para edição
$editManga = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $mangaId = intval($_GET['edit']);
    try {
        $stmt = $pdo->prepare("SELECT * FROM manga WHERE id = :id");
        $stmt->execute(['id' => $mangaId]);
        $editManga = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Erro ao buscar mangá para edição: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Gerenciar Mangás - Painel Admin</title>

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
        <h1 class="mb-4">Gerenciar Mangás</h1>

        <!-- Mensagens de Sucesso ou Erro -->
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Formulário para Adicionar ou Editar Mangá -->
        <div class="card mb-4">
            <div class="card-header"><?= $editManga ? 'Editar Mangá' : 'Adicionar Novo Mangá' ?></div>
            <div class="card-body">
                <form method="POST" action="mangas.php" enctype="multipart/form-data">
                    <?php if ($editManga): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($editManga['id']) ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="name">Nome</label>
                        <input type="text" name="name" id="name" class="form-control" value="<?= $editManga ? htmlspecialchars($editManga['name']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="image">Imagem de Capa</label>
                        <input type="file" name="image" id="image" class="form-control">
                        <small class="form-text text-muted">Faça upload de uma imagem ou insira uma URL abaixo.</small>
                    </div>
                    <div class="form-group">
                        <label for="image_url">URL da Imagem</label>
                        <input type="text" name="image_url" id="image_url" class="form-control" value="<?= $editManga ? htmlspecialchars($editManga['image']) : '' ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Descrição</label>
                        <textarea name="description" id="description" class="form-control" rows="3" required><?= $editManga ? htmlspecialchars($editManga['description']) : '' ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="genres">Gêneros</label>
                        <select name="genres[]" id="genres" class="form-control" multiple required>
                            <?php
                            $genres = $pdo->query("SELECT id, name FROM genres ORDER BY name ASC")->fetchAll();
                            foreach ($genres as $genre) {
                                $selected = $editManga && in_array($genre['id'], array_column($editManga['genres'], 'id')) ? 'selected' : '';
                                echo "<option value='{$genre['id']}' $selected>{$genre['name']}</option>";
                            }
                            ?>
                        </select>
                        <small class="form-text text-muted">Segure Ctrl (ou Cmd no Mac) para selecionar múltiplos gêneros.</small>
                    </div>
                    <div class="form-group">
                        <label for="author">Autor</label>
                        <input type="text" name="author" id="author" class="form-control" value="<?= $editManga ? htmlspecialchars($editManga['author']) : '' ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="Ongoing" <?= $editManga && $editManga['status'] === 'Ongoing' ? 'selected' : '' ?>>Em Andamento</option>
                            <option value="Completed" <?= $editManga && $editManga['status'] === 'Completed' ? 'selected' : '' ?>>Concluído</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="type">Tipo</label>
                        <select name="type" id="type" class="form-control" required>
                            <option value="Manga" <?= $editManga && $editManga['type'] === 'Manga' ? 'selected' : '' ?>>Mangá</option>
                            <option value="Manhwa" <?= $editManga && $editManga['type'] === 'Manhwa' ? 'selected' : '' ?>>Manhwa</option>
                            <option value="Comic" <?= $editManga && $editManga['type'] === 'Comic' ? 'selected' : '' ?>>Comic</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="language_id">Idioma</label>
                        <select name="language_id" id="language_id" class="form-control" required>
                            <?php
                            $languages = $pdo->query("SELECT id, name FROM languages")->fetchAll();
                            foreach ($languages as $language) {
                                $selected = $editManga && $editManga['language_id'] == $language['id'] ? 'selected' : '';
                                echo "<option value='{$language['id']}' $selected>{$language['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <button type="submit" name="<?= $editManga ? 'edit_manga' : 'add_manga' ?>" class="btn btn-primary"><?= $editManga ? 'Salvar Alterações' : 'Adicionar' ?></button>
                </form>
            </div>
        </div>

        <!-- Lista de Mangás -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Gênero</th>
                    <th>Autor</th>
                    <th>Status</th>
                    <th>Tipo</th>
                    <th>Idioma</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mangas as $manga): ?>
                    <tr>
                        <td><?= htmlspecialchars($manga['id']) ?></td>
                        <td><?= htmlspecialchars($manga['name']) ?></td>
                        <td><?= htmlspecialchars($manga['genre']) ?></td>
                        <td><?= htmlspecialchars($manga['author']) ?></td>
                        <td><?= htmlspecialchars($manga['status']) ?></td>
                        <td><?= htmlspecialchars($manga['type']) ?></td>
                        <td><?= htmlspecialchars($manga['language']) ?></td>
                        <td>
                            <a href="mangas.php?edit=<?= $manga['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="mangas.php?delete=<?= $manga['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir este mangá?')">Excluir</a>
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