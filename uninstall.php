<?php

// Check that code was called from WordPress with uninstallation
// constant declared

if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

// Check if options exist and delete them if present

if ( get_option( 'itf_gt_database_options_' ) != false ) {
	delete_option( 'itf_gt_database_options_' );
}
