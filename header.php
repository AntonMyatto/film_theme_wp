<?php
/**
 * The header for our theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="site-branding">
                    <h1 class="site-title">
                        <a href="<?php echo esc_url(home_url('/')); ?>">
                            <?php
                            $custom_logo_id = get_option('movie_catalog_logo');
                            if ($custom_logo_id) {
                                $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
                                if ($logo_url) {
                                    echo '<img src="' . esc_url($logo_url) . '" alt="' . get_bloginfo('name') . '" class="site-logo">';
                                }
                            } else {
                                echo '<img src="' . get_theme_file_uri('assets/icons/logo.svg') . '" alt="' . get_bloginfo('name') . '" class="site-logo">';
                            }
                            ?>
                            <span><?php bloginfo('name'); ?></span>
                        </a>
                    </h1>
                </div>

                <button class="burger-menu" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <nav class="main-navigation">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container' => 'div',
                        'container_class' => 'menu',
                        'menu_class' => 'menu-list',
                        'fallback_cb' => 'wp_page_menu',
                        'items_wrap' => '<ul class="%2$s">%3$s</ul>'
                    ));
                    ?>
                </nav>
            </div>
        </div>
    </header>

    <div id="content" class="site-content">
        <div id="primary" class="content-area">
            <main id="main" class="site-main"> 