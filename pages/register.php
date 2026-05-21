<?php
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    define('DB_PATH', __DIR__ . '/../database/forge_gym.db');
    try {
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if ($name && $username && $email && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role) VALUES (?, ?, ?, ?, 'member')");
            try {
                $stmt->execute([$name, $username, $email, $hash]);
                header("Location: login.php?registered=1");
                exit;
            } catch (PDOException $e) {
                // AGORA MOSTRAMOS O ERRO REAL DA BASE DE DADOS!
                $error = "DB ERROR: " . $e->getMessage();
            }
        } else {
            $error = "Please fill in all the fields.";
        }
    } catch (PDOException $e) {
        $error = "Connection error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Join Now — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body style="background: radial-gradient(circle at 50% 50%, rgba(254, 73, 42, 0.1), var(--color-bg));">

  <header class="header">
    <div class="container header__inner">
      <a href="../index.php" class="logo" aria-label="FORGE GYM homepage">
        <img src="../images/logo.png" alt="FORGE GYM" class="logo__img" />
        <span class="logo__text">FORGE GYM</span>
      </a>
      <nav class="nav" id="main-nav" aria-label="Primary navigation" style="justify-content: flex-end;">
        <div class="nav__actions">
          <a href="login.php" class="btn btn--ghost">Log in</a>
        </div>
      </nav>
    </div>
  </header>

  <main class="page-spacing container" style="display: flex; align-items: center; justify-content: center; min-height: 85vh;">
    <section class="card" style="width: 100%; max-width: 450px; border-top: 4px solid var(--color-accent); padding: var(--sp-8);">
      <div style="text-align: center; margin-bottom: var(--sp-6);">
        <h1 style="font-family: var(--font-display); font-size: 2.5rem; text-transform: uppercase;">Join the <span style="color: var(--color-accent);">Forge</span></h1>
        <p style="color: var(--color-text-muted);">Create your account to book classes.</p>
      </div>

      <?php if ($error): ?>
        <div style="background: rgba(254, 73, 42, 0.2); border: 1px solid var(--color-accent); padding: 10px; border-radius: 4px; color: #ff8a75; margin-bottom: 15px; text-align: center; font-size: 0.9rem;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form action="register.php" method="POST" class="form">
        <div class="form-group">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" id="name" name="name" class="form-control" required placeholder="John Doe">
        </div>

        <div class="form-group">
          <label for="username" class="form-label">Username</label>
          <input type="text" id="username" name="username" class="form-control" required placeholder="johndoe">
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" id="email" name="email" class="form-control" required placeholder="john@forgegym.pt">
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn--primary" style="width: 100%; margin-top: var(--sp-2);">Create Account</button>
      </form>
      
      <p style="text-align: center; margin-top: var(--sp-6); font-size: 0.9rem; color: var(--color-text-muted);">
        Already have an account? <a href="login.php" style="color: var(--color-gold); font-weight: 600;">Log in here</a>
      </p>
    </section>
  </main>

</body>
</html>