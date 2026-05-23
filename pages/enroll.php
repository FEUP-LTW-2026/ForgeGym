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
    // 1. Procurar saber quem é o treinador desta aula e os dados do user logado
    $stmtClassOwner = $pdo->prepare("SELECT trainer_id FROM classes WHERE id = ?");
    $stmtClassOwner->execute([$class_id]);
    $class_trainer_id = $stmtClassOwner->fetchColumn();

    // 2. Saber qual é o trainer_id do utilizador que está a tentar inscrever-se
    $stmtUserTrainer = $pdo->prepare("SELECT trainer_id FROM users WHERE id = ?");
    $stmtUserTrainer->execute([$user_id]);
    $user_trainer_id = $stmtUserTrainer->fetchColumn();

    // REGRA DE SEGURANÇA: Se for o treinador da própria aula, salta fora com erro!
    if ($action === 'book' && !empty($user_trainer_id) && $user_trainer_id == $class_trainer_id) {
        $_SESSION['flash_error'] = "You cannot enroll in your own class coach!";
        $redirect_url = $_SERVER['HTTP_REFERER'] ?? 'plan.php';
        header("Location: " . $redirect_url);
        exit;
    }

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
            $_SESSION['flash_success'] = "Successfully booked the class!";
        } else {
            $_SESSION['flash_error'] = "Too late! Someone just took the last spot.";
        }
    } elseif ($action === 'cancel' && $isEnrolled) {
        // Cancelar a inscrição
        $stmtDelete = $pdo->prepare("DELETE FROM enrollments WHERE user_id = ? AND class_id = ?");
        $stmtDelete->execute([$user_id, $class_id]);
        $_SESSION['flash_success'] = "Booking cancelled successfully.";
    }
}

$redirect_url = $_SERVER['HTTP_REFERER'] ?? 'plan.php';
header("Location: " . $redirect_url);
exit;
?>