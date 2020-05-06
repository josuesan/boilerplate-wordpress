<?php

/**
 * Common general functions
 */

/**
 * Load basic scripts
 */
function theme_enqueue_scripts_and_styles()
{
    wp_enqueue_style('style-style', get_stylesheet_directory_uri() . '/assets/css/styles.css');
    wp_enqueue_style('style-aos', get_stylesheet_directory_uri() . '/assets/css/aos.css');

    wp_deregister_script('jquery');
    wp_enqueue_script('script-jquery', get_template_directory_uri() . '/assets/js/min/jquery-3.3.1.min.js', array(), '1.0.1', false);
    wp_enqueue_script('script-owl', get_template_directory_uri() . '/assets/js/min/owl.carousel.min.js', array("script-jquery"), null, true);
    wp_enqueue_script('script-aos', get_template_directory_uri() . '/assets/js/min/aos.js', array("script-jquery"), null, true);
    wp_enqueue_script('script-index', get_template_directory_uri() . '/assets/js/min/index.js', array("script-jquery"), null, true);
}

//OPTIONS PAGE
if (function_exists('acf_add_options_page')) {

    acf_add_options_page(array(
        'page_title'     => 'Theme General Settings',
        'menu_title'    => 'Theme Settings',
        'menu_slug'     => 'theme-general-settings',
        'capability'    => 'edit_posts',
        'redirect'        => false,
    ));

    acf_add_options_sub_page(array(
        'page_title'     => 'Theme Footer Settings',
        'menu_title'    => 'Footer',
        'parent_slug'    => 'theme-general-settings',
    ));
    acf_add_options_sub_page(array(
        'page_title'     => 'Theme Header Settings',
        'menu_title'    => 'Header',
        'parent_slug'    => 'theme-general-settings',
    ));
}

/**
 * Create Menus
 */

function wpb_custom_new_menu()
{
    register_nav_menu('my-custom-menu', __('My Custom Menu'));
}

/**
 *  Create custom post types
 */
function remove_pagesextra()
{
    // Modify pages support
    remove_post_type_support('page', 'excerpt');
    remove_post_type_support('page', 'editor');
    remove_post_type_support('page', 'revisions');
    remove_post_type_support('page', 'discusion');
    remove_post_type_support('page', 'comments');

    // Editor
    if (isset($_GET['post'])) {
        $id = $_GET['post'];
        $template = get_post_meta($id, '_wp_page_template', true);
        if (in_array($template, ["site-terms.php", "privacy-policy.php"])) {
            add_post_type_support('page', 'editor');
        }
    }
}

function remove_menus()
{
    remove_menu_page('edit.php');  // Avoid posts showing in the Admin
}

//-- Init actions
add_action('init', 'disable_emojis');
add_action('init', 'wpb_custom_new_menu');
add_action('wp_enqueue_scripts', 'theme_enqueue_scripts_and_styles');
add_action('init', 'remove_pagesextra');
add_action('do_feed', 'itsme_disable_feed', 1);
add_action('do_feed_rdf', 'itsme_disable_feed', 1);
add_action('do_feed_rss', 'itsme_disable_feed', 1);
add_action('do_feed_rss2', 'itsme_disable_feed', 1);
add_action('do_feed_atom', 'itsme_disable_feed', 1);
add_action('do_feed_rss2_comments', 'itsme_disable_feed', 1);
add_action('do_feed_atom_comments', 'itsme_disable_feed', 1);
add_filter('style_loader_src', 'remove_version_from_style_js');
add_filter('script_loader_src', 'remove_version_from_style_js');
add_filter('clean_url', 'defer_parsing_of_javascript', 11, 1);
add_filter('login_errors', 'hide_login_hints');
add_filter('the_generator', 'remove_wp_version_rss');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');


/**
 * Remove version of wordpress
 */
function remove_wp_version_rss()
{
    return '';
}
/**
 * Filter function used to remove the tinymce emoji plugin.
 * 
 * @param array $plugins 
 * @return array Difference betwen the two arrays
 */
function disable_emojis_tinymce($plugins)
{
    if (is_array($plugins)) {
        return array_diff($plugins, array('wpemoji'));
    } else {
        return array();
    }
}
/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param array $urls URLs to print for resource hints.
 * @param string $relation_type The relation type the URLs are printed for.
 * @return array Difference betwen the two arrays.
 */
function disable_emojis_remove_dns_prefetch($urls, $relation_type)
{
    if ('dns-prefetch' == $relation_type) {
        /** This filter is documented in wp-includes/formatting.php */
        $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');

        $urls = array_diff($urls, array($emoji_svg_url));
    }

    return $urls;
}
/**
 * Disable the emoji's
 */
function disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('tiny_mce_plugins', 'disable_emojis_tinymce');
    add_filter('wp_resource_hints', 'disable_emojis_remove_dns_prefetch', 10, 2);
}

function hide_login_hints($error)
{
    $error = '<strong>ERROR: </strong>You just entered wrong username or password!';
    return $error;
}

function itsme_disable_feed()
{
    wp_redirect(home_url());
    exit();
}

// Pick out the version number from scripts and styles
function remove_version_from_style_js($src)
{
    if (strpos($src, 'ver=' . get_bloginfo('version')))
        $src = remove_query_arg('ver', $src);
    return $src;
}

function defer_parsing_of_javascript($url)
{
    if (is_user_logged_in()) return $url;
    if (FALSE === strpos($url, '.js')) return $url;
    if (strpos($url, 'jquery.js')) return $url;
    return str_replace(' src', ' defer src', $url);
}

/**
 * Remove Styles (Optional only for remove styles of plugins or unnessary styles from page)
 */
/*add_action( 'wp_print_styles', 'my_deregister_styles', 100 );
function my_deregister_styles() {
    //wp_deregister_style( 'gdwpm_styles-css' );
}*/

/**
 * Remove JS (Same case to styles)
 */
/*add_shortcode('pluginhandles', 'wpb_display_pluginhandles');
function wpb_display_pluginhandles()
{
    $wp_scripts = wp_scripts();
    $handlename .= "<ul>";
    foreach ($wp_scripts->queue as $handle) :
        $handlename .=  '<li>' . $handle . '</li>';
    endforeach;
    $handlename .= "</ul>";
    return $handlename;
}
add_shortcode( 'pluginhandles', 'wpb_display_pluginhandles'); 

add_action( 'wp_print_scripts', 'my_deregister_javascript', 100 );
 
function my_deregister_javascript() {
    if ( !is_page('Contact') ) {
        //wp_deregister_script( 'contact-form-7' );
    }
*/
