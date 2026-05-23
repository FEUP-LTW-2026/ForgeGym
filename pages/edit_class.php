<?php
session_start();

// BARREIRA DE SEGURANÇA: Só Admins ou Treinadores podem editar aulas
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'trainer'])) {
    header("Location: plan.php");
    exit;
}

define('DB_PATH', __DIR__ . '/../database/forge_gym.db');
$class_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error_msg = '';

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Ir buscar os dados atuais da aula
    $stmtClass = $pdo->prepare("SELECT * FROM classes WHERE id = ?");
    $stmtClass->execute([$class_id]);
    $class = $stmtClass->fetch(PDO::FETCH_ASSOC);

    if (!$class) {
        $_SESSION['flash_error'] = "Class not found.";
        header("Location: plan.php");
        exit;
    }

    // REGRA DE SEGURANÇA EXTRA: Se for um Trainer, ele só pode editar a própria aula!
    if ($_SESSION['role'] === 'trainer' && $_SESSION['trainer_id'] != $class['trainer_id']) {
        $_SESSION['flash_error'] = "Security access denied: You can only edit your own classes.";
        header("Location: plan.php");
        exit;
    }

    // 2. PROCESSAR SUBMISSÃO DO FORMULÁRIO (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? '';
        $class_date = $_POST['class_date'] ?? '';
        
        $day_of_week = date('l', strtotime($class_date)); 

        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $capacity = (int)($_POST['capacity'] ?? 0);
        $trainer_id = isset($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : $class['trainer_id'];

        if (empty($name) || empty($class_date) || empty($start_time) || empty($end_time) || $capacity <= 0) {
            $error_msg = "Please fill in all fields correctly.";
        } else {
            // Atualizar os dados na BD
            $stmtUpdate = $pdo->prepare("
                UPDATE classes 
                SET name = ?, type = ?, day_of_week = ?, class_date = ?, start_time = ?, end_time = ?, capacity = ?, trainer_id = ?
                WHERE id = ?
            ");
            $stmtUpdate->execute([$name, $type, $day_of_week, $class_date, $start_time, $end_time, $capacity, $trainer_id, $class_id]);

            $_SESSION['flash_success'] = "Class details updated successfully!";
            header("Location: plan.php");
            exit;
        }
    }

    // 3. Ir buscar a lista de treinadores para o dropdown
    $trainers = $pdo->query("SELECT id, name FROM trainers ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database failure: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Class — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: var(--color-bg); color: white;">

  <header class="header">
    <div class="container header__inner">
      <a href="../index.php" class="logo">
        <img src="../images/logo.png" alt="FORGE GYM" class="logo__img" />
        <span class="logo__text">FORGE GYM <span style="color: var(--color-gold); font-size: 0.8rem;">MANAGEMENT</span></span>
      </a>
      <nav class="nav" style="justify-content: flex-end;">
        <a href="plan.php" class="btn btn--ghost"><i class="fas fa-arrow-left"></i> Back to Schedule</a>
      </nav>
    </div>
  </header>

  <main class="container page-spacing" style="max-width: 600px; padding-top: 3rem;">
    <header class="page-header" style="text-align: left; margin-bottom: var(--sp-6);">
      <p class="eyebrow" style="color: var(--color-gold);">Class Editor</p>
      <h1 class="page-title">EDIT <span class="page-title--outline">CLASS</span></h1>
    </header>

    <?php if ($error_msg): ?>
      <div style="background: rgba(254, 73, 42, 0.2); border: 1px solid var(--color-accent); padding: 15px; border-radius: 4px; color: #ff8a75; margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_msg); ?>
      </div>
    <?php endif; ?>

    <section class="card" style="border-top: 4px solid var(--color-gold); background: #1a1a1a; padding: var(--sp-6);">
      <form action="edit_class.php?id=<?php echo $class_id; ?>" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
        
        <div class="input-group">
          <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted); font-size: 0.9rem;">Class Name</label>
          <input type="text" name="name" class="form-control" style="width: 100%; padding: 12px; background: rgba(0,0,0,0.4); border: 1px solid var(--color-border); color: white; border-radius: 4px;" value="<?php echo htmlspecialchars($class['name']); ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted); font-size: 0.9rem;">Class Type</label>
            <select name="type" class="form-control" style="width: 100%; padding: 12px; background: rgba(0,0,0,0.4); border: 1px solid var(--color-border); color: white; border-radius: 4px;">
              <option value="Strength" <?php echo $class['type'] === 'Strength' ? 'selected' : ''; ?>>Strength</option>
              <option value="HIIT" <?php echo $class['type'] === 'HIIT' ? 'selected' : ''; ?>>HIIT & Cardio</option>
              <option value="Yoga" <?php echo $class['type'] === 'Yoga' ? 'selected' : ''; ?>>Yoga & Mobility</option>
              <option value="Functional" <?php echo $class['type'] === 'Functional' ? 'selected' : ''; ?>>Functional</option>
              <option value="Pilates" <?php echo $class['type'] === 'Pilates' ? 'selected' : ''; ?>>Pilates</option>
            </select>
          </div>
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted); font-size: 0.9rem;">Day of the Week</label>
            <input type="text" id="day_display" class="form-control" style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--color-gold); border-radius: 4px; cursor: not-allowed; font-weight: bold;" value="<?php echo htmlspecialchars($class['day_of_week']); ?>" disabled>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted); font-size: 0.9rem;">Date</label>
            <input type="date" id="class_date_input" name="class_date" class="form-control" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.4); border: 1px solid var(--color-border); color: white; border-radius: 4px;" value="<?php echo $class['class_date']; ?>" required onchange="updateDay()">
          </div>
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted); font-size: 0.9rem;">Start</label>
            <input type="time" name="start_time" class="form-control" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.4); border: 1px solid var(--color-border); color: white; border-radius: 4px;" value="<?php echo $class['start_time']; ?>" required>
          </div>
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted); font-size: 0.9rem;">End</label>
            <input type="time" name="end_time" class="form-control" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.4); border: 1px solid var(--color-border); color: white; border-radius: 4px;" value="<?php echo $class['end_time']; ?>" required>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted); font-size: 0.9rem;">Max Capacity</label>
            <input type="number" name="capacity" class="form-control" style="width: 100%; padding: 12px; background: rgba(0,0,0,0.4); border: 1px solid var(--color-border); color: white; border-radius: 4px;" value="<?php echo $class['capacity']; ?>" min="1" required>
          </div>
          
          <div>
            <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted); font-size: 0.9rem;">Coach Assigned</label>
            <?php if ($_SESSION['role'] === 'admin'): ?>
              <select name="trainer_id" class="form-control" style="width: 100%; padding: 12px; background: rgba(0,0,0,0.4); border: 1px solid var(--color-border); color: var(--color-gold); border-radius: 4px;">
                <?php foreach ($trainers as $t): ?>
                  <option value="<?php echo $t['id']; ?>" <?php echo $class['trainer_id'] == $t['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($t['name']); ?></option>
                <?php endforeach; ?>
              </select>
            <?php else: ?>
              <input type="text" class="form-control" style="width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--color-text-muted); border-radius: 4px; cursor: not-allowed;" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? 'You'); ?>" disabled>
            <?php endif; ?>
          </div>
        </div>

        <button type="submit" class="btn btn--primary" style="margin-top: 10px; width: 100%; background: var(--color-gold); border-color: var(--color-gold); color: black; font-weight: bold; padding: 14px;">
          <i class="fas fa-save"></i> Save Changes
        </button>
      </form>
    </section>
  </main>

  <script>
    function updateDay() {
      const dateVal = document.getElementById('class_date_input').value;
      if (dateVal) {
        // Usa o fuso horário local garantindo que o dia não salta
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