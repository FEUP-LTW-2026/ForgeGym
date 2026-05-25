document.addEventListener("DOMContentLoaded", () => {
  const updateStatus = async () => {
    try {
      // Vai buscar o estado de ocupação atualizado à API
      const response = await fetch("api/equipment_status.php");
      if (!response.ok) throw new Error("Network response was not ok");
      
      const data = await response.json();

      // Procura todos os cartões de equipamentos que o PHP desenhou na página
      const cards = document.querySelectorAll(".equip-card");

      cards.forEach(card => {
        const id = card.getAttribute("data-id");
        // Procura se este equipamento existe nos dados vindos da Base de Dados
        const status = data.find(item => item.id == id);

        if (status) {
          // Se o equipamento existe, calcula a percentagem de uso
          const total = parseInt(status.total_units) || 1;
          const inUse = parseInt(status.in_use) || 0;
          const percentage = Math.min((inUse / total) * 100, 100);

          // Procura se a barrinha já existe dentro deste cartão
          let progressBarContainer = card.querySelector(".equip-card__status-container");

          // Se a barrinha ainda não existir (equipamento novo!), o JS cria-a agora mesmo
          if (!progressBarContainer) {
            progressBarContainer = document.createElement("div");
            progressBarContainer.className = "equip-card__status-container";
            progressBarContainer.style.marginTop = "var(--sp-3)";
            card.querySelector(".equip-card__info").appendChild(progressBarContainer);
          }

          // Atualiza o HTML interno da barrinha com os valores ao vivo
          progressBarContainer.innerHTML = `
            <div class="equip-card__status-text" style="display: flex; justify-content: space-between; font-size: var(--text-sm); margin-bottom: 4px; color: var(--color-text-muted);">
              <span>Occupancy</span>
              <span>${inUse}/${total} In Use</span>
            </div>
            <div class="equip-card__progress-bg" style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden;">
              <div class="equip-card__progress-bar" style="width: ${percentage}%; height: 100%; background: var(--color-gold); transition: width 0.5s ease;"></div>
            </div>
          `;
        }
      });
    } catch (error) {
      console.error("Error updating equipment status:", error);
    }
  };

  // Corre imediatamente ao carregar a página
  updateStatus();
  // Atualiza automaticamente a cada 5 segundos se o admin mexer na dashboard
  setInterval(updateStatus, 5000);
});