<?php
/**
 * Plugin Name: CodingFreaks Cookiemanager
 * Plugin URI: https://cookieapi.coding-freaks.com
 * Description: CodingFreaks Cookiemanager
 * Version: 1.0.1
 * Author: Florian Eibisberger
 * Author URI: https://coding-freaks.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


use CodingFreaks\Cookiemanager\Classes\Helpers;
use CodingFreaks\Cookiemanager\Classes\API;


if ( ! defined( 'ABSPATH' ) ) exit;
//Used for static text in the plugin
$codingfreaksCookiemanagerApiURL = "https://cookieapi.coding-freaks.com"; //Beta Endpoint for Wordpress Plugin Feature Testing


// Include the HTML modifier functions
include_once plugin_dir_path(__FILE__) . 'classes/Helpers.php'; //CodingFreaks Helper Functions
$codingfreakscookiemanagerhelpers = new Helpers(); //Register Global

include_once plugin_dir_path(__FILE__) . 'classes/API.php'; //CodingFreaks API
$options = get_option('codingfreaks_plugin_settings');
if($codingfreakscookiemanagerhelpers->isExtensionConfigValid($options)) {
    $codingfreakscookiemanagerapi = new API($options['codingfreaks_plugin_setting_api_key'], $options['codingfreaks_plugin_setting_api_secret'], $options['codingfreaks_plugin_setting_api_endpoint']); //Register Global
}

include_once plugin_dir_path(__FILE__) . 'html_modifier.php'; //Frontend "Middleware"
include_once plugin_dir_path(__FILE__) . 'ui/settings.php';// Register Settings Page
include_once plugin_dir_path(__FILE__) . 'ui/dashboard.php';// Register Backend Module




/**
 * Add Vue.js Configurator to Admin Backend (CSS and JS Files as script and style tags)
 */
function codingfreaks_register_enqueue_plugin_scripts() {
    $screen = get_current_screen();
    if ($screen->id != "toplevel_page_codingfreakscookiemenupage") {
        return;
    }

    $dir = plugin_dir_path(__FILE__) . 'build/';
    $url = plugin_dir_url(__FILE__) . 'build/';
    $css_files = glob($dir . '*.css');
    $js_files = glob($dir . '*.js');

    foreach ($css_files as $index => $file) {
        $file_url = $url . basename($file);
        wp_enqueue_style('codingfreaks-cookie-plugin-css-' . $index, $file_url, array(), '1.0');
    }

    foreach ($js_files as $index => $file) {
        $file_url = $url . basename($file);
        wp_enqueue_script('codingfreaks-cookie-plugin-js-' . $index, $file_url, array(), '1.0', true);
    }

}
add_action('admin_enqueue_scripts', 'codingfreaks_register_enqueue_plugin_scripts');

/**
 * Adds the Frontend Cookie Javascript
 */
function codingfreaks_cookiemanager_frontend_script_register() {
    $options = get_option('codingfreaks_plugin_settings');

    //use helper function validate config
    if (  $GLOBALS["codingfreakscookiemanagerhelpers"]->isExtensionConfigValid($options)) {
        // Register the frontend script
        wp_register_script('codingfreaks-cookie-consent-script', $options['codingfreaks_plugin_setting_api_endpoint'].'/cdn/consent/cf-cookie-'.$options['codingfreaks_plugin_setting_api_key'].'.js',[],'1.0',[
            "strategy" => "async",
        ]);

    }
    // Enqueue the script
    wp_enqueue_script('codingfreaks-cookie-consent-script');
}
add_action('wp_enqueue_scripts', 'codingfreaks_cookiemanager_frontend_script_register');

/**
 * Adds the Frontend Cookie Javascript data Attribute if Content blocker is enabled
 */
function codingfreaks_data_attribute_register($tag, $handle) {
    if ( 'codingfreaks-cookie-consent-script' !== $handle )
        return $tag;

    return str_replace( ' src', ' data-script-blocking-disabled="true" src', $tag );
}
add_filter('script_loader_tag', 'codingfreaks_data_attribute_register', 10, 2);