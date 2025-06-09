/**
 * Movie Catalog Main JavaScript
 */

// Modules
import MovieFilter from './modules/MovieFilter';
import { MovieLoader } from './modules/MovieLoader';
import { initBurgerMenu } from './components/burger-menu';
import { BurgerMenu } from './modules/BurgerMenu';

document.addEventListener('DOMContentLoaded', () => {
    // Инициализация фильтра фильмов
    const movieFilter = new MovieFilter();

    // Инициализация загрузки фильмов
    const loader = new MovieLoader();

    // Initialize burger menu
    new BurgerMenu();
}); 