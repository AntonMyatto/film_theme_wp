import { CustomSelect } from './CustomSelect';

export default class MovieFilter {
    constructor() {
        this.searchInput = document.getElementById('search-filter');
        this.genreFilter = document.getElementById('genre-filter');
        this.yearFilter = document.getElementById('year-filter');
        this.sortFilter = document.getElementById('sort-filter');
        this.loadMoreBtn = document.getElementById('load-more');
        this.moviesGrid = document.getElementById('movies-grid');
        this.moviesContainer = this.moviesGrid.querySelector('.movies-container');
        this.currentPage = 1;
        this.searchTimer = null;
        this.apiEndpoint = `${window.location.origin}/wp-json/movie-catalog/v1/movies`;
        this.form = document.querySelector('.movie-filters');
        this.isLoading = false;

        if (this.form) {
            this.initCustomSelects();
            this.bindEvents();
        }
    }

    initCustomSelects() {
        if (this.sortFilter) {
            this.sortCustomSelect = new CustomSelect(this.sortFilter, {
                onChange: (value) => {
                    this.filterMovies();
                }
            });
        }

        const filterSelects = this.form.querySelectorAll('select');
        filterSelects.forEach(select => {
            if (select.id !== 'sort-filter') { 
                new CustomSelect(select, {
                    onChange: () => {
                        this.filterMovies();
                    }
                });
            }
        });
    }

    bindEvents() {
        if (this.searchInput) {
            this.searchInput.addEventListener('input', () => {
                clearTimeout(this.searchTimer);
                this.searchTimer = setTimeout(() => this.filterMovies(), 500);
            });
        }

        if (this.genreFilter) {
            this.genreFilter.addEventListener('change', () => this.filterMovies());
        }
        
        if (this.yearFilter) {
            this.yearFilter.addEventListener('change', () => this.filterMovies());
        }
        
        if (this.sortFilter) {
            this.sortFilter.addEventListener('change', () => this.filterMovies());
        }

        if (this.loadMoreBtn) {
            this.loadMoreBtn.addEventListener('click', () => this.loadMore());
        }
    }

    getFilterParams(page = 1) {
        const params = {
            page: page
        };

        if (this.searchInput && this.searchInput.value.trim()) {
            params.search = this.searchInput.value.trim();
        }

        if (this.genreFilter && this.genreFilter.value) {
            params.genre = this.genreFilter.value;
        }
        
        if (this.yearFilter && this.yearFilter.value) {
            params.year = this.yearFilter.value;
        }
        
        if (this.sortFilter && this.sortFilter.value) {
            params.sort = this.sortFilter.value;
        }

        return params;
    }

    getFilterUrl(params) {
        const queryString = new URLSearchParams(params).toString();
        return `${this.apiEndpoint}?${queryString}`;
    }

    toggleLoading(show) {
        this.moviesGrid.classList.toggle('loading', show);
        if (this.loadMoreBtn) {
            this.loadMoreBtn.disabled = show;
        }
    }

    createMovieHtml(movie) {
        return `
            <article class="movie-card">
                <div class="movie-card__image">
                    ${movie.rating ? `
                        <div class="movie-card__rating">
                            <span class="movie-card__rating-block">
                                <span class="movie-card__rating-value">${Number(movie.rating).toFixed(1)}</span>
                                <span class="movie-card__rating-star">★</span>
                            </span>
                        </div>
                    ` : ''}
                    <a href="${movie.permalink}">
                        ${movie.poster?.url ? `
                            <img 
                                src="${movie.poster.url}" 
                                alt="${movie.poster.alt || movie.title}"
                                width="${movie.poster.width}"
                                height="${movie.poster.height}"
                                loading="lazy"
                            >
                        ` : ''}
                    </a>
                </div>

                <div class="movie-card__content">
                    <h2 class="movie-card__title">
                        <a href="${movie.permalink}">${movie.title}</a>
                    </h2>

                    <a href="${movie.permalink}" class="movie-card__button">
                        Read more
                    </a>
                </div>
            </article>
        `;
    }

    async filterMovies() {
        if (!this.moviesContainer) return;
        
        try {
            this.toggleLoading(true);
            this.currentPage = 1;

            const response = await fetch(this.getFilterUrl(this.getFilterParams()));
            const data = await response.json();

            if (response.ok) {
                const moviesHtml = data.movies.map(movie => this.createMovieHtml(movie)).join('');
                this.moviesContainer.innerHTML = moviesHtml || '<p class="no-movies">Фильмы не найдены</p>';
                
                if (this.loadMoreBtn) {
                    this.loadMoreBtn.style.display = data.current_page < data.total_pages ? 'block' : 'none';
                    this.loadMoreBtn.dataset.page = '1';
                    this.loadMoreBtn.dataset.maxPages = data.total_pages;
                }
            } else {
                throw new Error('Error filtering movies');
            }
        } catch (error) {
            console.error('Error filtering movies:', error);
            this.moviesContainer.innerHTML = '<p class="error">Ошибка при загрузке фильмов</p>';
        } finally {
            this.toggleLoading(false);
        }
    }

    async loadMore() {
        try {
            this.toggleLoading(true);
            this.currentPage++;

            const response = await fetch(this.getFilterUrl(this.getFilterParams(this.currentPage)));
            const data = await response.json();

            if (response.ok) {
                const moviesHtml = data.movies.map(movie => this.createMovieHtml(movie)).join('');
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = moviesHtml;
                
                while (tempContainer.firstChild) {
                    this.moviesContainer.appendChild(tempContainer.firstChild);
                }

                if (this.loadMoreBtn) {
                    this.loadMoreBtn.style.display = data.current_page < data.total_pages ? 'block' : 'none';
                    this.loadMoreBtn.dataset.page = this.currentPage;
                    this.loadMoreBtn.dataset.maxPages = data.total_pages;
                }
            } else {
                throw new Error('Error loading more movies');
            }
        } catch (error) {
            console.error('Error loading more movies:', error);
        } finally {
            this.toggleLoading(false);
        }
    }
} 