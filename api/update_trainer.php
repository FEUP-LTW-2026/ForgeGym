<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Ataque CSRF bloqueado! Token inválido ou ausente.");
    }
}

define('DB_PATH', __DIR__ . '/../database/forge_gym.db');
$pdo = new PDO('sqlite:' . DB_PATH);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['trainer_id'])) {
    $sql = "UPDATE trainers SET quote = ?, bio = ?, specializations = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['quote'],
        $_POST['bio'],
        $_POST['specializations'],
        $_SESSION['trainer_id']
    ]);
    
    header("Location: ../pages/trainer.php?id=" . $_SESSION['trainer_id']);
    exit;
}
?>