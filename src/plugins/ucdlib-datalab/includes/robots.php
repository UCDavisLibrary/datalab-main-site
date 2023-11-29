<?php

/**
 * Customizations to bot site indexing rules
 */
class UcdlibDatalabRobots {

  public function __construct( $plugin ){
    $this->plugin = $plugin;

    $this->init();
  }

  public function init(){
    add_filter( 'wp_robots', [$this, 'updateMetaTag'], 100 );
  }

  /**
   * Set attributes for the robots meta tag
   */
  public function updateMetaTag( $robots ){

    if ( !$this->plugin->config->allowSiteIndexing() ) {
      $robots = [
        'noindex' => true,
        'nofollow' => true,
        'follow' => false,
        'index' => false
      ];
    }

    return $robots;
  }

}
