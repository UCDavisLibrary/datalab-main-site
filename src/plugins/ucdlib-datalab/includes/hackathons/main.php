<?php

require_once( __DIR__ . '/hackathon-ctl.php' );
require_once( __DIR__ . '/hackathon-type.php' );
require_once( __DIR__ . '/rest.php' );

/**
 * Main class for setting up Datalab data challenges and hackathons post type and taxonomies
 */
class UcdlibDatalabHackathons {
  public $plugin;
  public $slugs = [
    'hackathon' => 'challenge',
    'taxonomies' => [
      'type' => 'hackathon-type'
    ],
    'meta' => [
      'landingPageTitle' => 'hackathonLandingPageTitle',
      'startDate' => 'hackathonStartDate',
      'endDate' => 'hackathonEndDate',
      'excerpt' => 'hackathonExcerpt',
      'hostedByExternal' => 'hackathonHostedByExternal',
      'contactEmail' => 'hackathonContactEmail',
      'contactUrl' => 'hackathonContactUrl',
      'showGrandchildrenInNav' => 'showGrandchildrenInNav'
    ]
  ];

  public $resultsPerPage = 10;
  public $hackathonsMenuId = 2039; // use dev tools on appearance->menus to find this id
  public $hackathonsPostId = '2037';

  public $ctl;
  public $rest;
  public $typeTaxonomy;

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

    // rest api for hackathons
    $this->rest = new UcdlibDatalabHackathonsRest( $this );

    // taxonomy for hackathon types
    $this->typeTaxonomy = new UcdlibDatalabHackathonsType( $this );
  }
}
