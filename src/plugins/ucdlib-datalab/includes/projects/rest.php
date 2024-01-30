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
      'args' => [
        'orderby' => [
          'type' => 'string',
          'sanitize_callback' => function ($param) {
            if (in_array($param, ['title', 'startDate'])) {
              return $param;
            }
            return 'startDate';
          },
        ],
        'page' => [
          'type' => 'integer',
          'sanitize_callback' => 'absint',
          'default' => 1
        ],
        'theme' => [
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ],
        'approach' => [
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ],
        'status' => [
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ],
      ],
      'permission_callback' => function(){return true;}
    ]);
  }

  public function projectSearchCallback($request){
    $model = UcdlibDatalabProjectsProjectModel::class;
    $query = [
      'orderby' => $request->get_param('orderby'),
      'paged' => $request->get_param('page'),
      'theme' => $request->get_param('theme'),
      'approach' => $request->get_param('approach'),
      'status' => $request->get_param('status')
    ];
    $results = $model::queryProjects($query);
    $foundPosts = $results['foundPosts'];
    $out = [
      'totalPageCt' => ceil($foundPosts / $this->projects->resultsPerPage),
      'results' => $results['results']
    ];

    return $out;
  }
}
