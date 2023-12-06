<?php

/**
 * Utility functions for the site
 */
class UcdlibDatalabUtils {

  public $plugin;

  public function __construct( $plugin ){
    $this->plugin = $plugin;
  }

  /**
   * Check if a plugin is active
   */
  private $activePlugins;
  public function isPluginActive($entry){
    if ( !isset($this->activePlugins) ) {
      $this->activePlugins = get_option('active_plugins', []);
    }

    // check if entry includes php extension, if not assume we are dealing with slug
    // and add parent directory and php extension
    if ( !preg_match('/\.php$/', $entry) ) {
      $entry = $entry . '/' . $entry . '.php';
    }

    return in_array($entry, $this->activePlugins, true);
  }

  /**
   * Get the URL for the logo set in theme settings
   */
  private $logoUrl;
  public function logoUrl(){
    if ( !empty($this->logoUrl) ) {
      return $this->logoUrl;
    }
    $logoId = get_theme_mod('custom_logo');
    if ( !$logoId ) return '';
    $attachment =  wp_get_attachment_image_src($logoId, 'full');
    if ( !$attachment ) return '';
    $this->logoUrl = $attachment[0];
    return $this->logoUrl;
  }

  /**
   * Get the width for the logo set in theme settings
   */
  private $logoWidth;
  public function logoWidth(){
    if ( !empty($this->logoWidth) ) {
      return $this->logoWidth;
    }
    $this->logoWidth = get_theme_mod('sf_branding_bar_logo_width', '150px');
    return $this->logoWidth;
  }

  /**
   * Check if current page is the plugins page
   */
  public function isPluginsPage(){
    if ( function_exists( 'get_current_screen' ) ) {
      $current_screen = get_current_screen();
      if ( !isset($current_screen->id) ) return false;
      if ( $current_screen->id == 'plugins' ) {
        return true;
      }
    }
    return false;
  }
}
