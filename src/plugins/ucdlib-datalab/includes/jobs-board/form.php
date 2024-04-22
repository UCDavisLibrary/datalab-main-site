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
   * Returns options for public select filters.
   * These are mapped to form fields from the admin settings
   */
  public function getPublicFilterOptions(){
    $out = [
      'sector' => [],
      'education' => []
    ];
    if ( !$this->apiAvailable() ) return $out;

    $settings = $this->jobsBoard->getAdminSettings();
    if ( empty($settings['selectedForm']) ) return $out;
    $formId = $settings['selectedForm'];

    $fields = $this->getFormFields($formId, true, true);

    foreach ( array_keys($out) as $key ) {
      if ( !isset($settings['selectedFormFields'][$key]) ) continue;
      $fieldId = $settings['selectedFormFields'][$key];
      foreach( $fields as $field ){
        if ( isset($field['options']) && $field['id'] == $fieldId ) {
          $out[$key] = $field['options'];
        }
      }
    }

    return $out;
  }

  /**
   * Check if a job submission entry has a given meta id
   */
  public function submissionHasMeta( $entryId, $metaId ){
    if ( !$this->apiAvailable() ) return false;

    // ensure submission is for the selected form
    $submission = $this->getSubmissionsById( [$entryId], true );
    if ( empty($submission) ) return false;
    $submission = $submission[0];

    global $wpdb;
    $sql = "SELECT COUNT(*) FROM {$this->entryMetaTable}
      WHERE entry_id = %d AND meta_id = %d";

    $sql = $wpdb->prepare( $sql, $entryId, $metaId );
    $count = $wpdb->get_var( $sql );
    $count = intval( $count );

    return $count > 0;
  }

  /**
   * Update a job submission entry meta value
   */
  public function updateSubmissionMeta($entryId, $metaId, $metaKey, $value, $validate=true){
    if ( !$this->apiAvailable() ) return false;

    // ensure submission is for the selected form
    $submission = $this->getSubmissionsById( [$entryId], true );
    if ( empty($submission) ) return false;
    $submission = $submission[0];

    if ( $validate ){
      $exists = $this->submissionHasMeta( $entryId, $metaId );
      if ( empty($meta) ) return false;
    }

    $submission->update_meta( $metaId, $metaKey, $value );

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
   * Maybe update statuses for all job submissions based on listing expiration date
   * A cron job should be set up to run this method daily
   * Is resource intensive.
   */
  public function updateStatusForAllSubmissions(){
    if ( !$this->apiAvailable() ) return false;

    // get all entries
    $settings = $this->jobsBoard->getAdminSettings();
    if ( empty($settings['selectedForm']) ) return false;
    $formId = $settings['selectedForm'];
    $entries = Forminator_API::get_entries( $formId );
    if ( is_wp_error($entries) ) return false;

    foreach( $entries as $entry ){
      $this->updateStatusFromDate( $entry );
    }
  }

  /**
   * Update job submission status based on listing expiration date
   */
  public function updateStatusFromDate( $submission ){
    if ( !$this->apiAvailable() ) return false;
    if ( !$this->jobsBoard->listingExpirationField() ) return false;

    // get submission if id is passed
    if ( is_numeric($submission) ) {
      $submission = $this->getSubmissionsById( [$submission], true );
      if ( empty($submission) ) return false;
      $submission = $submission[0];
    }
    if ( empty($submission->meta_data[ $this->jobsBoard->listingExpirationField() ]) ) {
      return;
    }

    // get current status, bail if pending
    $statusKey = $this->jobsBoard->metaKeyPrefix . $this->jobsBoard->jobStatusMetaKey;
    $status = $submission->get_meta( $statusKey );
    if ( empty($status) ) return false;
    if ( $status == 'pending' ) return false;

    // get new status, bail if no change
    $expirationDate = $submission->meta_data[ $this->jobsBoard->listingExpirationField() ];
    $expirationDateStamp = strtotime( $expirationDate['value'] );
    if ( !$expirationDateStamp ) {
      return;
    }
    if ( $expirationDateStamp < time() ) {
      $newStatus = 'expired';
    } else {
      $newStatus = 'active';
    }
    if ( $newStatus == $status ) return false;

    // update status
    $updated = $submission->update_meta( $submission->meta_data[ $statusKey ]['id'], $statusKey, $newStatus );

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

  public function queryJobs($args, &$count){
    global $wpdb;
    $count = 0;
    $entries = [];
    $statusMetaKey = $this->jobsBoard->metaKeyPrefix . $this->jobsBoard->jobStatusMetaKey;

    if ( !isset($args['page']) ) $args['page'] = 1;
    if ( !isset($args['status']) ) $args['status'] = 'active';

    $settings = $this->jobsBoard->getAdminSettings();
    if ( empty($settings['selectedForm']) ) return $entries;
    $formId = $settings['selectedForm'];

    $offset = ($args['page'] - 1) * $this->jobsBoard->jobsPerPage;

    // construct where clause
    $secondJoin = '';
    $where = $wpdb->prepare( 'WHERE em.meta_key = %s AND em.meta_value = %s', $statusMetaKey, $args['status'] );
    $where .= $wpdb->prepare( ' AND e.form_id = %d', esc_sql( $formId ) );
    $where .= $wpdb->prepare( ' AND e.is_spam = %s', esc_sql( 0 ) );
    if ( isset( $args['search'] ) ) {
			$where .= $wpdb->prepare( ' AND em2.meta_value LIKE %s', '%' . $wpdb->esc_like( $args['search'] ) . '%' );
      $secondJoin = "INNER JOIN {$this->entryMetaTable} em2 ON e.entry_id = em2.entry_id";
		}

    $sqlCount = "SELECT count(DISTINCT e.entry_id) as total_entries
      FROM {$this->entryTable} e
      INNER JOIN {$this->entryMetaTable} em ON e.entry_id = em.entry_id
      {$secondJoin}
      {$where}";

    $count = intval( $wpdb->get_var( $sqlCount ) );

    if ( $count ) {
      $groupBy = 'GROUP BY e.entry_id';
      $orderBy = 'ORDER BY e.entry_id';
      $order = 'DESC';
      $limit = $wpdb->prepare( 'LIMIT %d, %d', $offset, $this->jobsBoard->jobsPerPage );

      $sql = "SELECT e.entry_id AS entry_id
        FROM {$this->entryTable} e
        INNER JOIN {$this->entryMetaTable} em ON e.entry_id = em.entry_id
        {$secondJoin}
        {$where}
        {$groupBy}
        {$orderBy} {$order}
        {$limit}";

      $results = $wpdb->get_results( $sql );

      foreach ( $results as $result ) {
				$entries[] = new Forminator_Form_Entry_Model( $result->entry_id );
			}

    }

    return $entries;
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
  public function getFormFields($formId, $toBasicArray=false, $includeOptions=false){
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
    if ( $includeOptions ) {
      $propsToExtract['options'] = 'options';
    }
    $basicFields = [];
    foreach( $fields as $field ){
      $basicField = [];
      foreach( $propsToExtract as $prop => $newProp ){
        $basicField[$newProp] = isset($field[$prop]) ? $field[$prop] : '';
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
