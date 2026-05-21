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

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($name && $username && $email) {
        $query = "UPDATE users SET name = ?, username = ?, email = ? WHERE id = ?";
        $params = [$name, $username, $email, $user_id];
        
        if (!empty($password)) {
            $query = "UPDATE users SET name = ?, username = ?, email = ?, password = ? WHERE id = ?";
            $params = [$name, $username, $email, password_hash($password, PASSWORD_DEFAULT), $user_id];
        }
        
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
            $fileName = $_FILES['profile_photo']['name'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($fileExtension, $allowedExtensions)) {
                $uploadDir = __DIR__ . '/../images/profiles/';
                
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
            $_SESSION['username'] = $username; 
            $success_msg = "Profile updated successfully!";
        } catch (PDOException $e) {
            $error_msg = "Username or Email already exists.";
        }
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}

// Buscar dados do utilizador
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Buscar as aulas em que o utilizador está inscrito
$stmtEnrolled = $pdo->prepare("
    SELECT c.*, t.name as trainer_name, r.rating, r.comment
    FROM enrollments e 
    JOIN classes c ON e.class_id = c.id 
    JOIN trainers t ON c.trainer_id = t.id 
    LEFT JOIN reviews r ON r.class_id = c.id AND r.user_id = e.user_id
    WHERE e.user_id = ?
    ORDER BY CASE 
        WHEN day_of_week = 'Monday' THEN 1 
        WHEN day_of_week = 'Tuesday' THEN 2 
        WHEN day_of_week = 'Wednesday' THEN 3 
        WHEN day_of_week = 'Thursday' THEN 4 
        WHEN day_of_week = 'Friday' THEN 5 
        WHEN day_of_week = 'Saturday' THEN 6 
        ELSE 7 END, start_time ASC
");
$stmtEnrolled->execute([$user_id]);
$enrolledClasses = $stmtEnrolled->fetchAll(PDO::FETCH_ASSOC);

$photoPath = $user['profile_photo'];
if ($photoPath === 'images/profiles/default.png' || !file_exists(__DIR__ . '/../' . $photoPath)) {
    $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($user['name']) . "&background=CBA052&color=fff&size=150&bold=true";
} else {
    $avatar_url = '../' . $photoPath;
}

$currentDate = date('Y-m-d');
$currentTime = date('H:i');

$upcomingSessions = [];
$workoutHistory = [];

foreach ($enrolledClasses as $class) {
    $hasEnded = false;

    // 1. Usa a data real da base de dados (YYYY-MM-DD) para saber se já passou
    if ($class['class_date'] < $currentDate) {
        $hasEnded = true; 
    } elseif ($class['class_date'] === $currentDate && $currentTime >= $class['end_time']) {
        $hasEnded = true; 
    }

    // 2. Formata a data vinda da base de dados (ex: "18/05")
    $class['formatted_date'] = date('d/m', strtotime($class['class_date']));

    if ($hasEnded) {
        $workoutHistory[] = $class;
    } else {
        $upcomingSessions[] = $class;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Profile — FORGE GYM</title>
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
          <li><a href="shop.php" class="nav__link">Shop</a></li>
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
          
          <section class="planner-card" style="padding: var(--sp-6);">
            <div class="planner-card__content" style="display: flex; flex-direction: column; gap: var(--sp-6);">
              
              <div>
                <h3 style="font-family: var(--font-display); font-size: 1.4rem; text-transform: uppercase; color: var(--color-text); margin-bottom: var(--sp-3); display: flex; align-items: center; gap: 8px;">
                  <i class="fas fa-calendar-check" style="color: var(--color-gold);"></i> Active Bookings
                </h3>
                
                <?php if (count($upcomingSessions) > 0): ?>
                  <div class="profile-scrollbar" style="display: flex; flex-direction: column; gap: 10px; max-height: 180px; overflow-y: auto; padding-right: 5px;">
                    <?php foreach ($upcomingSessions as $class): ?>
                      <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-left: 4px solid var(--color-gold); padding: 12px; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                        <div>
                          <h4 style="font-family: var(--font-display); font-size: 1.2rem; margin-bottom: 2px; text-transform: uppercase;"><?php echo htmlspecialchars($class['name']); ?></h4>
                          <p style="font-size: 0.85rem; color: var(--color-text-muted);">
                            <i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($class['day_of_week']); ?> (<?php echo $class['formatted_date']; ?>) at <?php echo htmlspecialchars($class['start_time']); ?>
                          </p>
                        </div>
                        <form action="enroll.php" method="POST" style="margin: 0;">
                          <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                          <input type="hidden" name="action" value="cancel">
                          <button type="submit" class="btn btn--ghost btn--sm" style="border-color: var(--color-accent); color: var(--color-accent); font-size: 0.75rem; padding: 5px 10px;">Cancel</button>
                        </form>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <p style="color: var(--color-text-muted); font-size: 0.9rem;">No upcoming classes booked.</p>
                <?php endif; ?>
              </div>

              <div style="border-top: 1px solid var(--color-border); padding-top: var(--sp-4);">
                <h3 style="font-family: var(--font-display); font-size: 1.4rem; text-transform: uppercase; color: var(--color-text); margin-bottom: var(--sp-3); display: flex; align-items: center; gap: 8px;">
                  <i class="fas fa-history" style="color: var(--color-gold);"></i> Workout History
                </h3>
                
                <?php if (count($workoutHistory) > 0): ?>
                  <div class="profile-scrollbar" style="display: flex; flex-direction: column; gap: 10px; max-height: 180px; overflow-y: auto; padding-right: 5px;">
                    <?php foreach ($workoutHistory as $class): ?>
                      <div style="background: rgba(0,0,0,0.2); border: 1px solid var(--color-border); border-left: 4px solid #4caf50; padding: 12px; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                        <div>
                          <h4 style="font-family: var(--font-display); font-size: 1.2rem; margin-bottom: 2px; text-transform: uppercase; color: #e0e0e0;"><?php echo htmlspecialchars($class['name']); ?></h4>
                          <p style="font-size: 0.85rem; color: var(--color-text-muted);">
                            <i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($class['day_of_week']); ?> (<?php echo $class['formatted_date']; ?>) &nbsp;|&nbsp; Coach: <?php echo htmlspecialchars($class['trainer_name']); ?>
                          </p>
                        </div>
                        
                        <div>
                          <?php if ($class['rating']): ?>
                            <span style="color: var(--color-gold); font-size: 0.9rem; font-weight: 600;">
                              <?php echo str_repeat('★', $class['rating']) . str_repeat('☆', 5 - $class['rating']); ?>
                            </span>
                          <?php else: ?>
                            <a href="review.php?id=<?php echo $class['id']; ?>" class="btn btn--ghost btn--sm" style="border-color: var(--color-gold); color: var(--color-gold); font-size: 0.75rem; padding: 5px 10px;">Leave Review</a>
                          <?php endif; ?>
                        </div>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <p style="color: var(--color-text-muted); font-size: 0.9rem;">No past workouts completed yet this week.</p>
                <?php endif; ?>
              </div>
              
              <a href="plan.php" class="button-aesthetic" style="display: inline-flex; width: fit-content; background-color: var(--color-bg-surface); border: 1px solid var(--color-border); color: var(--color-text); margin-top: var(--sp-2);">
                <i class="fas fa-calendar-plus" style="color: var(--color-gold);"></i> Book Classes
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
        <li><a href="plan.php">Plans</a></li>
        <li><a href="../index.php#reviews">Reviews</a></li>
      </ul>
      <p class="footer__address">Rua Dr. Roberto Frias, 4200-465 Porto &nbsp;|&nbsp; info@forgegym.pt</p>
    </div>
    <div class="footer__bottom">
      <p>&copy; 2026 FORGE GYM. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>