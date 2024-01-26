<?php

require_once( __DIR__ . '/approach.php' );
require_once( __DIR__ . '/project-ctl.php' );
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
      'approach' => 'project-approach'
    ]
  ];

  public $approach;
  public $project;
  public $theme;

  public function __construct( $plugin ){
    $this->plugin = $plugin;

    // post type
    $this->project = new UcdlibDatalabProjectsProjectCtl( $this );

    // taxonomies
    $this->approach = new UcdlibDatalabProjectsProjectApproach( $this );
    $this->theme = new UcdlibDatalabProjectsProjectTheme( $this );
  }
}
