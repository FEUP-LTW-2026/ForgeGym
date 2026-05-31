// --- 1. Funções para o Pop-up de Eliminação de Aulas ---
function openDeleteModal(classId) {
  document.getElementById('modal-class-id').value = classId;
  document.getElementById('delete-modal').style.display = 'flex';
}

function closeDeleteModal() {
  document.getElementById('delete-modal').style.display = 'none';
}


// --- 2. Funções para o Pop-up da Lista de Inscritos (Roster) ---
function openRosterModal(classId) {
  const modal = document.getElementById('roster-modal');
  const list = document.getElementById('roster-list');
  
  // Proteção: Se o HTML não existir, avisa na consola em vez de quebrar o site
  if (!modal || !list) {
      console.error("Erro: O HTML do 'roster-modal' não foi encontrado no plan.php!");
      return;
  }

  list.innerHTML = '<p style="text-align:center;">Loading...</p>';
  modal.style.display = 'flex';
  
  fetch('../api/get_roster.php?class_id=' + classId)
    .then(r => r.json())
    .then(data => {
      if (!data || data.length === 0) {
        list.innerHTML = '<p style="text-align:center; color:#888; font-style:italic;">No members enrolled yet.</p>';
      } else {
        list.innerHTML = data.map(u => {
          const photo = u.avatar || u.profile_photo || 'images/profiles/profile1.png';
          
          return `
          <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px; padding:10px; border-bottom:1px solid #333;">
            <img src="../${photo}" 
                 style="width:40px; height:40px; border-radius:50%; object-fit:cover;"
                 onerror="this.src='../images/profiles/profile1.png';">
            <div>
              <strong style="color: white;">${u.name}</strong><br>
              <small style="color:var(--color-text-muted)">${u.email}</small>
            </div>
          </div>
        `}).join('');
      }
    })
    .catch(err => {
      console.error(err);
      list.innerHTML = '<p style="text-align:center; color:red;">Error loading list.</p>';
    });
}

function closeRosterModal() {
  const modal = document.getElementById('roster-modal');
  if (modal) modal.style.display = 'none';
}


// --- 3. Fechar qualquer um dos pop-ups se clicarem na parte escura do ecrã ---
window.onclick = function(event) {
  const deleteModal = document.getElementById('delete-modal');
  const rosterModal = document.getElementById('roster-modal');
  
  if (event.target == deleteModal) {
    closeDeleteModal();
  }
  if (event.target == rosterModal) {
    closeRosterModal();
  }
}