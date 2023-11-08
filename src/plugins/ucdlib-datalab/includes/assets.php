<?php

/**
 * Controls the assets (JS/CSS/Images) for the plugin
 */
class UcdlibDatalabAssets {
  public function __construct( $plugin ){
    $this->plugin = $plugin;

    $this->assetsDir = $this->plugin->config->pluginDirectory() . 'assets/assets';
    $this->jsDir = $this->assetsDir . '/js';
    $this->cssDir = $this->assetsDir . '/css';
    $this->filePrefix = 'ucdlib-datalab';
    $this->jsScript = $this->filePrefix . '.js';

    $this->init();

  }

  // sets hooks and filters
  public function init(){
    $this->removeThemeScripts();
    add_action( 'wp_enqueue_scripts', [$this, 'enqueuePublicScripts'] );
  }

  /**
   * Register and load public js/css assets
   */
  public function enqueuePublicScripts(){
    $slug = $this->plugin->config->slug;
    $pluginDir = $this->plugin->config->pluginDirectory();
    $jsPath = $this->jsDir . '/public-dist/' . $this->jsScript;
    $cssPath = $this->cssDir . '/' . $this->filePrefix . '-min.css';
    if ( $this->plugin->config->isDevEnv() ){
      $jsPath = $this->jsDir . '/public-dev/' . $this->jsScript;
      $cssPath = $this->cssDir . '/' . $this->filePrefix . '-dev.css';
    }

    wp_enqueue_script(
      $slug,
      $jsPath,
      array(),
      $this->bundleVersion()
    );
    wp_enqueue_style(
      $slug,
      $cssPath,
      array(),
      $this->bundleVersion()
    );
  }

  /**
   * Prevents theme js and css from loading.
   * We combine/load these assets with datalab custom assets in a single build.
   */
  public function removeThemeScripts(){

    add_action( 'wp_enqueue_scripts', function(){
      $s = 'ucd-public';
      wp_deregister_script($s);
      wp_deregister_style($s);
    }, 1000);

    add_action( 'enqueue_block_editor_assets', function(){
      wp_deregister_script('ucd-components');
    }, 1000);

  }

  /**
   * Returns the version number for the bundle.
   * Uses the APP_VERSION env (prod) or the current timestamp (dev).
   */
  private $bundleVersion;
  public function bundleVersion(){
    if ( !empty($this->bundleVersion) ) {
      return $this->bundleVersion;
    }
    $now = (new DateTime())->getTimestamp();
    $appVersion = $this->plugin->config->getAppVersion();

    if ( !empty($appVersion) ) {
      if ( substr_compare($appVersion, '-1', -strlen('-1')) === 0 ) {
        $bundleVersion = $now;
      } else {
        $bundleVersion = $appVersion;
      }
    } else {
      $bundleVersion = $now;
    }
    $this->bundleVersion = $bundleVersion;
    return $bundleVersion;
  }
}
