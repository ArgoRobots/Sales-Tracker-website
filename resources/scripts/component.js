document.addEventListener('DOMContentLoaded', function() {
    // Create and insert header if it doesn't exist
    let headerElement = document.querySelector('header');
    if (!headerElement) {
        headerElement = document.createElement('header');
        headerElement.classList.add('gradient-bg');
        document.body.prepend(headerElement);
    }

    // Load header and its styles
    loadStyles('resources/header/style.css');
    fetch('resources/header/index.html')
        .then(response => response.text())
        .then(data => {
            headerElement.insertAdjacentHTML('afterbegin', data);
            initializeMenu();
        })
        .catch(error => console.error('Error loading header:', error));

    // Load footer and its styles
    loadStyles('resources/footer/style.css');
    fetch('resources/footer/index.html')
        .then(response => response.text())
        .then(data => {
            const footer = document.createElement('footer');
            footer.classList.add('gradient-bg');
            footer.innerHTML = data;
            document.body.appendChild(footer);
        })
        .catch(error => console.error('Error loading footer:', error));
});

function loadStyles(path) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.type = 'text/css';
    link.href = path;
    document.head.appendChild(link);
}

function initializeMenu() {
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const menu = document.getElementById('menu');

    if (hamburgerBtn && menu) {
        hamburgerBtn.addEventListener('click', function() {
            const isOpen = menu.style.width === '250px';
            menu.style.width = isOpen ? '0' : '250px';
            hamburgerBtn.classList.toggle('active');
        });
    }
}