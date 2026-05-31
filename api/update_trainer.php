<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF attack blocked!");
    }

    $target_trainer_id = (int)$_POST['trainer_id'];
    $is_admin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
    $is_owner = (isset($_SESSION['trainer_id']) && $_SESSION['trainer_id'] == $target_trainer_id);

    if (!$is_admin && !$is_owner) {
        die("Access denied.");
    }

    define('DB_PATH', __DIR__ . '/../database/forge_gym.db');
    $pdo = new PDO('sqlite:' . DB_PATH);

    $sql = "UPDATE trainers SET quote = ?, bio = ?, specializations = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $_POST['quote'],
        $_POST['bio'],
        $_POST['specializations'],
        $target_trainer_id
    ]);
    
    header("Location: ../pages/trainer.php?id=" . $target_trainer_id);
    exit;
}
?>