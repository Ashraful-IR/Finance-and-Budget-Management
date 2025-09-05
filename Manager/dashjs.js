
    function toggleMenu() {
        const menu = document.querySelector('.menu');
        menu.classList.toggle('active');
    }

    function showSection(sectionId) {
        const sections = document.querySelectorAll('.section');
        const menu = document.querySelector('.menu');
        const container = document.querySelector('.container');

        sections.forEach(sec => sec.classList.remove('active'));
        document.getElementById(sectionId).classList.add('active');

        document.querySelectorAll('.menu a').forEach(link => link.classList.remove('active'));
        event.target.closest('a').classList.add('active');

        if (sectionId === 'Add') {
            menu.classList.add('collapsed');
            container.classList.add('full-add');
        } else {
            menu.classList.remove('collapsed');
            container.classList.remove('full-add');
        }
    }

    function toggleMenu() {
        const menu = document.querySelector('.menu');
        const container = document.querySelector('.container');

        menu.classList.toggle('collapsed');

        if (menu.classList.contains('collapsed')) {
            container.style.paddingLeft = '60px';
        } else {
            container.style.paddingLeft = '240px';
        }
    }