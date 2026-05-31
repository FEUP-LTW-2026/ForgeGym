<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

define('DB_PATH', __DIR__ . '/../database/forge_gym.db');
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed.");
}

$success_msg = '';
$error_msg = '';
$user_id = $_SESSION['user_id'];
$is_readonly = false;

if (isset($_GET['edit_user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $target_id = (int)$_GET['edit_user_id'];
    
    $stmtCheck = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmtCheck->execute([$target_id]);
    $target_role = $stmtCheck->fetchColumn();
    
    if ($target_role === 'admin' && $target_id !== $_SESSION['user_id']) {
        die("<h3>Security Alert</h3><p>Administrators cannot modify or view other administrators' profiles.</p><a href='admin_dashboard.php'>Go back</a>");
    }
    
    $user_id = $target_id;
    $is_readonly = true; 
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// 2. SEGUNDO: Processar o envio do formulário (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($name && $username && $email) {
        
        $photo_path = $user['profile_photo']; // Agora está 100% seguro e preenchido!
        $upload_error = '';

        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errCode = $_FILES['profile_photo']['error'];
            
            if ($errCode === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['profile_photo']['tmp_name'];
                $fileName = $_FILES['profile_photo']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($fileExtension, $allowedExtensions)) {
                    $uploadDir = __DIR__ . '/../images/profiles/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $newFileName = 'user_' . $user_id . '_' . time() . '.' . $fileExtension;
                    $dest_path = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($fileTmpPath, $dest_path)) {
                        $photo_path = 'images/profiles/' . $newFileName;
                    } else {
                        $upload_error = " Permission error while saving the image.";
                    }
                } else {
                    $upload_error = " Unsupported format. Please use JPG, PNG, GIF, or WEBP.";
                }
            } else {
                if ($errCode == 1 || $errCode == 2) {
                    $upload_error = " The image is too large (server limit reached). Please try a smaller photo.";
                } else {
                    $upload_error = " Upload error (Code: $errCode).";
                }
            }
        }

        $query = "UPDATE users SET name = ?, username = ?, email = ?, profile_photo = ?";
        $params = [$name, $username, $email, $photo_path];

        if (!empty($password)) {
            $query .= ", password = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        $query .= " WHERE id = ?";
        $params[] = $user_id;

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $_SESSION['username'] = $username; 
            
            // Atualizar os dados locais do utilizador imediatamente após o update
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($upload_error) {
                $error_msg = "Profile updated, BUT the photo upload failed:" . $upload_error;
            } else {
                $success_msg = "Profile updated successfully!";
            }
        } catch (PDOException $e) {
            $error_msg = "Username or Email already exists.";
        }
    } else {
        $error_msg = "Please fill in all required fields.";
    }
}

// 3. TERCEIRO: Buscar as aulas em que o utilizador está inscrito
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
if (empty($photoPath) || !file_exists(__DIR__ . '/../' . $photoPath)) {
    $avatarPath = "https://ui-avatars.com/api/?name=" . urlencode($user['name']) . "&background=CBA052&color=fff&size=150&bold=true";
} else {
    $avatarPath = '../' . $photoPath . '?' . time(); 
}

$currentDate = date('Y-m-d');
$currentTime = date('H:i');

$upcomingSessions = [];
$workoutHistory = [];

foreach ($enrolledClasses as $class) {
    $hasEnded = false;

    if ($class['class_date'] < $currentDate) {
        $hasEnded = true; 
    } elseif ($class['class_date'] === $currentDate && $currentTime >= $class['end_time']) {
        $hasEnded = true; 
    }

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
          <li><a href="forum.php" class="nav__link">Forum</a></li>
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
                <label for="profile-photo-upload" style="cursor: pointer; position: relative; display: block;">
                    <img src="<?php echo htmlspecialchars($avatarPath); ?>" class="profile-avatar-img" alt="Profile Photo">
                    <div style="position: absolute; bottom: 0; right: 0; background: var(--color-gold); border-radius: 50%; padding: 8px;">
                        <i class="fas fa-camera" style="color: black; font-size: 0.8rem;"></i>
                    </div>
                </label>
                <form id="avatar-form" action="../api/update_avatar.php" method="POST" enctype="multipart/form-data">
                    <input type="file" id="profile-photo-upload" name="profile_photo" accept="image/*" style="display: none;" 
                    onchange="
                        const file = this.files[0];
                        const maxSize = 2 * 1024 * 1024; 
                        if (file && file.size > maxSize) {
                            alert('The image is too large (max 2MB). Please, pick one smaller.');
                        } else {
                            this.form.submit();
                        }
                    ">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                </form>
            </div>
            <h2 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h2>
            <p class="user-handle">@<?php echo htmlspecialchars($user['username']); ?></p>
          </section>

          <section class="qr-card">
            <h3 class="qr-card__title">Gym Access</h3>
            
            <?php
            $user_role = $user['role'] ?? 'user';
            $user_plan = $user['plan'] ?? 'none';
            
            // Se for admin ou trainer, o plano é automaticamente Premium
            if ($user_role === 'admin' || $user_role === 'trainer') {
                $user_plan = 'Premium';
            }
            
            $has_active_plan = (!empty($user_plan) && $user_plan !== 'none');
            
            if ($has_active_plan): 
                // ID único
                $user_qr_data = "FORGE-" . str_pad($user['id'], 4, '0', STR_PAD_LEFT) . "-2026";
                
                $qr_color = "1a1a1a";
                $qr_bg = "ffffff";
                $wrapper_style = "";
                $text_style = "color: white;";
                $badge = "";

                if ($user_plan === 'Basic') {
                    $qr_bg = "e0e0e0"; // Prata/Cinza claro
                    $wrapper_style = "border: 2px solid #888; box-shadow: 0 4px 10px rgba(255,255,255,0.05);";
                    $text_style = "color: #b0b0b0;"; // Texto prateado
                } 
                elseif ($user_plan === 'Classic') {
                    $qr_bg = "CBA052"; // Dourado normal
                    $wrapper_style = "border: 3px solid var(--color-gold); box-shadow: 0 8px 25px rgba(203, 160, 82, 0.3);";
                    $text_style = "color: var(--color-gold);"; // Texto dourado
                } 
                elseif ($user_plan === 'Premium') {
                    $qr_bg = "CBA052";
                    $wrapper_style = "border: 4px solid #fff; outline: 3px solid var(--color-gold); outline-offset: 4px; box-shadow: 0 0 35px rgba(203, 160, 82, 0.5); margin-bottom: 8px;";
                    $text_style = "color: var(--color-gold); text-shadow: 0 0 15px rgba(203, 160, 82, 0.4); font-weight: 900;";
                    $badge = "<div style='position:absolute; top:-18px; right:-18px; background:#1a1a1a; color:var(--color-gold); border:2px solid var(--color-gold); border-radius:50%; width:45px; height:45px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; z-index:2; box-shadow: 0 4px 10px rgba(0,0,0,0.5);'><i class='fas fa-crown'></i></div>";
                }

                // Geração do link com as cores escolhidas dinamicamente
                $qr_code_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($user_qr_data) . "&color={$qr_color}&bgcolor={$qr_bg}&margin=10";
            ?>
              <p class="qr-card__desc">Scan this code at the entrance to enter the forge.</p>
              
              <div style="text-align: center; margin: 25px 0 30px 0;">
                  <span style="font-size: 0.85rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 1.5px; display: block; margin-bottom: 5px;">Active Plan</span>
                  <strong style="font-family: var(--font-display); font-size: 1.8rem; letter-spacing: 2px; text-transform: uppercase; <?php echo $text_style; ?>">
                      <?php echo htmlspecialchars($user_plan); ?>
                  </strong>
              </div>

              <div style="display: flex; justify-content: center; position: relative; width: max-content; margin: 0 auto 30px auto;">
                <?php echo $badge; ?>
                <img src="<?php echo $qr_code_url; ?>" alt="Access QR Code" style="border-radius: 8px; max-width: 150px; <?php echo $wrapper_style; ?>">
              </div>
              
              <p class="qr-card__id" style="text-align: center; font-family: monospace; letter-spacing: 1px; color: var(--color-text-muted);">
                ID: <?php echo $user_qr_data; ?>
              </p>

            <?php else: ?>
              <div style="position: relative; overflow: hidden; border-radius: var(--radius-md); background: rgba(0,0,0,0.2); margin: var(--sp-3) 0;">
                <div class="qr-code-wrapper" style="filter: blur(6px); opacity: 0.12; pointer-events: none; margin: 0; padding: var(--sp-4);">
                  <img src="../images/profiles/qrtherock.png" alt="Access QR Code" class="qr-img" style="display: block; margin: 0 auto; max-width: 150px;">
                </div>
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: var(--sp-4); background: rgba(20, 20, 20, 0.75); backdrop-filter: blur(2px); text-align: center;">
                  <h4 style="font-family: var(--font-display); font-size: 1.3rem; color: var(--color-accent); text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px;">No Active Plan</h4>
                  <p style="font-size: 0.85rem; color: #cccccc; margin-bottom: 12px; max-width: 180px; line-height: 1.3;">Membership required to unlock access</p>
                  <a href="../index.php#plans" class="btn btn--primary btn--sm" style="padding: 6px 16px; font-size: 0.8rem; text-transform: uppercase; font-weight: 600; width: auto;">Join Now</a>
                </div>
              </div>
            <?php endif; ?>
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
              <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && !$is_readonly): ?>
                <a href="admin_dashboard.php" class="button-aesthetic" style="display: inline-flex; width: fit-content; background-color: var(--color-accent); border: 1px solid var(--color-accent); color: white; margin-top: var(--sp-2);">
                  <i class="fas fa-user-shield"></i> Admin Dashboard
                </a>
              <?php endif; ?>
            </div>
          </section>

          <section class="info-card">
            <h3>Account Settings</h3>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'file_too_large'): ?>
                <div style="background: rgba(254, 73, 42, 0.2); border: 1px solid var(--color-accent); padding: 10px; border-radius: 4px; color: #ff8a75; margin-bottom: 20px; text-align: center; font-weight: bold;">
                    Error: The image is too large (max 2MB).
                </div>
            <?php endif; ?>
            
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
    
                <?php if ($is_readonly): ?>
                    <div style="background: rgba(0, 0, 0, 0.4); border: 1px solid var(--color-gold); padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                        <p style="color: var(--color-gold); margin: 0; font-weight: bold;"><i class="fas fa-eye"></i> ADMIN VIEW MODE</p>
                        <p style="font-size: 0.85rem; color: #ccc; margin: 0;">You are viewing <strong><?php echo htmlspecialchars($user['name']); ?>'s</strong> profile in read-only mode.</p>
                    </div>
                <?php endif; ?>

                <div class="profile-details-grid">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ""); ?>" <?php echo $is_readonly ? 'readonly style="opacity: 0.6; cursor: not-allowed;"' : 'required'; ?>>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ""); ?>" <?php echo $is_readonly ? 'readonly style="opacity: 0.6; cursor: not-allowed;"' : 'required'; ?>>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ""); ?>" <?php echo $is_readonly ? 'readonly style="opacity: 0.6; cursor: not-allowed;"' : 'required'; ?>>
                    </div>
                </div> <?php if (!$is_readonly): ?>
                    <div class="password-section" style="margin-top: var(--sp-6);">
                        <h3>Security</h3>
                        <div class="form-group">
                            <label class="form-label" for="password">New Password (leave blank to keep current)</label>
                            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••••••">
                        </div>
                    </div>

                    <div style="margin-top: var(--sp-6); display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn btn--primary">Save Changes</button>
                    </div>
                <?php else: ?>
                    <div style="margin-top: var(--sp-6); text-align: center; padding: 15px; background: rgba(0,0,0,0.2); border: 1px dashed #666; border-radius: 4px; color: #888;">
                        <i class="fas fa-lock"></i> Profile modifications are disabled for privacy.
                    </div>
                <?php endif; ?>
                
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