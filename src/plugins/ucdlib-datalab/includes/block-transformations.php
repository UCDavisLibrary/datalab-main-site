<?php

// Contains methods that transform the attributes of a block (mostly fetching additional data)
// See 'transform' property in $registry array in UCDThemeBlocks class.
class UcdlibDatalabBlockTransformations {
  /**
   * Retrieves current post object and saves in "post" attribute
   */
  public static function getCurrentPost($attrs=array()){
    $attrs['post'] = Timber::get_post();
    return $attrs;
  }
}
