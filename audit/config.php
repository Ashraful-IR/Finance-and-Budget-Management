function toggleMenu() {
  const menu = document.querySelector('.menu');
  menu.classList.toggle('active');
}

function showSection(sectionId) {
  document.querySelectorAll('.content-section').forEach(sec => {
    sec.classList.remove('active');
  });
  document.getElementById(sectionId).classList.add('active');
}
