<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $pdo = new PDO('sqlite:../database/forge_gym.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT fp.*, u.name as trainer_name, u.profile_photo as avatar
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

    <main class="container page-spacing" style="max-width: 800px;">
        
        <header class="page-header" style="text-align: center; margin-bottom: var(--sp-10);">
            <p class="eyebrow" style="justify-content: center;">Forge Community</p>
            <h1 class="page-title">THE FORGE FORUM</h1>
            <p class="text-muted">Exclusive daily tips from our Elite Coaches & member discussions.</p>
        </header>

        <?php if ($user_role === 'trainer'): ?>
            <section class="card" style="margin-bottom: var(--sp-8); border: 1px solid var(--color-gold);">
                <h2 style="font-family: var(--font-display); font-size: var(--text-xl); color: var(--color-gold); margin-bottom: var(--sp-4); text-transform: uppercase;">
                    <i class="far fa-lightbulb"></i> Share Today's Elite Tip
                </h2>
                <form action="../api/action_forum.php" method="POST" class="form">
                    <input type="hidden" name="action" value="create_post">
                    
                    <div class="form__group">
                        <label class="form__label">Tip Topic</label>
                        <input type="text" name="title" class="form__input" placeholder="e.g., Squat Mechanics, Hydration" required>
                    </div>
                    
                    <div class="form__group">
                        <label class="form__label">Tip Content</label>
                        <textarea name="content" class="form__input" rows="4" placeholder="Drop your knowledge, form tips, or motivation here..." required style="resize: vertical;"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn--primary" style="width: 100%;">Publish to Feed</button>
                </form>
            </section>
        <?php endif; ?>

        <section>
            <?php if (empty($posts)): ?>
                <div class="card" style="text-align: center; padding: var(--sp-10); border: 1px dashed var(--color-border);">
                    <p class="text-muted" style="font-style: italic; margin: 0;">No knowledge drops shared yet today. Check back soon!</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): 
                    $postPhoto = $post['avatar'];
                    if (empty($postPhoto) || $postPhoto === 'images/profiles/default.png' || !file_exists(__DIR__ . '/../' . $postPhoto)) {
                        $postAvatar = "https://ui-avatars.com/api/?name=" . urlencode($post['trainer_name']) . "&background=CBA052&color=fff&size=128&bold=true";
                    } else {
                        $postAvatar = '../' . $postPhoto;
                    }
                ?>
                    <article class="card" style="margin-bottom: var(--sp-6);">
                        
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--sp-4);">
                            <div style="display: flex; align-items: center; gap: var(--sp-3);">
                                <img src="<?php echo htmlspecialchars($postAvatar); ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-gold); flex-shrink: 0;">
                                <div>
                                    <h3 style="font-size: var(--text-lg); font-family: var(--font-display); color: var(--color-text); margin: 0; text-transform: uppercase;">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </h3>
                                    <small style="color: var(--color-gold); font-weight: 600;">Coach <?php echo htmlspecialchars($post['trainer_name']); ?> • <?php echo date('M d, H:i', strtotime($post['created_at'])); ?></small>
                                </div>
                            </div>
                            
                            <?php if ($user_role === 'admin' || ($user_role === 'trainer' && $post['user_id'] == $current_user_id)): ?>
                                <form action="../api/action_forum.php" method="POST" style="margin: 0; flex-shrink: 0;" onsubmit="return confirm('Delete this entire post and all its comments?');">
                                    <input type="hidden" name="action" value="delete_post">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" style="background: transparent; border: none; color: var(--color-danger); cursor: pointer; padding: 4px;" title="Delete Post">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <p style="color: var(--color-text-muted); line-height: 1.6; white-space: pre-line; margin-bottom: var(--sp-6); overflow-wrap: break-word; word-break: break-word;">
                            <?php echo htmlspecialchars($post['content']); ?>
                        </p>

                        <div style="border-top: 1px solid var(--color-border); padding-top: var(--sp-4);">
                            <h4 style="color: var(--color-text-muted); font-size: var(--text-sm); font-weight: 600; text-transform: uppercase; margin-bottom: var(--sp-3);">
                                <i class="far fa-comments"></i> Community Discussion
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

                            <div style="margin-bottom: var(--sp-4);">
                                <?php foreach ($comments as $comment): 
                                    $commPhoto = $comment['avatar'];
                                    if (empty($commPhoto) || $commPhoto === 'images/profiles/default.png' || !file_exists(__DIR__ . '/../' . $commPhoto)) {
                                        $commentAvatar = "https://ui-avatars.com/api/?name=" . urlencode($comment['name']) . "&background=333&color=fff&size=64&bold=true";
                                    } else {
                                        $commentAvatar = '../' . $commPhoto;
                                    }
                                    
                                    // Cores dinâmicas baseadas no cargo
                                    $borderColor = $comment['role'] === 'admin' ? 'var(--color-danger)' : ($comment['role'] === 'trainer' ? 'var(--color-gold)' : 'var(--color-border)');
                                ?>
                                    <div style="display: flex; gap: var(--sp-3); align-items: flex-start; padding: var(--sp-3); background: rgba(255,255,255,0.02); border-radius: var(--radius-sm); margin-bottom: 8px; border-left: 3px solid <?php echo $borderColor; ?>;">
                                        
                                        <img src="<?php echo htmlspecialchars($commentAvatar); ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid rgba(255,255,255,0.1);">
                                        
                                        <div style="flex: 1; min-width: 0;">
                                            <div style="display: flex; align-items: baseline; gap: 8px; margin-bottom: 2px;">
                                                <strong style="font-size: 0.9rem; color: var(--color-text);">
                                                    <?php echo htmlspecialchars($comment['name']); ?>
                                                </strong>
                                                <span class="badge" style="font-size: 0.6rem; padding: 2px 6px; background: <?php echo $borderColor; ?>; color: <?php echo $comment['role'] === 'user' ? 'var(--color-text)' : '#fff'; ?>;">
                                                    <?php echo $comment['role']; ?>
                                                </span>
                                            </div>
                                            <p style="font-size: 0.95rem; color: var(--color-text-muted); margin: 0; overflow-wrap: break-word; word-break: break-word; white-space: pre-wrap; line-height: 1.45;">
                                                <?php echo htmlspecialchars($comment['content']); ?>
                                            </p>
                                        </div>

                                        <?php if ($user_role === 'admin' || ($user_role === 'trainer' && $comment['user_id'] == $current_user_id)): ?>
                                            <form action="../api/action_forum.php" method="POST" style="margin:0; flex-shrink: 0;" onsubmit="return confirm('Delete this comment?');">
                                                <input type="hidden" name="action" value="delete_comment">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                <button type="submit" style="background: transparent; border: none; color: var(--color-danger); opacity: 0.7; cursor: pointer; padding: 4px;" title="Remove Comment">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <form action="../api/action_forum.php" method="POST" class="form" style="display: flex; gap: var(--sp-2); align-items: stretch; margin: 0;">
                                <input type="hidden" name="action" value="create_comment">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                
                                <div style="flex: 1; margin: 0;">
                                    <input type="text" name="content" class="form__input" placeholder="Ask a question or reply..." required style="margin: 0;">
                                </div>
                                <button type="submit" class="btn btn--primary" style="flex-shrink: 0; margin: 0;">Reply</button>
                            </form>
                            
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