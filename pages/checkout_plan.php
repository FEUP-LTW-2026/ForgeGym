<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['plan'])) {
    $plan = $_GET['plan'];
    $valid_plans = ['Basic', 'Classic', 'Premium'];
    
    if (in_array($plan, $valid_plans)) {
        try {
            $pdo = new PDO('sqlite:' . __DIR__ . '/../database/forge_gym.db');
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("UPDATE users SET plan = ? WHERE id = ?");
            $stmt->execute([$plan, $_SESSION['user_id']]);
        } catch (PDOException $e) {
            die("Database error.");
        }
    }
}
header("Location: profile.php");
exit;