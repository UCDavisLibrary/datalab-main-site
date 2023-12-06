<?php

/**
 * Controls the assets (JS/CSS/Images) for the plugin
 */
class UcdlibDatalabAssets {

  public $plugin;
  public $assetsUrl;
  public $assetsPath;
  public $filePrefix;
  public $cssUrl;
  public $cssPath;
  public $cssUrlDev;
  public $cssPathDev;
  public $cssUrlDist;
  public $cssPathDist;
  public $jsUrl;
  public $jsPath;
  public $jsScript;
  public $jsEditorScript;
  public $jsPublicUrlDev;
  public $jsPublicPathDev;
  public $jsPublicUrlDist;
  public $jsPublicPathDist;
  public $jsEditorUrlDev;
  public $jsEditorPathDev;
  public $jsEditorUrlDist;
  public $jsEditorPathDist;
  public $imgUrl;
  public $fontsUrl;


  public function __construct( $plugin ){
    $this->plugin = $plugin;

    $this->setAssetLocations();
    $this->init();

  }

  /**
   * Sets the file location class properties for the site's js/css assets
   */
  private function setAssetLocations(){

    $this->assetsUrl = $this->plugin->config->pluginUrl() . 'assets/assets';
    $this->assetsPath = $this->plugin->config->pluginPath() . 'assets/assets';
    $this->filePrefix = 'ucdlib-datalab';

    $this->cssUrl = $this->assetsUrl . '/css';
    $this->cssPath = $this->assetsPath . '/css';
    $this->cssUrlDev = $this->cssUrl . '/' . $this->filePrefix . '-dev.css';
    $this->cssPathDev = $this->cssPath . '/' . $this->filePrefix . '-dev.css';
    $this->cssUrlDist = $this->cssUrl . '/' . $this->filePrefix . '-min.css';
    $this->cssPathDist = $this->cssPath . '/' . $this->filePrefix . '-min.css';

    $this->jsUrl = $this->assetsUrl . '/js';
    $this->jsPath = $this->assetsPath . '/js';
    $this->jsScript = $this->filePrefix . '.js';
    $this->jsEditorScript = $this->filePrefix . '-editor.js';
    $this->jsPublicUrlDev = $this->jsUrl . '/public-dev/' . $this->jsScript;
    $this->jsPublicPathDev = $this->jsPath . '/public-dev/' . $this->jsScript;
    $this->jsPublicUrlDist = $this->jsUrl . '/public-dist/' . $this->jsScript;
    $this->jsPublicPathDist = $this->jsPath . '/public-dist/' . $this->jsScript;
    $this->jsEditorUrlDev = $this->jsUrl . '/editor-dev/' . $this->jsEditorScript;
    $this->jsEditorPathDev = $this->jsPath . '/editor-dev/' . $this->jsEditorScript;
    $this->jsEditorUrlDist = $this->jsUrl . '/editor-dist/' . $this->jsEditorScript;
    $this->jsEditorPathDist = $this->jsPath . '/editor-dist/' . $this->jsEditorScript;

    $this->imgUrl = $this->assetsUrl . '/images';
    $this->fontsUrl = $this->assetsUrl . '/fonts';

  }

  // sets hooks and filters
  public function init(){
    $this->removeThemeScripts();
    add_action( 'wp_enqueue_scripts', [$this, 'enqueuePublicScripts'] );
    add_action( 'after_setup_theme', [$this, 'enqueueEditorCss'] );
    add_action( 'enqueue_block_editor_assets', function(){
      wp_deregister_script('ucd-components');
    }, 1000);
    add_action('admin_enqueue_scripts', [$this, 'enqueuePublicScriptsInAdmin']);
    add_action('enqueue_block_editor_assets', [$this, 'enqueueEditorScripts'], 3);
    add_filter('ucd-theme/site/block-settings', [$this, 'modifyDefaultPostImgUrl'], 10, 1);
  }

  /**
   * Change where page cards/blocks look for their default image
   */
  public function modifyDefaultPostImgUrl($settings){
    $settings['imgBase'] = $this->imgUrl . '/block-defaults/';
    return $settings;
  }

  /**
   * Register and load public js bundle in certain admin screens
   * We code split the bundle so we only load what is needed
   */
  public function enqueuePublicScriptsInAdmin($hook){
    if ( strpos($hook, $this->plugin->config->slug) === false ) return;
    $this->enqueuePublicScripts(true);
    $this->enqueueFonts();
  }

  /**
   * Enqueue stylesheet that just loads the fonts
   * Need to add the font loading divs to the footer because of custom elements
   */
  public function enqueueFonts(){
    wp_enqueue_style(
      $this->plugin->config->slug . '-fonts',
      $this->fontsUrl . '/fonts.css',
      array(),
      $this->bundleVersion()
    );

    add_action('admin_footer', function(){
      echo '<div class="load-font-awesome">placeholder</div>';
    });
  }

  /**
   * Register and load public js/css assets
   */
  public function enqueuePublicScripts($dontLoadStyles=false){
    $slug = $this->plugin->config->slug;
    $pluginDir = $this->plugin->config->pluginUrl();
    $jsPath = $this->jsPublicUrlDist;
    $cssPath = $this->cssUrlDist;
    if ( $this->plugin->config->isDevEnv() ){
      $jsPath = $this->jsPublicUrlDev;
      $cssPath = $this->cssUrlDev;
    }

    wp_enqueue_script(
      $slug,
      $jsPath,
      array(),
      $this->bundleVersion()
    );

    if ( $dontLoadStyles ) return;
    wp_enqueue_style(
      $slug,
      $cssPath,
      array(),
      $this->bundleVersion()
    );
  }

  /**
   * Register and load public CSS on the editor
   */
  public function enqueueEditorCss(){
    remove_editor_styles();
    add_theme_support( 'editor-styles' );

    // path cant be absolute and must be relative to theme root for some reason
    if ( $this->plugin->config->isDevEnv() ){
      $path = "../../../plugins" . explode('plugins', $this->cssPathDev)[1];
      add_editor_style( $path );
    } else {
      $path = "../../../plugins" . explode('plugins', $this->cssPathDist)[1];
      add_editor_style( $path );
    }
  }

  /**
   * Register and load editor JS bundle
   */
  public function enqueueEditorScripts(){
    $adminScreens = array( 'customize');
    if ( in_array( get_current_screen()->id, $adminScreens ) ) return;

    $slug = $this->filePrefix . '-editor';

    add_filter( 'ucd-theme/assets/editor-settings-slug', function(){
      return $this->filePrefix . '-editor';
    } );

    $url = $this->jsEditorUrlDist;
    if ( $this->plugin->config->isDevEnv() ){
      $url = $this->jsEditorUrlDev;
    }
    wp_enqueue_script(
      $slug,
      $url,
      array(),
      $this->bundleVersion(),
      true
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
