<?php get_header(); ?>

<main class="site-main">
    <div class="container">
        <h1 class="page-title">Каталог фильмов</h1>

        <div class="catalog-layout">
            <!-- Фильтры -->
            <?php get_template_part('template-parts/filters'); ?>

            <!-- Список фильмов -->
            <div id="movies-grid" class="movies-grid">
                <div class="filter-group">
                    <label for="sort-filter">Sort&nbsp;by:</label>
                    <select id="sort-filter">
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
                    'paged' => get_query_var('paged') ? get_query_var('paged') : 1
                );

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

                if (get_query_var('genre')) {
                    $args['tax_query'][] = array(
                        'taxonomy' => 'movie_genre',
                        'field' => 'slug',
                        'terms' => get_query_var('genre')
                    );
                }

                if (get_query_var('year')) {
                    $args['meta_query'][] = array(
                        'key' => 'year',
                        'value' => get_query_var('year'),
                        'compare' => '='
                    );
                }

                $query = new WP_Query($args);

                if ($query->have_posts()) : 
                    while ($query->have_posts()) : $query->the_post();
                        get_template_part('template-parts/content', 'movie-card');
                    endwhile;
                    wp_reset_postdata();
                else : ?>
                    <p class="no-movies">Фильмы не найдены</p>
                <?php endif; ?>
                </div>

                <!-- Кнопка "Загрузить еще" -->
                <?php if ($query->max_num_pages > 1) : ?>
                    <div class="load-more-container">
                        <button id="load-more" class="load-more-button" 
                                data-page="1" 
                                data-max-pages="<?php echo esc_attr($query->max_num_pages); ?>">
                            Load more
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($query->max_num_pages > 1) : ?>
            <nav class="pagination">
                <?php
                echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'current' => max(1, get_query_var('paged')),
                    'total' => $query->max_num_pages,
                    'prev_text' => '&larr;',
                    'next_text' => '&rarr;',
                    'type' => 'list'
                ));
                ?>
            </nav>
        <?php endif; ?>
    </div>
</main>

<?php get_footer(); ?> 