<?php

/**
 * For integrating with Google services
 */
class UcdlibDatalabGoogle {

  public $plugin;

  public function __construct( $plugin ){
    $this->plugin = $plugin;

    $this->init();
  }

  public function init(){
    add_filter( 'timber/context', [$this, 'addAnalytics'] );
  }

  public function addAnalytics($context){
    $twigLocation = '@' . $this->plugin->config->slug . '/partials/gtag.twig';
    $context['twigHooks']['base']['postHead'][] = $twigLocation;
    return $context;
  }

}
