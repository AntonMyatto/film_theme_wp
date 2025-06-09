export function initBurgerMenu() {
    const burger = document.querySelector('.burger-menu');
    const nav = document.querySelector('.main-navigation');
    const body = document.body;

    if (!burger || !nav) return;

    burger.addEventListener('click', () => {
        burger.classList.toggle('active');
        nav.classList.toggle('active');
        body.classList.toggle('menu-open');
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!burger.contains(e.target) && !nav.contains(e.target) && nav.classList.contains('active')) {
            burger.classList.remove('active');
            nav.classList.remove('active');
            body.classList.remove('menu-open');
        }
    });

    // Close menu when pressing Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && nav.classList.contains('active')) {
            burger.classList.remove('active');
            nav.classList.remove('active');
            body.classList.remove('menu-open');
        }
    });
} 