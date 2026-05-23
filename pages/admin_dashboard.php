<?php
session_start();

// 1. BARREIRA DE SEGURANÇA MÁXIMA (Só Admins entram aqui)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

define('DB_PATH', __DIR__ . '/../database/forge_gym.db');
$success_msg = '';
$error_msg = '';

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 2. PROCESSAR ALTERAÇÕES DO FORMULÁRIO (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $target_user_id = $_POST['user_id'] ?? null;
        $action = $_POST['action'] ?? '';

        if ($target_user_id && $target_user_id != $_SESSION['user_id']) {
            
            // AÇÃO: ATUALIZAR CARGO
            if ($action === 'update_role') {
                $new_role = $_POST['role'] ?? 'user';

                // Ir buscar os dados ATUAIS antes de atualizar
                $stmtCurrent = $pdo->prepare("SELECT name, role, trainer_id FROM users WHERE id = ?");
                $stmtCurrent->execute([$target_user_id]);
                $currentData = $stmtCurrent->fetch(PDO::FETCH_ASSOC);
                
                $user_name = $currentData['name'];
                $old_role = $currentData['role'] ?? 'user';
                $old_trainer_id = $currentData['trainer_id'] ?? null;
                
                $final_trainer_id = $old_trainer_id; // Por defeito, mantém o que já tinha

                // Lógica Inteligente de Transição
                if ($new_role === 'trainer' && $old_role !== 'trainer') {
                    // Ver se já existe um perfil com este nome na tabela trainers (para reutilizar o ID antigo)
                    $stmtCheckTrainer = $pdo->prepare("SELECT id FROM trainers WHERE name = ?");
                    $stmtCheckTrainer->execute([$user_name]);
                    $existing_trainer_id = $stmtCheckTrainer->fetchColumn();

                    if ($existing_trainer_id) {
                        // Reutiliza o perfil existente
                        $final_trainer_id = $existing_trainer_id;
                    } else {
                        // Se for mesmo a primeira vez deste nome, cria um perfil novo do zero
                        $stmtInsertTrainer = $pdo->prepare("
                            INSERT INTO trainers (name, role, bio, specializations, quote, image_path) 
                            VALUES (?, 'Fitness Coach', 'Bio under construction...', 'General Fitness', 'Let''s train!', 'images/personal trainers/pt-quaresma.jpeg')
                        ");
                        $stmtInsertTrainer->execute([$user_name]);
                        $final_trainer_id = $pdo->lastInsertId();
                    }
                } 
                elseif ($new_role !== 'trainer' && $old_role === 'trainer' && !empty($old_trainer_id)) {
                    $final_trainer_id = null;
                }
                elseif ($new_role !== 'trainer') {
                    $final_trainer_id = null;
                }

                // Guardar as novas permissões do utilizador
                $stmt = $pdo->prepare("UPDATE users SET role = ?, trainer_id = ? WHERE id = ?");
                $stmt->execute([$new_role, $final_trainer_id, $target_user_id]);
                $success_msg = "Permissions updated! The system automatically managed the profile.";
                
            } 
            
            // AÇÃO: APAGAR UTILIZADOR
            elseif ($action === 'delete') {
                $old_trainer_id = $pdo->query("SELECT trainer_id FROM users WHERE id = $target_user_id")->fetchColumn();

                if ($old_trainer_id) {
                    $pdo->prepare("UPDATE classes SET trainer_id = NULL WHERE trainer_id = ?")->execute([$old_trainer_id]);
                    $pdo->prepare("DELETE FROM trainers WHERE id = ?")->execute([$old_trainer_id]);
                }

                $pdo->prepare("DELETE FROM enrollments WHERE user_id = ?")->execute([$target_user_id]);
                $pdo->prepare("DELETE FROM reviews WHERE user_id = ?")->execute([$target_user_id]);
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$target_user_id]);
                $success_msg = "Account and profiles permanently deleted from the system.";
            }

        } else {
            $error_msg = "Security alert: You cannot change your own active admin account.";
        }
    }

    // 3. IR BUSCAR TODOS OS UTILIZADORES
    $users = $pdo->query("SELECT id, name, username, email, role, trainer_id FROM users ORDER BY role ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: var(--color-bg);">

  <header class="header">
    <div class="container header__inner">
      <a href="../index.php" class="logo" aria-label="FORGE GYM homepage">
        <img src="../images/logo.png" alt="FORGE GYM" class="logo__img" />
        <span class="logo__text">FORGE GYM <span style="color: var(--color-accent); font-size: 0.8rem;">ADMIN</span></span>
      </a>
      <nav class="nav" id="main-nav" style="justify-content: flex-end;">
        <div class="nav__actions">
          <a href="profile.php" class="btn btn--ghost"><i class="fas fa-arrow-left"></i> Back to Profile</a>
        </div>
      </nav>
    </div>
  </header>

  <main class="page-spacing container" style="min-height: 85vh; padding-top: 2rem;">
    <header class="page-header" style="text-align: left; margin-bottom: var(--sp-6);">
      <p class="eyebrow" style="color: var(--color-accent);">System Control</p>
      <h1 class="page-title">USER <span class="page-title--outline">MANAGEMENT</span></h1>
    </header>

    <?php if ($success_msg): ?>
      <div style="background: rgba(76, 175, 130, 0.2); border: 1px solid #4caf82; padding: 15px; border-radius: 4px; color: #4caf82; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
      </div>
    <?php endif; ?>
    
    <?php if ($error_msg): ?>
      <div style="background: rgba(254, 73, 42, 0.2); border: 1px solid var(--color-accent); padding: 15px; border-radius: 4px; color: #ff8a75; margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
      </div>
    <?php endif; ?>

    <section class="card" style="border-top: 4px solid var(--color-accent); overflow-x: auto;">
      <table style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
          <tr style="border-bottom: 1px solid var(--color-border); color: var(--color-gold);">
            <th style="padding: 15px 10px;">ID</th>
            <th style="padding: 15px 10px;">Name</th>
            <th style="padding: 15px 10px;">Username</th>
            <th style="padding: 15px 10px;">Current Role</th>
            <th style="padding: 15px 10px; text-align: right;">Manage Permissions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
              <td style="padding: 15px 10px; color: var(--color-text-muted);">#<?php echo $u['id']; ?></td>
              <td style="padding: 15px 10px; font-weight: 600;"><?php echo htmlspecialchars($u['name']); ?></td>
              <td style="padding: 15px 10px; color: var(--color-text-muted);">@<?php echo htmlspecialchars($u['username']); ?></td>
              <td style="padding: 15px 10px;">
                <?php if ($u['role'] === 'admin'): ?>
                    <span class="badge" style="background: var(--color-accent); color: white;">Admin</span>
                <?php elseif ($u['role'] === 'trainer'): ?>
                    <span class="badge" style="background: var(--color-gold); color: black;">Trainer</span>
                <?php else: ?>
                    <span class="badge badge--dark">User</span>
                <?php endif; ?>
              </td>
              <td style="padding: 15px 10px; text-align: right;">
                
                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                    <div style="display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
                        
                        <form action="admin_dashboard.php" method="POST" style="margin: 0; display: flex; gap: 8px; align-items: center;">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            
                            <select name="role" class="form-control" style="padding: 5px 10px; background: rgba(0,0,0,0.4); color: white; border: 1px solid var(--color-border); font-size: 0.85rem; border-radius: 4px;">
                                <option value="user" <?php echo $u['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="trainer" <?php echo $u['role'] === 'trainer' ? 'selected' : ''; ?>>Trainer</option>
                                <option value="admin" <?php echo $u['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>

                            <button type="submit" name="action" value="update_role" class="btn btn--primary btn--sm" style="padding: 5px 12px; font-size: 0.8rem; background: var(--color-gold); border-color: var(--color-gold); color: black; font-weight: bold;">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </form>

                        <form action="admin_dashboard.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to permanently delete this account?');">
                            <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                            <button type="submit" name="action" value="delete" class="btn btn--ghost btn--sm" style="border-color: var(--color-accent); color: var(--color-accent); padding: 5px 10px; font-size: 0.8rem;" title="Delete User">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>

                    </div>
                <?php else: ?>
                    <span style="color: var(--color-text-muted); font-size: 0.85rem; font-style: italic; padding-right: 15px;">(Your Session)</span>
                <?php endif; ?>
                
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>

</body>
</html>