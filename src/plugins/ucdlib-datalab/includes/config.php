<?php

class UcdlibDatalabConfig {
  public function __construct( $plugin ){
    $this->plugin = $plugin;

    $this->slug = 'ucdlib-datalab';
  }

  private $envVariables;
  public function getEnv($key){
    if ( !is_array($this->envVariables) ) {
      $this->envVariables = [];
    }
    if ( isset($this->envVariables[$key]) ) {
      return $this->envVariables[$key];
    }
    $this->envVariables[$key] = getenv($key);
    return $this->envVariables[$key];
  }

  public function isDevEnv(){
    return $this->getEnv('UCDLIB_DATALAB_ENV') == 'dev';
  }

  public function getAppVersion(){
    return $this->getEnv('APP_VERSION');
  }

  public function getBuildTime(){
    return $this->getEnv('BUILD_TIME');
  }

  private $pluginDirectory;
  public function pluginDirectory(){
    if ( !empty($this->pluginDirectory) ) {
      return $this->pluginDirectory;
    }
    $this->pluginDirectory = trailingslashit( plugins_url() ) . $this->slug . '/';
    return $this->pluginDirectory;
  }
}
