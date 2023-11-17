<?php

/**
 * Controller/Model for jobs board submission form
 */
class UcdlibDatalabJobsBoardForm {
  public function __construct( $jobsBoard ){
    $this->plugin = $jobsBoard->plugin;
    $this->jobsBoard = $jobsBoard;

    $this->entryTable = 'wp_frmt_form_entry';
    $this->entryMetaTable = 'wp_frmt_form_entry_meta';

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
    $metaKey = 'forminator_addon_dl-jb_' . $this->jobsBoard->jobStatusMetaKey;
    global $wpdb;
    $sql = "SELECT COUNT(*) FROM {$this->entryTable} e
      INNER JOIN {$this->entryMetaTable} em ON e.entry_id = em.entry_id
      WHERE e.form_id = %d AND em.meta_key = %s AND em.meta_value = %s";

    $sql = $wpdb->prepare( $sql, $formId, $metaKey, $status );
    $count = $wpdb->get_var( $sql );

    // cache result
    wp_cache_set( $cacheKey, $count );

    return $count;

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
   */
  public function getFormFields($formId, $toBasicArray=false){
    if ( !$this->apiAvailable() ) return [];
    $fields = Forminator_API::get_form_wrappers($formId);
    if ( is_wp_error($fields) ) return [];
    if ( !$toBasicArray ) return $fields;

    $basicFields = [];
    foreach ($fields as $fieldWrapper) {
      if ( !isset($fieldWrapper['fields']) ) continue;
      foreach ($fieldWrapper['fields'] as $field) {
        $basicFields[] = [
          'id' => $field['element_id'],
          'label' => $field['field_label'],
          'type' => $field['type']
        ];
      }
    }
    return $basicFields;
  }
}
