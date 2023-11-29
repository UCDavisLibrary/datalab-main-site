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

  public function allowSiteIndexing(){
    return $this->getEnv('ALLOW_SITE_INDEXING') == 'true';
  }

  /**
   * Return the plugin's public url with trailing slash
   * e.g. https://datalab.ucdavis.edu/wp-content/plugins/ucdlib-datalab/
   */
  private $pluginUrl;
  public function pluginUrl(){
    if ( !empty($this->pluginUrl) ) {
      return $this->pluginUrl;
    }
    $this->pluginUrl = trailingslashit( plugins_url() ) . $this->slug . '/';
    return $this->pluginUrl;
  }

  public function pluginPath($trailingSlash = true){
    return WP_PLUGIN_DIR . '/' . $this->slug . ($trailingSlash ? '/' : '');
  }

  public function pluginEntryPoint(){
    return $this->pluginPath() . $this->slug . '.php';
  }
}
