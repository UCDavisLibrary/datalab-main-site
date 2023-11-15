<?php

require_once( __DIR__ . '/form.php' );
require_once( __DIR__ . '/rest.php' );

/**
 * Jobs board functionality
 */
class UcdlibDatalabJobsBoard {
  public function __construct( $plugin ){
    $this->plugin = $plugin;

    $this->capability = 'manage_jobs_board';
    $this->role = 'jobs_board_manager';
    $this->adminMenuSlug = $this->plugin->config->slug . '-jobs-board';

    $this->init();
  }

  public function init(){
    $this->form = new UcdlibDatalabJobsBoardForm( $this );
    $this->rest = new UcdlibDatalabJobsBoardRest( $this );

    add_action('current_screen', [$this, 'addCapability']);
    add_action('admin_menu', [$this, 'addMenuItem']);
  }

  /**
   * Add jobs board manager capability to administrator role
   * And creates jobs_board_manager role
   */
  public function addCapability(){
    if ( $this->isAdminMenu() ) {
      // create role if it doesn't exist
      if ( !get_role( $this->role ) ) {
        add_role( $this->role, 'Jobs Board Manager', ["$this->capability" => true] );
      }

      // admin
      $admin = get_role( 'administrator' );
      if ( !$admin->has_cap( $this->capability ) ){
        $admin->add_cap( $this->capability );
      }
    }
  }

  /**
   * Checks if jobs board menu is active screen
   * Can only be used fairly late in the wp lifecycle
   */
  private $isAdminMenu;
  public function isAdminMenu(){
    if ( isset($this->isAdminMenu) ) {
      return $this->isAdminMenu;
    }
    $this->isAdminMenu = false;
    if ( function_exists( 'get_current_screen' ) ) {
      $current_screen = get_current_screen();
      if ( !isset($current_screen->id) ) return false;
      if ( $current_screen->id == 'toplevel_page_' . $this->adminMenuSlug ) {
        $this->isAdminMenu = true;
      }
    }
    return $this->isAdminMenu;
  }

  /**
   * Add jobs board menu item
   */
  public function addMenuItem(){
    add_menu_page(
      'Jobs Board',
      'Jobs Board',
      $this->capability,
      $this->adminMenuSlug,
      [$this, 'renderAdminPage'],
      'dashicons-businessman',
      20
    );
  }

  /**
   * Callback for jobs board menu page
   * Set context and pass to timber
   */
  public function renderAdminPage(){
    $context = [
      'userCanView' => current_user_can( $this->capability ),
      'logoUrl' => $this->plugin->utils->logoUrl(),
      'logoWidth' => $this->plugin->utils->logoWidth(),
      'wpNonce' => wp_create_nonce( 'wp_rest' ),
      'restNamespace' => $this->rest->routeNamespace
    ];
    $this->plugin->timber->renderAdminTemplate( 'jobs-board', $context );
  }

}