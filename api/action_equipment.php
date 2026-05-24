<?php
session_start();

// Proteção: Apenas Admins podem aceder a este ficheiro
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Ligar à Base de Dados
define('DB_PATH', __DIR__ . '/../database/forge_gym.db');
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed.");
}

$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $zone = $_POST['zone'] ?? '';
    $total_units = (int)($_POST['total_units'] ?? 0);
    $image_path = trim($_POST['image_path'] ?? '');
    
    if (empty($image_path)) {
        $image_path = 'images/equipment/treadMills.png'; // Imagem genérica caso falte
    }

    if ($name && $zone && $total_units > 0) {
        $stmt = $pdo->prepare("INSERT INTO equipment (name, zone, total_units, in_use, image_path) VALUES (?, ?, ?, 0, ?)");
        $stmt->execute([$name, $zone, $total_units, $image_path]);
    }
} 
elseif ($action === 'update_occupancy') {
    $id = (int)$_POST['id'];
    $in_use = (int)$_POST['in_use'];

    // Validar para não permitir uma ocupação maior que o total de máquinas
    $stmt = $pdo->prepare("SELECT total_units FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
    $eq = $stmt->fetch();

    if ($eq && $in_use >= 0 && $in_use <= $eq['total_units']) {
        $stmt = $pdo->prepare("UPDATE equipment SET in_use = ? WHERE id = ?");
        $stmt->execute([$in_use, $id]);
    }
} 
elseif ($action === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
}

// Depois de executar, redireciona de volta para o painel de admin
header("Location: ../pages/admin_dashboard.php");
exit;