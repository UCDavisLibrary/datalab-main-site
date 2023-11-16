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
    register_rest_route( $this->routeNamespace, '/admin-settings', [
      'methods' => 'GET',
      'callback' => [$this, 'getAdminSettings'],
      'permission_callback' => [$this, 'routePermissionCallbackManager']
    ]);
    register_rest_route( $this->routeNamespace, '/admin-settings', [
      'methods' => 'POST',
      'callback' => [$this, 'saveAdminSettings'],
      'permission_callback' => [$this, 'routePermissionCallbackManager']
    ]);
  }

  /**
   * Callback for POST /admin-settings
   */
  public function saveAdminSettings( $request ){
    $data = $request->get_json_params();
    if ( !$data ) {
      return new WP_Error( 'no-data', 'No data provided', ['status' => 400] );
    }

    // update settings
    $settings = [];
    $keys = ['selectedForm'];
    foreach( $keys as $key ){
      if ( isset($data[$key]) ) {
        $settings[$key] = $data[$key];
      }
    }
    if ( count($settings)  ) {
      $this->jobsBoard->updateAdminSettings( $settings );
    }

    // update user roles
    if ( isset($data['addBoardManagers']) && is_array($data['addBoardManagers']) ) {
      foreach( $data['addBoardManagers'] as $userId ){
        $user = get_user_by( 'id', $userId );
        if ( $user ) {
          $user->add_role( $this->jobsBoard->role );
        }
      }
    }
    if ( isset($data['removeBoardManagers']) && is_array($data['removeBoardManagers']) ) {
      foreach( $data['removeBoardManagers'] as $userId ){
        $user = get_user_by( 'id', $userId );
        if ( $user ) {
          $user->remove_role( $this->jobsBoard->role );
        }
      }
    }

    return $this->getAdminSettings();
  }

  /**
   * Callback for GET /admin-settings
   */
  public function getAdminSettings(){
    $out = $this->jobsBoard->getAdminSettings();
    $out['forms'] = $this->jobsBoard->form->getForms(null, 1, 1000, true);

    // get list of users
    // datalab doesnt have a lot of users so no pagination is fine i think
    $users = [];
    $userQuery = new WP_User_Query([
      'orderby' => 'display_name',
      'order' => 'ASC'
    ]);
    if ( !empty($userQuery->results) ) {
      foreach( $userQuery->results as $user ){
        $users[] = [
          'id' => $user->ID,
          'name' => $user->display_name,
          'isSiteAdmin' => in_array( 'administrator', $user->roles ),
          'isBoardManager' => in_array( $this->jobsBoard->role, $user->roles )
        ];
      }
    }
    $out['users'] = $users;

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
