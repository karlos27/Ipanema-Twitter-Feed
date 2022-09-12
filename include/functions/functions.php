<?php
/**
 * File Name: functions.php
 * Folder Path: /include/functions
 * Plugin Name : Ipanema Twitter Feed
 * 
 **/

add_filter( 'the_generator', 'itf_hdr_generator_filter', 10, 2 );

function itf_hdr_generator_filter ( $html, $type ) {
	if ( $type == 'xhtml' ) {
		$html = preg_replace( '("WordPress.*?")', '"Ipanema Twitter Feed 1.0"', $html );
	}
    
	return $html;
}