<?php

require_once( __DIR__ . '/hackathon-ctl.php' );

/**
 * Main class for setting up Datalab data challenges and hackathons post type and taxonomies
 */
class UcdlibDatalabHackathons {
  public $plugin;
  public $slugs = [
    'hackathon' => 'hackathon',
    'taxonomies' => [
    ],
    'meta' => [
      'landingPageTitle' => 'hackathonLandingPageTitle',
      'startDate' => 'hackathonStartDate',
      'endDate' => 'hackathonEndDate'
    ]
  ];

  public $resultsPerPage = 10;
  public $hackathonsMenuId = 2039; // use dev tools on appearance->menus to find this id
  public $hackathonsPostId = '2037';

  public $ctl;

  public function __construct( $plugin, $init=true ){
    $this->plugin = $plugin;

    if ($init){
      $this->init();
    }

  }


  // run all actions and filters
  public function init( ){

    // controller for registering the post type
    $this->ctl = new UcdlibDatalabHackathonsCtl( $this );
  }
}
