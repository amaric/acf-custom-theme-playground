<?php
// Disable XML-RPC
add_filter('xmlrpc_enabled', '__return_false');

// Remove WordPress version from head and feeds
add_filter('the_generator', '__return_empty_string');

// Add security headers
function elysian_theme_security_headers() {
    if (!is_admin()) {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }
}
add_action('send_headers', 'elysian_theme_security_headers');

// Disable REST API user enumeration for non-logged-in users
function elysian_theme_restrict_user_endpoints($endpoints) {
    if (!is_user_logged_in()) {
        if (isset($endpoints['/wp/v2/users'])) {
            unset($endpoints['/wp/v2/users']);
        }
        if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
            unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
        }
    }
    return $endpoints;
}
add_filter('rest_endpoints', 'elysian_theme_restrict_user_endpoints');
