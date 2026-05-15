<?php
define('DB_PATH', __DIR__ . '/../database/forge_gym.db');

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed.");
}

// Lógica de Filtros (Opcional para agora, mas já fica preparada)
$typeFilter = $_GET['type'] ?? '';
$dayFilter = $_GET['day'] ?? '';

$query = "SELECT c.*, t.name as trainer_name 
          FROM classes c 
          JOIN trainers t ON c.trainer_id = t.id 
          WHERE 1=1";

if ($typeFilter) $query .= " AND c.type = " . $pdo->quote($typeFilter);
if ($dayFilter) $query .= " AND c.day_of_week = " . $pdo->quote($dayFilter);

$query .= " ORDER BY CASE 
            WHEN day_of_week = 'Monday' THEN 1 
            WHEN day_of_week = 'Tuesday' THEN 2 
            WHEN day_of_week = 'Wednesday' THEN 3 
            WHEN day_of_week = 'Thursday' THEN 4 
            WHEN day_of_week = 'Friday' THEN 5 
            WHEN day_of_week = 'Saturday' THEN 6 
            ELSE 7 END, start_time ASC";

$classes = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Schedule & Plans — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="icon" type="image/png" href="../images/logo16x16.png" />
  <link rel="icon" type="image/png" href="../images/logo-appletouch.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>

  <header class="header">
    <div class="container header__inner">

      <a href="../index.html" class="logo" aria-label="FORGE GYM homepage">
        <img src="../images/logo.png" alt="FORGE GYM" class="logo__img" />
        <span class="logo__text">FORGE GYM</span>
      </a>

      <input type="checkbox" id="nav-toggle" class="sr-only peer-nav" />
      <label for="nav-toggle" class="nav__toggle" aria-label="Open navigation menu">
        <span></span>
        <span></span>
        <span></span>
      </label>

      <nav class="nav" id="main-nav" aria-label="Primary navigation">
        <ul class="nav__list">
          <li><a href="plan.php" class="nav__link">Classes</a></li>
          <li><a href="profile.html" class="nav__link">Profile</a></li>
        </ul>
        <div class="nav__actions">
          <a href="login.html" class="btn btn--ghost">Log in</a>
          <a href="register.html" class="btn btn--primary">Join now</a>
        </div>
      </nav>
      </div>
  </header>

  <main class="page-spacing">
    
    <header class="page-header container">
      <p class="eyebrow">Plan your week</p>
      <h1 class="page-title">
        CLASS <span class="page-title--outline">SCHEDULE</span>
      </h1>
    </header>

    <section class="filters container" aria-label="Filter classes">
      <form class="filters__form" action="#" method="GET">
        
        <div class="input-group">
          <label for="filter-day" class="sr-only">Select Day</label>
          <input type="date" id="filter-day" class="form-control" value="2026-04-24" />
        </div>

        <div class="input-group">
          <label for="filter-type" class="sr-only">Class Type</label>
          <select id="filter-type" class="form-control">
            <option value="all">All Classes</option>
            <option value="hiit">HIIT & Conditioning</option>
            <option value="strength">Strength Forge</option>
            <option value="mobility">Mobility & Flow</option>
          </select>
        </div>

        <div class="input-group">
          <label for="filter-trainer" class="sr-only">Trainer</label>
          <select id="filter-trainer" class="form-control">
            <option value="all">All Trainers</option>
            <option value="luis">Luis Torrao</option>
            <option value="diogo">Diogo Quaresma</option>
            <option value="mafalda">Mafalda Pacheco</option>
            <option value="david">David Pinto</option>
            <option value="joao">Joao Lage</option>
          </select>
        </div>

        <button type="submit" class="btn btn--primary">Filter</button>
      </form>
      
    </section>
    <div class="schedule-grid">
      <?php if (count($classes) > 0): ?>
        <?php foreach ($classes as $class): ?>
          <article class="class-card" data-type="<?php echo strtolower($class['type']); ?>">
            <div class="class-card__time">
              <span class="time-start"><?php echo $class['start_time']; ?></span>
              <span class="time-end"><?php echo $class['end_time']; ?></span>
            </div>
            <div class="class-card__info">
              <div class="class-card__header">
                <h2 class="class-title"><?php echo htmlspecialchars($class['name']); ?></h2>
                <span class="badge badge--dark"><?php echo $class['day_of_week']; ?></span>
              </div>
              <p class="class-trainer">
                with <a href="trainer.php?id=<?php echo $class['trainer_id']; ?>" style="color: var(--color-gold);">
                  <?php echo htmlspecialchars($class['trainer_name']); ?>
                </a>
              </p>
            </div>
            <div class="class-card__footer">
              <span class="class-capacity">
                <i class="fas fa-users"></i> <?php echo $class['capacity']; ?> spots
              </span>
              <button class="btn btn--primary btn--sm">Book Now</button>
            </div>
      </article>
    <?php endforeach; ?>
  <?php else: ?>
    <p>No classes found for the selected filters.</p>
  <?php endif; ?>
</div>

    <section class="trainers-section container" aria-labelledby="trainers-title">
      
      <header class="section-header">
        <h2 class="section-title" id="trainers-title">MEET YOUR <span class="text-outline">FORGERS</span></h2>
        <p class="section-sub">Expert coaches dedicated to pushing your limits.</p>
      </header>

      <div class="trainers-grid">
        
        <article class="trainer-card">
          <a href="trainer.php?id=1" style="text-decoration: none; color: inherit;">
            <figure class="trainer-card__img-wrapper">
              <img src="../images/personal trainers/pt-torrao.jpeg" alt="Luis Torrao" class="trainer-card__img" />
            </figure>
            <div class="trainer-card__info">
              <h3 class="trainer-name">Luis Torrao</h3>
              <p class="trainer-role">Head of Strength & Conditioning</p>
              <div class="trainer-tags">
                <span class="badge badge--dark">Strength</span>
                <span class="badge badge--dark">Powerlifting</span>
              </div>
            </div>
          </a>
        </article>

        <article class="trainer-card">
          <a href="trainer.php?id=2" style="text-decoration: none; color: inherit;">
            <figure class="trainer-card__img-wrapper">
              <img src="../images/personal trainers/pt-quaresma.jpeg" alt="Diogo Quaresma" class="trainer-card__img" />
            </figure>
            <div class="trainer-card__info">
              <h3 class="trainer-name">Diogo Quaresma</h3>
              <p class="trainer-role">HIIT & Cardio Specialist</p>
              <div class="trainer-tags">
                <span class="badge badge--dark">HIIT</span>
                <span class="badge badge--dark">Endurance</span>
              </div>
            </div>
          </a>
        </article>

        <article class="trainer-card">
          <a href="trainer.php?id=3" style="text-decoration: none; color: inherit;">
            <figure class="trainer-card__img-wrapper">
              <img src="../images/personal trainers/pt-mafalda.jpeg" alt="Mafalda Pacheco" class="trainer-card__img" />
            </figure>
            <div class="trainer-card__info">
              <h3 class="trainer-name">Mafalda Pacheco</h3>
              <p class="trainer-role">Mobility & Yoga Master</p>
              <div class="trainer-tags">
                <span class="badge badge--dark">Yoga</span>
                <span class="badge badge--dark">Recovery</span>
              </div>
            </div>
          </a>
        </article>

        <article class="trainer-card">
          <a href="trainer.php?id=4" style="text-decoration: none; color: inherit;">
            <figure class="trainer-card__img-wrapper">
              <img src="../images/personal trainers/pt-david.jpeg" alt="David Pinto" class="trainer-card__img" />
            </figure>
            <div class="trainer-card__info">
              <h3 class="trainer-name">David Pinto</h3>
              <p class="trainer-role">Pilates & Core Expert</p>
              <div class="trainer-tags">
                <span class="badge badge--dark">Pilates</span>
                <span class="badge badge--dark">Core</span>
              </div>
            </div>
          </a>
        </article>

        <article class="trainer-card">
          <a href="trainer.php?id=5" style="text-decoration: none; color: inherit;">
            <figure class="trainer-card__img-wrapper">
              <img src="../images/personal trainers/pt-lage.jpeg" alt="Joao Lage" class="trainer-card__img" />
            </figure>
            <div class="trainer-card__info">
              <h3 class="trainer-name">Joao Lage</h3>
              <p class="trainer-role">Functional & Kettlebell</p>
              <div class="trainer-tags">
                <span class="badge badge--dark">Functional</span>
                <span class="badge badge--dark">CrossFit</span>
              </div>
            </div>
          </a>
        </article>

      </div>
    </section>

  </main>

  <footer class="footer">
    <div class="container footer__inner">
      
      <a href="../index.html" class="logo footer__logo" aria-label="FORGE GYM homepage">
        <img src="../images/logo.png" alt="FORGE GYM" class="logo__img" />
      </a>

      <ul class="footer__nav">
        <li><a href="../index.html">Home</a></li>
        <li><a href="../index.html#equipment">Equipment</a></li>
        <li><a href="../index.html#plans">Plans</a></li>
        <li><a href="../index.html#reviews">Reviews</a></li>
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