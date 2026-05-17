<?php
define('DB_PATH', __DIR__ . '/../database/forge_gym.db');

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed.");
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$stmt = $pdo->prepare("SELECT * FROM trainers WHERE id = ?");
$stmt->execute([$id]);
$trainer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trainer) {
    die("Trainer not found.");
}

$currentDate = date('Y-m-d');
$currentTime = date('H:i');

$stmtClasses = $pdo->prepare("
    SELECT * FROM classes 
    WHERE trainer_id = ? 
    AND (class_date > ? OR (class_date = ? AND end_time > ?))
    ORDER BY class_date ASC, start_time ASC
");
$stmtClasses->execute([$id, $currentDate, $currentDate, $currentTime]);
$classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

$stmtReviews = $pdo->prepare("
    SELECT r.rating, r.comment, r.created_at, u.name as user_name, u.profile_photo, c.name as class_name
    FROM reviews r
    JOIN classes c ON r.class_id = c.id
    JOIN users u ON r.user_id = u.id
    WHERE c.trainer_id = ?
    ORDER BY r.created_at DESC
");
$stmtReviews->execute([$id]);
$reviews = $stmtReviews->fetchAll(PDO::FETCH_ASSOC);

$specializations = explode(',', $trainer['specializations']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($trainer['name']); ?> — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .profile-scrollbar::-webkit-scrollbar {
      width: 6px;
    }
    .profile-scrollbar::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.02); 
      border-radius: 10px;
    }
    .profile-scrollbar::-webkit-scrollbar-thumb {
      background: var(--color-gold); 
      border-radius: 10px;
    }
    .profile-scrollbar::-webkit-scrollbar-thumb:hover {
      background: #e6b800; 
    }
  </style>
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
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="btn btn--ghost">Profile</a>
                <a href="logout.php" class="btn btn--primary">Log out</a>
            <?php else: ?>
                <a href="login.php" class="btn btn--ghost">Log in</a>
                <a href="register.php" class="btn btn--primary">Join now</a>
            <?php endif; ?>
        </div>
      </nav>
    </div>
  </header>

  <main class="profile-page page-spacing">
    <div class="container">
      
      <header class="page-header" style="margin-bottom: var(--sp-6);">
        <p class="eyebrow">Trainer Profile</p>
        <h1 class="page-title"><?php echo strtoupper(htmlspecialchars($trainer['name'])); ?></h1>
      </header>

      <div style="display: flex; flex-direction: column; gap: var(--sp-6);">
        
        <section class="info-card" style="border-top-color: var(--color-gold); padding: var(--sp-8);">
          
          <div style="display: flex; flex-direction: row; flex-wrap: wrap; gap: var(--sp-10); align-items: stretch;">
            
            <div style="flex: 0 0 320px; max-width: 320px; display: flex; flex-direction: column; align-items: center; text-align: center;">
              <div style="width: 100%; aspect-ratio: 3/4; border-radius: var(--radius-md); overflow: hidden; margin-bottom: var(--sp-4); border: 2px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                <img src="../<?php echo htmlspecialchars($trainer['image_path']); ?>" alt="<?php echo htmlspecialchars($trainer['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
              </div>
              
              <h2 class="profile-name" style="margin-bottom: 4px; font-size: var(--text-2xl);"><?php echo htmlspecialchars($trainer['name']); ?></h2>
              <p style="color: var(--color-accent); font-weight: 700; text-transform: uppercase; font-size: var(--text-xs); letter-spacing: 0.1em; margin-bottom: var(--sp-4);">
                <?php echo htmlspecialchars($trainer['role']); ?>
              </p>

              <div class="trainer-tags" style="justify-content: center; display: flex; flex-wrap: wrap; gap: 8px;">
                <?php foreach ($specializations as $spec): ?>
                  <span class="badge badge--dark"><?php echo htmlspecialchars(trim($spec)); ?></span>
                <?php endforeach; ?>
              </div>
            </div>

            <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; gap: var(--sp-8); justify-content: center;">
              
              <div style="display: flex; gap: var(--sp-4); align-items: flex-start;">
                <i class="fas fa-quote-left" style="color: var(--color-gold); font-size: 3rem; opacity: 0.6; margin-top: -5px;"></i>
                <div>
                  <p style="font-family: var(--font-display); font-size: 1.8rem; line-height: 1.3; color: var(--color-text); font-style: italic;">
                    <?php echo htmlspecialchars($trainer['quote']); ?>
                  </p>
                  <p style="color: var(--color-text-muted); margin-top: var(--sp-3); font-size: var(--text-sm); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                    — Message from <?php echo htmlspecialchars(explode(' ', trim($trainer['name']))[0]); ?>
                  </p>
                </div>
              </div>

              <hr style="border: 0; border-top: 1px solid var(--color-border); width: 100%; margin: 0;">

              <div>
                <h3 style="margin-bottom: var(--sp-3); font-size: 1.3rem; text-transform: uppercase; color: var(--color-text);">
                   About & Specialties
                </h3>
                <p style="color: var(--color-text-muted); font-size: 1.1rem; line-height: 1.8;">
                  <?php echo htmlspecialchars($trainer['bio']); ?>
                </p>
                
                <div style="margin-top: var(--sp-4);">
                  <p style="color: var(--color-text); font-weight: 600; margin-bottom: var(--sp-2);">Core Training Focus:</p>
                  <ul style="color: var(--color-text-muted); padding-left: 1.5rem; list-style-type: disc;">
                    <?php foreach ($specializations as $spec): ?>
                      <li style="margin-bottom: 5px;"><?php echo htmlspecialchars(trim($spec)); ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              </div>
              
            </div>
            
          </div>
        </section>

        <section class="info-card">
          <h3><i class="fas fa-calendar-alt" style="color: var(--color-gold); margin-right: 8px;"></i> Current Classes</h3>
          <div class="u-margin-top-md">
            <?php if (count($classes) > 0): ?>
              <div class="schedule-grid">
                <?php foreach ($classes as $class): ?>
                  <article class="class-card">
                    <div class="class-card__time">
                      <span class="time-start"><?php echo htmlspecialchars($class['start_time'] ?? '--:--'); ?></span>
                      <span class="time-end"><?php echo htmlspecialchars($class['end_time'] ?? '--:--'); ?></span>
                    </div>
                    <div class="class-card__info">
                      <h2 class="class-title"><?php echo htmlspecialchars($class['name']); ?></h2>
                      <p class="class-trainer"><?php echo htmlspecialchars($class['day_of_week']); ?> (<?php echo date('d/m', strtotime($class['class_date'])); ?>)</p>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div style="background: rgba(255,255,255,0.02); padding: var(--sp-6); border-radius: var(--radius-sm); text-align: center; border: 1px dashed var(--color-border);">
                <p class="text-muted">This trainer doesn't have any classes assigned right now.</p>
              </div>
            <?php endif; ?>
          </div>
        </section>

        <section class="info-card">
          <h3><i class="fas fa-star" style="color: var(--color-gold); margin-right: 8px;"></i> Member Reviews</h3>
          <div class="u-margin-top-md">
            <?php if (count($reviews) > 0): ?>
              <div class="profile-scrollbar" style="display: flex; flex-direction: column; gap: var(--sp-4); max-height: 240px; overflow-y: auto; padding-right: 8px;">
                <?php foreach ($reviews as $review): 
                    $photoPath = $review['profile_photo'];
                    if ($photoPath === 'images/profiles/default.png' || !file_exists(__DIR__ . '/../' . $photoPath)) {
                        $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($review['user_name']) . "&background=CBA052&color=fff&size=100&bold=true";
                    } else {
                        $avatar_url = '../' . $photoPath;
                    }
                ?>
                  <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-left: 4px solid var(--color-gold); padding: 15px; border-radius: var(--radius-sm); display: flex; gap: 15px; align-items: flex-start;">
                    
                    <img src="<?php echo htmlspecialchars($avatar_url); ?>" alt="<?php echo htmlspecialchars($review['user_name']); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-bg);">
                    
                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 5px;">
                            <h4 style="font-family: var(--font-display); font-size: 1.2rem; color: var(--color-text); text-transform: uppercase; margin: 0;">
                                <?php echo htmlspecialchars($review['user_name']); ?>
                                <span style="font-size: 0.85rem; color: var(--color-text-muted); font-family: var(--font-body); text-transform: none; margin-left: 8px; font-weight: normal;">
                                    in <strong style="color: var(--color-text);"><?php echo htmlspecialchars($review['class_name']); ?></strong>
                                </span>
                            </h4>
                            <span style="color: var(--color-gold); font-size: 0.9rem;">
                                <?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?>
                            </span>
                        </div>
                        <p style="color: var(--color-text-muted); font-size: 0.95rem; font-style: italic; line-height: 1.5; margin: 0;">
                            "<?php echo htmlspecialchars($review['comment']); ?>"
                        </p>
                    </div>

                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div style="background: rgba(255,255,255,0.02); padding: var(--sp-6); border-radius: var(--radius-sm); text-align: center; border: 1px dashed var(--color-border);">
                <p class="text-muted">No reviews yet for this trainer's classes.</p>
              </div>
            <?php endif; ?>
          </div>
        </section>

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
        <li><a href="plan.php">Classes</a></li>
      </ul>
      <p class="footer__address">Rua Dr. Roberto Frias, 4200-465 Porto &nbsp;|&nbsp; info@forgegym.pt</p>
    </div>
    <div class="footer__bottom">
      <p>&copy; 2026 FORGE GYM. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>