<?php

require_once( __DIR__ . '/assets.php' );
require_once( __DIR__ . '/config.php' );
require_once( __DIR__ . '/hummingbird.php' );
require_once( __DIR__ . '/jobs-board/main.php' );
require_once( __DIR__ . '/robots.php' );
require_once( __DIR__ . '/timber.php' );
require_once( __DIR__ . '/utils.php' );

class UcdlibDatalab {

  public $config;
  public $utils;
  public $assets;
  public $hummingbird;
  public $jobsBoard;
  public $robots;
  public $timber;

  public function __construct(){

    // load these first
    $this->config = new UcdlibDatalabConfig( $this );
    $this->utils = new UcdlibDatalabUtils( $this );

    $this->assets = new UcdlibDatalabAssets( $this );
    $this->hummingbird = new UcdlibDatalabHummingbird( $this );
    $this->jobsBoard = new UcdlibDatalabJobsBoard( $this );
    $this->robots = new UcdlibDatalabRobots( $this );
    $this->timber = new UcdlibDatalabTimber( $this );
  }
}
