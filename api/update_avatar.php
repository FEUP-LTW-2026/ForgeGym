<?php
session_start();
if (!isset($_SESSION['user_id'])) { die("Not authenticated"); }

if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        if ($_FILES['profile_photo']['error'] === 1) {
            header('Location: ../pages/profile.php?error=file_too_large');
            exit;
        }
        die("Erro no envio do ficheiro! Código: " . $_FILES['profile_photo']['error']);
    }

    if ($_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
        header('Location: ../pages/profile.php?error=file_too_large');
        exit;
    }

    $uploadDir = __DIR__ . '/../images/profiles/';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
    
    $newFileName = 'user_' . $_SESSION['user_id'] . '_' . time() . '.jpg';
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath)) {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../database/forge_gym.db');
        $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
        $stmt->execute(['images/profiles/' . $newFileName, $_SESSION['user_id']]);
        
        header('Location: ../pages/profile.php?success=1');
        exit;
    } else {
        die("Falha ao mover ficheiro.");
    }
}
?>