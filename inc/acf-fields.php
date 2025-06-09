<?php
/**
 * ACF Fields Registration
 */

if (!defined('ABSPATH')) {
    exit;
}

if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
        'key' => 'group_movie_details',
        'title' => 'Детали фильма',
        'fields' => array(
            array(
                'key' => 'field_poster',
                'label' => 'Постер фильма',
                'name' => 'poster',
                'type' => 'image',
                'required' => 1,
                'return_format' => 'array',
                'preview_size' => 'medium',
                'library' => 'all',
                'min_width' => 300,
                'min_height' => 450,
                'min_size' => '',
                'max_width' => 1200,
                'max_height' => 1800,
                'max_size' => 2,
                'mime_types' => 'jpg,jpeg,png',
                'instructions' => 'Загрузите постер фильма. Требования к изображению:<br>
                • Форматы: JPG, PNG<br>
                • Минимальный размер: 300×450 пикселей<br>
                • Максимальный размер: 1200×1800 пикселей<br>
                • Рекомендуемый размер: 600×900 пикселей<br>
                • Максимальный вес файла: 2MB',
                'min_width_message' => 'Ширина изображения должна быть не менее 300 пикселей. Текущая ширина: %d пикселей.',
                'min_height_message' => 'Высота изображения должна быть не менее 450 пикселей. Текущая высота: %d пикселей.',
                'max_width_message' => 'Ширина изображения не должна превышать 1200 пикселей. Текущая ширина: %d пикселей.',
                'max_height_message' => 'Высота изображения не должна превышать 1800 пикселей. Текущая высота: %d пикселей.',
                'max_size_message' => 'Размер файла не должен превышать 2MB. Текущий размер: %s.'
            ),
            array(
                'key' => 'field_release_date',
                'label' => 'Дата выхода',
                'name' => 'release_date',
                'type' => 'date_picker',
                'required' => 1,
                'display_format' => 'd.m.Y',
                'return_format' => 'Y-m-d',
                'first_day' => 1,
            ),
            array(
                'key' => 'field_rating',
                'label' => 'Рейтинг',
                'name' => 'rating',
                'type' => 'number',
                'required' => 1,
                'min' => 0,
                'max' => 10,
                'step' => 0.1,
            ),
            array(
                'key' => 'field_year',
                'label' => 'Год выпуска',
                'name' => 'year',
                'type' => 'number',
                'required' => 1,
                'min' => 1900,
                'max' => date('Y'),
            ),
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
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => array(
            'featured_image'
        ),
        'active' => true,
        'description' => '',
    ));
} 