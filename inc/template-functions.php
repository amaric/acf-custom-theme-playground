<?php
// Breadcrumb helper
function elysian_theme_breadcrumbs() {
    if (is_front_page()) return;

    $separator = '<span class="breadcrumb-separator" aria-hidden="true">/</span>';

    echo '<nav class="breadcrumbs" aria-label="' . esc_attr__('Breadcrumb', 'elysian-theme') . '">';
    echo '<a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'elysian-theme') . '</a>';

    if (is_archive()) {
        echo $separator;
        the_archive_title('<span aria-current="page">', '</span>');
    } elseif (is_search()) {
        echo $separator;
        echo '<span aria-current="page">' . esc_html__('Search results', 'elysian-theme') . '</span>';
    } elseif (is_404()) {
        echo $separator;
        echo '<span aria-current="page">' . esc_html__('Page not found', 'elysian-theme') . '</span>';
    } elseif (is_singular()) {
        $post_type = get_post_type();

        if ($post_type === 'post') {
            $page_for_posts = get_option('page_for_posts');
            if ($page_for_posts) {
                echo $separator;
                echo '<a href="' . esc_url(get_permalink($page_for_posts)) . '">' . esc_html(get_the_title($page_for_posts)) . '</a>';
            }
        }

        // Page ancestors
        if (is_page()) {
            $ancestors = get_post_ancestors(get_the_ID());
            $ancestors = array_reverse($ancestors);
            foreach ($ancestors as $ancestor_id) {
                echo $separator;
                echo '<a href="' . esc_url(get_permalink($ancestor_id)) . '">' . esc_html(get_the_title($ancestor_id)) . '</a>';
            }
        }

        echo $separator;
        echo '<span aria-current="page">' . esc_html(get_the_title()) . '</span>';
    }

    echo '</nav>';
}
