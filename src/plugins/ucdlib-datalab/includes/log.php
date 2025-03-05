<?php

// Class that rotates the wordpress debug log file
class UcdlibDatalabLog {

  public $plugin;
  public $cronJobHook = 'ucdlib_datalab_log';

  public function __construct($plugin){
    $this->plugin = $plugin;
    $this->init();
  }

  public function init(){
    add_action( 'wp', [$this, 'scheduleCronJob'] );
    add_action( $this->cronJobHook, [$this, 'cleanLog'] );
  }

  /**
   * Schedule cron job
   */
  public function scheduleCronJob(){
    if ( !wp_next_scheduled( $this->cronJobHook ) ) {
      wp_schedule_event( time(), 'daily', $this->cronJobHook );
    }
  }

  public function cleanLog(){
    $logSize = 10 * 1024 * 1024; // 10MB
    $logFile = '/var/log/wordpress/debug.log';
    if ( !file_exists( $logFile) ){
      error_log("Log file does not exist: " . $logFile);
      return;
    }
    if ( filesize($logFile) > $logSize ){
      file_put_contents( $logFile, '' );
    }
  }

}
