<?php
/**
 * Template part for displaying movie filters
 */
?>
<div class="movie-filters">
    <div class="search-wrapper">
        <input type="text" id="search-filter" placeholder="Search by name">
    </div>
    <h2 class="filters-heading">FILTER:</h2>

    <div class="filters-container">
        <div class="filter-group">
            <label for="genre-filter">Genre:</label>
            <select id="genre-filter" name="genre">
                <option value="">All genres</option>
                <?php
                $genres = get_terms([
                    'taxonomy' => 'movie_genre',
                    'hide_empty' => true,
                ]);
                foreach ($genres as $genre) {
                    echo sprintf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($genre->slug),
                        selected(get_query_var('genre'), $genre->slug, false),
                        esc_html($genre->name)
                    );
                }
                ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="year-filter">Year:</label>
            <select id="year-filter" name="year">
                <option value="">All years</option>
                <?php
                global $wpdb;
                $years = $wpdb->get_col(
                    $wpdb->prepare(
                        "SELECT DISTINCT meta_value 
                        FROM $wpdb->postmeta 
                        WHERE meta_key = %s 
                        AND meta_value != ''
                        ORDER BY meta_value DESC",
                        'year'
                    )
                );
                foreach ($years as $year) {
                    echo sprintf(
                        '<option value="%d" %s>%d</option>',
                        intval($year),
                        selected(get_query_var('year'), $year, false),
                        intval($year)
                    );
                }
                ?>
            </select>
        </div>
    </div>
</div> 