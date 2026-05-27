<?php
session_start();

// Guardar quem é o utilizador de forma fácil para usar no HTML
$userRole = $_SESSION['role'] ?? 'user';
$userTrainerId = $_SESSION['trainer_id'] ?? null;

define('DB_PATH', __DIR__ . '/../database/forge_gym.db');

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_student') {
    $enrollment_id = (int)$_POST['enrollment_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM enrollments WHERE id = ?");
        $stmt->execute([$enrollment_id]);
        
        header("Location: plan.php"); 
        exit;
    } catch (PDOException $e) {
        $error_msg = "Failed to remove member: " . $e->getMessage();
    }
}

// Lógica de Filtros
$typeFilter = $_GET['type'] ?? 'all';
$dayFilter = $_GET['day'] ?? '';

$query = "SELECT c.*, t.name as trainer_name,
                 (SELECT COUNT(*) FROM enrollments WHERE class_id = c.id) as current_spots,
                 (SELECT AVG(rating) FROM reviews WHERE class_id = c.id) as avg_rating,
                 (SELECT COUNT(*) FROM reviews WHERE class_id = c.id) as review_count
          FROM classes c 
          JOIN trainers t ON c.trainer_id = t.id 
          WHERE 1=1";

if ($typeFilter !== 'all' && $typeFilter !== '') {
    $query .= " AND c.type = " . $pdo->quote($typeFilter);
}
if ($dayFilter !== '') {
    $query .= " AND c.day_of_week = " . $pdo->quote($dayFilter);
}

$query .= " ORDER BY c.class_date ASC, c.start_time ASC";

$classes = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);

$user_enrollments = [];
if (isset($_SESSION['user_id'])) {
    $stmtUserEnrolled = $pdo->prepare("SELECT class_id FROM enrollments WHERE user_id = ?");
    $stmtUserEnrolled->execute([$_SESSION['user_id']]);
    $user_enrollments = $stmtUserEnrolled->fetchAll(PDO::FETCH_COLUMN);
}

$currentDate = date('Y-m-d');
$currentTime = date('H:i');

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
          <li><a href="forum.php" class="nav__link">Forum</a></li>
          <li><a href="shop.php" class="nav__link">Shop</a></li>
        </ul>
        <div class="nav__actions">
          <a href="shop.php?open_cart=1" class="nav__cart-btn" aria-label="Ir para o carrinho">
            <i class="fas fa-shopping-cart"></i>
          </a>
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
            
            <?php foreach ($classes as $class): 
                // Verificar se a aula já terminou
                $hasEnded = false;
                if ($class['class_date'] < $currentDate) {
                    $hasEnded = true;
                } elseif ($class['class_date'] === $currentDate && $currentTime >= $class['end_time']) {
                    $hasEnded = true;
                }
                
                // Formatar data real: ex: "18/05"
                $formattedDate = date('d/m', strtotime($class['class_date']));
            ?>
              <article class="equip-card" style="border: 1px solid <?php echo $hasEnded ? 'rgba(255,255,255,0.1)' : 'var(--color-border)'; ?>; <?php echo $hasEnded ? 'opacity: 0.8;' : ''; ?>">
                
                <div class="equip-card__image" style="position: relative;">
                  <img src="<?php echo getClassImage($class['type']); ?>" alt="<?php echo htmlspecialchars($class['name']); ?>" style="<?php echo $hasEnded ? 'filter: grayscale(80%);' : ''; ?>">
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
                      <span><?php echo htmlspecialchars($class['day_of_week']); ?> (<?php echo $formattedDate; ?>)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; color: var(--color-text);">
                      <i class="far fa-clock" style="color: var(--color-gold);"></i>
                      <span><?php echo htmlspecialchars($class['start_time']); ?></span>
                    </div>
                  </div>

                  <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                    
                    <?php if ($hasEnded): ?>
                        <span style="color: var(--color-text-muted); font-size: 0.85rem;">
                          <i class="fas fa-users" style="color: var(--color-gold); margin-right: 5px;"></i> <?php echo $class['current_spots']; ?> attended
                        </span>
                        
                        <span style="color: var(--color-text-muted); font-size: 0.85rem; font-weight: 600;">
                          <i class="fas fa-star" style="color: var(--color-gold); margin-right: 3px;"></i> 
                          <?php echo $class['review_count'] > 0 ? round($class['avg_rating'], 1) . ' (' . $class['review_count'] . ' revs)' : 'No reviews'; ?>
                        </span>

                    <?php else: ?>
                        <span style="color: var(--color-text-muted); font-size: 0.9rem;">
                          <i class="fas fa-users" style="margin-right: 5px;"></i> 
                          <?php echo $class['current_spots']; ?> / <?php echo $class['capacity']; ?> spots
                        </span>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                          <div style="display: flex; flex-direction: column; gap: 5px; margin-top: 10px;">
    
                          <?php if ($userRole === 'trainer' && $userTrainerId == $class['trainer_id']): ?>
                              <span class="btn btn--ghost btn--sm" style="padding: 0.3rem 0.8rem; font-size: 0.8rem; border-color: var(--color-gold); color: var(--color-gold); cursor: default; opacity: 0.8; text-align: center;">Your Class</span>
                          <?php endif; ?>

                          <?php if (($userRole === 'trainer' && $userTrainerId == $class['trainer_id']) || $userRole === 'admin'): ?>
                              <button type="button" onclick="openRosterModal(<?php echo $class['id']; ?>)" class="btn btn--ghost btn--sm" style="padding: 0.2rem 0.6rem; font-size: 0.75rem; border-color: #666; color: #ddd; width: 100%;">
                                  <i class="fas fa-users-cog"></i> View Roster
                              </button>
                          <?php endif; ?>

                          <?php if (!($userRole === 'trainer' && $userTrainerId == $class['trainer_id'])): ?>
                              <form action="enroll.php" method="POST" style="margin: 0; margin-top: 5px;">
                                  <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                  <?php if (in_array($class['id'], $user_enrollments)): ?>
                                      <input type="hidden" name="action" value="cancel">
                                      <button type="submit" class="btn btn--ghost btn--sm" style="padding: 0.5rem 1rem; font-size: 0.9rem; border-color: var(--color-accent); color: var(--color-accent); width: 100%;">Cancel Booking</button>
                                  <?php else: ?>
                                      <input type="hidden" name="action" value="book">
                                      <?php if ($class['current_spots'] >= $class['capacity']): ?>
                                          <button type="button" class="btn btn--ghost btn--sm" style="padding: 0.5rem 1rem; font-size: 0.9rem; opacity: 0.5; width: 100%;" disabled>Full</button>
                                      <?php else: ?>
                                          <button type="submit" class="btn btn--primary btn--sm" style="padding: 0.5rem 1rem; font-size: 0.9rem; width: 100%;">Book Now</button>
                                      <?php endif; ?>
                                  <?php endif; ?>
                              </form>
                          <?php endif; ?>
                      </div>
                        <?php else: ?>
                          <a href="login.php" class="btn btn--primary btn--sm" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Book Now</a>
                        <?php endif; ?>
                    <?php endif; ?>
                  </div>

                  <?php 
                    $canEdit = false;
                    if ($userRole === 'admin') {
                        $canEdit = true; // Admin pode tudo
                    } elseif ($userRole === 'trainer' && $userTrainerId == $class['trainer_id']) {
                        $canEdit = true; // Trainer só pode se a aula for dele
                    }
                  ?>

                  <?php if ($canEdit): ?>
                    <div style="display: flex; gap: 10px; margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 15px;">
                      
                      <a href="edit_class.php?id=<?php echo $class['id']; ?>" class="btn btn--ghost btn--sm" style="flex: 1; text-align: center; font-size: 0.8rem; padding: 0.4rem;">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                      
                      <button type="button" onclick="openDeleteModal(<?php echo $class['id']; ?>)" class="btn btn--ghost btn--sm" style="flex: 1; width: 100%; color: #ff8a75; border-color: #ff8a75; font-size: 0.8rem; padding: 0.4rem;">
                        <i class="fas fa-trash"></i> Delete
                      </button>

                    </div>
                  <?php endif; ?>
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
          
          <?php if ($userRole === 'admin' || $userRole === 'trainer'): ?>
            <div style="margin-bottom: var(--sp-4); padding-bottom: var(--sp-4); border-bottom: 1px solid rgba(255,255,255,0.1);">
              <a href="create_class.php" class="btn btn--primary" style="width: 100%; background: var(--color-accent); border-color: var(--color-accent);">
                <i class="fas fa-plus"></i> Add New Class
              </a>
            </div>
          <?php endif; ?>

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
  <div id="delete-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 9999; justify-content: center; align-items: center;">
    <div class="card" style="max-width: 420px; width: 90%; border-top: 4px solid var(--color-accent); text-align: center; padding: var(--sp-6); background: #1a1a1a; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
      <i class="fas fa-exclamation-triangle" style="font-size: 3.5rem; color: var(--color-accent); margin-bottom: var(--sp-4);"></i>
      <h3 style="text-transform: uppercase; font-family: 'Barlow Condensed', sans-serif; font-size: 1.8rem; letter-spacing: 1px; margin-bottom: 10px; color: white;">Delete Class?</h3>
      <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: var(--sp-6); line-height: 1.5;">Are you sure you want to permanently remove this class from the schedule? This action cannot be undone.</p>
      
      <div style="display: flex; gap: 15px;">
        <button onclick="closeDeleteModal()" class="btn btn--ghost" style="flex: 1; padding: 10px; font-size: 0.9rem;">Cancel</button>
        <form id="modal-delete-form" action="../api/action_delete_class.php" method="POST" style="flex: 1; margin: 0;">
          <input type="hidden" name="class_id" id="modal-class-id" value="">
          <button type="submit" class="btn btn--primary" style="width: 100%; background: var(--color-accent); border-color: var(--color-accent); padding: 10px; font-size: 0.9rem;">Delete</button>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openDeleteModal(classId) {
      document.getElementById('modal-class-id').value = classId;
      document.getElementById('delete-modal').style.display = 'flex';
    }
    function closeDeleteModal() {
      document.getElementById('delete-modal').style.display = 'none';
    }
    // Fechar o modal se clicarem fora dele por acidente
    window.onclick = function(event) {
      const modal = document.getElementById('delete-modal');
      if (event.target == modal) {
        closeDeleteModal();
      }
    }
  </script>    
  <div id="roster-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 9999; justify-content: center; align-items: center;">
  <div class="card" style="width: 400px; padding: 20px; background: #1a1a1a;">
    <h3 style="color: var(--color-gold);">Class Roster</h3>
    <div id="roster-list" style="margin-top: 15px; max-height: 300px; overflow-y: auto;"></div>
    <button onclick="document.getElementById('roster-modal').style.display='none'" class="btn btn--ghost" style="margin-top: 15px; width: 100%;">Close</button>
  </div>
</div>

<script>
function openRosterModal(classId) {
  const modal = document.getElementById('roster-modal');
  const list = document.getElementById('roster-list');
  list.innerHTML = '<p style="text-align:center;">Loading...</p>';
  modal.style.display = 'flex';
  
  fetch('../api/get_roster.php?class_id=' + classId)
    .then(r => r.json())
    .then(data => {
      if (!data || data.length === 0) {
        list.innerHTML = '<p style="text-align:center; color:#888; font-style:italic;">No members enrolled yet.</p>';
      } else {
        list.innerHTML = data.map(u => {
          const photo = u.avatar || u.profile_photo || 'images/profiles/profile1.png';
          
          return `
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; padding:10px; border-bottom:1px solid #333;">
            
            <div style="display:flex; align-items:center; gap:10px;">
              <img src="../${photo}" 
                   style="width:40px; height:40px; border-radius:50%; object-fit:cover;"
                   onerror="this.src='../images/profiles/profile1.png';">
              <div>
                <strong style="color: white;">${u.name}</strong><br>
                <small style="color:var(--color-text-muted)">${u.email}</small>
              </div>
            </div>

            <form action="plan.php" method="POST" style="margin:0;" onsubmit="return confirm('Remove ${u.name} from this class?');">
              <input type="hidden" name="action" value="remove_student">
              <input type="hidden" name="enrollment_id" value="${u.enrollment_id}">
              <button type="submit" class="btn btn--sm" style="background: rgba(231, 76, 60, 0.1); border: 1px solid #e74c3c; color: #e74c3c; padding: 4px 8px; font-size: 0.8rem; cursor: pointer; border-radius: 4px;" title="Remove Member">
                  <i class="fas fa-user-times"></i>
              </button>
            </form>

          </div>
        `}).join('');
      }
    })
    .catch(err => {
      list.innerHTML = '<p style="text-align:center; color:red;">Error loading list.</p>';
    });
}
</script>   
</body>
</html>