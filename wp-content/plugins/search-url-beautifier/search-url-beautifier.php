<?php
/*
Plugin Name: Search URL Beautifier
Plugin URI: https://store.devilhunter.net/wordpress-plugin/search-url-beautifier/
Description: Convert ugly page/post Search URLs into clean, memorable, share-friendly links to match your brand. 
Version: 7.0
Author: Tawhidur Rahman Dear
Author URI: https://www.tawhidurrahmandear.com
Requires at least: 6.0
Requires PHP: 7.4
Domain Path: /languages
License: GPL 2.0 or later
Text Domain: search-url-beautifier
*/

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}



/**
 * Plugin Settings (LEFT SIDE - next to Activate/Deactivate)
 */
 
	function tawhidurrahmandearsub_action_links($actions) {
		$settings_link = '<a href="' . esc_url(admin_url('options-permalink.php')) . '">' . esc_html__('Settings', 'search-url-beautifier') . '</a>';
		array_unshift($actions, $settings_link);
		return $actions;
	}
	add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'tawhidurrahmandearsub_action_links', 10, 1);

/**
 * Plugin Row Meta - (RIGHT SIDE - under plugin description)
 */
 
	function tawhidurrahmandearsub_row_meta($plugin_meta, $plugin_file) {
		if (plugin_basename(__FILE__) !== $plugin_file) {
			return $plugin_meta;
		}
		$plugin_meta[] = '<a href="' . esc_url('https://store.devilhunter.net/wordpress-plugin/search-url-beautifier/') . '" target="_blank">' . esc_html__('Introduction to Plugin', 'search-url-beautifier') . '</a>';
		$plugin_meta[] = '<a href="' . esc_url('https://store.devilhunter.net/documentation/search-url-beautifier/') . '" target="_blank">' . esc_html__('Online Documentation', 'search-url-beautifier') . '</a>';
		$plugin_meta[] = '<a href="' . esc_url('https://wordpress.org/plugins/search-url-beautifier#reviews') . '" target="_blank">' . esc_html__('Rate and Review on WordPress.org', 'search-url-beautifier') . '</a>';
		$plugin_meta[] = '<a href="' . esc_url('https://dearstore.gumroad.com/l/search-url-beautifier') . '" target="_blank">' . esc_html__('Upgrade to Pro for WooCommerce URL Beautification', 'search-url-beautifier') . '</a>';
		return $plugin_meta;
	}
	add_filter('plugin_row_meta', 'tawhidurrahmandearsub_row_meta', 10, 2);



/**
 * Search URL Beautifier - Free version
 */



/* =========================================================
 * Section 1. DEFINE DEFAULT VALUES
 * ========================================================= */

define('tawhidurrahmandearsub_DEFAULT_POST_SLUG', 'articles');
define('tawhidurrahmandearsub_DEFAULT_POST_TITLE', '%1$s articles on %2$s');
define('tawhidurrahmandearsub_DEFAULT_POST_META', 'Explore all articles about %1$s on %2$s Magazine');


/* =========================================================
 * Section 2. ACTIVATION and DEACTIVATION HOOKS
 * ========================================================= */

register_activation_hook(__FILE__, 'tawhidurrahmandearsub_activate');
function tawhidurrahmandearsub_activate() {
    update_option('tawhidurrahmandearsub_needs_flush', true);
    
    $post_slug = get_option('tawhidurrahmandearsub_post_slug', tawhidurrahmandearsub_DEFAULT_POST_SLUG);
    
    // Convert Unicode to URL-safe slug
    $url_safe_slug = sanitize_title($post_slug);
    
    add_rewrite_rule(
        '^' . $url_safe_slug . '/([^/]+)/?$',
        'index.php?tawhidurrahmandearsub_search=$matches[1]&tawhidurrahmandearsub_context=page_post',
        'top'
    );
    
    flush_rewrite_rules();
}

register_deactivation_hook(__FILE__, 'tawhidurrahmandearsub_deactivate');
function tawhidurrahmandearsub_deactivate() {
    flush_rewrite_rules();
}


/* =========================================================
 * Section 3. REGISTER SECTION AND SETTINGS
 * ========================================================= */

add_action('admin_init', function() {

    // Register settings with sanitization callbacks
    register_setting(
        'permalink', // Same as the options page
        'tawhidurrahmandearsub_post_slug',
        array(
            'type' => 'string',
            'sanitize_callback' => 'tawhidurrahmandearsub_sanitize_slug',
            'default' => tawhidurrahmandearsub_DEFAULT_POST_SLUG
        )
    );
    
    register_setting(
        'permalink',
        'tawhidurrahmandearsub_post_title',
        array(
            'type' => 'string',
            'sanitize_callback' => 'tawhidurrahmandearsub_sanitize_text',
            'default' => tawhidurrahmandearsub_DEFAULT_POST_TITLE
        )
    );
    
    register_setting(
        'permalink',
        'tawhidurrahmandearsub_post_meta',
        array(
            'type' => 'string',
            'sanitize_callback' => 'tawhidurrahmandearsub_sanitize_textarea',
            'default' => tawhidurrahmandearsub_DEFAULT_POST_META
        )
    );

    // Add settings section
    add_settings_section(
        'tawhidurrahmandearsub_section',
        __('Search URL Beautifier', 'search-url-beautifier'),
        function() {
            echo '<p>' . esc_html__('Configure custom search URL slugs, titles, and meta descriptions for posts and pages.', 'search-url-beautifier') . '</p>';
        },
        'permalink'
    );

    // Post/Page search settings
    add_settings_field(
        'tawhidurrahmandearsub_post_slug',
        __('Post/Page Search URL Slug', 'search-url-beautifier'),
        function() {
            $val = get_option('tawhidurrahmandearsub_post_slug', tawhidurrahmandearsub_DEFAULT_POST_SLUG);
            echo '<input type="text" name="tawhidurrahmandearsub_post_slug" value="' . esc_attr($val) . '" class="regular-text">';
            echo '<p class="description">' . esc_html__('What type of posts do you have? You can type article or post or posts or stories or editorial or something else, even non-English words in Unicode (প্রবন্ধ, شرط, 文章, סָעִיף, लेख, 記事 etc.) replacing the word articles. Output should be like this - www.example.com/articles/search-term', 'search-url-beautifier') . '</p>';
        },
        'permalink',
        'tawhidurrahmandearsub_section'
    );

    add_settings_field(
        'tawhidurrahmandearsub_post_title',
        __('Post/Page Search Title', 'search-url-beautifier'),
        function() {
            $val = get_option('tawhidurrahmandearsub_post_title', tawhidurrahmandearsub_DEFAULT_POST_TITLE);
            echo '<input type="text" name="tawhidurrahmandearsub_post_title" value="' . esc_attr($val) . '" class="regular-text">';
            echo '<p class="description">' . esc_html__('If someone search for Paris city, and if your website title is XYZ, then output should be - "Paris city articles on XYZ". You can even change language, just keep the structure intact and use Unicode.', 'search-url-beautifier') . '</p>';
        },
        'permalink',
        'tawhidurrahmandearsub_section'
    );

    add_settings_field(
        'tawhidurrahmandearsub_post_meta',
        __('Post/Page Meta Description', 'search-url-beautifier'),
        function() {
            $val = get_option('tawhidurrahmandearsub_post_meta', tawhidurrahmandearsub_DEFAULT_POST_META);
            echo '<textarea name="tawhidurrahmandearsub_post_meta" rows="3" class="large-text">' . esc_textarea($val) . '</textarea>';
            echo '<p class="description">' . esc_html__('For this settings, output should be like this - "Explore all articles about Paris city on XYZ Magazine". You can even change language, just keep the structure intact and use Unicode.', 'search-url-beautifier') . '</p>';
        },
        'permalink',
        'tawhidurrahmandearsub_section'
    );

	// Reset Button
	add_settings_field(
		'tawhidurrahmandearsub_reset_defaults',
		__('Reset Settings', 'search-url-beautifier'),
		function() {
			echo '<button type="submit" name="tawhidurrahmandearsub_reset" value="1" class="button">' . esc_html__('Reset to default values of Search URL Beautifier', 'search-url-beautifier') . '</button>';
			wp_nonce_field('tawhidurrahmandearsub_reset_action', 'tawhidurrahmandearsub_reset_nonce');
			wp_nonce_field('tawhidurrahmandearsub_save_action', 'tawhidurrahmandearsub_save_nonce');
		},
		'permalink',
		'tawhidurrahmandearsub_section'
	);
});


// Sanitization functions
function tawhidurrahmandearsub_sanitize_slug($input) {
    // Allow Unicode characters - only remove invalid characters
    // Don't convert to percent-encoding here
    $sanitized = sanitize_text_field($input);
    // Remove slashes
    $sanitized = stripslashes($sanitized);
    // Remove any characters that are absolutely not allowed in URLs
    $sanitized = preg_replace('/[?&:#%\s]/', '', $sanitized);
    if (empty($sanitized)) {
        $sanitized = tawhidurrahmandearsub_DEFAULT_POST_SLUG;
    }
    return $sanitized;
}

function tawhidurrahmandearsub_sanitize_text($input) {
    $sanitized = sanitize_text_field($input);
    if (empty($sanitized)) {
        $sanitized = tawhidurrahmandearsub_DEFAULT_POST_TITLE;
    }
    return $sanitized;
}

function tawhidurrahmandearsub_sanitize_textarea($input) {
    $sanitized = sanitize_textarea_field($input);
    if (empty($sanitized)) {
        $sanitized = tawhidurrahmandearsub_DEFAULT_POST_META;
    }
    return $sanitized;
}


/* =========================================================
 * Section 4. MANUALLY SAVE SETTINGS (Permalinks Page)
 * ========================================================= */

	add_action('admin_init', function() {
	if (isset($_POST['tawhidurrahmandearsub_reset']) && $_POST['tawhidurrahmandearsub_reset'] === '1') {
		return; 
	}
    
    // Check if we're on the permalinks page and form is submitted
    if (isset($_POST['permalink_structure']) && current_user_can('manage_options')) {
        
        // Verify nonce - try WordPress core nonce first
        $nonce_valid = false;
        
        if (isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'update-permalink')) {
            $nonce_valid = true;
        }
        
        // Also check our custom nonce as fallback
        if (isset($_POST['tawhidurrahmandearsub_save_nonce']) && 
            wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tawhidurrahmandearsub_save_nonce'])), 'tawhidurrahmandearsub_save_action')) {
            $nonce_valid = true;
        }
        
        if (!$nonce_valid) {
            return;
        }
        
        // Save Post Slug
        $slug_value = isset($_POST['tawhidurrahmandearsub_post_slug']) 
            ? sanitize_text_field(wp_unslash($_POST['tawhidurrahmandearsub_post_slug']))
            : '';
        if (empty($slug_value)) {
            $slug_value = tawhidurrahmandearsub_DEFAULT_POST_SLUG;
        }
        update_option('tawhidurrahmandearsub_post_slug', $slug_value);
        
        // Save Post Title
        $title_value = isset($_POST['tawhidurrahmandearsub_post_title']) 
            ? sanitize_text_field(wp_unslash($_POST['tawhidurrahmandearsub_post_title']))
            : '';
        if (empty($title_value)) {
            $title_value = tawhidurrahmandearsub_DEFAULT_POST_TITLE;
        }
        update_option('tawhidurrahmandearsub_post_title', $title_value);
        
        // Save Post Meta Description
        $meta_value = isset($_POST['tawhidurrahmandearsub_post_meta']) 
            ? sanitize_textarea_field(wp_unslash($_POST['tawhidurrahmandearsub_post_meta']))
            : '';
        if (empty($meta_value)) {
            $meta_value = tawhidurrahmandearsub_DEFAULT_POST_META;
        }
        update_option('tawhidurrahmandearsub_post_meta', $meta_value);
        
        // Flush rewrite rules to apply changes
        flush_rewrite_rules();
    }
}, 20);


/* =========================================================
 * Section 5. HANDLE RESET BUTTON
 * ========================================================= */

add_action('admin_init', function() {
    // Check if reset button was clicked
    if (isset($_POST['tawhidurrahmandearsub_reset']) && 
        $_POST['tawhidurrahmandearsub_reset'] === '1' &&
        isset($_POST['tawhidurrahmandearsub_reset_nonce']) &&
        wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tawhidurrahmandearsub_reset_nonce'])), 'tawhidurrahmandearsub_reset_action') &&
        current_user_can('manage_options')) {
        
        // Reset to default values
        update_option('tawhidurrahmandearsub_post_slug', tawhidurrahmandearsub_DEFAULT_POST_SLUG);
        update_option('tawhidurrahmandearsub_post_title', tawhidurrahmandearsub_DEFAULT_POST_TITLE);
        update_option('tawhidurrahmandearsub_post_meta', tawhidurrahmandearsub_DEFAULT_POST_META);
        
        // Flush rewrite rules to apply changes
        flush_rewrite_rules();
        
        // Add admin notice for success
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Search URL Beautifier Settings reset to defaults.', 'search-url-beautifier') . '</p></div>';
        });
        
        // Redirect to remove POST data to prevent resubmission
        wp_safe_redirect(add_query_arg('settings-updated', 'true', wp_get_referer()));
        exit;
    }
});


/* =========================================================
 * Section 6. REWRITE RULES + QUERY VARS
 * ========================================================= */

function tawhidurrahmandearsub_register_rewrite_rules() {
    $post_slug = get_option('tawhidurrahmandearsub_post_slug', tawhidurrahmandearsub_DEFAULT_POST_SLUG);
    
    // Convert Unicode to URL-safe slug when creating rewrite rule
    $url_safe_slug = sanitize_title($post_slug);
    
    add_rewrite_rule(
        '^' . $url_safe_slug . '/(.+)/?$',
        'index.php?tawhidurrahmandearsub_search=$matches[1]&tawhidurrahmandearsub_context=page_post',
        'top'
    );
}

add_action('init', 'tawhidurrahmandearsub_register_rewrite_rules');

add_filter('query_vars', function($vars) {
    $vars[] = 'tawhidurrahmandearsub_search';
    $vars[] = 'tawhidurrahmandearsub_context';
    return $vars;
});


/* =========================================================
 * Section 7. APPLY VANITY SEARCH TO MAIN QUERY
 * ========================================================= */

add_action('pre_get_posts', function($query) {
    if (is_admin() || !$query->is_main_query()) return;
    
    $search_slug = get_query_var('tawhidurrahmandearsub_search');
    
    if (!empty($search_slug)) {
        $original_term = tawhidurrahmandearsub_get_search_term();
        
        if (!empty($original_term)) {
            $search_term = $original_term;
            tawhidurrahmandearsub_clear_search_term();
        } else {
            $search_term = urldecode($search_slug);
            $search_term = str_replace('-', ' ', $search_term);
        }
        
        $query->set('s', $search_term);
        $query->is_search = true;
        $query->is_404 = false;
        
        // ONLY search posts and pages, NOT products
        if (class_exists('WooCommerce')) {
            $query->set('post_type', array('post', 'page'));
            
            // EXCLUDE products by taxonomy
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => array('simple', 'variable', 'grouped', 'external'),
                    'operator' => 'NOT EXISTS',
                )
            ));
        } else {
            $query->set('post_type', array('post', 'page'));
        }
    }
});


/* =========================================================
 * Section 8. PREVENT PRODUCT SEARCH FROM BEAUTIFICATION
 * ========================================================= */

// Hook into request very early to prevent product search redirection
add_action('parse_request', function($wp) {
    if (class_exists('WooCommerce') && is_search()) {
        $post_type = get_query_var('post_type');
        
		$product_param = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		if (!empty($product_param)) {
			$product_param = sanitize_text_field($product_param);
		}
        
        // If product search detected, prevent our plugin from processing
        if ($post_type === 'product' || $product_param === 'product') {
            // Prevent our plugin from processing
            return;
        }
    }
}, 1);


/* =========================================================
 * Section 9. TRANSIENT HANDLER FOR PRESERVING SEARCH TERM CASE
 * ========================================================= */

// Store original search term using WordPress transients (no session_start!)
function tawhidurrahmandearsub_store_search_term($search_term) {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    $key = 'tawhidurrahmandearsub_term_' . md5($ip . $ua);
    
    // Store for 30 seconds - enough time for redirect to complete
    set_transient($key, sanitize_text_field($search_term), 30);
}

// Retrieve original search term from transient
function tawhidurrahmandearsub_get_search_term() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    $key = 'tawhidurrahmandearsub_term_' . md5($ip . $ua);
    
    $term = get_transient($key);
    if ($term) {
        delete_transient($key); // Clear after retrieval
        return sanitize_text_field($term);
    }
    return '';
}

// Clear function (kept for compatibility, but transients auto-expire)
function tawhidurrahmandearsub_clear_search_term() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';
    $ua = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';
    $key = 'tawhidurrahmandearsub_term_' . md5($ip . $ua);
    delete_transient($key);
}


/* =========================================================
 * Section 10. REDIRECT DEFAULT SEARCH to BEAUTY URL
 * ========================================================= */

add_action('template_redirect', function() {
    if (get_query_var('tawhidurrahmandearsub_search')) return;
    if (!is_search() || !is_main_query()) return;
    if (wp_doing_ajax()) return;
    
    // DETECT PRODUCT SEARCH - Multiple ways to check
    $post_type = get_query_var('post_type');
    $product_search = false;
    
    // Check if post_type is product
    if ($post_type === 'product') {
        $product_search = true;
    }
    
	// Check URL parameter directly using filter_input
	$get_post_type = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	if (!empty($get_post_type) && $get_post_type === 'product') {
		$product_search = true;
	}
    
	// Check if searching only products via WooCommerce
	if (class_exists('WooCommerce') && !is_admin()) {
		$referer = isset($_SERVER['HTTP_REFERER']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
		if (strpos($referer, 'product') !== false || strpos($referer, 'shop') !== false) {
		}
	}
    
    // If this is a product search, do NOT redirect (let Pro version handle it)
    if ($product_search) {
        return;
    }
    
    // Also skip if searching by SKU or specific WooCommerce meta
	if (class_exists('WooCommerce')) {
		$sku = filter_input(INPUT_GET, 'sku', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
		if (!empty($sku)) {
			return;
		}
	}
    
    $search = get_search_query(false);
    if (empty($search)) return;
    
    tawhidurrahmandearsub_store_search_term($search);
    
    $url_slug = rawurlencode($search);
    $url_slug = str_replace('%20', '-', $url_slug);
    
	$slug = get_option('tawhidurrahmandearsub_post_slug', tawhidurrahmandearsub_DEFAULT_POST_SLUG);
	$url_safe_slug = sanitize_title($slug);
	$url = home_url("/{$url_safe_slug}/{$url_slug}/");
    
    wp_safe_redirect($url, 301);
    exit;
});


/* =========================================================
 * Section 11. TITLE + META DESCRIPTION
 * ========================================================= */

// Custom Title
add_filter('pre_get_document_title', function($title) {
    $search_slug = get_query_var('tawhidurrahmandearsub_search');
    $is_regular_search = is_search() && empty($search_slug);
    
    // SKIP if this is a product search
    $post_type = get_query_var('post_type');
    $is_product_search = (class_exists('WooCommerce') && $post_type === 'product');
    
    if ($is_product_search) {
        return $title;
    }
    
	$get_post_type = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	if (!empty($get_post_type) && $get_post_type === 'product') {
		return $title;
	}
    
    if (empty($search_slug) && !$is_regular_search) {
        return $title;
    }
    
    $original_term = tawhidurrahmandearsub_get_search_term();
    
    if (!empty($original_term)) {
        $term = $original_term;
    } elseif ($search_slug) {
        $term = urldecode($search_slug);
        $term = str_replace('-', ' ', $term);
    } else {
        $term = get_search_query();
    }
    
    if (empty($term)) {
        return $title;
    }
    
    $template = get_option('tawhidurrahmandearsub_post_title', tawhidurrahmandearsub_DEFAULT_POST_TITLE);
    return sprintf($template, esc_html($term), esc_html(get_bloginfo('name')));
});

// Custom Meta Description
add_action('wp_head', function() {
    $search_slug = get_query_var('tawhidurrahmandearsub_search');
    $is_regular_search = is_search() && empty($search_slug);
    
    if (empty($search_slug) && !$is_regular_search) {
        return;
    }
    
    // Skip if this is a product search
    $post_type = get_query_var('post_type');
    $is_product_search = (class_exists('WooCommerce') && $post_type === 'product');
    
    if ($is_product_search) {
        return;
    }
    
	$get_post_type = filter_input(INPUT_GET, 'post_type', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	if (!empty($get_post_type) && $get_post_type === 'product') {
		return;
	}
    
    $original_term = tawhidurrahmandearsub_get_search_term();
    
    if (!empty($original_term)) {
        $term = $original_term;
    } elseif ($search_slug) {
        $term = urldecode($search_slug);
        $term = str_replace('-', ' ', $term);
    } else {
        $term = get_search_query();
    }
    
    if (empty($term)) {
        return;
    }
    
    $template = get_option('tawhidurrahmandearsub_post_meta', tawhidurrahmandearsub_DEFAULT_POST_META);
    printf('<meta name="description" content="%s">' . "\n", esc_attr(sprintf($template, $term, get_bloginfo('name'))));
});


/* =========================================================
 * Section 12. UNINSTALL HOOK
 * ========================================================= */

register_uninstall_hook( __FILE__, 'tawhidurrahmandearsub_uninstall' );

function tawhidurrahmandearsub_uninstall() {
    // Delete all plugin options
    delete_option( 'tawhidurrahmandearsub_post_slug' );
    delete_option( 'tawhidurrahmandearsub_post_title' );
    delete_option( 'tawhidurrahmandearsub_post_meta' );
    delete_option( 'tawhidurrahmandearsub_needs_flush' );
    
    // Note: Transients auto-expire after 30 seconds - no manual cleanup needed.
}