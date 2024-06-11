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
      'themeFilters' => array_values($model::getAllThemes(true)),
      'approachFilters' => array_values($model::getAllApproaches(true)),
      'statusFilters' => $model::getStatusOptions(),
      'restNamespace' => 'ucdlib-datalab/project',
    ];

    if (isset($GLOBALS['UcdSite'])){
      $props['defaultImage'] = $GLOBALS['UcdSite']->customBlocks->getImageByAspectRatio('4x3');
    }

    $attrs['eleProps'] = $props;
    return $attrs;
  }

  /**
   * Construct json object to be passed to hackathon search element for initial state
   */
  public static function getHackathonsElementProps($attrs=array()){
    $model = UcdlibDatalabHackathonsModel::class;
    $props = [
      'typeFilters' => array_values($model::getAllTypes(true)),
      'yearFilters' => array_values($model::getAllStartYears()),
      'restNamespace' => 'ucdlib-datalab/hackathon',
    ];

    if (isset($GLOBALS['UcdSite'])){
      $props['defaultImage'] = $GLOBALS['UcdSite']->customBlocks->getImageByAspectRatio('1x1');
    }

    $attrs['eleProps'] = $props;
    return $attrs;
  }

  public static function getCurrentHackathons($attrs=[]){
    $model = UcdlibDatalabHackathonsModel::class;
    $posts = [];
    foreach ($model::getCurrentHackathons() as $post) {
      $posts[] = $post;
    }
    $attrs['posts'] = array_chunk($posts, 2);
    return $attrs;
  }

  public static function getHackathonContact($attrs=[]){
    $post = Timber::get_post();
    if ( ! $post ) return $attrs;
    $contact = [];
    if ( $post->hackathonHostedByExternal() ){

      if ( $post->hackathonContactUrl() ){
        $contact[] = [
          'type' => 'url',
          'value' => $post->hackathonContactUrl(),
          'icon' => 'ucd-public:fa-network-wired'
        ];
      }

      if ( $post->hackathonContactEmail() ){
        $contact[] = [
          'type' => 'email',
          'value' => $post->hackathonContactEmail(),
          'link' => 'mailto:' . $post->hackathonContactEmail(),
          'icon' => 'ucd-public:fa-envelope'
        ];
      }

    } else {
      $contact[] = [
        'type' => 'email',
        'value' => 'datalab@ucdavis.edu',
        'link' => 'mailto:datalab@ucdavis.edu',
        'icon' => 'ucd-public:fa-envelope'
      ];
    }

    $attrs['contact'] = $contact;
    $attrs['hasContact'] = count($contact) > 0;
    $attrs['hostedExternally'] = $post->hackathonHostedByExternal();
    $attrs['icons'] = ['ucd-public:fa-network-wired', 'ucd-public:fa-envelope'];

    return $attrs;
  }
}
