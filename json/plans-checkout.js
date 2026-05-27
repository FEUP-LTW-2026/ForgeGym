document.addEventListener('DOMContentLoaded', () => {
  const checkoutModal = document.getElementById('plan-checkout-modal');
  const successModal = document.getElementById('plan-success-modal');
  const closeBtn = document.getElementById('close-plan-checkout');
  const form = document.getElementById('plan-checkout-form');
  
  const planNameEl = document.getElementById('checkout-plan-name');
  const planPriceEl = document.getElementById('checkout-plan-price');
  
  let selectedPlan = '';

  // 1. Abrir o modal quando clica num plano
  document.querySelectorAll('.plan-checkout-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      selectedPlan = btn.getAttribute('data-plan');
      const planPrice = btn.getAttribute('data-price');
      
      planNameEl.textContent = selectedPlan + ' PLAN';
      planPriceEl.textContent = planPrice;
      
      checkoutModal.style.display = 'flex';
    });
  });

  // 2. Fechar o modal
  closeBtn.addEventListener('click', () => {
    checkoutModal.style.display = 'none';
  });

  // 3. Submeter o formulário de pagamento
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    
    checkoutModal.style.display = 'none';
    successModal.style.display = 'flex';
    
    setTimeout(() => {
      window.location.href = 'pages/checkout_plan.php?plan=' + selectedPlan;
    }, 2500);
  });
});