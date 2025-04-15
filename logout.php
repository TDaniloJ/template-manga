<?php
// filepath: c:\xampp\htdocs\logout.php

// Iniciar a sessão
session_start();

// Destruir a sessão
session_destroy();

// Redirecionar para a página inicial
header('Location: index.php');
exit;
?>