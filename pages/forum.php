<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $pdo = new PDO('sqlite:../database/forge_gym.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT fp.*, u.name as trainer_name, u.profile_photo as avatar, u.role as author_role
        FROM forum_posts fp 
        JOIN users u ON fp.user_id = u.id 
        ORDER BY fp.created_at DESC
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

$user_role = $_SESSION['role'] ?? 'user';
$current_user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Forum — FORGE GYM</title>
    <link rel="icon" type="image/png" href="../images/logo32x32.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
            <a href="shop.php?open_cart=1" class="nav__cart-btn cart-toggle" aria-label="Ir para o carrinho">
                <i class="fas fa-shopping-cart"></i>
                <span class="cart-badge global-badge" style="display: none;">0</span>
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

    <main class="container page-spacing" style="max-width: 800px;">
        
        <header class="page-header" style="text-align: center; margin-bottom: var(--sp-10);">
            <p class="eyebrow" style="justify-content: center;">Forge Community</p>
            <h1 class="page-title">THE FORGE FORUM</h1>
            <p class="text-muted">Exclusive updates from Management & Elite Trainers.</p>
        </header>

        <?php if ($user_role === 'trainer' || $user_role === 'admin'): 
            $stmt_avatar = $pdo->prepare("SELECT profile_photo, name FROM users WHERE id = ?");
            $stmt_avatar->execute([$current_user_id]);
            $curr_user = $stmt_avatar->fetch(PDO::FETCH_ASSOC);
            $currAvatar = (!empty($curr_user['profile_photo']) && $curr_user['profile_photo'] !== 'images/profiles/default.png' && file_exists(__DIR__ . '/../' . $curr_user['profile_photo']))
                            ? '../' . $curr_user['profile_photo']
                            : "https://ui-avatars.com/api/?name=" . urlencode($curr_user['name'] ?? 'User') . "&background=CBA052&color=fff&size=128&bold=true";
        ?>
            <section class="card" style="margin-bottom: var(--sp-8); border: 1px solid rgba(203, 160, 82, 0.4); background: rgba(203, 160, 82, 0.03); padding: var(--sp-6);">
                <div style="display: flex; gap: var(--sp-4); align-items: flex-start;">
                    <img src="<?php echo htmlspecialchars($currAvatar); ?>" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-gold); flex-shrink: 0; margin-top: 4px;">
                    <form action="../api/action_forum.php" method="POST" style="flex: 1; display: flex; flex-direction: column; gap: var(--sp-2); margin: 0;">
                        <input type="hidden" name="action" value="create_post">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <input type="text" name="title" placeholder="Topic Title (e.g., Squat Mechanics)" required
                               style="background: transparent; border: none; border-bottom: 1px solid rgba(255,255,255,0.1); color: #fff; font-family: var(--font-display); font-size: 1.3rem; padding: 8px 0; outline: none; transition: border-color 0.3s;"
                               onfocus="this.style.borderBottomColor='var(--color-gold)'" onblur="this.style.borderBottomColor='rgba(255,255,255,0.1)'">
                        
                        <textarea name="content" rows="3" placeholder="What knowledge are you dropping today?" required
                                  style="background: transparent; border: none; color: var(--color-text); font-family: var(--font-body); font-size: 1rem; padding: 12px 0; resize: vertical; min-height: 70px; outline: none;"></textarea>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: var(--sp-3); margin-top: var(--sp-2);">
                            <span style="font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                                <i class="fas fa-bullhorn" style="color: var(--color-gold); margin-right: 4px;"></i> Official Announcement
                            </span>
                            <button type="submit" class="btn btn--primary" style="padding: 8px 24px; font-size: 0.9rem; border-radius: 30px;">
                                Post to Feed <i class="fas fa-paper-plane" style="margin-left: 6px;"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        <?php endif; ?>

        <section>
            <?php if (empty($posts)): ?>
                <div class="card" style="text-align: center; padding: var(--sp-10); border: 1px dashed var(--color-border);">
                    <p class="text-muted" style="font-style: italic; margin: 0;">No posts shared yet today. Check back soon!</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): 
                    $postPhoto = $post['avatar'];
                    if (empty($postPhoto) || $postPhoto === 'images/profiles/default.png' || !file_exists(__DIR__ . '/../' . $postPhoto)) {
                        $postAvatar = "https://ui-avatars.com/api/?name=" . urlencode($post['trainer_name']) . "&background=CBA052&color=fff&size=128&bold=true";
                    } else {
                        $postAvatar = '../' . $postPhoto;
                    }

                    $authorPrefix = $post['author_role'] === 'admin' ? 'Admin' : 'Coach';
                ?>
                    <article class="card" style="margin-bottom: var(--sp-6); padding: var(--sp-6);">
                        
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--sp-4);">
                            <div style="display: flex; align-items: center; gap: var(--sp-3);">
                                <img src="<?php echo htmlspecialchars($postAvatar); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-gold); flex-shrink: 0;">
                                <div>
                                    <h3 style="font-size: var(--text-lg); font-family: var(--font-display); color: var(--color-text); margin: 0; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </h3>
                                    <small style="color: var(--color-gold); font-weight: 600;"><?php echo $authorPrefix . ' ' . htmlspecialchars($post['trainer_name']); ?> • <?php echo date('M d, H:i', strtotime($post['created_at'])); ?></small>
                                </div>
                            </div>
                            
                            <?php if ($user_role === 'admin' || ($user_role === 'trainer' && $post['user_id'] == $current_user_id)): ?>
                                <form action="../api/action_forum.php" method="POST" style="margin: 0; flex-shrink: 0;" onsubmit="return confirm('Delete this entire post and all its comments?');">
                                    <input type="hidden" name="action" value="delete_post">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                                    <button type="submit" style="background: transparent; border: none; color: var(--color-danger); cursor: pointer; padding: 4px;" title="Delete Post">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <p style="color: var(--color-text-muted); line-height: 1.6; white-space: pre-line; margin-bottom: var(--sp-6); overflow-wrap: break-word; word-break: break-word;">
                            <?php echo htmlspecialchars($post['content']); ?>
                        </p>

                        <div style="border-top: 1px solid rgba(255,255,255,0.08); padding-top: var(--sp-4);">
                            <h4 style="color: rgba(255,255,255,0.3); font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: var(--sp-3);">
                                <i class="far fa-comments"></i> Discussion
                            </h4>
                            
                            <?php
                            $stmt_comments = $pdo->prepare("
                                SELECT fc.*, u.name, u.role, u.profile_photo as avatar 
                                FROM forum_comments fc 
                                JOIN users u ON fc.user_id = u.id 
                                WHERE fc.post_id = ? 
                                ORDER BY fc.created_at ASC
                            ");
                            $stmt_comments->execute([$post['id']]);
                            $comments = $stmt_comments->fetchAll(PDO::FETCH_ASSOC);
                            ?>

                            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                                <?php foreach ($comments as $comment): 
                                    $indent = !empty($comment['parent_id']) ? '40px' : '0px';
                                    
                                    $commPhoto = $comment['avatar'];
                                    $commentAvatar = (!empty($commPhoto) && file_exists(__DIR__ . '/../' . $commPhoto)) 
                                                    ? '../' . $commPhoto 
                                                    : "https://ui-avatars.com/api/?name=" . urlencode($comment['name']) . "&background=222&color=fff&size=64&bold=true";
                                ?>
                                
                                <div id="comment-<?php echo $comment['id']; ?>" style="margin-left: <?php echo $indent; ?>;">
                                    <div style="display: flex; gap: 10px; padding: 10px; background: rgba(255,255,255,0.02); border-radius: 6px; border-left: <?php echo !empty($comment['parent_id']) ? '2px solid var(--color-gold)' : 'none'; ?>;">
                                        <img src="<?php echo htmlspecialchars($commentAvatar); ?>" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; flex-shrink: 0; display: block;">
                                        <div style="flex: 1;">
                                            <strong style="font-size: 0.8rem; color: var(--color-gold);"><?php echo htmlspecialchars($comment['name']); ?></strong>
                                            <p style="font-size: 0.85rem; margin: 2px 0; color: #fff;"><?php echo htmlspecialchars($comment['content']); ?></p>
                                            
                                            <button onclick="document.getElementById('reply-form-<?php echo $comment['id']; ?>').style.display='block'" 
                                                    style="background:none; border:none; color:rgba(255,255,255,0.5); font-size: 0.7rem; cursor:pointer;">
                                                Reply
                                            </button>
                                        </div>
                                    </div>

                                    <div id="reply-form-<?php echo $comment['id']; ?>" style="display:none; margin-top: 5px; margin-left: <?php echo $indent; ?>;">
                                        <form action="../api/action_forum.php" method="POST" style="display: flex; gap: 5px;">
                                            <input type="hidden" name="action" value="create_comment">
                                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                            <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            
                                            <input type="text" name="content" placeholder="Reply to <?php echo htmlspecialchars($comment['name']); ?>..." required 
                                                style="flex: 1; background: #222; border: 1px solid #444; color: #fff; padding: 5px; border-radius: 4px;">
                                            <button type="submit" style="background: var(--color-gold); border:none; padding: 0 10px; border-radius: 4px; cursor:pointer;">OK</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if (($user_role === 'admin' || $user_role === 'trainer' || $post['author_role'] === 'trainer') && $post['author_role'] !== 'admin'): ?>
                                <form action="../api/action_forum.php" method="POST" style="display: flex; gap: var(--sp-3); align-items: flex-start; margin-top: var(--sp-4); padding: var(--sp-3); background: rgba(203, 160, 82, 0.03); border: 1px solid rgba(203, 160, 82, 0.4); border-radius: var(--radius-md);">
                                    
                                    <img src="<?php echo htmlspecialchars($currAvatar); ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--color-gold); flex-shrink: 0;">
                                    
                                    <div style="flex: 1; display: flex; flex-direction: column; gap: var(--sp-2);">
                                        <input type="hidden" name="action" value="create_comment">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        
                                        <textarea name="content" placeholder="Write a reply..." required 
                                                style="background: transparent; border: none; color: var(--color-text); font-family: var(--font-body); font-size: 0.9rem; resize: vertical; min-height: 40px; outline: none; width: 100%;"></textarea>
                                        
                                        <div style="display: flex; justify-content: flex-end;">
                                            <button type="submit" class="btn btn--primary" style="padding: 6px 16px; font-size: 0.8rem; border-radius: 20px; text-transform: uppercase;">
                                                Reply
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            <?php endif; ?>
                            
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
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