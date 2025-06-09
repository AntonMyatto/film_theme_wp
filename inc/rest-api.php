<?php
/**
 * REST API endpoints for Movie Catalog
 */

if (!defined('ABSPATH')) {
    exit;
}

class Movie_Catalog_REST_API {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * Register REST API routes
     */
    public function register_routes() {
        register_rest_route('movie-catalog/v1', '/movies', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_movies'),
            'permission_callback' => '__return_true',
            'args' => array(
                'search' => array(
                    'type' => 'string',
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'genre' => array(
                    'type' => 'string',
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'year' => array(
                    'type' => 'integer',
                    'required' => false,
                    'sanitize_callback' => 'absint'
                ),
                'sort' => array(
                    'type' => 'string',
                    'required' => false,
                    'enum' => array('rating', 'year', 'date'),
                    'default' => 'date'
                ),
                'page' => array(
                    'type' => 'integer',
                    'required' => false,
                    'default' => 1,
                    'sanitize_callback' => 'absint'
                )
            )
        ));
    }

    /**
     * Get movies handler
     */
    public function get_movies($request) {
        $args = array(
            'post_type' => 'movie',
            'posts_per_page' => get_option('posts_per_page'),
            'paged' => $request['page']
        );

        if (!empty($request['search'])) {
            $args['s'] = $request['search'];
        }

        if (!empty($request['genre'])) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'movie_genre',
                    'field' => 'slug',
                    'terms' => $request['genre']
                )
            );
        }

        if (!empty($request['year'])) {
            $args['meta_query'] = array(
                array(
                    'key' => 'year',
                    'value' => $request['year'],
                    'compare' => '='
                )
            );
        }

        if (!empty($request['sort'])) {
            switch ($request['sort']) {
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
            }
        }

        $query = new WP_Query($args);
        $movies = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $poster = get_field('poster');
                
                $movies[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'permalink' => get_permalink(),
                    'rating' => get_field('rating'),
                    'poster' => array(
                        'url' => $poster ? $poster['url'] : '',
                        'alt' => $poster ? $poster['alt'] : get_the_title(),
                        'width' => $poster ? $poster['width'] : '',
                        'height' => $poster ? $poster['height'] : ''
                    )
                );
            }
            wp_reset_postdata();
        }

        return new WP_REST_Response(array(
            'movies' => $movies,
            'total_pages' => $query->max_num_pages,
            'current_page' => $request['page'],
            'total_posts' => $query->found_posts
        ), 200);
    }
}

new Movie_Catalog_REST_API(); 