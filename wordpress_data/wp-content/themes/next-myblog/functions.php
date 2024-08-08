<?php

// カスタムJS読み込み
function enqueue_custom_admin_script() {
    wp_enqueue_script('custom-admin-js', get_template_directory_uri() . '/js/custom-admin.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'enqueue_custom_admin_script');

// アイキャッチ画像の設定
add_theme_support('post-thumbnails');

// 画像サイズの追加（retina対応）
add_image_size('2x-thumbnail', 300, 300, false);
add_image_size('2x-medium', 960, 960, false);
add_image_size('2x-large', 1600, 1600, false);
add_image_size('ogp', 1200, 1200, false);

// 記事ページの画像タグ置換
if ( !function_exists( 'responsive_insert_image' ) ) { // 関数が既に存在しない場合に実行される
    function responsive_insert_image($html, $id, $caption, $title, $align, $url, $size) { // responsive_insert_image関数を定義。パラメータとして画像のHTML、ID、キャプション、タイトル、配置、URL、サイズを受け取る
        $alt_text = get_post_meta($id, '_wp_attachment_image_alt', true); // 画像のIDからALTテキストを取得
        if ( !$alt_text ) { 
            $alt_text = esc_html( get_the_title($id) ); // ALTテキストが存在しない場合、画像のタイトルを取得して使用
        }

        $full = wp_get_attachment_image_src($id, 'full'); // 画像のフルサイズを取得
        $lg = wp_get_attachment_image_src($id, 'large'); // 画像のラージサイズを取得
        $md = wp_get_attachment_image_src($id, 'medium'); // 画像のミディアムサイズを取得
        $lg2x = wp_get_attachment_image_src($id, '2x-large'); // 画像のラージサイズ2xを取得
        $md2x = wp_get_attachment_image_src($id, '2x-medium'); // 画像のミディアムサイズ2xを取得

        // lg2xが存在しない場合はfullサイズを使用
        if (!$lg2x) {
            $lg2x = $full;
        }

        $lg_width = $lg[1]; // ラージサイズの幅を取得
        $lg_height = $lg[2]; // ラージサイズの高さを取得

        $class = 'resp-image-link'; // 画像リンクのクラスを定義

        $attributes  = (!empty($id) ? ' id="attachment_' . esc_attr($id) . '"' : '' ); // 画像のID属性を設定
        $class = ' class="' . 'align' . esc_attr($align) . ' ' . $class . '"'; // 画像のクラス属性を設定
        $a_elem = ''; // $a_elem の初期化
        if ($a_elem != "" || $caption != "") {
            $pic_atts = ""; // 画像にa要素かキャプションがある場合の属性を設定
        } else {
            $pic_atts = $attributes . $class; // それ以外の場合の属性を設定
        }
        if ($caption != "") {
            $link_atts = "img-link"; // キャプションがある場合のリンク属性を設定
        } else {
            $link_atts = $attributes . $class; // それ以外の場合のリンク属性を設定
        }

        $linkptrn = "/<a[^>]*>/"; // aタグを探すための正規表現パターンを定義
        $found = preg_match($linkptrn, $html, $a_elem); // aタグを探して、見つかった場合に$a_elemに格納
        if ($found > 0) {
            $a_elem = $a_elem[0];
            if (strstr($a_elem, "class=\"") !== false) { // 既にクラス属性がある場合
                $a_elem = str_replace("class=\"", "href=\"" . esc_url($full[0]) . "\" target=\"_blank\" " . $class . $link_atts . " ", $a_elem); // クラス属性にtargetとクラスを追加
            } else { // クラス属性がない場合
                $a_elem = str_replace("<a ", "<a href=\"" . esc_url($full[0]) . "\" " . $class . $attributes . " target=\"_blank\" ", $a_elem); // aタグにクラス属性とtargetを追加
            }
        } else {
            $a_elem = '<a href="' . esc_url($full[0]) . '" ' . $class . $attributes . ' target="_blank">'; // aタグが見つからない場合、新しく作成
        }

        if (in_array($size, ['full', 'extra-large', 'large', 'medium', 'thumbnail'])) { // サイズが指定された場合
            if ($caption) {
                $html = '<figure' . $attributes . '>';
            } else {
                $html = '';
            }
            $html .= $a_elem;
            $html .= '<picture>'; // pictureタグを開始
            $html .= '<source media="(max-width: 480px)" type="image/webp" srcset="' . $md[0] . '.webp, ' . $md2x[0] . '.webp 2x">';
            $html .= '<source media="(max-width: 480px)" type="image/jpeg" srcset="' . $md[0] . ', ' . $md2x[0] . ' 2x">';
            $html .= '<source media="(min-width: 481px)" type="image/webp" srcset="' . $lg[0] . '.webp, ' . $lg2x[0] . '.webp 2x">';
            $html .= '<source media="(min-width: 481px)" type="image/jpeg" srcset="' . $lg[0] . ', ' . $lg2x[0] . ' 2x">';
            $html .= '<img loading="lazy" decoding="async" src="' . $lg[0] . '" class="size-full" alt="' . $alt_text . '" width="' . $lg_width . '" height="' . $lg_height . '">';
            $html .= '</picture>'; // pictureタグを終了
            if ($a_elem != "") {
                $html .= '</a>'; // aタグを閉じる
            }
            if ($caption) {
                $html .= '<figcaption class="caption">' . $caption . '</figcaption>';
                $html .= '</figure>'; // figureタグを閉じる
            }
        }
        return $html; // 最終的なHTMLを返す
    }
    add_filter('image_send_to_editor', 'responsive_insert_image', 10, 9); // フィルターフックを追加して、画像をエディターに送信する際に関数を適用
    add_filter('disable_captions', function($a) { return true; }); // キャプションを無効化するフィルターフックを追加
}

// REST APIにカスタム画像サイズを追加するフィールドを追加
add_action('graphql_register_types', function () {
    // JSONスカラータイプを登録
    register_graphql_scalar('JSON', [
        'description' => __('The `JSON` scalar type represents JSON values as specified by ECMA-404.', 'your-text-domain'),
        'serialize' => function ($value) {
            return $value;
        },
        'parse_value' => function ($value) {
            return $value;
        },
        'parse_literal' => function ($ast) {
            return $ast->value;
        },
    ]);

    // カスタムフィールドを登録
    register_graphql_field('MediaItem', 'customImageSizes', [
        'type' => 'JSON',
        'description' => __('Custom image sizes URLs', 'your-text-domain'),
        'args' => [
            'sizes' => [
                'type' => ['list_of' => 'String'],
                'description' => __('List of image sizes', 'your-text-domain'),
            ],
        ],
        'resolve' => function($source, $args, $context, $info) {
            $imageUrls = [];
            $sizes = isset($args['sizes']) ? $args['sizes'] : ['thumbnail', 'medium', 'large'];

            foreach ($sizes as $size) {
                $image_src = wp_get_attachment_image_src($source->ID, $size);
                if ($image_src) {
                    $imageUrls[$size] = $image_src[0];
                }
            }
            return $imageUrls;
        },
    ]);
});
