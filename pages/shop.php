<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Shop — FORGE GYM</title>
  <link rel="icon" type="image/png" href="../images/logo32x32.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css" />
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
    <div class="container">
      <header class="page-header" style="text-align: center; margin-bottom: var(--sp-10);">
        <p class="eyebrow">Represent the forge</p>
        <h1 class="page-title">FORGE <span class="page-title--outline">GEAR</span></h1>
        <p class="section-sub" style="margin-top: var(--sp-2);">Premium gym apparel and accessories to fuel your workouts.</p>
      </header>

      <section class="shop-grid" aria-label="Merchandise products">
        
        <article class="product-card card">
          <div class="product-card__image">
            <span class="badge badge--danger product-badge">New</span>
            <img src="https://images.unsplash.com/photo-1556821840-3a63f95609a7?q=80&w=800&auto=format&fit=crop" alt="Forge Premium Hoodie" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Premium Oversized Hoodie</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">Black / Heavyweight Cotton</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">45.00€</span>
              <button type="button" class="btn btn--primary btn--sm" aria-label="Add Premium Oversized Hoodie to cart">
                <i class="fas fa-shopping-cart"></i> Add
              </button>
            </div>
          </div>
        </article>

        <article class="product-card card">
          <div class="product-card__image">
            <img src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?q=80&w=800&auto=format&fit=crop" alt="Forge Pump T-Shirt" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Forge Pump T-Shirt</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">White / Breathable fit</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">25.00€</span>
              <button type="button" class="btn btn--primary btn--sm" aria-label="Add Forge Pump T-Shirt to cart">
                <i class="fas fa-shopping-cart"></i> Add
              </button>
            </div>
          </div>
        </article>

        <article class="product-card card">
          <div class="product-card__image">
            <span class="badge badge--dark product-badge">Best Seller</span>
            <img src="https://images.unsplash.com/photo-1622543925917-763c34d1a86e?q=80&w=800&auto=format&fit=crop" alt="Pro Shaker 700ml" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Pro Shaker 700ml</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">Matte Black / BPA Free</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">12.50€</span>
              <button type="button" class="btn btn--primary btn--sm" aria-label="Add Pro Shaker 700ml to cart">
                <i class="fas fa-shopping-cart"></i> Add
              </button>
            </div>
          </div>
        </article>

        <article class="product-card card" style="opacity: 0.7;">
          <div class="product-card__image">
            <span class="badge badge--warning product-badge" style="background: var(--color-bg); color: var(--color-text-muted); border: 1px solid var(--color-border);">Out of Stock</span>
            <img src="https://images.unsplash.com/photo-1553062407-98eeb64c6a62?q=80&w=800&auto=format&fit=crop" alt="Tactical Gym Duffle" style="filter: grayscale(80%);" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Tactical Gym Duffle</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">Olive Green / Waterproof</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">55.00€</span>
              <button type="button" class="btn btn--ghost btn--sm" disabled style="opacity: 0.5; cursor: not-allowed;">
                Sold Out
              </button>
            </div>
          </div>
        </article>

      </section>
    </div>
  </main>

  <footer class="footer">
    <div class="container footer__inner">
      <a href="../index.php" class="logo footer__logo" aria-label="FORGE GYM homepage">
        <img src="../images/logo.png" alt="FORGE GYM" class="logo__img" />
      </a>
      <ul class="footer__nav">
        <li><a href="../index.php">Home</a></li>
        <li><a href="plan.php">Classes</a></li>
        <li><a href="shop.php">Shop</a></li>
      </ul>
      <p class="footer__address">Rua Dr. Roberto Frias, 4200-465 Porto &nbsp;|&nbsp; info@forgegym.pt</p>
    </div>
    <div class="footer__bottom">
      <p>&copy; 2026 FORGE GYM. All rights reserved.</p>
    </div>
  </footer>

</body>
</html>