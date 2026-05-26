<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'trainer') {
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
$trainer_id = $_SESSION['trainer_id'] ?? 0;

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        $bio = trim($_POST['bio'] ?? '');
        $specializations = trim($_POST['specializations'] ?? '');
        $quote = trim($_POST['quote'] ?? '');
        
        if ($bio && $specializations) {
            $stmt = $pdo->prepare("UPDATE trainers SET bio = ?, specializations = ?, quote = ? WHERE id = ?");
            $stmt->execute([$bio, $specializations, $quote, $trainer_id]);
            $success_msg = "Public profile updated successfully!";
        } else {
            $error_msg = "Biography and Specializations are required.";
        }
    }
    
    if ($_POST['action'] === 'create_class') {
        $name = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $class_date = $_POST['class_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $capacity = (int)($_POST['capacity'] ?? 10);
        
        if ($name && $type && $class_date && $start_time && $end_time && $capacity > 0) {
            $day_of_week = date('l', strtotime($class_date));
            $stmt = $pdo->prepare("
                INSERT INTO classes (name, type, trainer_id, class_date, day_of_week, start_time, end_time, capacity) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $type, $trainer_id, $class_date, $day_of_week, $start_time, $end_time, $capacity]);
            $success_msg = "New class '$name' created successfully!";
        } else {
            $error_msg = "Please fill in all fields correctly.";
        }
    }

    if ($_POST['action'] === 'remove_student') {
        $enrollment_id = (int)$_POST['enrollment_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
            $stmt->execute([$enrollment_id]);
            $success_msg = "Member successfully removed from the class!";
        } catch (PDOException $e) {
            $error_msg = "Failed to remove member: " . $e->getMessage();
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM trainers WHERE id = ?");
$stmt->execute([$trainer_id]);
$trainer = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtClasses = $pdo->prepare("SELECT * FROM classes WHERE trainer_id = ? ORDER BY class_date DESC, start_time DESC");
$stmtClasses->execute([$trainer_id]);
$myClasses = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);

$currentDate = date('Y-m-d');
$currentTime = date('H:i');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trainer Portal — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .trainer-grid { display: grid; grid-template-columns: 1fr; gap: var(--sp-6); }
    @media (min-width: 992px) { .trainer-grid { grid-template-columns: 320px 1fr; } }
    .roster-list { display: flex; flex-direction: column; gap: 8px; margin-top: var(--sp-3); background: rgba(0,0,0,0.2); padding: 10px; border-radius: var(--radius-sm); max-height: 200px; overflow-y: auto; }
    .roster-item { display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; background: rgba(255,255,255,0.02); border: 1px solid var(--color-border); border-radius: var(--radius-sm); }
    .roster-user { display: flex; align-items: center; gap: 10px; }
    .roster-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--color-gold); }
    .profile-scrollbar::-webkit-scrollbar { width: 6px; }
    .profile-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.02); }
    .profile-scrollbar::-webkit-scrollbar-thumb { background: var(--color-gold); border-radius: 10px; }
  </style>
</head>
<body style="background: var(--color-bg); color: white;">

  <header class="header">
    <div class="container header__inner">
      <a href="../index.php" class="logo">
        <img src="../images/logo.png" alt="FORGE GYM" class="logo__img" />
        <span class="logo__text">FORGE GYM</span>
      </a>
      <nav class="nav">
        <ul class="nav__list">
          <li><a href="plan.php" class="nav__link">Classes</a></li>
          <li><a href="shop.php" class="nav__link">Shop</a></li>
        </ul>
        <div class="nav__actions">
          <a href="trainer_dashboard.php" class="btn btn--ghost">Trainer Portal</a>
          <a href="logout.php" class="btn btn--primary">Log out</a>
        </div>
      </nav>
    </div>
  </header>

  <main class="profile-page page-spacing">
    <div class="container">
      
      <header class="page-header">
        <p class="eyebrow">Coach Workspace</p>
        <h1 class="page-title">TRAINER <span class="page-title--outline">PORTAL</span></h1>
      </header>

      <div class="trainer-grid">
        
        <aside class="profile-sidebar">
          <section class="profile-card">
            <div style="width: 100%; aspect-ratio: 3/4; border-radius: var(--radius-md); overflow:hidden; border: 1px solid var(--color-border);">
              <img src="../<?php echo htmlspecialchars($trainer['image_path']); ?>" alt="<?php echo htmlspecialchars($trainer['name']); ?>" style="width:100%; height:100%; object-fit:cover;">
            </div>
            <h2 class="profile-name" style="margin-top: var(--sp-3);"><?php echo htmlspecialchars($trainer['name']); ?></h2>
            <p style="color: var(--color-gold); font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px;"><?php echo htmlspecialchars($trainer['role']); ?></p>
          </section>
        </aside>

        <div style="display: flex; flex-direction: column; gap: var(--sp-6);">
          
          <?php if ($success_msg): ?>
            <div style="background: rgba(76, 175, 130, 0.2); border: 1px solid #4caf82; padding: 12px; border-radius: 4px; color: #4caf82; text-align: center; font-weight: 500;">
              <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_msg); ?>
            </div>
          <?php endif; ?>

          <?php if ($error_msg): ?>
            <div style="background: rgba(254, 73, 42, 0.2); border: 1px solid var(--color-accent); padding: 12px; border-radius: 4px; color: #ff8a75; text-align: center; font-weight: 500;">
              <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_msg); ?>
            </div>
          <?php endif; ?>

          <section class="info-card" style="border-top: 4px solid var(--color-accent);">
            <h3><i class="fas fa-plus-circle" style="color: var(--color-accent); margin-right: 8px;"></i> Create a New Fitness Class</h3>
            
            <form action="trainer_dashboard.php" method="POST" class="form" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--sp-4);">
              <input type="hidden" name="action" value="create_class">

              <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Class Title / Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>

              <div class="form-group">
                <label class="form-label">Class Type</label>
                <select name="type" class="form-control" required style="width: 100%; background: rgba(0,0,0,0.3); color: white; padding: 10px; border: 1px solid var(--color-border); border-radius: 4px;">
                  <option value="Strength">Strength</option>
                  <option value="HIIT">HIIT & Cardio</option>
                  <option value="Yoga">Yoga & Mobility</option>
                  <option value="Functional">Functional</option>
                  <option value="Pilates">Pilates</option>
                </select>
              </div>

              <div class="form-group">
                <label class="form-label">Capacity</label>
                <input type="number" name="capacity" class="form-control" min="5" max="50" value="15" required>
              </div>

              <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Date</label>
                <input type="date" name="class_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Start Time</label>
                <input type="time" name="start_time" class="form-control" required>
              </div>

              <div class="form-group">
                <label class="form-label">End Time</label>
                <input type="time" name="end_time" class="form-control" required>
              </div>

              <div style="grid-column: span 2; display: flex; justify-content: flex-end; margin-top: var(--sp-2);">
                <button type="submit" class="btn btn--primary" style="background: var(--color-accent); border-color: var(--color-accent);">Publish Class</button>
              </div>
            </form>
          </section>

          <section class="info-card">
            <h3><i class="fas fa-calendar-alt" style="color: var(--color-gold); margin-right: 8px;"></i> My Assigned Schedule & Member Rosters</h3>
            
            <?php if (count($myClasses) > 0): ?>
              <div style="display: flex; flex-direction: column; gap: var(--sp-4); margin-top: var(--sp-4);">
                <?php foreach ($myClasses as $class): 
                    $isPast = false;
                    if ($class['class_date'] < $currentDate) {
                        $isPast = true;
                    } elseif ($class['class_date'] === $currentDate && $currentTime >= $class['end_time']) {
                        $isPast = true;
                    }

                    $stmtRoster = $pdo->prepare("
                        SELECT e.id as enrollment_id, u.name, u.username, u.email, u.profile_photo 
                        FROM enrollments e
                        JOIN users u ON e.user_id = u.id
                        WHERE e.class_id = ?
                    ");
                    $stmtRoster->execute([$class['id']]);
                    $roster = $stmtRoster->fetchAll(PDO::FETCH_ASSOC);
                ?>
                  <div style="background: rgba(255,255,255,0.01); border: 1px solid var(--color-border); border-left: 4px solid <?php echo $isPast ? '#757575' : 'var(--color-gold)'; ?>; padding: var(--sp-4); border-radius: var(--radius-sm);">
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
                      <div>
                        <span class="badge" style="background: <?php echo $isPast ? 'rgba(255,255,255,0.05)' : 'rgba(203,160,82,0.1)'; ?>; color: <?php echo $isPast ? '#999' : 'var(--color-gold)'; ?>; margin-bottom: 5px; display: inline-block;">
                          <?php echo $isPast ? 'Concluded' : 'Upcoming'; ?>
                        </span>
                        <h4 style="font-family: var(--font-display); font-size: 1.4rem; text-transform: uppercase; margin: 0;">
                          <?php echo htmlspecialchars($class['name']); ?> 
                          <span style="font-family: var(--font-body); font-size: 0.85rem; color: var(--color-text-muted); text-transform: none; margin-left: 10px;">(<?php echo htmlspecialchars($class['type']); ?>)</span>
                        </h4>
                        <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-top: 4px;">
                          <i class="far fa-calendar-alt"></i> <?php echo htmlspecialchars($class['day_of_week']); ?> (<?php echo date('d/m', strtotime($class['class_date'])); ?>) &nbsp;|&nbsp; 
                          <i class="far fa-clock"></i> <?php echo htmlspecialchars($class['start_time']); ?> - <?php echo htmlspecialchars($class['end_time']); ?>
                        </p>
                      </div>
                      <div>
                        <span style="font-size: 1.1rem; font-weight: 600; font-family: var(--font-display);">
                          <i class="fas fa-users" style="color: var(--color-gold);"></i> <?php echo count($roster); ?> / <?php echo $class['capacity']; ?> spots
                        </span>
                      </div>
                    </div>

                    <div style="margin-top: var(--sp-3); border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px;">
                      <p style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: var(--color-gold);"><i class="fas fa-users-cog"></i> Member Attendance List:</p>
                      
                      <?php if (count($roster) > 0): ?>
                        <div class="roster-list profile-scrollbar">
                          <?php foreach ($roster as $member): 
                              $photo = $member['profile_photo'];
                              if ($photo === 'images/profiles/default.png' || !file_exists(__DIR__ . '/../' . $photo)) {
                                  $avatar = "https://ui-avatars.com/api/?name=" . urlencode($member['name']) . "&background=CBA052&color=fff&size=64&bold=true";
                              } else {
                                  $avatar = '../' . $photo;
                              }
                          ?>
                            <div class="roster-item">
                              <div class="roster-user">
                                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar" class="roster-avatar">
                                <div>
                                  <strong style="font-size: 0.95rem; color: #fff;"><?php echo htmlspecialchars($member['name']); ?></strong>
                                  <span style="font-size: 0.8rem; color: var(--color-text-muted); display: block;">@<?php echo htmlspecialchars($member['username']); ?></span>
                                </div>
                              </div>
                              
                              <div style="display: flex; align-items: center; gap: 15px;">
                                <span style="font-size: 0.85rem; color: var(--color-text-muted);"><i class="far fa-envelope"></i> <?php echo htmlspecialchars($member['email']); ?></span>
                                
                                <form action="trainer_dashboard.php" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to remove <?php echo htmlspecialchars($member['name']); ?> from this class?');">
                                    <input type="hidden" name="action" value="remove_student">
                                    <input type="hidden" name="enrollment_id" value="<?php echo $member['enrollment_id']; ?>">
                                    <button type="submit" class="btn btn--sm" style="background: rgba(231, 76, 60, 0.1); border: 1px solid #e74c3c; color: #e74c3c; padding: 4px 8px; font-size: 0.8rem; cursor: pointer; border-radius: 4px;" title="Remove Member">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </form>
                              </div>
                            </div>
                          <?php endforeach; ?>
                        </div>
                      <?php else: ?>
                        <p style="color: var(--color-text-muted); font-size: 0.85rem; font-style: italic; margin-top: 5px;">No members enrolled yet.</p>
                      <?php endif; ?>
                    </div>

                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-muted" style="text-align: center; padding: var(--sp-4); margin: 0;">No classes found in your agenda.</p>
            <?php endif; ?>
          </section>

          <section class="info-card">
            <h3><i class="fas fa-user-edit" style="color: var(--color-gold); margin-right: 8px;"></i> Edit My Public Profile Bio & Focus</h3>
            
            <form action="trainer_dashboard.php" method="POST" class="form">
              <input type="hidden" name="action" value="update_profile">

              <div class="form-group">
                <label class="form-label">Specializations & Focus Areas</label>
                <input type="text" name="specializations" class="form-control" value="<?php echo htmlspecialchars($trainer['specializations']); ?>" required>
              </div>

              <div class="form-group">
                <label class="form-label">Motivational Punchline / Quote</label>
                <input type="text" name="quote" class="form-control" value="<?php echo htmlspecialchars($trainer['quote']); ?>">
              </div>

              <div class="form-group">
                <label class="form-label">Professional Biography (Bio)</label>
                <textarea name="bio" class="form-control" rows="5" required style="resize: vertical; background: rgba(0,0,0,0.3); color: #fff; padding: 10px; border: 1px solid var(--color-border); border-radius: 4px; width: 100%;"><?php echo htmlspecialchars($trainer['bio']); ?></textarea>
              </div>

              <div style="display: flex; justify-content: flex-end; margin-top: var(--sp-4);">
                <button type="submit" class="btn btn--primary">Save Changes</button>
              </div>
            </form>
          </section>

        </div>
      </div>
    </div>
  </main>

  <footer class="footer">
    <div class="container footer__inner" style="text-align: center;">
      <p>&copy; 2026 FORGE GYM. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>