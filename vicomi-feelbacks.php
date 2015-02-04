<?php
/*
Plugin Name: Vicomi Feelbacks
Plugin URI: http://vicomi.com/
Description: [ ADD DESCRIPTION HERE ]
Author: Vicomi <support@vicomi.com>
Version: 1.0
Author URI: http://vicomi.com/
*/

require_once(dirname(__FILE__) . '/lib/vc-api.php');
define('VICOMI_FEELBACKS_V', '1.0');

function vicomi_feelbacks_plugin_basename($file) {
    $file = dirname($file);

    // From WP2.5 wp-includes/plugin.php:plugin_basename()
    $file = str_replace('\\','/',$file); // sanitize for Win32 installs
    $file = preg_replace('|/+|','/', $file); // remove any duplicate slash
    $file = preg_replace('|^.*/' . PLUGINDIR . '/|','',$file); // get relative path from plugins dir

    if ( strstr($file, '/') === false ) {
        return $file;
    }

    $pieces = explode('/', $file);
    return !empty($pieces[count($pieces)-1]) ? $pieces[count($pieces)-1] : $pieces[count($pieces)-2];
}

if ( !defined('WP_CONTENT_URL') ) {
    define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
}
if ( !defined('PLUGINDIR') ) {
    define('PLUGINDIR', 'wp-content/plugins'); // back compat.
}

define('VICOMI_FEELBACKS_PLUGIN_URL', WP_CONTENT_URL . '/plugins/' . vicomi_feelbacks_plugin_basename(__FILE__));

// api ref
$vicomi_feelbacks_api = new VicomiAPI();


function vicomi_feelbacks_is_installed() {
    return get_option('vicomi_feelbacks_api_key');
}

/**************************************************
* register plugin state events
**************************************************/
function vicomi_feelbacks_activate() {
    $vicomi_feelbacks_api = new VicomiAPI();
    $vicomi_feelbacks_api->plugin_activate(get_option('vicomi_feelbacks_api_key'), 'feelbacks');
}

function vicomi_feelbacks_deactivate() {
    $vicomi_feelbacks_api = new VicomiAPI();
    $vicomi_feelbacks_api->plugin_deactivate(get_option('vicomi_feelbacks_api_key'), 'feelbacks');
}

function vicomi_feelbacks_uninstall() {
    $vicomi_feelbacks_api = new VicomiAPI();
    $vicomi_feelbacks_api->plugin_uninstall(get_option('vicomi_feelbacks_api_key'), 'feelbacks');
}

register_activation_hook( __FILE__, 'vicomi_feelbacks_activate' );
register_deactivation_hook( __FILE__, 'vicomi_feelbacks_deactivate' );
register_uninstall_hook( __FILE__, 'vicomi_feelbacks_uninstall' );

function vicomi_feelbacks_can_replace() {
    global $id, $post;

    if (get_option('vicomi_feelbacks_active') === '0'){ return false; }

    $replace = get_option('vicomi_feelbacks_replace');

    if ( is_feed() )                       { return false; }
    if ( 'draft' == $post->post_status )   { return false; }
	if ( !get_option('vicomi_feelbacks_api_key') ) { return false; }
    else if ( 'all' == $replace )          { return true; }
}

function vicomi_feelbacks_manage_dialog($message, $error = false) {
    global $wp_version;

    echo '<div '
        . 'class="error fade'
        . ( (version_compare($wp_version, '2.5', '<') && $error) ? '-ff0000' : '' )
        . '"><p><strong>'
        . $message
        . '</strong></p></div>';
}


/**************************************************
* add vicomi to settings menu
**************************************************/
function add_feelbacks_settings_menu(){
     add_options_page('Vicomi Feelbacks', 'Vicomi', 'manage_options', 'vicomi-feelbacks', 'vicomi_feelbacks_manage');
}

function vicomi_feelbacks_manage() {
    include_once(dirname(__FILE__) . '/manager.php');
}

add_action('admin_menu', 'add_feelbacks_settings_menu');

/**************************************************
* add action links to plgins page
**************************************************/
function vicomi_feelbacks_plugin_action_links($links, $file) {
    $plugin_file = basename(__FILE__);
    if (basename($file) == $plugin_file) {
        if (!vicomi_feelbacks_is_installed()) {
            $settings_link = '<a href="options-general.php?page=vicomi-feelbacks">Configure</a>';
        } else {
            $settings_link = '<a href="options-general.php?page=vicomi-feelbacks#adv">Settings</a>';    
        }
        array_unshift($links, $settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'vicomi_feelbacks_plugin_action_links', 10, 2);

/**************************************************
* add styles and javascript to head
**************************************************/
function vicomi_feelbacks_admin_head() {

    if (isset($_GET['page']) && $_GET['page'] == 'vicomi-feelbacks') { ?>

        <style>

        .vicomi-feelbacks-menu {
        	height: 20px;
        	list-style: none;
        	margin: 0;
        	padding: 0;
        	border: 0;
        }

        .vicomi-feelbacks-menu span {
        	float: left;
        	color: #333;
        	padding: 0.25em;
        	height: 16px;
        	cursor: pointer;
        	margin-right: 8px;
        	text-align: right;
        }

        .vicomi-feelbacks-menu span.selected {
        	font-weight: bold;
        }

        .vicomi-feelbacks-header {
        	height:20px;
        	border-bottom: 1px solid #ccc;
        	padding: 10px 0;
        }

        .vicomi-feelbacks-content {
        }

        .vicomi-feelbacks-btn {
        	display: inline-block;
        	padding: 6px 12px;
        	margin-bottom: 0;
        	font-size: 14px;
        	font-weight: normal;
        	line-height: 1.428571429;
        	text-align: center;
        	white-space: nowrap;
        	vertical-align: middle;
        	cursor: pointer;
        	border: 1px solid transparent;
        	border-radius: 4px;
        	-webkit-user-select: none;
        	-moz-user-select: none;
        	-ms-user-select: none;
        	-o-user-select: none;
        	user-select: none;
        	
        	color: #333333;
        	background-color: #ffffff;
        	border-color: #cccccc;
        }

        .vicomi-feelbacks-btn:hover{
        	color: #333333;
        	background-color: #ebebeb;
        	border-color: #adadad;
        }

        .form-section{
        	padding-top:10px;
        }

        .form-section input{
        	width: 200px;
        	height: 30px;
        }

        </style>

        <script type="text/javascript">
            jQuery(function($) {
                $('.vicomi-feelbacks-menu span').click(function() {
                    $('.vicomi-feelbacks-menu span.selected').removeClass('selected');
                    $('.vicomi-feelbacks-page, .vicomi-feelbacks-settings').hide();
                    $('.' + $(this).attr('rel')).show();
            		$(this).addClass('selected');
                });
            });
        </script>
    <?php

    }
}
add_action('admin_head', 'vicomi_feelbacks_admin_head');

/**************************************************
* add feelbacks container and script to page
**************************************************/

$EMBED = false;
function vicomi_feelbacks_template($content) {
    global $EMBED;

    if ( !( is_singular() ) ) {
        return;
    }

    if ( !vicomi_feelbacks_is_installed() || !vicomi_feelbacks_can_replace() ) {
        return $content;
    }

    $EMBED = true;

    $plugin_content = '<div id="vc-feelback-main" data-access-token="' . get_option('vicomi_feelbacks_api_key') . '"></div>' .
        '<script type="text/javascript" src="http://assets-prod.vicomi.com/vicomi.js"></script>';

    return $content . $plugin_content;
}

add_action('the_content', 'vicomi_feelbacks_template');


/**
 * JSON ENCODE for PHP < 5.2.0
 * Checks if json_encode is not available and defines json_encode
 * to use php_json_encode in its stead
 * Works on iteratable objects as well - stdClass is iteratable, so all WP objects are gonna be iteratable
 */
if(!function_exists('cf_json_encode')) {
    function cf_json_encode($data) {
// json_encode is sending an application/x-javascript header on Joyent servers
// for some unknown reason.
//         if(function_exists('json_encode')) { return json_encode($data); }
//         else { return cfjson_encode($data); }
        return cfjson_encode($data);
    }

    function cfjson_encode_string($str) {
        if(is_bool($str)) {
            return $str ? 'true' : 'false';
        }

        return str_replace(
            array(
                '"'
                , '/'
                , "\n"
                , "\r"
            )
            , array(
                '\"'
                , '\/'
                , '\n'
                , '\r'
            )
            , $str
        );
    }

    function cfjson_encode($arr) {
        $json_str = '';
        if (is_array($arr)) {
            $pure_array = true;
            $array_length = count($arr);
            for ( $i = 0; $i < $array_length ; $i++) {
                if (!isset($arr[$i])) {
                    $pure_array = false;
                    break;
                }
            }
            if ($pure_array) {
                $json_str = '[';
                $temp = array();
                for ($i=0; $i < $array_length; $i++) {
                    $temp[] = sprintf("%s", cfjson_encode($arr[$i]));
                }
                $json_str .= implode(',', $temp);
                $json_str .="]";
            }
            else {
                $json_str = '{';
                $temp = array();
                foreach ($arr as $key => $value) {
                    $temp[] = sprintf("\"%s\":%s", $key, cfjson_encode($value));
                }
                $json_str .= implode(',', $temp);
                $json_str .= '}';
            }
        }
        else if (is_object($arr)) {
            $json_str = '{';
            $temp = array();
            foreach ($arr as $k => $v) {
                $temp[] = '"'.$k.'":'.cfjson_encode($v);
            }
            $json_str .= implode(',', $temp);
            $json_str .= '}';
        }
        else if (is_string($arr)) {
            $json_str = '"'. cfjson_encode_string($arr) . '"';
        }
        else if (is_numeric($arr)) {
            $json_str = $arr;
        }
        else if (is_bool($arr)) {
            $json_str = $arr ? 'true' : 'false';
        }
        else {
            $json_str = '"'. cfjson_encode_string($arr) . '"';
        }
        return $json_str;
    }
}

?>
