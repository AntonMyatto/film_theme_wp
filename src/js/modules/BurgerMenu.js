/**
 * BurgerMenu class for handling mobile menu functionality
 */
export class BurgerMenu {
    constructor() {
        this.burger = document.querySelector('.burger-menu');
        this.nav = document.querySelector('.main-navigation');
        this.menuItems = document.querySelectorAll('.main-navigation .menu-item a');
        this.isOpen = false;

        if (this.burger && this.nav) {
            this.init();
        }
    }

    init() {
        this.burger.addEventListener('click', () => this.toggleMenu());

        this.menuItems.forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    this.closeMenu();
                }
            });
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 768 && this.isOpen) {
                this.closeMenu();
            }
        });

        document.addEventListener('click', (e) => {
            if (this.isOpen && 
                !e.target.closest('.main-navigation') && 
                !e.target.closest('.burger-menu')) {
                this.closeMenu();
            }
        });
    }

    toggleMenu() {
        if (this.isOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }

    openMenu() {
        this.isOpen = true;
        this.burger.classList.add('active');
        this.nav.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    closeMenu() {
        this.isOpen = false;
        this.burger.classList.remove('active');
        this.nav.classList.remove('active');
        document.body.style.overflow = '';
    }
} 