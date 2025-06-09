/**
 * MovieLoader class for handling load more functionality
 */
export class MovieLoader {
    constructor() {
        this.button = document.getElementById('load-more');
        this.grid = document.getElementById('movies-grid');
        this.moviesContainer = this.grid?.querySelector('.movies-container');
        this.currentPage = 1;
        this.loading = false;
        this.maxPages = 1;
        this.debug = true;

        if (this.button && this.moviesContainer) {
            this.maxPages = parseInt(this.button.dataset.maxPages) || 1;
            this.button.addEventListener('click', () => this.loadMore());
            this.updateButtonVisibility();
            this.log('Initial state:', {
                currentPage: this.currentPage,
                maxPages: this.maxPages
            });
        }
    }

    hasMorePages() {
        return this.currentPage < this.maxPages;
    }

    updateButtonVisibility() {
        if (!this.button) return;
        
        if (!this.hasMorePages()) {
            this.button.style.display = 'none';
        } else {
            this.button.style.display = 'inline-block';
        }
        
        this.log('Button visibility updated:', {
            currentPage: this.currentPage,
            maxPages: this.maxPages,
            visible: this.hasMorePages()
        });
    }

    async loadMore() {
        if (!this.moviesContainer || this.loading || !this.hasMorePages()) {
            this.log('Loading blocked:', { 
                isLoading: this.loading, 
                currentPage: this.currentPage, 
                maxPages: this.maxPages,
                hasContainer: !!this.moviesContainer
            });
            return;
        }

        try {
            this.loading = true;
            this.button.classList.add('loading');
            const nextPage = this.currentPage + 1;
            
            this.log('Loading page:', nextPage);

            const filterForm = document.querySelector('.movie-filters');
            const formData = new FormData();
            
            formData.append('action', 'load_more_movies');
            formData.append('page', nextPage);

            if (filterForm) {
                const genre = filterForm.querySelector('#genre-filter')?.value;
                const year = filterForm.querySelector('#year-filter')?.value;
                const sort = document.querySelector('#sort-filter')?.value;

                if (genre) formData.append('genre', genre);
                if (year) formData.append('year', year);
                if (sort) formData.append('sort', sort);
            }

            const response = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            this.log('Server response:', data);

            if (data.success && data.data) {
                const tempContainer = document.createElement('div');
                tempContainer.innerHTML = data.data.html;
                
                while (tempContainer.firstChild) {
                    this.moviesContainer.appendChild(tempContainer.firstChild);
                }
                
                this.currentPage = parseInt(data.data.currentPage) || this.currentPage;
                this.maxPages = parseInt(data.data.maxPages) || this.maxPages;
                
                this.log('State updated:', {
                    currentPage: this.currentPage,
                    maxPages: this.maxPages,
                    totalPosts: data.data.totalPosts,
                    debug: data.data.debug
                });

                if (this.button) {
                    this.button.dataset.page = this.currentPage;
                    this.button.dataset.maxPages = this.maxPages;
                }
                
                this.updateButtonVisibility();
            } else {
                console.error('Error loading movies:', data.data?.message || 'Unknown error');
                this.updateButtonVisibility();
            }
        } catch (error) {
            console.error('Error loading movies:', error);
            this.updateButtonVisibility();
        } finally {
            this.loading = false;
            if (this.button) {
                this.button.classList.remove('loading');
            }
        }
    }

    log(...args) {
        if (this.debug) {
            console.log('[MovieLoader]', ...args);
        }
    }
} 