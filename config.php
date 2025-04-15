<?php
// filepath: c:\xampp\htdocs\config.php

// Configurações do banco de dados
define('DB_HOST', 'localhost'); // Host do banco de dados
define('DB_NAME', 'manga_man'); // Nome do banco de dados
define('DB_USER', 'root');      // Usuário do banco de dados
define('DB_PASS', '');          // Senha do banco de dados

try {
    // Criar uma conexão com o banco de dados usando PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Ativar modo de erros
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Configurar o modo de fetch
} catch (PDOException $e) {
    // Exibir mensagem de erro caso a conexão falhe
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}
?>