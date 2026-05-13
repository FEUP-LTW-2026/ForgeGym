// json/equipment-status.js
// Carrega a disponibilidade de todos os equipamentos ao iniciar a página
// e injeta os dados diretamente nos cartões. Sem painel lateral.

(function () {
  'use strict';

  const API_ENDPOINT = 'api/equipment_status.php';

  const STATUS_LABEL = {
    free:     'Available',
    moderate: 'Moderate',
    busy:     'Busy',
  };

  // --- Injeta os dados num cartão específico ---
  function updateCard(card, data) {
    const infoEl = card.querySelector('.equip-card__info');
    if (!infoEl) return;

    // Remove injeção anterior (evita duplicados em chamadas futuras)
    const existing = infoEl.querySelector('.equip-card__availability');
    if (existing) existing.remove();

    // Cria o bloco de disponibilidade
    const block = document.createElement('div');
    block.className = 'equip-card__availability';

    block.innerHTML = `
      <div class="equip-avail__bar" aria-label="${data.percentage}% in use">
        <div class="equip-avail__bar-fill equip-avail__bar-fill--${data.status}"
             style="width: ${data.percentage}%"></div>
      </div>
      <div class="equip-avail__row">
        <span class="equip-avail__badge equip-avail__badge--${data.status}">
          ${STATUS_LABEL[data.status]}
        </span>
        <span class="equip-avail__text">
          ${data.in_use} of ${data.total_units} in use
        </span>
      </div>
    `;

    infoEl.appendChild(block);
  }

  // --- Mostra erro subtil no cartão ---
  function markCardError(card) {
    const infoEl = card.querySelector('.equip-card__info');
    if (!infoEl) return;
    const existing = infoEl.querySelector('.equip-card__availability');
    if (existing) existing.remove();

    const err = document.createElement('p');
    err.className = 'equip-card__availability equip-avail__error';
    err.textContent = 'Status unavailable';
    infoEl.appendChild(err);
  }

  // --- Fetch único para todos os equipamentos ---
  async function loadAllStatus() {
    let data;
    try {
      const res = await fetch(API_ENDPOINT);
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      data = await res.json();
    } catch {
      // Se a API falhar, marca todos os cartões com erro
      document.querySelectorAll('.equip-card[data-id]').forEach(markCardError);
      return;
    }

    // Cria um mapa id → dados para acesso rápido
    const byId = {};
    data.forEach(item => { byId[item.id] = item; });

    // Atualiza cada cartão
    document.querySelectorAll('.equip-card[data-id]').forEach(card => {
      const id   = parseInt(card.dataset.id, 10);
      const item = byId[id];
      item ? updateCard(card, item) : markCardError(card);
    });
  }

  // --- Arranque ---
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadAllStatus);
  } else {
    loadAllStatus();
  }

})();