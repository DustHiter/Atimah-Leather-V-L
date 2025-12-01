// Custom JavaScript will go here

document.addEventListener('DOMContentLoaded', () => {
    // --- Theme Toggle Functionality ---
    const themeToggleButton = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');

    function applyTheme(theme) {
        if (theme === 'dark') {
            htmlElement.classList.add('dark');
            if (themeToggleButton) themeToggleButton.innerHTML = '<i class="bi bi-sun-fill"></i>';
        } else {
            htmlElement.classList.remove('dark');
            if (themeToggleButton) themeToggleButton.innerHTML = '<i class="bi bi-moon-fill"></i>';
        }
    }

    function initializeTheme() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            applyTheme(savedTheme);
        } else {
            applyTheme(prefersDarkScheme.matches ? 'dark' : 'light');
        }
    }

    if (themeToggleButton) {
        themeToggleButton.addEventListener('click', () => {
            const newTheme = htmlElement.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem('theme', newTheme);
            applyTheme(newTheme);
        });
    }

    prefersDarkScheme.addEventListener('change', (e) => {
        if (!localStorage.getItem('theme')) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });

    initializeTheme();

    // --- AOS Initialization ---
    AOS.init({
        duration: 800,
        once: true,
    });


});
