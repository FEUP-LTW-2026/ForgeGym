<?php
session_start();

// Só utilizadores logados se podem inscrever
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

define('DB_PATH', __DIR__ . '/../database/forge_gym.db');

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed.");
}

$user_id = $_SESSION['user_id'];
$class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$action = $_POST['action'] ?? '';

if ($class_id > 0) {
    // Verificar se o utilizador já está inscrito nesta aula
    $stmtCheck = $pdo->prepare("SELECT id FROM enrollments WHERE user_id = ? AND class_id = ?");
    $stmtCheck->execute([$user_id, $class_id]);
    $isEnrolled = $stmtCheck->fetch();

    if ($action === 'book' && !$isEnrolled) {
        // Verificar capacidade antes de inscrever
        $stmtCapacity = $pdo->prepare("
            SELECT capacity, 
                   (SELECT COUNT(*) FROM enrollments WHERE class_id = ?) as current_enrolled 
            FROM classes WHERE id = ?
        ");
        $stmtCapacity->execute([$class_id, $class_id]);
        $classInfo = $stmtCapacity->fetch();

        if ($classInfo && $classInfo['current_enrolled'] < $classInfo['capacity']) {
            $stmtInsert = $pdo->prepare("INSERT INTO enrollments (user_id, class_id) VALUES (?, ?)");
            $stmtInsert->execute([$user_id, $class_id]);
        }
    } elseif ($action === 'cancel' && $isEnrolled) {
        // Cancelar a inscrição
        $stmtDelete = $pdo->prepare("DELETE FROM enrollments WHERE user_id = ? AND class_id = ?");
        $stmtDelete->execute([$user_id, $class_id]);
    }
}

$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'plan.php';
header("Location: " . $redirect_url);
exit;