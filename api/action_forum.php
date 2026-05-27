<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

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

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';

if ($action === 'create_post' && $user_role === 'trainer') {
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

        $stmtDelComm = $pdo->prepare("DELETE FROM forum_comments WHERE post_id = ?");
        $stmtDelComm->execute([$post_id]);
        
        $stmtDelPost = $pdo->prepare("DELETE FROM forum_posts WHERE id = ?");
        $stmtDelPost->execute([$post_id]);
    }
}

if ($action === 'create_comment') {
    $post_id = (int)$_POST['post_id'];
    $content = trim($_POST['content'] ?? '');
    
    if ($post_id && $content) {
        $stmt = $pdo->prepare("INSERT INTO forum_comments (post_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $user_id, $content]);
    }
}

if ($action === 'delete_comment') {
    $comment_id = (int)$_POST['comment_id'];
    
    $stmtCheck = $pdo->prepare("SELECT user_id FROM forum_comments WHERE id = ?");
    $stmtCheck->execute([$comment_id]);
    $comment_owner = $stmtCheck->fetchColumn();
    
    if ($user_role === 'admin' || ($user_role === 'trainer' && $comment_owner == $user_id)) {
        $stmtDel = $pdo->prepare("DELETE FROM forum_comments WHERE id = ?");
        $stmtDel->execute([$comment_id]);
    }
}

header('Location: ../pages/forum.php');
exit;