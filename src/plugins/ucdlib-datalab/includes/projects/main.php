<?php

require_once( __DIR__ . '/approach.php' );
require_once( __DIR__ . '/partner.php' );
require_once( __DIR__ . '/project-ctl.php' );
require_once( __DIR__ . '/rest.php' );
require_once( __DIR__ . '/theme.php' );

/**
 * Main class for setting up Datalab projects post type and taxonomies
 */
class UcdlibDatalabProjects {

  public $plugin;
  public $slugs = [
    'project' => 'project',
    'taxonomies' => [
      'theme' => 'project-theme',
      'approach' => 'project-approach',
      'partner' => 'project-partner'
    ],
    'meta' => [
      'status' => 'projectStatus',
      'startDate' => 'projectStartDate',
      'endDate' => 'projectEndDate',
      'showLink' => 'showLink'
    ]
  ];

  // settings. sorry, no admin interface for these yet
  public $resultsPerPage = 10;
  public $projectsMenuId = '91'; // use dev tools on appearance->menus to find this id

  public $approach;
  public $project;
  public $theme;
  public $partner;
  public $rest;

  public function __construct( $plugin, $init=true ){
    $this->plugin = $plugin;

    if ($init){
      $this->init();
    }

  }

  // run all actions and filters
  public function init(){
    // post type
    $this->project = new UcdlibDatalabProjectsProjectCtl( $this );

    // taxonomies
    $this->approach = new UcdlibDatalabProjectsProjectApproach( $this );
    $this->theme = new UcdlibDatalabProjectsProjectTheme( $this );
    $this->partner = new UcdlibDatalabProjectsProjectPartner( $this );

    // rest
    $this->rest = new UcdlibDatalabProjectsRest( $this );

  }
}
