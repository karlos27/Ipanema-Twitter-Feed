<?php
/**
 * File Name: internationalization.php
 * Folder Path: /internationalization
 * Plugin Name : Ipanema Twitter Feed
 * 
 **/

define('ITF_TRANSLATION_TEXTDOMAIN', 'ipanema-twitter-feed');

add_action( 'init', 'itf_mn_plugin_internationalization' );

function itf_mn_plugin_internationalization() {
	$locale = apply_filters( 'plugin_locale', get_locale(), ITF_TRANSLATION_TEXTDOMAIN );

	// Search for Translation in /wp-content/languages/plugin/
	if (file_exists( trailingslashit( WP_LANG_DIR ) . 'plugins' . ITF_TRANSLATION_TEXTDOMAIN . '-' . $locale . '.mo' ) ) {
		load_plugin_textdomain( ITF_TRANSLATION_TEXTDOMAIN, false, trailingslashit( WP_LANG_DIR ) );
	}
	// Search for Translation in /wp-content/languages/
	elseif (file_exists( trailingslashit( WP_LANG_DIR ) . ITF_TRANSLATION_TEXTDOMAIN . '-' . $locale . '.mo' ) ) {
		load_textdomain( ITF_TRANSLATION_TEXTDOMAIN, trailingslashit( WP_LANG_DIR ) . ITF_TRANSLATION_TEXTDOMAIN . '-' . $locale . '.mo' );
	// Search for Translation in /wp-content/plugins/ipanema-film-reviews/languages/
	} else {
		load_plugin_textdomain( ITF_TRANSLATION_TEXTDOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
}