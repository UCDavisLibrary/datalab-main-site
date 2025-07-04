<?php

/**
 * Rest endpoint controller for jobs board
 */
class UcdlibDatalabJobsBoardRest {

  public $plugin;
  public $jobsBoard;

  public $routeNamespace;
  public $allowedJobActions;

  public function __construct( $jobsBoard ){
    $this->plugin = $jobsBoard->plugin;
    $this->jobsBoard = $jobsBoard;
    $this->routeNamespace = $this->plugin->config->slug . '/jobs-board';
    $this->allowedJobActions = ['approve', 'deny', 'expire', 'revertToPending', 'delete'];

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
    register_rest_route( $this->routeNamespace, '/run-status-check', [
      'methods' => 'POST',
      'callback' => [$this, 'runStatusCheck'],
      'permission_callback' => [$this, 'routePermissionCallbackManager']
    ]);
    register_rest_route( $this->routeNamespace, '/form-fields/(?P<id>[\d]+)', [
      'methods' => 'GET',
      'callback' => [$this, 'getFormFields'],
      'permission_callback' => [$this, 'routePermissionCallbackManager']
    ]);
    register_rest_route( $this->routeNamespace, '/update-meta/(?P<id>[\d]+)', [
      'methods' => 'POST',
      'callback' => [$this, 'updateSubmissionMeta'],
      'args' => [
        'meta_data' => [
          'required' => true,
          'validate_callback' => function($param, $request, $key){
            return is_array($param);
          }
        ],
        'id' => [
          'required' => true,
          'validate_callback' => function($param, $request, $key){
            return is_numeric($param);
          }
        ]
        ],
      'permission_callback' => [$this, 'routePermissionCallbackManager']
    ]);
    register_rest_route( $this->routeNamespace, '/submissions/(?P<status>[a-zA-Z0-9-]+)', [
      'methods' => 'GET',
      'callback' => [$this, 'submissionsByStatus'],
      'args' => [
        'status' => [
          'required' => true
        ],
        'page' => [
          'required' => false,
          'validate_callback' => function($param, $request, $key){
            return is_numeric($param);
          }
        ]
      ],
      'permission_callback' => [$this, 'routePermissionCallbackManager']
    ]);
    register_rest_route( $this->routeNamespace, '/submissions/(?P<status>[a-zA-Z0-9-]+)', [
      'methods' => 'POST',
      'callback' => [$this, 'submissionsByStatus'],
      'args' => [
        'status' => [
          'required' => true
        ],
        'actions' => [
          'required' => true,
          'validate_callback' => [$this, 'validateSubmissionByStatusPost'],
          'sanitize_callback' => [$this, 'sanitizeSubmissionByStatusPost']
        ],
      ],
      'permission_callback' => [$this, 'routePermissionCallbackManager']
    ]);

    register_rest_route( $this->routeNamespace, '/jobs', [
      'methods' => 'GET',
      'callback' => [$this, 'getJobs'],
      'args' => [
        'page' => [
          'required' => false,
          'validate_callback' => function($param, $request, $key){
            return is_numeric($param);
          }
        ],
        'search' => [
          'required' => false,
          'validate_callback' => function($param, $request, $key){
            return is_string($param);
          }
        ],
        'sector' => [
          'required' => false,
          'validate_callback' => function($param, $request, $key){
            return is_string($param);
          }
        ],
        'education' => [
          'required' => false,
          'validate_callback' => function($param, $request, $key){
            return is_string($param);
          }
        ]
      ],
      'permission_callback' => [$this, 'routePermissionCallbackPublic']
    ]);
  }

  public function getJobs( $request ){
    $page = $request->get_param('page') ?: 1;
    $search = $request->get_param('search');
    $search = $search ? sanitize_text_field( $search ) : '';

    $sector = $request->get_param('sector');
    $sector = $sector ? sanitize_text_field( $sector ) : '';

    $education = $request->get_param('education');
    $education = $education ? sanitize_text_field( $education ) : '';

    $settings = $this->jobsBoard->getAdminSettings();
    $totalCt = 0;
    $query = [
      'page' => $request->get_param('page') ?: 1
    ];
    if ( $search ) {
      $query['search'] = $search;
    }
    if ( $sector ) {
      $query['sector'] = $sector;
    }
    if ( $education ) {
      $query['education'] = $education;
    }
    $jobs = $this->jobsBoard->form->queryJobs( $query, $totalCt );
    $formFields = $this->jobsBoard->form->getActiveFormFields(true);

    $out = [
      'fieldOrder' => $settings['publicFieldDisplayOrder'],
      'totalCt' => $totalCt,
      'totalPageCt' => ceil( $totalCt / $this->jobsBoard->jobsPerPage ),
      'jobs' => $jobs,
      'assignedFormFields' => $settings['selectedFormFields'],
      'formFields' => $formFields,
      'filters' => $this->jobsBoard->form->getPublicFilterOptions()
    ];
    return $out;
  }

  public function runStatusCheck( $request ){
    $transientKey = $this->jobsBoard->plugin->config->slug . '-status-check';
    $transient = get_transient( $transientKey );
    if ( $transient ) {
      return [
        'status' => 'running'
      ];
    }
    set_transient( $transientKey, true);
    $this->jobsBoard->form->updateStatusForAllSubmissions();
    delete_transient( $transientKey );

    return [
      'status' => 'complete'
    ];
  }

  /**
   * Sanitize callback for actions arg for POST /submissions/{status}
   */
  public function sanitizeSubmissionByStatusPost( $param, $request, $key ){
    foreach ($this->allowedJobActions as $action) {
      if ( !isset($param[$action]) ) {
        $param[$action] = [];
      }
      foreach( $param[$action] as $i => $id ){
        $param[$action][$i] = intval($id);
      }
    }
    return $param;
  }

  /**
   * Validate callback for actions arg for POST /submissions/{status}
   */
  public function validateSubmissionByStatusPost( $param, $request, $key ){
    if ( !is_array($param) ) return false;

    // must have at least one recognized action as a list of ids
    $hasAllowedAction = false;
    foreach( $param as $action => $ids ){
      if ( !in_array($action, $this->allowedJobActions) ) continue;
      if ( !is_array($ids) ) continue;

      $validIds = [];
      foreach( $ids as $id ){
        if ( is_numeric($id) ) {
          $validIds[] = intval($id);
        }
      }
      $submissions = $this->jobsBoard->form->getSubmissionsById( $validIds, true );
      if ( count($submissions) !== count($ids) ) continue;
      $hasAllowedAction = true;
    }
    return $hasAllowedAction;
  }

  /**
   * Callback for POST /update-meta/{id}
   */
  public function updateSubmissionMeta( $request ){
    $metaData = $request->get_param('meta_data');
    $id = $request->get_param('id');

    $missingFields = [];
    foreach( $metaData as $metaKey => $value ){
      if ( !isset($value['value']) ) continue;
      if ( !isset($value['id']) ) {
        $missingFields[] = $metaKey;
        continue;
      }
      if ( !$this->jobsBoard->form->submissionHasMeta( $id, $value['id'] ) ){
        $missingFields[] = $metaKey;
      }
    }
    if ( count($missingFields) ) {
      return new WP_Error( 'missing-fields', 'Missing fields', ['status' => 400, 'missingFields' => $missingFields] );
    }
    foreach( $metaData as $metaKey => $value ){
      $v = isset($value['value']) ? $value['value'] : '';
      $this->jobsBoard->form->updateSubmissionMeta( $id, $value['id'], $metaKey, $v, false );
    }

    // maybe update status
    $this->jobsBoard->form->updateStatusFromDate( $id );

    // return submission
    $submission = $this->jobsBoard->form->getSubmissionsById( [$id], true );
    if ( empty($submission) ) {
      return new WP_Error( 'submission-not-found', 'Submission not found', ['status' => 404] );
    }
    $submission = $submission[0];
    return $submission;

  }

  /**
   * Callback for GET and POST /submissions/{status}
   */
  public function submissionsByStatus( $request ){
    $status = $request->get_param('status');
    $page = $request->get_param('page') ?: 1;
    if ( !$status ) {
      return new WP_Error( 'no-status', 'No status provided', ['status' => 400] );
    }
    $allowedStatuses = array_keys( $this->jobsBoard->jobStatuses );
    if ( !in_array($status, $allowedStatuses) ) {
      return new WP_Error( 'invalid-status', 'Invalid status provided', ['status' => 400] );
    }

    // handle any status updates
    $isPost = $request->get_method() === 'POST';
    if ( $isPost ){
      $actions = $request->get_param('actions');
      if ( !empty($actions['deny']) || !empty($actions['delete']) ){
        $ids = empty($actions['deny']) ? $actions['delete'] : $actions['deny'];
        $removed = $this->jobsBoard->form->deleteSubmissions( $ids );
        if (!$removed) {
          return new WP_Error( 'delete-error', 'Error deleting submissions', ['status' => 500] );
        }
      }
      if ( !empty($actions['approve']) ){
        $submissions = $this->jobsBoard->form->getSubmissionsById( $actions['approve'], true );
        foreach( $submissions as $submission ){
          $newStatus = '';
          if ( !$this->jobsBoard->listingExpirationField() ){
            continue;
          }
          if ( empty($submission->meta_data[ $this->jobsBoard->listingExpirationField() ]) ) {
            continue;
          }
          $expirationDate = $submission->meta_data[ $this->jobsBoard->listingExpirationField() ];
          $expirationDateStamp = strtotime( $expirationDate['value'] );
          if ( !$expirationDateStamp ) {
            continue;
          }
          if ( $expirationDateStamp < time() ) {
            $newStatus = 'expired';
          } else {
            $newStatus = 'active';
          }

          if ( $newStatus ) {
            $metaKey = $this->jobsBoard->metaKeyPrefix . $this->jobsBoard->jobStatusMetaKey;
            if ( empty($submission->meta_data[ $metaKey ]) ){
              continue;
            }
            $updated = $submission->update_meta( $submission->meta_data[ $metaKey ]['id'], $metaKey, $newStatus );
            if ( is_wp_error($updated) ) {
              return new WP_Error( 'update-error', 'Error updating submission', ['status' => 500] );
            }
            $postedDateKey = $this->jobsBoard->metaKeyPrefix . $this->jobsBoard->postedDateMetaKey;
            if ( empty($submission->meta_data[ $postedDateKey ]) ){
              continue;
            }
            $updated = $submission->update_meta( $submission->meta_data[ $postedDateKey ]['id'], $postedDateKey, date('Y-m-d') );
          }
        }
      }
      if ( !empty( $actions['expire']) ){
        $ids = $actions['expire'];
        $submissions = $this->jobsBoard->form->getSubmissionsById( $ids, true );
        foreach ($submissions as $submission) {
          $metaKey = $this->jobsBoard->metaKeyPrefix . $this->jobsBoard->jobStatusMetaKey;
          if ( empty($submission->meta_data[ $metaKey ]) ){
            continue;
          }
          $updated = $submission->update_meta( $submission->meta_data[ $metaKey ]['id'], $metaKey, 'expired' );
          if ( is_wp_error($updated) ) {
            return new WP_Error( 'update-error', 'Error updating submission', ['status' => 500] );
          }
        }
      }
      if ( !empty($actions['revertToPending']) ){
        $ids = $actions['revertToPending'];
        $submissions = $this->jobsBoard->form->getSubmissionsById( $ids, true );
        foreach ($submissions as $submission) {
          $metaKey = $this->jobsBoard->metaKeyPrefix . $this->jobsBoard->jobStatusMetaKey;
          if ( empty($submission->meta_data[ $metaKey ]) ){
            continue;
          }
          $updated = $submission->update_meta( $submission->meta_data[ $metaKey ]['id'], $metaKey, 'pending' );
          if ( is_wp_error($updated) ) {
            return new WP_Error( 'update-error', 'Error updating submission', ['status' => 500] );
          }
        }
      }
    }

    $totalCt = $this->jobsBoard->form->getSubmissionCountByStatus( $status );
    $submissions = $this->jobsBoard->form->getSubmissionsByStatus( $status, $page );
    $formFields = $this->jobsBoard->form->getActiveFormFields(true);

    $out = [
      'totalCt' => $totalCt,
      'totalPageCt' => ceil( $totalCt / $this->jobsBoard->jobsPerPage ),
      'page' => $page,
      'submissions' => $submissions,
      'formFields' => $formFields,
      'assignedFormFields' => $this->jobsBoard->getAdminSettings()['selectedFormFields']
    ];
    return $out;

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
    $keys = ['selectedForm', 'selectedFormFields', 'publicFieldDisplayOrder'];
    foreach( $keys as $key ){
      if ( isset($data[$key]) ) {
        $settings[$key] = $data[$key];
      }
    }
    if ( count($settings)  ) {
      $this->jobsBoard->updateAdminSettings( $settings );
    }
    $this->jobsBoard->form->saveIntegrationSettings();

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

    // form fields
    $out['formFields'] = [];
    if ( isset($out['selectedForm']) ) {
      $out['formFields'] = $this->jobsBoard->form->getFormFields( $out['selectedForm'], true );
    }

    return $out;
  }

  /**
   * Callback for GET /form-fields/{id}
   */
  public function getFormFields( $request ){
    $formId = $request->get_param('id');
    if ( !$formId ) {
      return new WP_Error( 'no-form-id', 'No form id provided', ['status' => 400] );
    }
    $fields = $this->jobsBoard->form->getFormFields( $formId, true );
    return $fields;
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
