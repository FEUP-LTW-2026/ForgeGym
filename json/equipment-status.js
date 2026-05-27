document.addEventListener("DOMContentLoaded", () => {
  const updateStatus = async () => {
    try {
      const response = await fetch("api/equipment_status.php");
      if (!response.ok) throw new Error("Network response was not ok");
      
      const data = await response.json();
      const cards = document.querySelectorAll(".equip-card");

      cards.forEach(card => {
        const id = card.getAttribute("data-id");
        const status = data.find(item => item.id == id);

        if (status) {
          // 1. Calcula a percentagem de ocupação
          const total = parseInt(status.total_units) || 1;
          const inUse = parseInt(status.in_use) || 0;
          const percentage = Math.min((inUse / total) * 100, 100);

          // 2. LÓGICA DAS CORES
          let barColor = "#4caf50"; // Verde (Pouca ocupação)
          
          if (percentage > 80) {
            barColor = "#e63946"; // Vermelho (Muito ocupado)
          } else if (percentage >= 50) {
            barColor = "var(--color-gold)"; // Amarelo/Dourado (Ocupação média)
          }

          let progressBarContainer = card.querySelector(".equip-card__status-container");

          // Cria a barrinha se ela ainda não existir
          if (!progressBarContainer) {
            progressBarContainer = document.createElement("div");
            progressBarContainer.className = "equip-card__status-container";
            progressBarContainer.style.marginTop = "var(--sp-3)";
            card.querySelector(".equip-card__info").appendChild(progressBarContainer);
          }

          // Atualiza a barra com os valores, a COR certa e o NEON (box-shadow)
          progressBarContainer.innerHTML = `
            <div class="equip-card__status-text" style="display: flex; justify-content: space-between; font-size: var(--text-sm); margin-bottom: 8px; color: var(--color-text-muted);">
              <span>Occupancy</span>
              <span>${inUse}/${total} In Use</span>
            </div>
            <div class="equip-card__progress-bg" style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px;">
              <div class="equip-card__progress-bar" style="width: ${percentage}%; height: 100%; background: ${barColor}; border-radius: 3px; box-shadow: 0 0 8px ${barColor}, 0 0 12px ${barColor}; transition: width 0.5s ease, background-color 0.5s ease, box-shadow 0.5s ease;"></div>
            </div>
          `;
        } else {
          // Se o equipamento foi removido na BD, apaga o cartão
          card.remove();
        }
      });
    } catch (error) {
      console.error("Error updating equipment status:", error);
    }
  };

  updateStatus();
  setInterval(updateStatus, 5000);
});