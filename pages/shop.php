pages/shop.php<?php
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
        </ul>
        <div class="nav__actions">
          <button id="cart-btn" class="nav__cart-btn" aria-label="Abrir carrinho">
            <i class="fas fa-shopping-cart"></i>
            <span id="cart-count" class="cart-badge">0</span>
          </button>

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

  <div id="cart-sidebar" class="cart-sidebar">
    <div class="cart-sidebar__header">
      <h2 class="font-display">YOUR CART</h2>
      <button id="close-cart" class="cart-sidebar__close">&times;</button>
    </div>
    
    <div id="cart-items-container" class="cart-sidebar__items">
      <p class="cart-empty-msg text-muted">Your cart is empty.</p>
    </div>

    <div class="cart-sidebar__footer">
      <div class="cart-total-row">
        <span>Total:</span>
        <span id="cart-total-price">0.00€</span>
      </div>
      <div class="cart-actions-row">
        <button id="clear-cart-btn" class="btn btn--ghost" style="width: 100%;">Clear Cart</button>
        <button id="checkout-btn" class="btn btn--primary" style="width: 100%;">Checkout</button>
      </div>
    </div>
  </div>
  <div id="cart-overlay" class="cart-overlay"></div>

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
            <img src="../images/merch/PremiumOversizedHoodie.png" alt="Forge Premium Hoodie" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Premium Oversized Hoodie</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">Black / Heavyweight Cotton</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">45.00€</span>
              <button type="button" class="btn btn--primary btn--sm add-to-cart-btn" data-id="1" data-name="Premium Oversized Hoodie" data-price="45.00" aria-label="Add Premium Oversized Hoodie to cart">
                <i class="fas fa-shopping-cart"></i> Add
              </button>
            </div>
          </div>
        </article>

        <article class="product-card card">
          <div class="product-card__image">
            <img src="../images/merch/ForgePumpTShirt.png" alt="Forge Pump T-Shirt" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Forge Pump T-Shirt</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">White / Breathable fit</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">25.00€</span>
              <button type="button" class="btn btn--primary btn--sm add-to-cart-btn" data-id="2" data-name="Forge Pump T-Shirt" data-price="25.00" aria-label="Add Forge Pump T-Shirt to cart">
                <i class="fas fa-shopping-cart"></i> Add
              </button>
            </div>
          </div>
        </article>

        <article class="product-card card">
          <div class="product-card__image">
            <span class="badge badge--dark product-badge">Best Seller</span>
            <img src="../images/merch/ProShaker.png" alt="Pro Shaker 700ml" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Pro Shaker 700ml</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">Matte Black / BPA Free</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">12.50€</span>
              <button type="button" class="btn btn--primary btn--sm add-to-cart-btn" data-id="3" data-name="Pro Shaker 700ml" data-price="12.50" aria-label="Add Pro Shaker 700ml to cart">
                <i class="fas fa-shopping-cart"></i> Add
              </button>
            </div>
          </div>
        </article>

        <article class="product-card card" style="opacity: 0.7;">
          <div class="product-card__image">
            <span class="badge badge--warning product-badge" style="background: var(--color-bg); color: var(--color-text-muted); border: 1px solid var(--color-border);">Out of Stock</span>
            <img src="../images/merch/TacticalGymDuffle.png" alt="Tactical Gym Duffle" style="filter: grayscale(80%);" loading="lazy">
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

        <article class="product-card card">
          <div class="product-card__image">
            <span class="badge badge--danger product-badge">Hot</span>
            <img src="../images/merch/ForgePumpTShirt.png" alt="Forge Performance Shorts" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Performance Shorts</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">Black / Lightweight</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">30.00€</span>
              <button type="button" class="btn btn--primary btn--sm add-to-cart-btn" data-id="4" data-name="Performance Shorts" data-price="30.00" aria-label="Add Performance Shorts to cart">
                <i class="fas fa-shopping-cart"></i> Add
              </button>
            </div>
          </div>
        </article>

        <article class="product-card card">
          <div class="product-card__image">
            <img src="../images/merch/ProShaker.png" alt="Lifting Straps" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Heavy Lifting Straps</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">Red & Black / Pair</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">15.00€</span>
              <button type="button" class="btn btn--primary btn--sm add-to-cart-btn" data-id="5" data-name="Heavy Lifting Straps" data-price="15.00" aria-label="Add Heavy Lifting Straps to cart">
                <i class="fas fa-shopping-cart"></i> Add
              </button>
            </div>
          </div>
        </article>

        <article class="product-card card">
          <div class="product-card__image">
            <img src="../images/merch/PremiumOversizedHoodie.png" alt="Women's Crop Top" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Forge Crop Top</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">White / Form-fitting</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">22.50€</span>
              <button type="button" class="btn btn--primary btn--sm add-to-cart-btn" data-id="6" data-name="Forge Crop Top" data-price="22.50" aria-label="Add Forge Crop Top to cart">
                <i class="fas fa-shopping-cart"></i> Add
              </button>
            </div>
          </div>
        </article>

        <article class="product-card card">
          <div class="product-card__image">
            <span class="badge badge--dark product-badge">Restocked</span>
            <img src="../images/merch/ProShaker.png" alt="Metal Water Jug" loading="lazy">
          </div>
          <div class="product-card__info">
            <div>
              <h3 class="product-title">Metal Water Jug 2L</h3>
              <p class="text-muted" style="font-size: var(--text-sm);">Matte Black / Insulated</p>
            </div>
            <div class="product-card__footer">
              <span class="product-price">35.00€</span>
              <button type="button" class="btn btn--primary btn--sm add-to-cart-btn" data-id="7" data-name="Metal Water Jug 2L" data-price="35.00" aria-label="Add Metal Water Jug to cart">
                <i class="fas fa-shopping-cart"></i> Add
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

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // 1. CARREGAR ITENS DA MEMÓRIA DO NAVEGADOR (para não perder os itens ao sair da página)
      let cart = JSON.parse(localStorage.getItem('meu_carrinho')) || [];

      const cartBtn = document.getElementById('cart-btn');
      const closeCartBtn = document.getElementById('close-cart');
      const cartSidebar = document.getElementById('cart-sidebar');
      const cartOverlay = document.getElementById('cart-overlay');
      const cartItemsContainer = document.getElementById('cart-items-container');
      const cartCountEl = document.getElementById('cart-count');
      const cartTotalPriceEl = document.getElementById('cart-total-price');
      const clearCartBtn = document.getElementById('clear-cart-btn');
      const checkoutBtn = document.getElementById('checkout-btn');
      const addButtons = document.querySelectorAll('.add-to-cart-btn');

      // Função para guardar o carrinho na memória
      const saveCart = () => {
        localStorage.setItem('meu_carrinho', JSON.stringify(cart));
      };

      // 1. Abrir e Fechar Menu Lateral
      const toggleCart = () => {
        cartSidebar.classList.toggle('open');
        cartOverlay.classList.toggle('open');
      };

      cartBtn.addEventListener('click', toggleCart);
      closeCartBtn.addEventListener('click', toggleCart);
      cartOverlay.addEventListener('click', toggleCart);

      // 2. Renderizar os itens do carrinho na Sidebar
      const renderCart = () => {
        cartItemsContainer.innerHTML = '';
        
        if (cart.length === 0) {
          cartItemsContainer.innerHTML = '<p class="cart-empty-msg text-muted">Your cart is empty.</p>';
          cartCountEl.textContent = '0';
          cartTotalPriceEl.textContent = '0.00€';
          saveCart(); // Guarda vazio
          return;
        }

        let totalItems = 0;
        let totalPrice = 0;

        cart.forEach(item => {
          totalItems += item.quantity;
          totalPrice += item.price * item.quantity;

          const itemRow = document.createElement('div');
          itemRow.classList.add('cart-item-row');
          itemRow.innerHTML = `
            <div class="cart-item-details">
              <span class="cart-item-title">${item.name}</span>
              <span class="cart-item-price">${(item.price * item.quantity).toFixed(2)}€</span>
            </div>
            <div class="cart-item-controls">
              <div class="quantity-picker">
                <button class="qty-btn minus-qty" data-id="${item.id}">-</button>
                <span class="qty-value">${item.quantity}</span>
                <button class="qty-btn plus-qty" data-id="${item.id}">+</button>
              </div>
              <button class="cart-remove-btn" data-id="${item.id}" aria-label="Remove item"><i class="fas fa-trash-alt"></i></button>
            </div>
          `;
          cartItemsContainer.appendChild(itemRow);
        });

        cartCountEl.textContent = totalItems;
        cartTotalPriceEl.textContent = totalPrice.toFixed(2) + '€';
        saveCart(); // Guarda as alterações
      };

      // 3. Adicionar item ao Carrinho
      addButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.preventDefault();
          const id = btn.getAttribute('data-id');
          const name = btn.getAttribute('data-name');
          const price = parseFloat(btn.getAttribute('data-price'));

          const existingItem = cart.find(item => item.id === id);

          if (existingItem) {
            existingItem.quantity++;
          } else {
            cart.push({ id, name, price, quantity: 1 });
          }

          renderCart();

          // Feedback visual de animação do badge
          cartCountEl.classList.add('pop');
          setTimeout(() => cartCountEl.classList.remove('pop'), 300);

          // Feedback visual no botão clicado
          const originalHTML = btn.innerHTML;
          btn.innerHTML = '<i class="fas fa-check"></i> Added';
          btn.style.backgroundColor = '#4caf50';
          btn.style.borderColor = '#4caf50';
          setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.style.backgroundColor = '';
            btn.style.borderColor = '';
          }, 1200);
        });
      });

      // 4. Detetar Cliques dentro do Carrinho (Mais, Menos e Eliminar)
      cartItemsContainer.addEventListener('click', (e) => {
        const target = e.target.closest('button');
        if (!target) return;

        const id = target.getAttribute('data-id');
        const item = cart.find(item => item.id === id);

        if (target.classList.contains('plus-qty')) {
          item.quantity++;
        } else if (target.classList.contains('minus-qty')) {
          if (item.quantity > 1) {
            item.quantity--;
          } else {
            cart = cart.filter(i => i.id !== id);
          }
        } else if (target.classList.contains('cart-remove-btn')) {
          cart = cart.filter(i => i.id !== id);
        }

        renderCart();
      });

      // 5. Botão de Limpar Carrinho
      clearCartBtn.addEventListener('click', () => {
        if (cart.length > 0) {
          cart = [];
          renderCart();
        }
      });

      // 6. Botão de Checkout (Simulação)
      checkoutBtn.addEventListener('click', () => {
        if (cart.length === 0) {
          alert('Your cart is empty!');
          return;
        }
        alert('Proceeding to checkout! Integration coming soon.');
      });

      // 7. VERIFICAR SE VEIO DE OUTRA PÁGINA PARA ABRIR O CARRINHO
      const urlParams = new URLSearchParams(window.location.search);
      if (urlParams.get('open_cart') === '1') {
        toggleCart(); // Abre o carrinho
        // Limpa a URL para ficar bonita (tira o ?open_cart=1)
        window.history.replaceState({}, document.title, "shop.php");
      }

      // Renderizar o carrinho assim que a página carrega
      renderCart();
    });
  </script>
</body>
</html>