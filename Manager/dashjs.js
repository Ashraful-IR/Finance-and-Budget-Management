function toggleMenu() {
  const menu = document.querySelector(".menu");
  const container = document.querySelector(".container");

  menu.classList.toggle("collapsed");

  if (menu.classList.contains("collapsed")) {
    container.style.paddingLeft = "60px";
  } else {
    container.style.paddingLeft = "240px";
  }
}
function showSection(sectionId, event) {
  const sections = document.querySelectorAll(".section");
  sections.forEach(sec => sec.classList.remove("active"));

  const current = document.getElementById(sectionId);
  if (current) current.classList.add("active");

  document.querySelectorAll(".menu a").forEach(link => link.classList.remove("active"));
  if (event) event.currentTarget.classList.add("active");

  const addButtons = document.getElementById("addButtons");
  if (sectionId === "Add" && addButtons) {
    addButtons.style.display = "flex";
  } else if (addButtons) {
    addButtons.style.display = "none";
  }
}

function logout() {
  sessionStorage.clear();
  localStorage.clear();
  window.location.replace("../LogIn/login.php");
  alert("Are You Sure You Want To Logout?");
}
function deleteExpense(id, btn) {
            if (!confirm('Are you sure you want to delete this user?')) return;
            const data = new URLSearchParams();
            data.append('ajax','deleteExpense');
            data.append('Id', Id);
            fetch('dash.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: data.toString()
            })
            .then(r => r.json())
            .then(j => {
                if (j && j.success) {
                    const tr = btn.closest('tr');
                    const tbody = tr.parentElement;
                    tr.remove();
                    if (tbody.children.length === 0) {
                        const empty = document.createElement('tr');
                        const td = document.createElement('td');
                        td.colSpan = 8;
                        td.textContent = 'No users found';
                        empty.appendChild(td);
                        tbody.appendChild(empty);
                    }
                } else {
                    alert('Delete failed');
                }
            })
            .catch(() => alert('Delete failed'));
        }