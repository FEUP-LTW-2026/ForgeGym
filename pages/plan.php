<?php
define('DB_PATH', __DIR__ . '/../database/forge_gym.db');

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed.");
}

// Lógica de Filtros
$typeFilter = $_GET['type'] ?? 'all';
$dayFilter = $_GET['day'] ?? '';

$query = "SELECT c.*, t.name as trainer_name 
          FROM classes c 
          JOIN trainers t ON c.trainer_id = t.id 
          WHERE 1=1";

if ($typeFilter !== 'all' && $typeFilter !== '') {
    $query .= " AND c.type = " . $pdo->quote($typeFilter);
}
if ($dayFilter !== '') {
    $query .= " AND c.day_of_week = " . $pdo->quote($dayFilter);
}

$query .= " ORDER BY CASE 
            WHEN day_of_week = 'Monday' THEN 1 
            WHEN day_of_week = 'Tuesday' THEN 2 
            WHEN day_of_week = 'Wednesday' THEN 3 
            WHEN day_of_week = 'Thursday' THEN 4 
            WHEN day_of_week = 'Friday' THEN 5 
            WHEN day_of_week = 'Saturday' THEN 6 
            ELSE 7 END, start_time ASC";

$classes = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

function getClassImage($type) {
    switch(strtolower($type)) {
        case 'strength': return 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=600&auto=format&fit=crop';
        case 'hiit': return 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=600&auto=format&fit=crop';
        case 'yoga': return 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?q=80&w=600&auto=format&fit=crop';
        case 'functional': return 'https://images.unsplash.com/photo-1599058917212-d750089bc07e?q=80&w=600&auto=format&fit=crop';
        case 'pilates': return 'https://images.unsplash.com/photo-1518611012118-696072aa579a?q=80&w=600&auto=format&fit=crop';
        default: return 'https://images.unsplash.com/photo-1534438327276-14e5300c3a48?q=80&w=600&auto=format&fit=crop';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Schedule & Plans — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css" />
  
  <style>
    /* Design da barra de scroll apenas para a zona das aulas */
    .custom-scrollbar::-webkit-scrollbar {
      width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.05); 
      border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: var(--color-gold); 
      border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
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

  <main class="page-spacing">
    
    <header class="page-header container" style="margin-bottom: var(--sp-8);">
      <p class="eyebrow">Plan your week</p>
      <h1 class="page-title">CLASS <span class="page-title--outline">SCHEDULE</span></h1>
    </header>

    <div class="container" style="display: flex; flex-wrap: wrap; gap: var(--sp-8); align-items: flex-start; margin-bottom: var(--sp-10);">
      
      <section class="custom-scrollbar" style="flex: 1; min-width: 300px; max-height: 75vh; overflow-y: auto; padding-right: 15px;" aria-label="Available classes">
        
        <?php if (count($classes) > 0): ?>
          <div class="equip-grid">
            
            <?php foreach ($classes as $class): ?>
              <article class="equip-card" style="border: 1px solid var(--color-border);">
                
                <div class="equip-card__image" style="position: relative;">
                  <img src="<?php echo getClassImage($class['type']); ?>" alt="<?php echo htmlspecialchars($class['name']); ?>">
                  <div style="position: absolute; top: 10px; right: 10px;">
                    <span class="badge badge--dark" style="background: rgba(0,0,0,0.8); border: 1px solid var(--color-gold);"><?php echo htmlspecialchars($class['type']); ?></span>
                  </div>
                </div>

                <div class="equip-card__info" style="gap: var(--sp-2);">
                  <h2 class="equip-card__name" style="text-transform: uppercase; margin-bottom: 0;">
                    <?php echo htmlspecialchars($class['name']); ?>
                  </h2>
                  <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: var(--sp-2);">
                    with <a href="trainer.php?id=<?php echo $class['trainer_id']; ?>" style="color: var(--color-gold); text-decoration: none; font-weight: 600;"><?php echo htmlspecialchars($class['trainer_name']); ?></a>
                  </p>

                  <div style="display: flex; gap: 15px; margin-bottom: var(--sp-3); padding-bottom: var(--sp-3); border-bottom: 1px solid var(--color-border);">
                    <div style="display: flex; align-items: center; gap: 6px; color: var(--color-text);">
                      <i class="far fa-calendar-alt" style="color: var(--color-gold);"></i>
                      <span><?php echo htmlspecialchars($class['day_of_week']); ?></span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; color: var(--color-text);">
                      <i class="far fa-clock" style="color: var(--color-gold);"></i>
                      <span><?php echo htmlspecialchars($class['start_time']); ?></span>
                    </div>
                  </div>

                  <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                    <span style="color: var(--color-text-muted); font-size: 0.9rem;">
                      <i class="fas fa-users" style="margin-right: 5px;"></i> 0 / <?php echo $class['capacity']; ?> spots
                    </span>
                    <button class="btn btn--primary btn--sm" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Book Now</button>
                  </div>
                </div>

              </article>
            <?php endforeach; ?>
            
          </div>
        <?php else: ?>
          <div class="card" style="text-align: center; padding: var(--sp-8);">
            <i class="fas fa-search" style="font-size: 3rem; color: var(--color-gold); opacity: 0.5; margin-bottom: 1rem;"></i>
            <h3>No classes found</h3>
            <p class="text-muted">Try adjusting your filters on the right to see more options.</p>
          </div>
        <?php endif; ?>

      </section>

      <aside style="flex: 0 0 300px; width: 100%; position: sticky; top: 100px;">
        <section class="card" style="border-top: 4px solid var(--color-gold);" aria-label="Filter classes">
          <h3 style="margin-bottom: var(--sp-4); text-transform: uppercase;"><i class="fas fa-sliders-h" style="color: var(--color-gold); margin-right: 8px;"></i> Filters</h3>
          
          <form action="plan.php" method="GET" style="display: flex; flex-direction: column; gap: var(--sp-4);">
            
            <div class="input-group">
              <label for="filter-day" style="display: block; margin-bottom: 5px; color: var(--color-text-muted); font-size: 0.9rem;">Day of the week</label>
              <select id="filter-day" name="day" class="form-control" style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--color-border); color: white; padding: 10px; border-radius: 4px;">
                <option value="">Any Day</option>
                <option value="Monday" <?php if($dayFilter == 'Monday') echo 'selected'; ?>>Monday</option>
                <option value="Tuesday" <?php if($dayFilter == 'Tuesday') echo 'selected'; ?>>Tuesday</option>
                <option value="Wednesday" <?php if($dayFilter == 'Wednesday') echo 'selected'; ?>>Wednesday</option>
                <option value="Thursday" <?php if($dayFilter == 'Thursday') echo 'selected'; ?>>Thursday</option>
                <option value="Friday" <?php if($dayFilter == 'Friday') echo 'selected'; ?>>Friday</option>
                <option value="Saturday" <?php if($dayFilter == 'Saturday') echo 'selected'; ?>>Saturday</option>
              </select>
            </div>

            <div class="input-group">
              <label for="filter-type" style="display: block; margin-bottom: 5px; color: var(--color-text-muted); font-size: 0.9rem;">Class Type</label>
              <select id="filter-type" name="type" class="form-control" style="width: 100%; background: rgba(0,0,0,0.3); border: 1px solid var(--color-border); color: white; padding: 10px; border-radius: 4px;">
                <option value="all">All Types</option>
                <option value="Strength" <?php if($typeFilter == 'Strength') echo 'selected'; ?>>Strength</option>
                <option value="HIIT" <?php if($typeFilter == 'HIIT') echo 'selected'; ?>>HIIT & Cardio</option>
                <option value="Yoga" <?php if($typeFilter == 'Yoga') echo 'selected'; ?>>Yoga & Mobility</option>
                <option value="Functional" <?php if($typeFilter == 'Functional') echo 'selected'; ?>>Functional</option>
                <option value="Pilates" <?php if($typeFilter == 'Pilates') echo 'selected'; ?>>Pilates</option>
              </select>
            </div>

            <button type="submit" class="btn btn--primary" style="margin-top: var(--sp-2); width: 100%;">Apply Filters</button>
            <a href="plan.php" class="btn btn--ghost" style="text-align: center; width: 100%;">Clear</a>
          </form>
        </section>
      </aside>

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