<?php
/**
 * Plugin Name: Ipanema Twitter Feed
 * Plugin URI: https://github.com/karlos27/Ipanema-Twitter-Feed.git
 * Description: This plugin lets you add a twitter feed into your WordPress site.
 * Author: segcgonz
 * Author URI: https://github.com/karlos27
 * Version: 1.0
 * Requires at least: 6.0
 * Tested up to: 6.0
 * Requires PHP: 7.0
 * Text Domain: ipanema-twitter-feed
 * Domain Path: /languages
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

define( 'is_ipanema_tf_admin', 1 );


// Check if user is visiting administration pages and load external code file if that is the case
if ( is_admin() ) {
    // Include administration page
    require plugin_dir_path( __FILE__ ) . 'include/administration/administration.php';
}


// Include language files
include plugin_dir_path( __FILE__ ) . 'include/internationalization/internationalization.php';


// If possible, modify site generator meta tag
include plugin_dir_path( __FILE__ ) . 'include/callback/callback.php';


// Add stylesheet
add_action( 'admin_enqueue_scripts', 'itf_mn_stylesheet_admin_page' );


function itf_mn_stylesheet_admin_page() {
    wp_enqueue_style( 'stylesheet', plugins_url( 'css/style.css', __FILE__ ) );
}


// Declare shortcode 'twitterfeed' with associated function
add_shortcode( 'twitterfeed', 'itf_twttr_shortcode' );

// Function that is called when the 'twitterfeed' shortcode is found
function itf_twttr_shortcode( $atts ) {
	extract( shortcode_atts( array(
		// 'user_name' => esc_html__( 'WordPress', 'ipanema-twitter-feed' ),
		'option_id' => 1
	), $atts ) );
	
	if ( intval( $option_id ) < 1 || intval( $option_id ) > 5 ) {          
        $option_id = 1; 
    }	
	
    $options   = itf_gt_database_options( $option_id );
    
    $user_name = sanitize_text_field( $options['tw_user_name'] );
    $theme     = sanitize_text_field( $options['tw_theme'] );

    if( $theme == false) { $theme = 'light'; } else { $theme = 'dark'; }
 
	if ( !empty( $user_name ) ) {
		$output = '<a class="twitter-timeline" href="'; 
        // $output .= esc_url( 'https://twitter.com/' . $user_name );
        $output .= esc_url( 'https://twitter.com/' . $user_name );
        $output .= '" data-lang="' . $options['tw_lang'] . '" ';
        $output .= '" data-width="' . $options['tw_width'] . '" ';
        $output .= '" data-height="' . $options['tw_height'] . '" ';
        $output .= '" data-theme="' . $theme . '" ';
        $output .= 'data-tweet-limit="' . $options['tw_number_of_tweets'];
        $output .= '">' . 'Tweets by ' . esc_html__( $user_name, 'ipanema-twitter-feed' );
        $output .= '</a><script async ';
        $output .= 'src="//platform.twitter.com/widgets.js"';
        $output .= ' charset="utf-8"></script>';
	} else {
		$output = '';
	}
	return $output;
}

// Assign function to be called when plugin is activated or upgraded
register_activation_hook( __FILE__, 'itf_st_default_options_array' ); 


// Function to create default options if they don't exist upon activation
function itf_st_default_options_array() { 
    itf_gt_database_options();
}

function itf_gt_database_options( $id = 1 ) {
    $options = get_option( 'itf_gt_database_options_' . $id, array() );

    $new_options['tw_setting_name'] = 'Default'; 
    $new_options['tw_user_name'] = 'WordPress'; 
    $new_options['tw_width'] = 560; 
    $new_options['tw_height'] = 200; 
    $new_options['tw_number_of_tweets'] = 3;
    $new_options['tw_theme'] = false;
    $new_options['tw_lang'] = 'en'; 
	
    $merged_options = wp_parse_args( $options, $new_options ); 

    $compare_options = array_diff_key( $new_options, $options );   
    if ( empty( $options ) || !empty( $compare_options ) ) {
        update_option( 'itf_gt_database_options_' . $id, $merged_options );
    }
    return $merged_options;
}
