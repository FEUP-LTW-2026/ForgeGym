<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

try {
    $pdo = new PDO('sqlite:../database/forge_gym.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Ataque CSRF bloqueado no Fórum!");
    }
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

if ($action === 'create_post' && ($user_role === 'trainer' || $user_role === 'admin')) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    if ($title && $content) {
        $stmt = $pdo->prepare("INSERT INTO forum_posts (user_id, title, content) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $title, $content]);
    }
}

if ($action === 'delete_post') {
    $post_id = (int)$_POST['post_id'];
    $stmtCheck = $pdo->prepare("SELECT user_id FROM forum_posts WHERE id = ?");
    $stmtCheck->execute([$post_id]);
    $post_owner = $stmtCheck->fetchColumn();
    
    if ($user_role === 'admin' || ($user_role === 'trainer' && $post_owner == $user_id)) {
        $pdo->prepare("DELETE FROM forum_comments WHERE post_id = ?")->execute([$post_id]);
        $pdo->prepare("DELETE FROM forum_posts WHERE id = ?")->execute([$post_id]);
    }
}

if ($action === 'create_comment') {
    $post_id = (int)$_POST['post_id'];
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null; 
    $content = trim($_POST['content'] ?? '');
    
    if ($post_id && $content) {
        $stmtCheckPost = $pdo->prepare("SELECT u.role FROM forum_posts fp JOIN users u ON fp.user_id = u.id WHERE fp.id = ?");
        $stmtCheckPost->execute([$post_id]);
        $post_author_role = $stmtCheckPost->fetchColumn();
        
        if ($post_author_role !== 'admin') {
            if ($user_role === 'admin' || $user_role === 'trainer' || $post_author_role === 'trainer') {
                $stmt = $pdo->prepare("INSERT INTO forum_comments (post_id, user_id, content, parent_id) VALUES (?, ?, ?, ?)");
                $stmt->execute([$post_id, $user_id, $content, $parent_id]);
    }
}
    }
}

header('Location: ../pages/forum.php');
exit;