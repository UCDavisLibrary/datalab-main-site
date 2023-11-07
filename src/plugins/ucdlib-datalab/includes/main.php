<?php

require_once( __DIR__ . '/config.php' );

class UcdlibDatalab {
  public function __construct(){

    $this->config = new UcdlibDatalabConfig( $this );
  }
}
