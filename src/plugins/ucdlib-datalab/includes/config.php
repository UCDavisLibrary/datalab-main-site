<?php

class UcdlibDatalabConfig {

  public $slug = 'ucdlib-datalab';
  public $plugin;

  public function __construct( $plugin ){
    $this->plugin = $plugin;
    $this->setBuildEnvVars();
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

  // sets the build environment variables from cork-build-info
  public function setBuildEnvVars(){
    $mainBuildInfo = $this->readBuildInfo('datalab-main-site.json');
    if ( $mainBuildInfo ) {
      $appVersion = $this->getBuildVersion($mainBuildInfo);
      if ( $appVersion ) {
        putenv('APP_VERSION=' . $appVersion);
      }
      if ( array_key_exists('date', $mainBuildInfo) ) {
        putenv('BUILD_TIME=' . $mainBuildInfo['date']);
      }
    }

    $themeBuildInfo = $this->readBuildInfo('ucdlib-theme-wp.json');
    if ( $themeBuildInfo ) {
      $websiteTag = $this->getBuildVersion($themeBuildInfo);
      if ( $websiteTag ) {
        putenv('WEBSITE_TAG=' . $websiteTag);
      }
    }
  }

  // reads build info from a cork-build-info file
  public function readBuildInfo($filename) {
    $filePath = '/cork-build-info/' . $filename;
    if (!file_exists($filePath)) {
      return null;
    }
    $jsonContent = file_get_contents($filePath);
    return json_decode($jsonContent, true);
  }

  public function getBuildVersion($buildInfo){
    if ( !empty($buildInfo['tag']) ) {
      return $buildInfo['tag'];
    } else if ( !empty($buildInfo['branch']) ) {
      return $buildInfo['branch'];
    } else if ( !empty($buildInfo['imageTag']) ) {
      $imageTag = explode(':', $buildInfo['imageTag']);
      return end($imageTag);
    }
    return null;
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
