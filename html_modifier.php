<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use CodingFreaks\Cookiemanager\Classes\RenderUtility;

include_once plugin_dir_path(__FILE__) . 'classes/RenderUtility.php';//Import codingfreaks Render Utility

/* Hook into HTML to Replace iframes if found in our config. */
/**
 * Callback function for content modification.
 * It uses the RenderUtility class to modify GDPR content.
 *
 * @param string $buffer The buffer content to be modified.
 * @return string The modified buffer content.
 */
function codingfreaks_content_modifier_callback($buffer) {
    $renderUtility = new RenderUtility();
    $buffer = $renderUtility->codingfreaksHook($buffer,["scriptBlocking" => 0]);
    return "$buffer";
}

/**
 * Starts output buffering and sets the callback function for content modification.
 */
function codingfreaks_content_modifier() {
    ob_start('codingfreaks_content_modifier_callback');
}

/**
 * Ends output buffering if it is active and sends the output to the browser.
 */
function codingfreaks_content_modifier_end() {
    if (ob_get_length()) ob_end_flush();
}
add_action('template_redirect', 'codingfreaks_content_modifier');
add_action('shutdown', 'codingfreaks_content_modifier_end', 0);
/* Hook HTML END*/
