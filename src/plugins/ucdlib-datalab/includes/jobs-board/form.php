<?php

/**
 * Controller/Model for jobs board submission form
 */
class UcdlibDatalabJobsBoardForm {
  public function __construct( $jobsBoard ){
    $this->plugin = $jobsBoard->plugin;
    $this->jobsBoard = $jobsBoard;

    $this->init();
  }

  /**
   * sets hooks and filters
   */
  public function init(){

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
}
