// js/equipment-status.js
// Gere o clique nos cartões de equipamento e mostra a disponibilidade.

(function () {
  'use strict';

  const API_ENDPOINT = 'api/equipment_status.php';

  // --- Injetar o painel de disponibilidade no DOM ---
  function createStatusPanel() {
    const panel = document.createElement('div');
    panel.className = 'equip-status-panel';
    panel.setAttribute('aria-live', 'polite');
    panel.setAttribute('role', 'status');
    panel.innerHTML = `
      <button class="equip-status-panel__close" aria-label="Fechar">✕</button>
      <div class="equip-status-panel__content"></div>
    `;
    document.body.appendChild(panel);

    panel.querySelector('.equip-status-panel__close')
      .addEventListener('click', () => closePanel(panel));

    return panel;
  }

  // --- Renderizar os dados no painel ---
  function renderStatus(panel, data) {
    const { name, total_units, in_use, available, percentage, status, zone } = data;

    const statusLabel = { free: 'Disponível', moderate: 'Moderado', busy: 'Ocupado' };
    const usedWidth   = `${percentage}%`;

    panel.querySelector('.equip-status-panel__content').innerHTML = `
      <p class="equip-status-panel__zone">${zone}</p>
      <h3 class="equip-status-panel__title">${name}</h3>

      <div class="equip-status-panel__stat">
        <span class="equip-status-panel__number">${in_use}</span>
        <span class="equip-status-panel__label">de ${total_units} em uso</span>
      </div>

      <div class="equip-status-panel__bar" aria-label="${percentage}% em uso">
        <div class="equip-status-panel__bar-fill equip-status-panel__bar-fill--${status}"
             style="width: ${usedWidth}"></div>
      </div>

      <div class="equip-status-panel__footer">
        <span class="equip-status-panel__badge equip-status-panel__badge--${status}">
          ${statusLabel[status]}
        </span>
        <span class="equip-status-panel__available">${available} máquinas livres</span>
      </div>
    `;
  }

  // --- Mostrar estado de loading ---
  function renderLoading(panel) {
    panel.querySelector('.equip-status-panel__content').innerHTML = `
      <div class="equip-status-panel__loading">
        <span class="equip-status-panel__spinner"></span>
        A verificar disponibilidade…
      </div>
    `;
  }

  // --- Abrir/fechar painel ---
  function openPanel(panel) {
    panel.classList.add('equip-status-panel--visible');
  }

  function closePanel(panel) {
    panel.classList.remove('equip-status-panel--visible');
    // Remove o estado ativo do cartão
    document.querySelectorAll('.equip-card--active')
      .forEach(c => c.classList.remove('equip-card--active'));
  }

  // --- Fetch à API ---
  async function fetchEquipmentStatus(id) {
    const res = await fetch(`${API_ENDPOINT}?id=${encodeURIComponent(id)}`);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  }

  // --- Inicialização ---
  function init() {
    const panel = createStatusPanel();

    document.querySelectorAll('.equip-card[data-id]').forEach(card => {
      card.addEventListener('click', async () => {
        const id = card.dataset.id;

        // Marcar cartão como ativo
        document.querySelectorAll('.equip-card--active')
          .forEach(c => c.classList.remove('equip-card--active'));
        card.classList.add('equip-card--active');

        openPanel(panel);
        renderLoading(panel);

        try {
          const data = await fetchEquipmentStatus(id);
          renderStatus(panel, data);
        } catch {
          panel.querySelector('.equip-status-panel__content').innerHTML = `
            <p class="equip-status-panel__error">
              Não foi possível carregar a disponibilidade.
            </p>
          `;
        }
      });
    });

    // Fechar ao clicar fora do painel
    document.addEventListener('click', (e) => {
      if (!e.target.closest('.equip-card') && !e.target.closest('.equip-status-panel')) {
        closePanel(panel);
      }
    });
  }

  // Aguardar o DOM
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();