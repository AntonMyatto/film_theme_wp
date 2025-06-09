<?php
/**
 * Movie Catalog Theme Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once get_template_directory() . '/inc/acf-loader.php';

require_once get_template_directory() . '/inc/acf-fields.php';

require_once get_template_directory() . '/inc/rest-api.php';

define('MOVIE_CATALOG_VERSION', '1.0.0');
define('MOVIE_CATALOG_PATH', get_template_directory());
define('MOVIE_CATALOG_URL', get_template_directory_uri());

/**
 * Theme Setup
 */
function movie_catalog_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption'
    ));

    register_nav_menus(array(
        'primary' => __('Primary Menu', 'movie-catalog'),
        'footer' => __('Footer Menu', 'movie-catalog')
    ));
}
add_action('after_setup_theme', 'movie_catalog_setup');

/**
 * Register Movie post type
 */
function movie_catalog_register_post_types() {
    register_post_type('movie', array(
        'labels' => array(
            'name'               => 'Фильмы',
            'singular_name'      => 'Фильм',
            'add_new'            => 'Добавить фильм',
            'add_new_item'       => 'Добавить новый фильм',
            'edit_item'          => 'Редактировать фильм',
            'new_item'           => 'Новый фильм',
            'view_item'          => 'Просмотреть фильм',
            'search_items'       => 'Искать фильмы',
            'not_found'          => 'Фильмы не найдены',
            'not_found_in_trash' => 'В корзине фильмов не найдено'
        ),
        'public'              => true,
        'has_archive'         => true,
        'show_in_rest'        => true,
        'menu_icon'           => 'dashicons-video-alt2',
        'supports'            => array('title', 'editor', 'thumbnail'),
        'rewrite'             => array('slug' => 'movies')
    ));

    register_taxonomy('movie_genre', 'movie', array(
        'labels' => array(
            'name'              => 'Жанры',
            'singular_name'     => 'Жанр',
            'search_items'      => 'Искать жанры',
            'all_items'         => 'Все жанры',
            'edit_item'         => 'Редактировать жанр',
            'update_item'       => 'Обновить жанр',
            'add_new_item'      => 'Добавить новый жанр',
            'new_item_name'     => 'Название нового жанра',
            'menu_name'         => 'Жанры'
        ),
        'hierarchical'      => true,
        'show_ui'          => true,
        'show_admin_column' => true,
        'query_var'        => true,
        'rewrite'          => array('slug' => 'genre'),
        'show_in_rest'     => true
    ));
}
add_action('init', 'movie_catalog_register_post_types');

/**
 * Register ACF Fields
 */
function movie_catalog_register_acf_fields() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key' => 'group_movie_details',
            'title' => 'Детали фильма',
            'fields' => array(
                array(
                    'key' => 'field_release_date',
                    'label' => 'Дата выхода',
                    'name' => 'release_date',
                    'type' => 'date_picker',
                    'required' => 1,
                    'display_format' => 'd.m.Y',
                    'return_format' => 'Y-m-d'
                ),
                array(
                    'key' => 'field_rating',
                    'label' => 'Рейтинг',
                    'name' => 'rating',
                    'type' => 'number',
                    'required' => 1,
                    'min' => 0,
                    'max' => 10,
                    'step' => 0.1
                ),
                array(
                    'key' => 'field_year',
                    'label' => 'Год выпуска',
                    'name' => 'year',
                    'type' => 'number',
                    'required' => 1,
                    'min' => 1900,
                    'max' => date('Y')
                ),
                array(
                    'key' => 'field_poster',
                    'label' => 'Постер',
                    'name' => 'poster',
                    'type' => 'image',
                    'return_format' => 'array',
                    'preview_size' => 'medium',
                    'library' => 'all'
                )
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'movie',
                    ),
                ),
            ),
        ));
    }
}
add_action('acf/init', 'movie_catalog_register_acf_fields');

/**
 * Enqueue scripts and styles
 */
function movie_catalog_enqueue_scripts() {
    wp_enqueue_style(
        'google-fonts-roboto',
        'https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap',
        array(),
        null
    );

    wp_enqueue_style(
        'movie-catalog-style',
        MOVIE_CATALOG_URL . '/assets/css/style.css',
        array('google-fonts-roboto'),
        filemtime(MOVIE_CATALOG_PATH . '/assets/css/style.css')
    );

    wp_enqueue_script(
        'movie-catalog-main',
        MOVIE_CATALOG_URL . '/assets/js/main.js',
        array(),
        filemtime(MOVIE_CATALOG_PATH . '/assets/js/main.js'),
        true
    );

    wp_localize_script('movie-catalog-main', 'movieCatalogData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('movie_catalog_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'movie_catalog_enqueue_scripts');

/**
 * AJAX handler for movie filtering
 */
function movie_catalog_filter_movies() {
    check_ajax_referer('movie_catalog_nonce', 'nonce');

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $posts_per_page = get_option('posts_per_page');

    $args = array(
        'post_type' => 'movie',
        'posts_per_page' => $posts_per_page,
        'paged' => $page
    );

    if (!empty($_GET['genre'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'movie_genre',
                'field' => 'slug',
                'terms' => sanitize_text_field($_GET['genre'])
            )
        );
    }

    if (!empty($_GET['year'])) {
        $args['meta_query'][] = array(
            'key' => 'year',
            'value' => intval($_GET['year']),
            'compare' => '='
        );
    }

    if (!empty($_GET['sort'])) {
        switch ($_GET['sort']) {
            case 'rating':
                $args['meta_key'] = 'rating';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'year':
                $args['meta_key'] = 'year';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }
    }

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            get_template_part('template-parts/content', 'movie-card');
        }
        wp_reset_postdata();

        $debug_info = array(
            'query_vars' => $query->query_vars,
            'request' => $query->request,
            'found_posts' => intval($query->found_posts),
            'max_num_pages' => intval($query->max_num_pages),
            'current_page' => intval($page),
            'posts_per_page' => intval($posts_per_page),
            'post_count' => count($query->posts)
        );

        wp_send_json_success(array(
            'html' => ob_get_clean(),
            'maxPages' => intval($query->max_num_pages),
            'totalPosts' => intval($query->found_posts),
            'currentPage' => intval($page),
            'postsPerPage' => intval($posts_per_page),
            'debug' => $debug_info
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'Фильмы не найдены',
            'maxPages' => intval($query->max_num_pages),
            'currentPage' => intval($page),
            'debug' => array(
                'query_vars' => $query->query_vars,
                'request' => $query->request
            )
        ));
    }
}
add_action('wp_ajax_filter_movies', 'movie_catalog_filter_movies');
add_action('wp_ajax_nopriv_filter_movies', 'movie_catalog_filter_movies');

/**
 * AJAX handler for loading more movies
 */
function load_more_movies() {
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $posts_per_page = get_option('movie_catalog_movies_per_page', 10);
    
    $args = array(
        'post_type' => 'movie',
        'posts_per_page' => $posts_per_page,
        'paged' => $page,
        'tax_query' => array(),
        'meta_query' => array(),
    );

    if (!empty($_POST['genre'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'movie_genre',
            'field' => 'slug',
            'terms' => sanitize_text_field($_POST['genre'])
        );
    }

    if (!empty($_POST['year'])) {
        $args['meta_query'][] = array(
            'key' => 'year',
            'value' => intval($_POST['year']),
            'compare' => '='
        );
    }

    if (!empty($_POST['sort'])) {
        switch ($_POST['sort']) {
            case 'rating':
                $args['meta_key'] = 'rating';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'year':
                $args['meta_key'] = 'year';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }
    }

    $movies = new WP_Query($args);
    ob_start();
    
    if ($movies->have_posts()) {
        while ($movies->have_posts()) {
            $movies->the_post();
            get_template_part('template-parts/content', 'movie-card');
        }
        wp_reset_postdata();
        
        $debug_info = array(
            'query_vars' => $movies->query_vars,
            'request' => $movies->request,
            'found_posts' => intval($movies->found_posts),
            'max_num_pages' => intval($movies->max_num_pages),
            'current_page' => intval($page),
            'posts_per_page' => intval($args['posts_per_page']),
            'post_count' => count($movies->posts)
        );
        
        wp_send_json_success(array(
            'html' => ob_get_clean(),
            'maxPages' => intval($movies->max_num_pages),
            'totalPosts' => intval($movies->found_posts),
            'currentPage' => intval($page),
            'postsPerPage' => intval($args['posts_per_page']),
            'debug' => $debug_info
        ));
    } 
    
    wp_send_json_error(array(
        'message' => 'Фильмы не найдены',
        'maxPages' => 0,
        'currentPage' => intval($page),
        'debug' => array(
            'query_vars' => $movies->query_vars,
            'request' => $movies->request
        )
    ));
}
add_action('wp_ajax_load_more_movies', 'load_more_movies');
add_action('wp_ajax_nopriv_load_more_movies', 'load_more_movies');

/**
 * Register custom query vars
 */
function movie_catalog_query_vars($vars) {
    $vars[] = 'sort';
    $vars[] = 'genre';
    $vars[] = 'year';
    return $vars;
}
add_filter('query_vars', 'movie_catalog_query_vars');

/**
 * Modify main query for movie archive
 */
function movie_catalog_pre_get_posts($query) {
    if (!is_admin() && $query->is_main_query() && is_post_type_archive('movie')) {
        $sort = get_query_var('sort', 'date');
        switch ($sort) {
            case 'rating':
                $query->set('meta_key', 'rating');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'title':
                $query->set('orderby', 'title');
                $query->set('order', 'ASC');
                break;
            case 'year':
                $query->set('meta_key', 'year');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            default:
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
        }

        if (get_query_var('genre')) {
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'movie_genre',
                    'field' => 'slug',
                    'terms' => get_query_var('genre')
                )
            ));
        }

        if (get_query_var('year')) {
            $query->set('meta_query', array(
                array(
                    'key' => 'year',
                    'value' => get_query_var('year'),
                    'compare' => '='
                )
            ));
        }
    }
}
add_action('pre_get_posts', 'movie_catalog_pre_get_posts');

/**
 * Include theme settings
 */
require get_template_directory() . '/inc/theme-settings.php';

/**
 * Enqueue admin scripts and styles
 */
function movie_catalog_admin_scripts($hook) {
    if ('appearance_page_movie-catalog-settings' !== $hook) {
        return;
    }

    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'movie_catalog_admin_scripts');

/**
 * Generate breadcrumbs for movies
 */
function movie_catalog_get_breadcrumbs() {
    $html = '<nav class="breadcrumbs" aria-label="Breadcrumbs">';
    $html .= '<ul class="breadcrumbs-list">';
    
    $html .= '<li><a href="' . esc_url(home_url('/')) . '">Главная</a></li>';
    
    if (is_singular('movie')) {
        $html .= '<li><a href="' . esc_url(get_post_type_archive_link('movie')) . '">Фильмы</a></li>';
        
        $html .= '<li>' . get_the_title() . '</li>';
        
    } elseif (is_post_type_archive('movie')) {
        $html .= '<li>Фильмы</li>';
    } elseif (is_tax('movie_genre')) {
        $html .= '<li><a href="' . esc_url(get_post_type_archive_link('movie')) . '">Фильмы</a></li>';
        
        $term = get_queried_object();
        $html .= '<li>' . esc_html($term->name) . '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Upload minimal content
 */
function upload_minimal_content() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'upload_minimal_content')) {
        wp_send_json_error(array('message' => __('Ошибка безопасности', 'movie-catalog')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('У вас нет прав для выполнения этого действия', 'movie-catalog')));
    }

    if (!function_exists('update_field')) {
        wp_send_json_error(array('message' => __('ACF плагин не активен', 'movie-catalog')));
        return;
    }

    $genres = array(
        'action' => 'Боевик',
        'comedy' => 'Комедия',
        'drama' => 'Драма',
        'thriller' => 'Триллер'
    );

    foreach ($genres as $slug => $name) {
        wp_insert_term($name, 'movie_genre', array('slug' => $slug));
    }

    $movies = array(
        array(
            'title' => 'Inception',
            'year' => '2010',
            'rating' => '6.8',  
            'release_date' => '20100716',
            'poster' => get_template_directory_uri() . '/assets/img/inception.jpg',
            'genres' => array('action', 'thriller'),
            'description' => 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.'
        ),
        array(
            'title' => 'The Shawshank Redemption',
            'year' => '1994',
            'rating' => '5.3',
            'release_date' => '19940923',
            'poster' => get_template_directory_uri() . '/assets/img/shawshank.jpg',
            'genres' => array('drama'),
            'description' => 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.'
        ),
        array(
            'title' => 'The Dark Knight',
            'year' => '2008',
            'rating' => '4.0',
            'release_date' => '20080718',
            'poster' => get_template_directory_uri() . '/assets/img/bat.jpg',
            'genres' => array('action', 'drama'),
            'description' => 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.'
        ),
        array(
            'title' => 'Pulp Fiction',
            'year' => '1994',
            'poster' => get_template_directory_uri() . '/assets/img/pulp.jpg',
            'rating' => '8.9',
            'release_date' => '19941014',
            'genres' => array('thriller', 'drama'),
            'description' => 'The lives of two mob hitmen, a boxer, a gangster and his wife, and a pair of diner bandits intertwine in four tales of violence and redemption.'
        ),
        array(
            'title' => 'Taxi',
            'year' => '2001',
            'rating' => '10',
            'release_date' => '20010101',
            'poster' => get_template_directory_uri() . '/assets/img/taxi.jpg',
            'genres' => array('thriller', 'drama'),
            'description' => 'The lives of two mob hitmen, a boxer, a gangster and his wife, and a pair of diner bandits intertwine in four tales of violence and redemption.'
        ),
        array(
            'title' => 'The Matrix',
            'year' => '1999',
            'rating' => '9.2',
            'release_date' => '19990331',
            'poster' => get_template_directory_uri() . '/assets/img/matrix.jpg',
            'genres' => array('action', 'thriller'),
            'description' => 'When a beautiful stranger leads computer hacker Neo to a forbidding underworld, he discovers the shocking truth--the life he knows is the elaborate deception of an evil cyber-intelligence.'
        ),
        array(
            'title' => 'Forrest Gump',
            'year' => '1994',
            'rating' => '8.8',
            'release_date' => '19940706',
            'poster' => get_template_directory_uri() . '/assets/img/forest.jpg',
            'genres' => array('drama', 'comedy'),
            'description' => 'The presidencies of Kennedy and Johnson, the Vietnam War, the Watergate scandal and other historical events unfold from the perspective of an Alabama man with an IQ of 75, whose only desire is to be reunited with his childhood sweetheart.'
        ),
        array(
            'title' => 'Fight Club',
            'year' => '1999',
            'rating' => '8.7',
            'release_date' => '19991015',
            'poster' => get_template_directory_uri() . '/assets/img/fight.jpg',
            'genres' => array('drama', 'thriller'),
            'description' => 'An insomniac office worker and a devil-may-care soapmaker form an underground fight club that evolves into something much, much more.'
        )
    );

    $debug_info = array();
    $uploaded_movies = 0;
    
    foreach ($movies as $movie) {
        $post_data = array(
            'post_title' => $movie['title'],
            'post_content' => $movie['description'],
            'post_status' => 'publish',
            'post_type' => 'movie'
        );

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            $movie_debug = array(
                'post_id' => $post_id,
                'title' => $movie['title']
            );

            update_field('year', $movie['year'], $post_id);
            $movie_debug['year'] = array(
                'value' => $movie['year'],
                'status' => get_field('year', $post_id)
            );

            update_field('rating', $movie['rating'], $post_id);
            $movie_debug['rating'] = array(
                'value' => $movie['rating'],
                'status' => get_field('rating', $post_id)
            );

            if (isset($movie['release_date'])) {
                update_field('release_date', $movie['release_date'], $post_id);
                $movie_debug['release_date'] = array(
                    'value' => $movie['release_date'],
                    'status' => get_field('release_date', $post_id)
                );
            }

            if (!empty($movie['genres'])) {
                wp_set_object_terms($post_id, $movie['genres'], 'movie_genre');
            }

            if (!empty($movie['poster'])) {
                require_once(ABSPATH . 'wp-admin/includes/media.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/image.php');

                $image_url = media_sideload_image($movie['poster'], $post_id, $movie['title'], 'id');
                
                if (!is_wp_error($image_url)) {
                    set_post_thumbnail($post_id, $image_url);
                    
                    update_field('field_poster', $image_url, $post_id);
                    
                    $movie_debug['poster'] = array(
                        'attachment_id' => $image_url,
                        'featured_image' => get_post_thumbnail_id($post_id),
                        'acf_field' => get_field('field_poster', $post_id)
                    );
                }
            }

            $debug_info[] = $movie_debug;
            $uploaded_movies++;
        }
    }

    wp_send_json_success(array(
        'message' => sprintf(
            __('Успешно добавлено: %d фильмов и %d жанров', 'movie-catalog'),
            $uploaded_movies,
            count($genres)
        ),
        'debug_info' => $debug_info
    ));
}
add_action('wp_ajax_upload_minimal_content', 'upload_minimal_content');

/**
 * Export all movies
 */
function get_minimal_content() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'export_movies')) {
        wp_send_json_error(array('message' => __('Ошибка безопасности', 'movie-catalog')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('У вас нет прав для выполнения этого действия', 'movie-catalog')));
    }

    $args = array(
        'post_type' => 'movie',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );

    $movies = array();
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $genres = wp_get_post_terms($post_id, 'movie_genre', array('fields' => 'slugs'));
            $year = get_post_meta($post_id, 'year', true);
            $poster_url = '';
            $poster = get_field('poster', $post_id);
            if ($poster && is_array($poster)) {
                $poster_url = $poster['url'];
            }

            $movies[] = array(
                'title' => get_the_title(),
                'year' => $year,
                'genres' => $genres,
                'poster_url' => $poster_url
            );
        }
        wp_reset_postdata();
    }

    $json = json_encode($movies, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="movies-' . date('Y-m-d') . '.json"');
    echo $json;
    exit;
}
add_action('wp_ajax_export_movies', 'get_minimal_content');

/**
 * Debug thumbnails
 */
function debug_movie_thumbnails() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'debug_thumbnails')) {
        wp_send_json_error(array('message' => __('Ошибка безопасности', 'movie-catalog')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('У вас нет прав для выполнения этого действия', 'movie-catalog')));
    }

    $args = array(
        'post_type' => 'movie',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );

    $debug_info = array();
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $thumbnail_id = get_post_thumbnail_id($post_id);
            $thumbnail_meta = wp_get_attachment_metadata($thumbnail_id);
            
            $debug_info[] = array(
                'post_id' => $post_id,
                'title' => get_the_title(),
                'has_thumbnail' => has_post_thumbnail($post_id),
                'thumbnail_id' => $thumbnail_id,
                'thumbnail_exists' => $thumbnail_id ? file_exists(get_attached_file($thumbnail_id)) : false,
                'thumbnail_meta' => $thumbnail_meta
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success(array(
        'message' => 'Debug info retrieved',
        'debug_info' => $debug_info
    ));
}
add_action('wp_ajax_debug_thumbnails', 'debug_movie_thumbnails');

/**
 * Debug ACF poster field
 */
function debug_acf_poster() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'debug_acf')) {
        wp_send_json_error(array('message' => __('Ошибка безопасности', 'movie-catalog')));
    }

    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('У вас нет прав для выполнения этого действия', 'movie-catalog')));
    }

    $args = array(
        'post_type' => 'movie',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );

    $debug_info = array();
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_id = get_the_ID();

            $poster = get_field('poster', $post_id);
            
            $debug_info[] = array(
                'post_id' => $post_id,
                'title' => get_the_title(),
                'acf_field_exists' => get_field_object('poster', $post_id) !== false,
                'poster_data' => $poster,
                'raw_poster_data' => get_post_meta($post_id, 'poster', true),
            );
        }
        wp_reset_postdata();
    }

    wp_send_json_success(array(
        'message' => 'ACF debug info retrieved',
        'debug_info' => $debug_info
    ));
}
add_action('wp_ajax_debug_acf', 'debug_acf_poster'); 