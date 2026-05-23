<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'trainer'])) {
    header("Location: ../pages/plan.php");
    exit;
}

define('DB_PATH', __DIR__ . '/../database/forge_gym.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;

    if ($class_id > 0) {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 1. Limpar primeiro as inscrições dos alunos para não violar chaves estrangeiras
            $stmtDelEnrollments = $pdo->prepare("DELETE FROM enrollments WHERE class_id = ?");
            $stmtDelEnrollments->execute([$class_id]);

            // 2. Limpar também as reviews dessa aula se existirem
            $stmtDelReviews = $pdo->prepare("DELETE FROM reviews WHERE class_id = ?");
            $stmtDelReviews->execute([$class_id]);

            // 3. Eliminar a aula definitivamente
            $stmtDelClass = $pdo->prepare("DELETE FROM classes WHERE id = ?");
            $stmtDelClass->execute([$class_id]);

            $_SESSION['flash_success'] = "Class has been successfully removed from the schedule.";
        } catch (PDOException $e) {
            $_SESSION['flash_error'] = "Database error: Could not delete the class.";
        }
    }
}

header("Location: ../pages/plan.php");
exit;
?>