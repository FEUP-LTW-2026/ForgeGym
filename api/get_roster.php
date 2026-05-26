<?php
// Garantir que a base de dados é a correta
define('DB_PATH', __DIR__ . '/../database/forge_gym.db');

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $class_id = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

    // Query robusta para ir buscar os dados dos utilizadores
    $stmt = $pdo->prepare("
        SELECT e.id as enrollment_id, u.id as user_id, u.name, u.email, u.profile_photo 
        FROM users u 
        JOIN enrollments e ON u.id = e.user_id 
        WHERE e.class_id = ?
    ");
    $stmt->execute([$class_id]);
    $roster = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($roster);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>