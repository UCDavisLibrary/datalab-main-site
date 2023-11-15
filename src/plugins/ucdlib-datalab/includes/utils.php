<?php

/**
 * Utility functions for the site
 */
class UcdlibDatalabUtils {
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

  private $logoWidth;
  public function logoWidth(){
    if ( !empty($this->logoWidth) ) {
      return $this->logoWidth;
    }
    $this->logoWidth = get_theme_mod('sf_branding_bar_logo_width', '150px');
    return $this->logoWidth;
  }
}
