<?php

/**
 * Rest endpoint controller for jobs board
 */
class UcdlibDatalabJobsBoardRest {
  public function __construct( $jobsBoard ){
    $this->plugin = $jobsBoard->plugin;
    $this->jobsBoard = $jobsBoard;
    $this->routeNamespace = $this->plugin->config->slug . '/jobs-board';

    $this->init();
  }

  /**
   * sets hooks and filters
   */
  public function init(){
    add_action( 'rest_api_init', [$this, 'registerRoutes'] );
  }

  /**
   * Register API routes
   */
  public function registerRoutes(){
    register_rest_route( $this->routeNamespace, '/admin-init', [
      'methods' => 'GET',
      'callback' => [$this, 'adminInitRoute'],
      'permission_callback' => [$this, 'routePermissionCallbackManager']
      //'permission_callback' => [$this, 'routePermissionCallbackPublic']
    ]);
  }

  /**
   * Callback for /admin-init route
   * Gets all data needed to initialize the admin jobs board page
   */
  public function adminInitRoute(){
    $out = [
      'forms' => $this->jobsBoard->form->getForms(null, 1, 1000, true)
    ];
    return $out;
  }

  /**
   * Permission callback for public routes (no authentication required)
   */
  public function routePermissionCallbackPublic(){
    return true;
  }

  /**
   * Permission callback for manager routes (user must have manage_jobs_board capability)
   */
  public function routePermissionCallbackManager(){
    return current_user_can( $this->jobsBoard->capability );
  }
}
