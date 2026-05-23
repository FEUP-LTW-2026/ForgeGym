<?php
session_start();

// 1. BARREIRA DE SEGURANÇA (RBAC)
// Se não tiver login feito, ou se for apenas um 'user' normal, é expulso para o plan.php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'trainer')) {
    header("Location: plan.php");
    exit;
}

$userRole = $_SESSION['role'];
$userTrainerId = $_SESSION['trainer_id'] ?? null;
$error = '';
$success = '';

define('DB_PATH', __DIR__ . '/../database/forge_gym.db');

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ir buscar os treinadores para o dropdown
    if ($userRole === 'admin') {
        // Admin pode escolher qualquer treinador
        $trainers = $pdo->query("SELECT id, name FROM trainers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Trainer só pode escolher-se a si próprio
        $stmt = $pdo->prepare("SELECT id, name FROM trainers WHERE id = ?");
        $stmt->execute([$userTrainerId]);
        $trainers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. PROCESSAR O FORMULÁRIO (QUANDO CLICAM EM "CREATE CLASS")
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? '';
        
        // Se for admin, confia no POST. Se for trainer, força o seu próprio ID por segurança
        $trainer_id = ($userRole === 'admin') ? $_POST['trainer_id'] : $userTrainerId;
        
        $class_date = $_POST['class_date'] ?? '';
        $day_of_week = date('l', strtotime($class_date));
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $capacity = (int)($_POST['capacity'] ?? 0);

        if ($name && $type && $trainer_id && $class_date && $day_of_week && $start_time && $end_time && $capacity > 0) {
            $sql = "INSERT INTO classes (name, type, trainer_id, class_date, day_of_week, start_time, end_time, capacity) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            try {
                $stmt->execute([$name, $type, $trainer_id, $class_date, $day_of_week, $start_time, $end_time, $capacity]);
                header("Location: plan.php"); // Volta para o calendário após criar
                exit;
            } catch (PDOException $e) {
                $error = "Error saving class to the database.";
            }
        } else {
            $error = "Please fill in all the required fields correctly.";
        }
    }

} catch (PDOException $e) {
    die("Database connection failed.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Class — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
          <a href="plan.php" class="btn btn--ghost"><i class="fas fa-arrow-left"></i> Back to Schedule</a>
        </div>
      </nav>
    </div>
  </header>

  <main class="page-spacing container" style="display: flex; align-items: center; justify-content: center; min-height: 85vh; padding-top: 2rem;">
    <section class="card" style="width: 100%; max-width: 600px; border-top: 4px solid var(--color-gold); padding: var(--sp-8);">
      <div style="text-align: center; margin-bottom: var(--sp-6);">
        <h1 style="font-family: var(--font-display); font-size: 2.5rem; text-transform: uppercase;">Create <span style="color: var(--color-gold);">Class</span></h1>
        <p style="color: var(--color-text-muted);">Add a new session to the gym schedule.</p>
      </div>

      <?php if ($error): ?>
        <div style="background: rgba(254, 73, 42, 0.2); border: 1px solid var(--color-accent); padding: 10px; border-radius: 4px; color: #ff8a75; margin-bottom: 20px; text-align: center; font-size: 0.9rem;">
          <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form action="create_class.php" method="POST" class="form">
        
        <div class="form-group">
          <label for="name" class="form-label">Class Name</label>
          <input type="text" id="name" name="name" class="form-control" required placeholder="e.g. Extreme Powerlifting">
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label for="type" class="form-label">Class Type</label>
              <select id="type" name="type" class="form-control" required style="background: rgba(0,0,0,0.3); color: white;">
                <option value="">Select a type...</option>
                <option value="Strength">Strength</option>
                <option value="HIIT">HIIT & Cardio</option>
                <option value="Yoga">Yoga & Mobility</option>
                <option value="Functional">Functional</option>
                <option value="Pilates">Pilates</option>
              </select>
            </div>

            <div class="form-group" style="flex: 1;">
              <label for="trainer_id" class="form-label">Trainer</label>
              <select id="trainer_id" name="trainer_id" class="form-control" required style="background: rgba(0,0,0,0.3); color: white;">
                <?php foreach ($trainers as $trainer): ?>
                    <option value="<?php echo $trainer['id']; ?>"><?php echo htmlspecialchars($trainer['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label for="class_date" class="form-label">Date</label>
              <input type="date" id="class_date" name="class_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>" onchange="updateDay()">
            </div>

            <div class="form-group" style="flex: 1;">
              <label for="day_display" class="form-label">Day of the Week</label>
              <input type="text" id="day_display" class="form-control" style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--color-gold); border-radius: 4px; cursor: not-allowed; font-weight: bold;" value="Select a date..." disabled>
            </div>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="form-group" style="flex: 1;">
              <label for="start_time" class="form-label">Start Time</label>
              <input type="time" id="start_time" name="start_time" class="form-control" required>
            </div>

            <div class="form-group" style="flex: 1;">
              <label for="end_time" class="form-label">End Time</label>
              <input type="time" id="end_time" name="end_time" class="form-control" required>
            </div>
            
            <div class="form-group" style="flex: 1;">
              <label for="capacity" class="form-label">Capacity</label>
              <input type="number" id="capacity" name="capacity" class="form-control" required min="1" max="50" placeholder="e.g. 15">
            </div>
        </div>

        <button type="submit" class="btn btn--primary" style="width: 100%; margin-top: var(--sp-4); background: var(--color-gold); color: black;">
          <i class="fas fa-check"></i> Create Class
        </button>
      </form>
      
    </section>
  </main>

  <script>
    function updateDay() {
      const dateVal = document.getElementById('class_date').value;
      if (dateVal) {
        const dateObj = new Date(dateVal + 'T12:00:00'); 
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        document.getElementById('day_display').value = days[dateObj.getDay()];
      } else {
        document.getElementById('day_display').value = 'Select a date...';
      }
    }
  </script>

</body>
</html>