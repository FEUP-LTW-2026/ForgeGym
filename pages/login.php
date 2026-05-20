<?php
session_start();
$error = '';
$success = isset($_GET['registered']) ? 'Account created successfully! Please log in.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    define('DB_PATH', __DIR__ . '/../database/forge_gym.db');
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Procurar o utilizador na base de dados
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar se o utilizador existe e se a password está correta
        if ($user && password_verify($password, $user['password'])) {
            // Guardar os dados na sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // AS NOVAS COLUNAS PARA OS 3 NÍVEIS DE ACESSO
            $_SESSION['role'] = $user['role']; 
            $_SESSION['trainer_id'] = $user['trainer_id']; 
            
            header("Location: ../index.php");
            exit;        
        } else {
            $error = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        $error = "Database error. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body style="background: radial-gradient(circle at 50% 50%, rgba(203, 160, 82, 0.1), var(--color-bg));">

  <header class="header">
    <div class="container header__inner">
      <a href="../index.php" class="logo" aria-label="FORGE GYM homepage">
        <img src="../images/logo.png" alt="FORGE GYM" class="logo__img" />
        <span class="logo__text">FORGE GYM</span>
      </a>
      <nav class="nav" id="main-nav" aria-label="Primary navigation" style="justify-content: flex-end;">
        <div class="nav__actions">
          <a href="register.php" class="btn btn--primary">Join now</a>
        </div>
      </nav>
    </div>
  </header>

  <main class="page-spacing container" style="display: flex; align-items: center; justify-content: center; min-height: 85vh;">
    <section class="card" style="width: 100%; max-width: 400px; border-top: 4px solid var(--color-gold); padding: var(--sp-8);">
      <div style="text-align: center; margin-bottom: var(--sp-6);">
        <h1 style="font-family: var(--font-display); font-size: 2.5rem; text-transform: uppercase;">Welcome <span style="color: var(--color-gold);">Back</span></h1>
        <p style="color: var(--color-text-muted);">Enter your details to access your account.</p>
      </div>

      <?php if ($success): ?>
        <div style="background: rgba(76, 175, 130, 0.2); border: 1px solid #4caf82; padding: 10px; border-radius: 4px; color: #4caf82; margin-bottom: 15px; text-align: center; font-size: 0.9rem;">
          <?php echo htmlspecialchars($success); ?>
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div style="background: rgba(254, 73, 42, 0.2); border: 1px solid var(--color-accent); padding: 10px; border-radius: 4px; color: #ff8a75; margin-bottom: 15px; text-align: center; font-size: 0.9rem;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST" class="form">
        <div class="form-group">
          <label for="username" class="form-label">Username</label>
          <input type="text" id="username" name="username" class="form-control" required placeholder="Enter username">
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" required placeholder="Enter password">
        </div>

        <button type="submit" class="btn btn--primary" style="width: 100%; margin-top: var(--sp-2);">Log In</button>
      </form>
      
      <p style="text-align: center; margin-top: var(--sp-6); font-size: 0.9rem; color: var(--color-text-muted);">
        Don't have an account? <a href="register.php" style="color: var(--color-accent); font-weight: 600;">Sign up</a>
      </p>
    </section>
  </main>

</body>
</html>