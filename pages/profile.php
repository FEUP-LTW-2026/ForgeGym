<?php
session_start();

// Se o utilizador não estiver logado, é mandado para o login
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
$success_msg = '';
$error_msg = '';

// Processar a edição do perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($name && $username && $email) {
        $query = "UPDATE users SET name = ?, username = ?, email = ? WHERE id = ?";
        $params = [$name, $username, $email, $user_id];
        
        // Se preencheu uma nova password, atualiza o hash
        if (!empty($password)) {
            $query = "UPDATE users SET name = ?, username = ?, email = ?, password = ? WHERE id = ?";
            $params = [$name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $user_id];
        }
        
        // Processar o upload da foto de perfil
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
            $fileName = $_FILES['profile_photo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadDir = __DIR__ . '/../images/profiles/';
                
                // Garantir que a pasta existe
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $newFileName = 'user_' . $user_id . '_' . time() . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $photo_path = 'images/profiles/' . $newFileName;
                    
                    if (!empty($password)) {
                        $query = "UPDATE users SET name = ?, username = ?, email = ?, password = ?, profile_photo = ? WHERE id = ?";
                        $params = [$name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $photo_path, $user_id];
                    } else {
                        $query = "UPDATE users SET name = ?, username = ?, email = ?, profile_photo = ? WHERE id = ?";
                        $params = [$name, $username, $email, $photo_path, $user_id];
                    }
                }
            }
        }
        
        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $_SESSION['username'] = $username; // Atualiza o nome na sessão
            $success_msg = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error_msg = "Username or Email already exists.";
        }
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}

// Buscar os dados atualizados do utilizador
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$photoPath = $user['profile_photo'];
// Verifica se é o caminho padrão da base de dados OU se o ficheiro físico não existe
if ($photoPath === 'images/profiles/default.png' || !file_exists(__DIR__ . '/../' . $photoPath)) {
    // Gera um avatar com as iniciais do nome, fundo cor-de-laranja e letras brancas
    $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($user['name']) . "&background=CBA052&color=fff&size=150&bold=true";
} else {
    $avatar_url = '../' . $photoPath;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="icon" type="image/png" href="../images/logo16x16.png" />
  <link rel="icon" type="image/png" href="../images/logo-appletouch.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

  <header class="header">
    <div class="container header__inner">
      <a href="../index.php" class="logo" aria-label="FORGE GYM homepage">
        <img src="../images/logo.png" alt="FORGE GYM" class="logo__img" />
        <span class="logo__text">FORGE GYM</span>
      </a>
      <input type="checkbox" id="nav-toggle" class="sr-only peer-nav" />
      <label for="nav-toggle" class="nav__toggle" aria-label="Open navigation menu">
        <span></span><span></span><span></span>
      </label>
      <nav class="nav" id="main-nav" aria-label="Primary navigation">
        <ul class="nav__list">
          <li><a href="plan.php" class="nav__link">Classes</a></li>
          <li><a href="profile.php" class="nav__link">Profile</a></li>
        </ul>
        <div class="nav__actions">
          <a href="profile.php" class="btn btn--ghost">Profile</a>
          <a href="logout.php" class="btn btn--primary">Log out</a>
        </div>
      </nav>
    </div>
  </header>

  <main class="profile-page">
    <div class="container">
      <header class="page-header">
        <p class="eyebrow">Account Overview</p>
        <h1 class="page-title">YOUR <span class="page-title--outline">PROFILE</span></h1>
      </header>

      <div class="profile-grid">
        <aside class="profile-sidebar">
          <section class="profile-card">
            <div class="profile-avatar-wrapper">
              <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>" class="profile-avatar-img">
            </div>
            <h2 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h2>
            <p class="user-handle">@<?php echo htmlspecialchars($user['username']); ?></p>
          </section>

          <section class="qr-card">
            <h3 class="qr-card__title">Gym Access</h3>
            <p class="qr-card__desc">Scan this code at the entrance to enter the forge.</p>
            <div class="qr-code-wrapper">
              <img src="../images/profiles/qrtherock.png" alt="Access QR Code" class="qr-img">
            </div>
            <p class="qr-card__id">ID: FORGE-<?php echo str_pad($user['id'], 4, '0', STR_PAD_LEFT); ?>-2026</p>
          </section>
        </aside>

        <div class="profile-main-content">
          <section class="planner-card">
            <div class="planner-card__content">
              <h3>Next Session</h3>
              <p>Ready to push your limits? Check out the active schedules.</p>
              <a href="plan.php" class="button-aesthetic">
                <i class="fas fa-calendar-alt"></i> Open Workout Planner
              </a>
            </div>
          </section>

          <section class="info-card">
            <h3>Account Settings</h3>
            
            <?php if ($success_msg): ?>
              <div style="background: rgba(76, 175, 130, 0.2); border: 1px solid #4caf82; padding: 10px; border-radius: 4px; color: #4caf82; margin-bottom: 20px; text-align: center; font-size: 0.9rem;">
                <?php echo htmlspecialchars($success_msg); ?>
              </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
              <div style="background: rgba(254, 73, 42, 0.2); border: 1px solid var(--color-accent); padding: 10px; border-radius: 4px; color: #ff8a75; margin-bottom: 20px; text-align: center; font-size: 0.9rem;">
                <?php echo htmlspecialchars($error_msg); ?>
              </div>
            <?php endif; ?>

            <form action="profile.php" method="POST" enctype="multipart/form-data" class="form">
              <div class="profile-details-grid">
                <div class="form-group">
                  <label class="form-label" for="name">Full Name</label>
                  <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="username">Username</label>
                  <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="email">Email Address</label>
                  <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label" for="profile_photo">Change Profile Photo</label>
                  <input type="file" id="profile_photo" name="profile_photo" class="form-control" style="padding: 6px;">
                </div>
              </div>

              <div class="password-section">
                <h3>Security</h3>
                <div class="form-group">
                  <label class="form-label" for="password">New Password (leave blank to keep current)</label>
                  <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••">
                </div>
              </div>

              <div style="margin-top: var(--sp-6); display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn--primary">Save Changes</button>
              </div>
            </form>
          </section>
        </div>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="container footer__inner">
      
      <a href="../index.php" class="logo footer__logo" aria-label="FORGE GYM homepage">
        <img src="../images/logo.png" alt="FORGE GYM" class="logo__img" />
      </a>

      <ul class="footer__nav">
        <li><a href="../index.php">Home</a></li>
        <li><a href="../index.php#equipment">Equipment</a></li>
        <li><a href="../index.php#plans">Plans</a></li>
        <li><a href="../index.php#reviews">Reviews</a></li>
      </ul>

      <p class="footer__address">
        Rua Dr. Roberto Frias, 4200-465 Porto &nbsp;|&nbsp; info@forgegym.pt
      </p>

    </div>
    
    <div class="footer__bottom">
      <p>&copy; 2026 FORGE GYM. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>