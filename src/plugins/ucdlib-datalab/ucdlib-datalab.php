<?php
/**
 * Plugin Name: UC Davis Library Datalab
 * Plugin URI: https://github.com/UCDavisLibrary/datalab-main-site
 * Description: Customizations for the UC Davis Library Datalab
 * Author: UC Davis Library Online Strategy
 */

require_once(ABSPATH . 'wp-admin/includes/file.php');
$composer_autoload = get_home_path() . 'vendor/autoload.php';
if ( file_exists( $composer_autoload ) ) {
  require_once $composer_autoload;
}

require_once( __DIR__ . '/includes/main.php' );
$GLOBALS['ucdlibDatalab'] = new UcdlibDatalab();
