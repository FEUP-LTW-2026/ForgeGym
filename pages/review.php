<?php
session_start();
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

$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Buscar detalhes da aula para o título
$stmtClass = $pdo->prepare("SELECT c.name, t.name as trainer_name FROM classes c JOIN trainers t ON c.trainer_id = t.id WHERE c.id = ?");
$stmtClass->execute([$class_id]);
$class = $stmtClass->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    header("Location: profile.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
    $comment = trim($_POST['comment'] ?? '');

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        try {
            // INSERT OR REPLACE garante que se a pessoa já tiver feito review, atualiza a antiga
            $stmtInsert = $pdo->prepare("INSERT OR REPLACE INTO reviews (user_id, class_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$user_id, $class_id, $rating, $comment]);
            $success = "Review submitted successfully! Redirecting back to profile...";
            header("refresh:2;url=profile.php"); // Espera 2s e manda-te de volta ao perfil
        } catch (PDOException $e) {
            $error = "Error submitting review.";
        }
    } else {
        $error = "Please provide a rating and a comment.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Leave a Review — FORGE GYM</title>
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
          <a href="shop.php?open_cart=1" class="nav__cart-btn" aria-label="Ir para o carrinho">
            <i class="fas fa-shopping-cart"></i>
          </a>
          <a href="profile.php" class="btn btn--ghost">Profile</a>
          <a href="logout.php" class="btn btn--primary">Log out</a>
        </div>
      </nav>
    </div>
  </header>

  <main class="page-spacing container" style="display: flex; align-items: center; justify-content: center; min-height: 85vh;">
    <section class="card" style="width: 100%; max-width: 500px; border-top: 4px solid var(--color-gold); padding: var(--sp-8);">
      <div style="text-align: center; margin-bottom: var(--sp-6);">
        <h1 style="font-family: var(--font-display); font-size: 2.5rem; text-transform: uppercase;">Rate your <span style="color: var(--color-gold);">Experience</span></h1>
        <p style="color: var(--color-text-muted);">How was <strong><?php echo htmlspecialchars($class['name']); ?></strong> with <?php echo htmlspecialchars($class['trainer_name']); ?>?</p>
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

      <?php if (!$success): ?>
        <form action="review.php?id=<?php echo $class_id; ?>" method="POST" class="form">
          <div class="form-group">
            <label for="rating" class="form-label">Rating</label>
            <select name="rating" id="rating" class="form-control" style="background: rgba(0,0,0,0.3); color: white;" required>
                <option value="5">★★★★★ (5/5) - Excellent</option>
                <option value="4">★★★★☆ (4/5) - Very Good</option>
                <option value="3">★★★☆☆ (3/5) - Good</option>
                <option value="2">★★☆☆☆ (2/5) - Fair</option>
                <option value="1">★☆☆☆☆ (1/5) - Poor</option>
            </select>
          </div>

          <div class="form-group">
            <label for="comment" class="form-label">Your Review</label>
            <textarea id="comment" name="comment" class="form-control" rows="4" style="background: rgba(0,0,0,0.3); color: white;" required placeholder="Tell us what you liked about this class..."></textarea>
          </div>

          <button type="submit" class="btn btn--primary" style="width: 100%; margin-top: var(--sp-2);">Submit Review</button>
          <a href="profile.php" class="btn btn--ghost" style="width: 100%; text-align: center;">Cancel</a>
        </form>
      <?php endif; ?>
    </section>
  </main>

</body>
</html>