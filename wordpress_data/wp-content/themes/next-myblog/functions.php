<?php

// アイキャッチ画像の設定
add_theme_support('post-thumbnails');

// 画像サイズの追加（retina対応）
add_image_size('2x-thumbnail', 300, 300, false);
add_image_size('2x-medium', 960, 960, false);
add_image_size('2x-large', 1600, 1600, false);
add_image_size('2x-ogp', 2400, 2400, false);
add_image_size('ogp', 1200, 1200, false);

// カスタムJS読み込み
function enqueue_custom_admin_script() {
    wp_enqueue_script('custom-admin-js', get_template_directory_uri() . '/js/custom-admin.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'enqueue_custom_admin_script');



