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
    const sections = document.querySelectorAll('.section');
    sections.forEach(sec => sec.style.display = 'none');

    const current = document.getElementById(sectionId);
    if (current) current.style.display = 'block';

    if (event) {
        document.querySelectorAll('.menu a').forEach(link => link.classList.remove('active'));
        event.currentTarget.classList.add('active');
    }

    const addButtons = document.getElementById('addButtons');
    if(sectionId === 'Add' && addButtons) {
        addButtons.style.display = 'flex';
    } else if(addButtons) {
        addButtons.style.display = 'none';
    }
}


function logout() {
  sessionStorage.clear();
  localStorage.clear();
  window.location.replace("../LogIn/login.php");
}
