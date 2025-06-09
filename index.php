<?php get_header(); ?>

<section class="hero-section">
    <div class="hero-container">
        <div class="hero-content">
            <h1>Explore a <span>World</span> of Cinematic Wonders</h1>
            <p>Our database not only includes blockbusters but also independent films, documentary features, and works from talented directors worldwide.</p>
            <div class="hero-actions">
                <a href="#" class="register-btn">REGISTER NOW</a>
                <a href="#" class="about-link">About us</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/icons/hero1.svg" alt="Movie collection showcase">
        </div>
    </div>
</section>

<div class="container">
    <h1 class="page-title page-title--catalog">Discover a <span>Universe</span> of Cinematic Marvels</h1>

    <div class="catalog-layout">
        <?php get_template_part('template-parts/filters'); ?>

        <div id="movies-grid" class="movies-grid">
            <div class="filter-group">
                <label for="sort-filter">Sort&nbsp;by:</label>
                <select id="sort-filter" name="sort">
                    <option value="rating" selected>Rating</option>
                    <option value="date">Date added</option>
                    <option value="year">Release year</option>
                </select>
            </div>

            <div class="movies-container">
            <?php
            $posts_per_page = get_option('movie_catalog_movies_per_page', 10);
            $args = array(
                'post_type' => 'movie',
                'posts_per_page' => $posts_per_page,
                'paged' => 1
            );

            if (get_query_var('genre')) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'movie_genre',
                        'field' => 'slug',
                        'terms' => get_query_var('genre')
                    )
                );
            }

            if (get_query_var('year')) {
                $args['meta_query'] = array(
                    array(
                        'key' => 'year',
                        'value' => get_query_var('year'),
                        'compare' => '='
                    )
                );
            }

            $sort = get_query_var('sort', 'rating');
            switch ($sort) {
                case 'date':
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                    break;
                case 'year':
                    $args['meta_key'] = 'year';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                default:
                    $args['meta_key'] = 'rating';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
            }

            $movies = new WP_Query($args);

            if ($movies->have_posts()) :
                while ($movies->have_posts()) : $movies->the_post();
                    get_template_part('template-parts/content', 'movie-card');
                endwhile;
                wp_reset_postdata();
            else :
            ?>
                <div class="no-movies">
                    <p>Фильмы не найдены</p>
                </div>
            <?php endif; ?>
            </div>

            <?php if ($movies->max_num_pages > 1) : ?>
                <div class="load-more-container">
                    <button id="load-more" class="load-more-button" 
                            data-page="1" 
                            data-max-pages="<?php echo esc_attr($movies->max_num_pages); ?>">
                        Load more
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?> 