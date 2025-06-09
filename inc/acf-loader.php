<?php
/**
 * ACF Fields Loader
 * 
 * Handles ACF fields registration and JSON sync
 */

if (!defined('ABSPATH')) {
    exit;
}

class Movie_Catalog_ACF_Loader {
    /**
     * Constructor
     */
    public function __construct() {
        add_filter('acf/settings/save_json', array($this, 'set_acf_json_save_point'));
        add_filter('acf/settings/load_json', array($this, 'add_acf_json_load_point'));

        add_action('admin_notices', array($this, 'check_acf_active'));
        
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            add_filter('acf/settings/show_admin', '__return_false');
        }
    }

    /**
     * Set ACF JSON save point
     */
    public function set_acf_json_save_point($path) {
        $path = get_stylesheet_directory() . '/acf-json';
        return $path;
    }

    /**
     * Add ACF JSON load point
     */
    public function add_acf_json_load_point($paths) {
        $paths[] = get_stylesheet_directory() . '/acf-json';
        return $paths;
    }

    /**
     * Check if ACF is active and display notice if not
     */
    public function check_acf_active() {
        if (!class_exists('ACF')) {
            ?>
            <div class="notice notice-error">
                <p>Для работы темы Movie Catalog необходим плагин Advanced Custom Fields. 
                <a href="<?php echo esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=advanced-custom-fields')); ?>">Установить плагин</a></p>
            </div>
            <?php
        }
    }
}

new Movie_Catalog_ACF_Loader(); 