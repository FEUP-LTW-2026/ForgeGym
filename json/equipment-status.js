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
          let barColor = "#4caf50"; // Verde por defeito (Pouca ocupação, ex: < 50%)
          
          if (percentage > 80) {
            barColor = "#e63946"; // Vermelho (Muito ocupado, > 80%)
          } else if (percentage >= 50) {
            barColor = "var(--color-gold)"; // Amarelo/Dourado (Ocupação média, 50% a 80%)
          }

          let progressBarContainer = card.querySelector(".equip-card__status-container");

          // Cria a barrinha se ela ainda não existir
          if (!progressBarContainer) {
            progressBarContainer = document.createElement("div");
            progressBarContainer.className = "equip-card__status-container";
            progressBarContainer.style.marginTop = "var(--sp-3)";
            card.querySelector(".equip-card__info").appendChild(progressBarContainer);
          }

          // Atualiza a barra com os valores e a COR certa
          progressBarContainer.innerHTML = `
            <div class="equip-card__status-text" style="display: flex; justify-content: space-between; font-size: var(--text-sm); margin-bottom: 4px; color: var(--color-text-muted);">
              <span>Occupancy</span>
              <span>${inUse}/${total} In Use</span>
            </div>
            <div class="equip-card__progress-bg" style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden;">
              <div class="equip-card__progress-bar" style="width: ${percentage}%; height: 100%; background: ${barColor}; transition: width 0.5s ease, background-color 0.5s ease;"></div>
            </div>
          `;
        } else {
          // 3. APAGAR AO VIVO: Se o equipamento foi removido na BD, o JS apaga o cartão inteiro instantaneamente!
          card.remove();
        }
      });
    } catch (error) {
      console.error("Error updating equipment status:", error);
    }
  };

  // Corre imediatamente e depois a cada 5 segundos
  updateStatus();
  setInterval(updateStatus, 5000);
});