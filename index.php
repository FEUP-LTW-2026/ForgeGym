<?php 
session_start();

$user_plan = 'none';

if (isset($_SESSION['user_id'])) {
    try {
        $pdo = new PDO('sqlite:' . __DIR__ . '/database/forge_gym.db');
        $stmt = $pdo->prepare("SELECT plan FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user_plan = $stmt->fetchColumn() ?: 'none';
    } catch (Exception $e) {
        // Ignorar erro de DB silenciosamente na homepage
    }
}
?>
<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FORGE GYM</title>
    <link rel="icon" type="image/png" href="images/logo32x32.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;900&family=Barlow:wght@400;500;600&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="css/style.css" />
  </head>

  <body>
    <header class="header">
      <div class="container header__inner">
        <a href="index.php" class="logo" aria-label="FORGE GYM homepage">
          <img src="images/logo.png" alt="FORGE GYM" class="logo__img" />
          <span class="logo__text">FORGE GYM</span>
        </a>
        <input type="checkbox" id="nav-toggle" class="sr-only peer-nav" />
        <label for="nav-toggle" class="nav__toggle" aria-label="Open navigation menu">
          <span></span><span></span><span></span>
        </label>
        <nav class="nav" id="main-nav" aria-label="Primary navigation">
          <ul class="nav__list">
            <li><a href="pages/plan.php" class="nav__link">Classes</a></li>
            <li><a href="pages/shop.php" class="nav__link">Shop</a></li>
          </ul>
          <div class="nav__actions">
            <a href="pages/shop.php?open_cart=1" class="nav__cart-btn" aria-label="Ir para o carrinho">
              <i class="fas fa-shopping-cart"></i>
            </a>
            <?php if (isset($_SESSION['user_id'])): ?>
              <a href="pages/profile.php" class="btn btn--ghost">Profile</a>
              <a href="pages/logout.php" class="btn btn--primary">Log out</a>
            <?php else: ?>
              <a href="pages/login.php" class="btn btn--ghost">Log in</a>
              <a href="pages/register.php" class="btn btn--primary">Join now</a>
            <?php endif; ?>
          </div>
        </nav>
      </div>
    </header>

    <main>
      <section class="hero" aria-labelledby="hero-title">
        <div class="hero__bg" aria-hidden="true"></div>

        <div class="container hero__content">
          <p class="eyebrow">No excuses, No shortcuts.</p>
          <h1 class="hero__title" id="hero-title">
            FORGE<br />
            <span class="hero__title--outline">YOUR</span><br />
            BEST SELF
          </h1>
          <p class="hero__sub">
            Book classes, see our equipment, and train with coaches
            who push you further than you thought possible.
          </p>
          <div class="hero__cta">
            <?php if ($user_plan !== 'none'): ?>
              <a href="#plans" class="btn btn--primary btn--lg">Upgrade now</a>
            <?php else: ?>
              <a href="#plans" class="btn btn--primary btn--lg">Start for free</a>
            <?php endif; ?>
            <a href="pages/plan.php" class="btn btn--ghost btn--lg">Explore classes</a>
          </div>
        </div>

        <div class="stats" aria-label="Gym highlights">
          <div class="container stats__inner">
            <div class="stat">
              <strong class="stat__number">40<span>+</span></strong>
              <span class="stat__label">Weekly classes</span>
            </div>
            <div class="stat">
              <strong class="stat__number">5</strong>
              <span class="stat__label">Expert trainers</span>
            </div>
            <div class="stat">
              <strong class="stat__number">98<span>%</span></strong>
              <span class="stat__label">Member satisfaction</span>
            </div>
            <div class="stat">
              <strong class="stat__number">50<span>+</span></strong>
              <span class="stat__label">Equipment pieces</span>
            </div>
          </div>
        </div>
      </section>
      <section class="section" id="equipment" aria-labelledby="equipment-title">
        <div class="container">

          <header class="section__header">
            <h1 class="equipment__title">
              GYM <span class="equipment__title--outline">FLOOR</span>
            </h1>
            <h2 class="section__title" id="equipment-title">Our Equipment</h2>
            <p class="section__desc">
              Everything you need to train the way you want —
              cardio, strength, functional and more.
            </p>
          </header>

          <ul class="equip-grid" aria-label="Equipment catalogue">
            <?php
            // Ligar à base de dados para ir buscar os equipamentos dinamicamente
            try {
                $pdo = new PDO('sqlite:' . __DIR__ . '/database/forge_gym.db');
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Vai buscar todos os equipamentos ordenados pela zona e depois pelo nome
                $stmt = $pdo->query("SELECT * FROM equipment ORDER BY zone, name");
                
                while ($equip = $stmt->fetch(PDO::FETCH_ASSOC)):
            ?>
            <li class="equip-card" data-id="<?php echo $equip['id']; ?>">
              <div class="equip-card__image">
                <img src="<?php echo htmlspecialchars($equip['image_path']); ?>" alt="<?php echo htmlspecialchars($equip['name']); ?>" loading="lazy" />
              </div>
              <div class="equip-card__info">
                <h3 class="equip-card__name"><?php echo htmlspecialchars($equip['name']); ?></h3>
                <p class="equip-card__detail">
                  <span aria-hidden="true">📍</span> <?php echo $equip['total_units']; ?> units · <?php echo htmlspecialchars($equip['zone']); ?>
                </p>
              </div>
            </li>
            <?php 
                endwhile;
            } catch (PDOException $e) {
                echo "<p style='color: white;'>Error loading equipment catalogue.</p>";
            }
            ?>
          </ul>
  
        </div>
      </section>

      <section class="section" id="plans" aria-labelledby="plans-title">
        <div class="container">

          <header class="section__header">
            <h2 class="equipment__title">
              FLEXIBLE <span class="equipment__title--outline">PLANS</span>
            </h2>
            <p class="section__desc">
              Choose the membership that fits your goals. No hidden fees, cancel anytime.
            </p>
          </header>

          <div class="plans-grid">
            
            <div class="plan-card">
              <div class="plan-card__name">BASIC OP</div>
              <div class="plan-card__price">
                <span class="price-decimals">30€</span>
                <span class="price-period">/month</span>
              </div>

              <ul class="plan-features">
                <li class="invisible-line" aria-hidden="true">fantasma</li>
                <li>Gym floor & cardio zone</li>
                <li>Locker room access</li>
                <li>Off-peak hours only</li>
                <li class="invisible-line" aria-hidden="true">fantasma</li>
              </ul>

              <?php if ($user_plan === 'Basic'): ?>
                <div class="btn btn--ghost" style="width: 100%; text-align: center; opacity: 0.5; cursor: not-allowed;">Current Plan</div>
              <?php else: ?>
                <button type="button" class="btn btn--primary plan-checkout-btn" data-plan="Basic" data-price="30.00€" style="width: 100%;">Join Now</button>
              <?php endif; ?>
            
            </div>


            <div class="plan-card plan-card--highlight">
              <div class="plan-card__badge">Recommended Choice</div>
              <div class="plan-card__name">CLASSIC</div>
              <div class="plan-card__price">
                <span class="price-decimals">45€</span>
                <span class="price-period">/month</span>
              </div>

              <ul class="plan-features">
                <li>24/7 Gym access</li>
                <li>All gym floor zones</li>
                <li>Unlimited group classes</li>
                <li>Locker room access</li>
              </ul>

              <?php if ($user_plan === 'Classic'): ?>
                <div class="btn btn--ghost" style="width: 100%; text-align: center; opacity: 0.6; cursor: not-allowed; border-color: white; color: white;">Current Plan</div>
              <?php else: ?>
                <button type="button" class="btn btn--white plan-checkout-btn" data-plan="Classic" data-price="45.00€" style="width: 100%;">Join Now</button>
              <?php endif; ?>

            </div>

            <div class="plan-card">
              <div class="plan-card__name">PREMIUM</div>
              <div class="plan-card__price">
                <span class="price-decimals">60€</span>
                <span class="price-period">/month</span>
              </div>

              <ul class="plan-features">
                <li>24/7 Gym access</li>
                <li>Unlimited group classes</li>
                <li>1 PT Session / month</li>
                <li>Free towel & water service</li>
                <li>Bring a friend (Weekends)</li>
              </ul>
              
              <?php if ($user_plan === 'Premium'): ?>
                <div class="btn btn--ghost" style="width: 100%; text-align: center; opacity: 0.5; cursor: not-allowed;">Current Plan</div>
              <?php else: ?>
                <button type="button" class="btn btn--primary plan-checkout-btn" data-plan="Premium" data-price="60.00€" style="width: 100%;">Join Now</button>
              <?php endif; ?>

            </div>
          </div>
      </section>

      <section class="section" id="reviews" aria-labelledby="reviews-title">
        <div class="container">

          <header class="section__header">
            <h2 class="equipment__title">
              MEMBER <span class="equipment__title--outline">REVIEWS</span>
            </h2>
            <p class="section__desc">
              Don't just take our word for it. Hear what our community has to say about their journey.
            </p>
          </header>

          <div class="reviews-grid">
            
            <div class="review-card">
              <div class="review-card__avatar">
                <img src="images/profiles/profile1.png" alt="João Silva" loading="lazy" />
              </div>
              
              <div class="review-card__stars" aria-label="5 out of 5 stars">
                ★★★★★
              </div>
              <blockquote class="review-card__quote">
                "The best gym I've ever joined. The trainers actually care about your progress and the equipment is top-notch. The Classic plan is totally worth it!"
              </blockquote>
              <div class="review-card__author">
                <strong>João Silva</strong>
                <span>Member for 2 years</span>
              </div>
            </div>

            <div class="review-card">
              <div class="review-card__avatar">
                <img src="images/profiles/profile2.png" alt="Maria Santos" loading="lazy" />
              </div>
              
              <div class="review-card__stars" aria-label="5 out of 5 stars">
                ★★★★★
              </div>
              <blockquote class="review-card__quote">
                "Love the atmosphere! No egos, just people putting in the work. The classes are brutal but exactly what I needed to break my plateau."
              </blockquote>
              <div class="review-card__author">
                <strong>Maria Santos</strong>
                <span>Classic Plan</span>
              </div>
            </div>

            <div class="review-card">
              <div class="review-card__avatar">
                <img src="images/profiles/profile3.png" alt="Tiago Costa" loading="lazy" />
              </div>
              
              <div class="review-card__stars" aria-label="4 out of 5 stars">
                ★★★★☆
              </div>
              <blockquote class="review-card__quote">
                "Great facilities and the staff is super helpful. It gets a bit busy on Monday evenings, but there's enough equipment for everyone."
              </blockquote>
              <div class="review-card__author">
                <strong>Tiago Costa</strong>
                <span>Premium Plan</span>
              </div>
            </div>

          </div>

        </div>
      </section>

    </main>
    <footer class="footer">
      <div class="container footer__inner">
        
        <a href="index.php" class="logo footer__logo" aria-label="FORGE GYM homepage">
          <img src="images/logo.png" alt="FORGE GYM" class="logo__img" />
        </a>

        <ul class="footer__nav">
          <li><a href="#">Home</a></li>
          <li><a href="#equipment">Equipment</a></li>
          <li><a href="#plans">Plans</a></li>
          <li><a href="#reviews">Reviews</a></li>
        </ul>

        <p class="footer__address">
          Rua Dr. Roberto Frias, 4200-465 Porto &nbsp;|&nbsp; info@forgegym.pt
        </p>

      </div>
      
      <div class="footer__bottom">
        <p>&copy; 2026 FORGE GYM. All rights reserved.</p>
      </div>
    </footer>
    
    <div id="plan-checkout-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 10000; justify-content: center; align-items: center;">
      <div class="card" style="width: 90%; max-width: 500px; padding: 2rem; background: #1a1a1a; border-top: 4px solid var(--color-gold); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
          <h2 style="font-family: var(--font-display); color: white; margin: 0; letter-spacing: 1px;">JOIN <span id="checkout-plan-name" style="color: var(--color-gold);"></span></h2>
          <button type="button" id="close-plan-checkout" style="background: none; border: none; color: var(--color-text-muted); font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <form id="plan-checkout-form" style="display: flex; flex-direction: column; gap: 1rem;">
          
          <div class="form-group">
            <label for="p-name" class="form-label" style="color: #ccc; margin-bottom: 5px; display: block;">Name on Card</label>
            <input type="text" id="p-name" class="form-control" required placeholder="e.g. John Doe" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.3); border: 1px solid var(--color-border); color: white; border-radius: 4px;">
          </div>
          
          <h3 style="margin-top: 1rem; color: var(--color-gold); font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 5px;">Payment Details</h3>
          
          <div class="form-group">
            <label for="p-card" class="form-label" style="color: #ccc; margin-bottom: 5px; display: block;">Card Number</label>
            <input type="text" id="p-card" class="form-control" required placeholder="0000 0000 0000 0000" pattern="\d{16}" title="16-digit card number" maxlength="16" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.3); border: 1px solid var(--color-border); color: white; border-radius: 4px;">
          </div>
          
          <div style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex: 1;">
              <label for="p-exp" class="form-label" style="color: #ccc; margin-bottom: 5px; display: block;">Expiry Date</label>
              <input type="text" id="p-exp" class="form-control" required placeholder="MM/YY" maxlength="5" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.3); border: 1px solid var(--color-border); color: white; border-radius: 4px;">
            </div>
            <div class="form-group" style="flex: 1;">
              <label for="p-cvv" class="form-label" style="color: #ccc; margin-bottom: 5px; display: block;">CVV / CVC</label>
              <input type="password" id="p-cvv" class="form-control" required placeholder="123" pattern="\d{3,4}" title="3 or 4 digit security code" maxlength="4" style="width: 100%; padding: 10px; background: rgba(0,0,0,0.3); border: 1px solid var(--color-border); color: white; border-radius: 4px;">
            </div>
          </div>
          
          <button type="submit" class="btn btn--primary" style="margin-top: 1.5rem; width: 100%; font-size: 1.1rem;">
            <i class="fas fa-lock"></i> Subscribe for <span id="checkout-plan-price"></span>
          </button>
        </form>

      </div>
    </div>

    <div id="plan-success-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px); z-index: 10001; justify-content: center; align-items: center;">
      <div class="card" style="width: 90%; max-width: 400px; padding: 2rem; background: #1a1a1a; border-top: 4px solid #4caf50; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <i class="fas fa-check-circle" style="font-size: 4rem; color: #4caf50; margin-bottom: 1.5rem;"></i>
        <h2 style="font-family: var(--font-display); color: white; margin-bottom: 1rem; letter-spacing: 1px;">WELCOME TO THE FORGE</h2>
        <p style="color: var(--color-text-muted); margin-bottom: 1.5rem; line-height: 1.5;">
          Your membership is now active! Redirecting to your profile to generate your VIP Pass...
        </p>
      </div>
    </div>

    <script src="json/plans-checkout.js"></script>
    <script src="json/equipment-status.js"></script>
  </body>
  
</html>
