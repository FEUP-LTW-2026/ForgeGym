// Funções para o Menu de Eliminação de Aulas
function openDeleteModal(classId) {
  document.getElementById('modal-class-id').value = classId;
  document.getElementById('delete-modal').style.display = 'flex';
}

function closeDeleteModal() {
  document.getElementById('delete-modal').style.display = 'none';
}

// Fechar o menu de eliminação se clicarem fora dele por acidente
window.onclick = function(event) {
  const modal = document.getElementById('delete-modal');
  if (event.target == modal) {
    closeDeleteModal();
  }
}

// Função para o Menu da Lista de Inscritos 
function openRosterModal(classId) {
  const modal = document.getElementById('roster-modal');
  const list = document.getElementById('roster-list');
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
          <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; padding:10px; border-bottom:1px solid #333;">
            
            <div style="display:flex; align-items:center; gap:10px;">
              <img src="../${photo}" 
                   style="width:40px; height:40px; border-radius:50%; object-fit:cover;"
                   onerror="this.src='../images/profiles/profile1.png';">
              <div>
                <strong style="color: white;">${u.name}</strong><br>
                <small style="color:var(--color-text-muted)">${u.email}</small>
              </div>
            </div>

            <form action="plan.php" method="POST" style="margin:0;" onsubmit="return confirm('Remove ${u.name} from this class?');">
              <input type="hidden" name="action" value="remove_student">
              <input type="hidden" name="enrollment_id" value="${u.enrollment_id}">
              <button type="submit" class="btn btn--sm" style="background: rgba(231, 76, 60, 0.1); border: 1px solid #e74c3c; color: #e74c3c; padding: 4px 8px; font-size: 0.8rem; cursor: pointer; border-radius: 4px;" title="Remove Member">
                  <i class="fas fa-user-times"></i>
              </button>
            </form>

          </div>
        `}).join('');
      }
    })
    .catch(err => {
      list.innerHTML = '<p style="text-align:center; color:red;">Error loading list.</p>';
    });
}