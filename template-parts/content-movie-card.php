<?php
/**
 * Template part for displaying movie cards
 */

$poster = get_field('poster');
$rating = get_field('rating');
?>

<article class="movie-card">
    <div class="movie-card__image">
        <?php if ($rating) : ?>
            <div class="movie-card__rating">
                <span class="movie-card__rating-block">
                    <span class="movie-card__rating-value"><?php echo esc_html(number_format($rating, 1)); ?></span>
                    <span class="movie-card__rating-star">★</span>
                </span>
            </div>
        <?php endif; ?>
        <a href="<?php the_permalink(); ?>">
            <?php if ($poster) : ?>
                <img 
                    src="<?php echo esc_url($poster['url']); ?>" 
                    alt="<?php echo esc_attr($poster['alt'] ?: get_the_title()); ?>"
                    width="<?php echo esc_attr($poster['width']); ?>"
                    height="<?php echo esc_attr($poster['height']); ?>"
                    loading="lazy"
                >
            <?php endif; ?>
        </a>
    </div>

    <div class="movie-card__content">
        <h2 class="movie-card__title">
            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
        </h2>

        <a href="<?php the_permalink(); ?>" class="movie-card__button">
            Read more
        </a>
    </div>
</article> 