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

  /**
   * Construct json object to be passed to projects element for initial state
   */
  public static function getProjectsElementProps($attrs=array()){
    $model = UcdlibDatalabProjectsProjectModel::class;
    $props = [
      'themeFilters' => $model::getAllThemes(true),
      'approachFilters' => $model::getAllApproaches(true),
      'statusFilters' => $model::getStatusOptions(),
      'restNamespace' => 'ucdlib-datalab/project',
    ];

    $attrs['eleProps'] = $props;
    return $attrs;
  }
}
