<?php

/**
 * Controller/Model for jobs board submission form
 */
class UcdlibDatalabJobsBoardForm {

  public $plugin;
  public $jobsBoard;

  public $entryTable;
  public $entryMetaTable;

  public $cacheKeys;

  public function __construct( $jobsBoard ){
    $this->plugin = $jobsBoard->plugin;
    $this->jobsBoard = $jobsBoard;

    $this->entryTable = 'wp_frmt_form_entry';
    $this->entryMetaTable = 'wp_frmt_form_entry_meta';

    $this->cacheKeys = [];

    $this->init();
  }

  /**
   * sets hooks and filters
   */
  public function init(){
    add_action( 'forminator_addons_loaded', [$this, 'registerAddon'] );
  }

  /**
   * Register addon for intercepting form submissions
   */
  public function registerAddon(){
    require_once dirname( __FILE__ ) . '/form-addon.php';
    if ( class_exists( 'Forminator_Addon_Loader' ) ) {
      Forminator_Addon_Loader::get_instance()->register( 'UcdlibDatalabJobsBoardAddon' );
    }
  }

  /**
   * Check if forminator API is available
   */
  private $apiAvailable;
  public function apiAvailable(){
    if ( isset($this->apiAvailable) ) {
      return $this->apiAvailable;
    }
    return class_exists( 'Forminator_API' );
  }

  public function clearWpCache(){
    foreach( $this->cacheKeys as $cacheKey ){
      wp_cache_delete( $cacheKey );
    }
  }

  /**
   * Get the total number of job submission entries by status
   */
  public function getSubmissionCountByStatus($status){
    if ( !$this->apiAvailable() ) return 0;

    // check cache from wp cache
    $cacheKey = $this->plugin->config->slug . '_jobs_board_submission_count_' . $status;
    $count = wp_cache_get( $cacheKey );
    if ( $count !== false ) return $count;

    // get form id
    $settings = $this->jobsBoard->getAdminSettings();
    if ( empty($settings['selectedForm']) ) return 0;
    $formId = $settings['selectedForm'];

    // construct sql query
    $metaKey = $this->jobsBoard->metaKeyPrefix . $this->jobsBoard->jobStatusMetaKey;
    global $wpdb;
    $sql = "SELECT COUNT(*) FROM {$this->entryTable} e
      INNER JOIN {$this->entryMetaTable} em ON e.entry_id = em.entry_id
      WHERE e.form_id = %d AND em.meta_key = %s AND em.meta_value = %s AND e.is_spam = 0";

    $sql = $wpdb->prepare( $sql, $formId, $metaKey, $status );
    $count = $wpdb->get_var( $sql );
    $count = intval( $count );

    // cache result
    wp_cache_set( $cacheKey, $count );
    if ( !in_array($cacheKey, $this->cacheKeys) ) {
      $this->cacheKeys[] = $cacheKey;
    }

    return $count;

  }

  /**
   * Get job submission entries by status
   */
  public function getSubmissionsByStatus($status, $page=1){
    $ids = $this->getSubmissionIdsByStatus($status, $page);
    return $this->getSubmissionsById($ids);
  }

  /**
   * Get job submission entries by id
   */
  public function getSubmissionsById($ids=[], $skipIfMissing=false){
    if ( !$this->apiAvailable() ) return [];
    if ( empty($ids) ) return [];

    // get form id
    $settings = $this->jobsBoard->getAdminSettings();
    if ( empty($settings['selectedForm']) ) return [];
    $formId = $settings['selectedForm'];

    $submissions = [];
    foreach( $ids as $id ){
      $submission = Forminator_API::get_entry( $formId, $id );
      if ( is_wp_error($submission) ) continue;
      if ( $skipIfMissing && empty($submission->entry_id) ) continue;
      $submissions[] = $submission;
    }

    return $submissions;
  }

  /**
   * Delete job submission entries by id
   */
  public function deleteSubmissions($ids=[]){
    if ( !$this->apiAvailable() ) return false;
    if ( empty($ids) ) return false;

    // get form id
    $settings = $this->jobsBoard->getAdminSettings();
    if ( empty($settings['selectedForm']) ) return false;
    $formId = $settings['selectedForm'];

    $deleted = Forminator_API::delete_entries( $formId, $ids );
    if ( is_wp_error($deleted) ) return false;
    $this->clearWpCache();

    return $deleted;
  }

  /**
   * Get job submission entry IDs by status
   * ordered by entry_id desc
   */
  public function getSubmissionIdsByStatus($status, $page=1){
    if ( !$this->apiAvailable() ) return [];

    // check cache
    $cacheKey = $this->plugin->config->slug . '_jobs_board_submissions_' . $status . '_' . $page;
    $submissions = wp_cache_get( $cacheKey );
    if ( $submissions !== false ) return $submissions;

    // get form id
    $settings = $this->jobsBoard->getAdminSettings();
    if ( empty($settings['selectedForm']) ) return [];
    $formId = $settings['selectedForm'];

    // construct sql query with pagination
    $metaKey = $this->jobsBoard->metaKeyPrefix . $this->jobsBoard->jobStatusMetaKey;
    $offset = ($page - 1) * $this->jobsBoard->jobsPerPage;
    global $wpdb;
    $sql = "SELECT e.entry_id FROM {$this->entryTable} e
      INNER JOIN {$this->entryMetaTable} em ON e.entry_id = em.entry_id
      WHERE e.form_id = %d AND em.meta_key = %s AND em.meta_value = %s AND e.is_spam = 0
      ORDER BY e.entry_id DESC
      LIMIT %d, %d";

    $sql = $wpdb->prepare( $sql, $formId, $metaKey, $status, $offset, $this->jobsBoard->jobsPerPage );
    $submissions = $wpdb->get_col( $sql );
    $submissions = array_map( 'intval', $submissions );

    // cache result
    wp_cache_set( $cacheKey, $submissions );
    if ( !in_array($cacheKey, $this->cacheKeys) ) {
      $this->cacheKeys[] = $cacheKey;
    }

    return $submissions;
  }

  /**
   * Get forms from forminator API
   * https://wpmudev.com/docs/api-plugin-development/forminator-api-docs/
   */
  public function getForms($formIds=null, $currentPage=1, $perPage=10, $toBasicArray=false ){
    if ( !$this->apiAvailable() ) return [];
    $forms = Forminator_API::get_forms($formIds, $currentPage, $perPage);

    if ( !$toBasicArray ) return $forms;
    $basicForms = [];
    foreach( $forms as $form ){
      $basicForms[] = [
        'id' => $form->id,
        'title' => $form->settings['formName']
      ];
    }
    return $basicForms;
  }

  /**
   * Get form fields from forminator API
   * Returns either a sequential array of form field wrappers
   * or if $toBasicArray is true, a sequential array of just the field objects (brief view)
   */
  public function getFormFields($formId, $toBasicArray=false){
    if ( !$this->apiAvailable() ) return [];
    $fields = Forminator_API::get_form_wrappers($formId);
    if ( is_wp_error($fields) ) return [];
    if ( !$toBasicArray ) return $fields;

    $fields = $this->getFieldsFromWrappers($fields);
    $propsToExtract = [
      'element_id' => 'id',
      'field_label' => 'label',
      'type' => 'type'
    ];
    $basicFields = [];
    foreach( $fields as $field ){
      $basicField = [];
      foreach( $propsToExtract as $prop => $newProp ){
        $basicField[$newProp] = $field[$prop];
      }
      $basicFields[] = $basicField;
    }
    return $basicFields;
  }

  /**
   * Get form fields as associative array
   */
  public function getFieldsFromWrappers($form_wrappers){
    $fields = [];
    foreach ($form_wrappers as $fieldWrapper) {
      if ( !isset($fieldWrapper['fields']) ) continue;
      foreach ($fieldWrapper['fields'] as $field) {
        $fields[$field['element_id']] = $field;
      }
    }
    return $fields;
  }

  /**
   * Get form fields for currently selected job submission form
   * Returns either a sequential array of form field wrappers
   * or if $toBasicArray is true, a sequential array of just the field objects (brief view)
   */
  public function getActiveFormFields($toBasicArray=false){
    $settings = $this->jobsBoard->getAdminSettings();
    if ( empty($settings['selectedForm']) ) return [];
    $formId = $settings['selectedForm'];
    return $this->getFormFields($formId, $toBasicArray);
  }
}
