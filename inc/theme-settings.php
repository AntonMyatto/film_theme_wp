<?php
/**
 * Theme Settings Class
 */
class Movie_Catalog_Theme_Settings {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_theme_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('admin_bar_menu', array($this, 'add_admin_bar_menu'), 100);
    }

    /**
     * Add admin bar menu
     */
    public function add_admin_bar_menu($admin_bar) {
        if (!current_user_can('manage_options')) {
            return;
        }

        $admin_bar->add_menu(array(
            'id'    => 'movie-catalog-settings',
            'title' => '<span class="ab-icon dashicons dashicons-admin-generic"></span>' . __('Настройки Movie Catalog', 'movie-catalog'),
            'href'  => admin_url('admin.php?page=movie-catalog-settings'),
            'meta'  => array(
                'title' => __('Настройки Movie Catalog', 'movie-catalog'),
            ),
        ));

        add_action('admin_head', array($this, 'admin_bar_styles'));
        add_action('wp_head', array($this, 'admin_bar_styles'));
    }

    /**
     * Add admin bar styles
     */
    public function admin_bar_styles() {
        ?>
        <style>
            #wpadminbar #wp-admin-bar-movie-catalog-settings .ab-icon {
                top: 2px;
            }
            #wpadminbar #wp-admin-bar-movie-catalog-settings .ab-icon:before {
                content: "\f111";
                top: 2px;
            }
        </style>
        <?php
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles($hook) {
        if ('toplevel_page_movie-catalog-settings' !== $hook) {
            return;
        }

        wp_enqueue_media();

        ?>
        <style>
            .logo-upload-wrapper {
                margin-bottom: 20px;
            }
            
            .logo-preview {
                margin: 15px 0;
                padding: 10px;
                background: #f0f0f1;
                border: 1px solid #c3c4c7;
                border-radius: 4px;
                display: inline-block;
            }
            
            .logo-preview img {
                display: block;
                max-width: 200px;
                height: auto;
            }
            
            #remove_logo_button {
                margin-left: 10px;
                color: #a00;
            }
            
            #remove_logo_button:hover {
                color: #dc3232;
                border-color: #dc3232;
            }

            .form-table td p.description {
                margin-top: 10px;
            }
        </style>
        <?php
    }

    /**
     * Add theme page
     */
    public function add_theme_page() {
        add_menu_page(
            __('Настройки Movie Catalog', 'movie-catalog'),
            __('Movie Catalog', 'movie-catalog'),
            'manage_options',
            'movie-catalog-settings',
            array($this, 'render_theme_page'),
            'dashicons-admin-settings'
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting(
            'movie_catalog_settings',
            'movie_catalog_logo',
            array(
                'type' => 'integer',
                'sanitize_callback' => array($this, 'sanitize_logo'),
                'default' => ''
            )
        );
        
        register_setting(
            'movie_catalog_settings',
            'movie_catalog_movies_per_page',
            array(
                'type' => 'integer',
                'sanitize_callback' => array($this, 'sanitize_movies_per_page'),
                'default' => 10
            )
        );

        add_settings_section(
            'movie_catalog_general_settings',
            __('Основные настройки', 'movie-catalog'),
            array($this, 'render_section_description'),
            'movie-catalog-settings'
        );

        add_settings_field(
            'movie_catalog_upload_content',
            __('Минимальный контент', 'movie-catalog'),
            array($this, 'render_upload_content_field'),
            'movie-catalog-settings',
            'movie_catalog_general_settings'
        );

        add_settings_field(
            'movie_catalog_logo',
            __('Логотип сайта', 'movie-catalog'),
            array($this, 'render_logo_field'),
            'movie-catalog-settings',
            'movie_catalog_general_settings'
        );

        add_settings_field(
            'movie_catalog_movies_per_page',
            __('Фильмов на странице', 'movie-catalog'),
            array($this, 'render_number_field'),
            'movie-catalog-settings',
            'movie_catalog_general_settings'
        );
    }

    /**
     * Sanitize logo
     */
    public function sanitize_logo($value) {
        if (empty($value)) {
            return '';
        }
        
        $attachment = get_post($value);
        if (!$attachment || $attachment->post_type !== 'attachment' || !wp_attachment_is_image($value)) {
            add_settings_error(
                'movie_catalog_messages',
                'movie_catalog_logo_error',
                __('Пожалуйста, выберите корректное изображение для логотипа', 'movie-catalog'),
                'error'
            );
            return '';
        }
        
        return absint($value);
    }

    /**
     * Sanitize movies per page value
     */
    public function sanitize_movies_per_page($value) {
        $value = absint($value);
        if ($value < 1) {
            $value = 1;
        } elseif ($value > 100) {
            $value = 100;
        }
        return $value;
    }

    /**
     * Render section description
     */
    public function render_section_description() {
        ?>
        <p><?php _e('Настройте параметры вашей темы ниже.', 'movie-catalog'); ?></p>
        <p>
            <?php _e('Ссылка на репозиторий темы: ', 'movie-catalog'); ?>
            <a href="https://github.com/AntonMyatto/film_theme_wp" target="_blank">Movie Catalog Theme</a>
        </p>
        <p>
            <strong><?php _e('API маршруты:', 'movie-catalog'); ?></strong>
        </p>
        <ul style="list-style: disc; padding-left: 20px;">
            <li>
                <code>/wp-json/movie-catalog/v1/movies</code> - 
                <?php _e('Получение списка фильмов с пагинацией и фильтрацией', 'movie-catalog'); ?>
            </li>
            <li>
                <code>/wp-json/movie-catalog/v1/movies/genres</code> - 
                <?php _e('Получение списка всех жанров', 'movie-catalog'); ?>
            </li>
            <li>
                <code>/wp-json/movie-catalog/v1/movies/years</code> - 
                <?php _e('Получение списка всех годов выпуска', 'movie-catalog'); ?>
            </li>
        </ul>
        <?php
    }

    /**
     * Render theme page
     */
    public function render_theme_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <?php settings_errors('movie_catalog_messages'); ?>
            <form action="options.php" method="post">
                <?php
                settings_fields('movie_catalog_settings');
                do_settings_sections('movie-catalog-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render upload content field
     */
    public function render_upload_content_field() {
        ?>
        <div class="upload-content-wrapper">
            <button type="button" class="button button-primary" id="upload_content_button">
                <?php _e('Выгрузить минимальный контент', 'movie-catalog'); ?>
            </button>
            <p class="description">
                <?php _e('Нажмите кнопку, чтобы загрузить минимальный набор фильмов и жанров.', 'movie-catalog'); ?>
            </p>
            <div id="upload_content_status" style="margin-top: 10px; display: none;">
                <span class="spinner is-active" style="float: none; margin: 0 5px 0 0;"></span>
                <span class="status-text"></span>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var uploadButton = $('#upload_content_button');
            var statusDiv = $('#upload_content_status');
            var statusText = statusDiv.find('.status-text');

            uploadButton.on('click', function(e) {
                e.preventDefault();
                
                if (uploadButton.prop('disabled')) {
                    return;
                }

                if (!confirm('<?php _e('Вы уверены? Это действие добавит демонстрационный контент на сайт.', 'movie-catalog'); ?>')) {
                    return;
                }

                uploadButton.prop('disabled', true);
                statusDiv.show();
                statusText.text('<?php _e('Загрузка контента...', 'movie-catalog'); ?>');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'upload_minimal_content',
                        nonce: '<?php echo wp_create_nonce('upload_minimal_content'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            statusText.text(response.data.message);
                            statusDiv.removeClass('notice-error').addClass('notice-success');
                        } else {
                            statusText.text(response.data.message || '<?php _e('Произошла ошибка при загрузке контента', 'movie-catalog'); ?>');
                            statusDiv.removeClass('notice-success').addClass('notice-error');
                        }
                    },
                    error: function() {
                        statusText.text('<?php _e('Произошла ошибка при загрузке контента', 'movie-catalog'); ?>');
                        statusDiv.removeClass('notice-success').addClass('notice-error');
                    },
                    complete: function() {
                        uploadButton.prop('disabled', false);
                        statusDiv.find('.spinner').removeClass('is-active');
                    }
                });
            });
        });
        </script>
        <?php
    }

    /**
     * Render logo field
     */
    public function render_logo_field() {
        $logo_id = get_option('movie_catalog_logo');
        $logo_url = '';
        
        if ($logo_id) {
            $logo_url = wp_get_attachment_image_url($logo_id, 'full');
        }
        ?>
        <div class="logo-upload-wrapper">
            <input type="hidden" name="movie_catalog_logo" id="movie_catalog_logo" value="<?php echo esc_attr($logo_id); ?>">
            
            <div class="logo-preview" style="<?php echo $logo_url ? '' : 'display: none;'; ?>">
                <?php if ($logo_url): ?>
                    <img src="<?php echo esc_url($logo_url); ?>" alt="Logo preview" style="max-width: 200px;">
                <?php endif; ?>
            </div>
            
            <button type="button" class="button" id="upload_logo_button">
                <?php echo $logo_url ? __('Изменить логотип', 'movie-catalog') : __('Загрузить логотип', 'movie-catalog'); ?>
            </button>
            
            <button type="button" class="button" id="remove_logo_button" style="<?php echo $logo_url ? '' : 'display: none;'; ?>">
                <?php _e('Удалить логотип', 'movie-catalog'); ?>
            </button>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var frame;
            var logoPreview = $('.logo-preview');
            var uploadButton = $('#upload_logo_button');
            var removeButton = $('#remove_logo_button');
            var logoInput = $('#movie_catalog_logo');

            uploadButton.on('click', function(e) {
                e.preventDefault();

                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: '<?php _e('Выберите логотип', 'movie-catalog'); ?>',
                    button: {
                        text: '<?php _e('Использовать как логотип', 'movie-catalog'); ?>'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });

                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    logoInput.val(attachment.id);
                    logoPreview.html('<img src="' + attachment.url + '" alt="Logo preview" style="max-width: 200px;">');
                    logoPreview.show();
                    removeButton.show();
                    uploadButton.text('<?php _e('Изменить логотип', 'movie-catalog'); ?>');
                });

                frame.open();
            });

            removeButton.on('click', function(e) {
                e.preventDefault();
                logoInput.val('');
                logoPreview.hide().html('');
                removeButton.hide();
                uploadButton.text('<?php _e('Загрузить логотип', 'movie-catalog'); ?>');
            });
        });
        </script>
        <?php
    }

    /**
     * Render number field
     */
    public function render_number_field() {
        $value = get_option('movie_catalog_movies_per_page', 10);
        ?>
        <input type="number" name="movie_catalog_movies_per_page" value="<?php echo esc_attr($value); ?>" min="1" max="100">
        <?php
    }

    /**
     * Render export field
     */
    public function render_export_field() {
        ?>
        <div class="export-wrapper">
            <button type="button" class="button button-primary" id="export_minimal_button">
                <?php _e('Минимальный контент', 'movie-catalog'); ?>
            </button>
            <p class="description">
                <?php _e('Название, год, жанры, постер', 'movie-catalog'); ?>
            </p>
        </div>

        <script>
        jQuery(document).ready(function($) {
            var exportButton = $('#export_minimal_button');

            exportButton.on('click', function(e) {
                e.preventDefault();
                
                var form = $('<form>', {
                    'method': 'POST',
                    'action': ajaxurl,
                    'target': '_blank'
                }).append(
                    $('<input>', {
                        'type': 'hidden',
                        'name': 'action',
                        'value': 'export_movies'
                    }),
                    $('<input>', {
                        'type': 'hidden',
                        'name': 'nonce',
                        'value': '<?php echo wp_create_nonce('export_movies'); ?>'
                    })
                );

                $('body').append(form);
                form.submit();
                form.remove();
            });
        });
        </script>
        <?php
    }
}

new Movie_Catalog_Theme_Settings(); 