<?php

class UcdlibDatalabProjectsRest {

  public $projects;

  public $routeNamespace;

  public function __construct( $projects ){
    $this->projects = $projects;

    $this->routeNamespace = $this->projects->plugin->config->slug . '/' . $this->projects->slugs['project'];

    add_action('rest_api_init', [$this, 'registerRoutes']);
  }

  public function registerRoutes(){
    register_rest_route( $this->routeNamespace, '/search', [
      'methods' => 'GET',
      'callback' => [$this, 'projectSearchCallback'],
      'permission_callback' => function(){return true;}
    ]);
  }

  public function projectSearchCallback($request){
    $model = UcdlibDatalabProjectsProjectModel::class;
    $results = $model::queryProjects();
    $foundPosts = $results['foundPosts'];
    $out = [
      'totalPageCt' => ceil($foundPosts / $this->projects->resultsPerPage),
      'results' => $results['results']
    ];

    return $out;
  }
}
