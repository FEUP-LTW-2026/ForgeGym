<?php
session_start();

// 1. BARREIRA DE SEGURANÇA GLOBAL
// Se não tiver login feito, ou se for um user normal, salta fora logo aqui.
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'trainer')) {
    header('Location: ../pages/plan.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_id = $_POST['class_id'] ?? null;

    if ($class_id) {
        define('DB_PATH', __DIR__ . '/../database/forge_gym.db');
        
        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // 2. Ir buscar a aula à BD para sabermos de quem ela é
            $stmt = $pdo->prepare("SELECT trainer_id FROM classes WHERE id = ?");
            $stmt->execute([$class_id]);
            $class = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($class) {
                $userRole = $_SESSION['role'];
                $userTrainerId = $_SESSION['trainer_id'] ?? null;

                // 3. VERIFICAÇÃO DE AUTORIZAÇÃO (RBAC)
                $canDelete = false;
                if ($userRole === 'admin') {
                    $canDelete = true; // Admin manda nisto tudo
                } elseif ($userRole === 'trainer' && $userTrainerId == $class['trainer_id']) {
                    $canDelete = true; // Trainer só apaga se a aula for dele
                }

                // 4. EXECUÇÃO
                if ($canDelete) {
                    // Boa prática: Apagar primeiro as inscrições e reviews associadas a esta aula 
                    // para a base de dados não dar erro de conflito
                    $pdo->prepare("DELETE FROM enrollments WHERE class_id = ?")->execute([$class_id]);
                    $pdo->prepare("DELETE FROM reviews WHERE class_id = ?")->execute([$class_id]);

                    // Finalmente, apagar a aula
                    $stmtDel = $pdo->prepare("DELETE FROM classes WHERE id = ?");
                    $stmtDel->execute([$class_id]);
                }
            }
        } catch (PDOException $e) {
            // Em caso de erro na base de dados, ignoramos e deixamos o redirecionamento acontecer
            error_log("Erro ao apagar aula: " . $e->getMessage());
        }
    }
}

// 5. REDIRECIONAMENTO
// Quer tenha apagado com sucesso ou falhado por falta de permissões, 
// recambiamos a pessoa de volta para a página das aulas.
header('Location: ../pages/plan.php');
exit;
?>