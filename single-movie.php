<?php get_header(); ?>

<div class="container">
    <?php echo movie_catalog_get_breadcrumbs(); ?>
    
    <article class="movie-single">
        <?php while (have_posts()) : the_post(); 
            $poster = get_field('poster');
        ?>
            <div class="movie-header">
                <div class="movie-poster">
                    <?php if ($poster) : ?>
                        <img 
                            src="<?php echo esc_url($poster['url']); ?>" 
                            alt="<?php echo esc_attr($poster['alt'] ?: get_the_title()); ?>"
                            width="<?php echo esc_attr($poster['width']); ?>"
                            height="<?php echo esc_attr($poster['height']); ?>"
                            loading="lazy"
                        >
                    <?php endif; ?>
                </div>

                <div class="movie-meta">
                    <h1 class="movie-title"><?php the_title(); ?></h1>
                    
                    <div class="movie-details">
                        <?php if ($rating = get_field('rating')) : ?>
                            <div class="detail-item">
                                <span class="label">Рейтинг:</span>
                                <span class="value rating-value">
                                    <?php echo esc_html($rating); ?>
                                    <span class="rating-star">★</span>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($year = get_field('year')) : ?>
                            <div class="detail-item">
                                <span class="label">Год выпуска:</span>
                                <span class="value"><?php echo esc_html($year); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($release_date = get_field('release_date')) : ?>
                            <div class="detail-item">
                                <span class="label">Дата выхода:</span>
                                <span class="value"><?php echo esc_html(date('d.m.Y', strtotime($release_date))); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php
                        $genres = get_the_terms(get_the_ID(), 'movie_genre');
                        if ($genres && !is_wp_error($genres)) : ?>
                            <div class="detail-item">
                                <span class="label">Жанры:</span>
                                <div class="movie-genres">
                                    <?php foreach ($genres as $genre) : ?>
                                        <a href="<?php echo esc_url(get_term_link($genre)); ?>" class="genre-tag">
                                            <?php echo esc_html($genre->name); ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="movie-content">
                <h2>Описание</h2>
                <?php the_content(); ?>
            </div>
        <?php endwhile; ?>
    </article>
</div>

<?php get_footer(); ?> 