<?php

// rest api for hackathons
class UcdlibDatalabHackathonsRest {

  public $hackathons;
  public $routeNamespace;
  public function __construct( $hackathons ){
    $this->hackathons = $hackathons;
    $this->routeNamespace = $this->hackathons->plugin->config->slug . '/' . $this->hackathons->slugs['hackathon'];

    add_action( 'rest_api_init', [$this, 'registerRoutes'] );
  }

  public function registerRoutes(){

    // returns the hackathon metadata for a given hackathon content page (e.g. any subpage or main landing page)
    register_rest_route($this->routeNamespace, 'page/(?P<id>\d+)', array(
      'methods' => 'GET',
      'callback' => array($this, 'getPageCallback'),
      'permission_callback' => function (){return true;}
    ) );

    register_rest_route( $this->routeNamespace, '/search-past', [
      'methods' => 'GET',
      'callback' => [$this, 'pastSearchCallback'],
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
        'type' => [
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ],
        'start-year' => [
          'type' => 'string',
          'sanitize_callback' => 'sanitize_text_field'
        ]
      ],
      'permission_callback' => function(){return true;}
    ]);
  }

  public function getPageCallback($request){
    $id = $request['id'];

    $post = Timber::get_post( [
      'post_type' => $this->hackathons->slugs['hackathon'],
      'p' => $id
    ] );

    if ( !$post ) {
      return new WP_Error( 'rest_not_found', 'This hackathon page does not exist.', array( 'status' => 404 ) );
    }

    return rest_ensure_response( $post->hackathonMeta() );
  }

  public function pastSearchCallback($request){
    $model = UcdlibDatalabHackathonsModel::class;
    $query = [
      'orderby' => $request->get_param('orderby'),
      'paged' => $request->get_param('page'),
      'type' => $request->get_param('type'),
      'start-year' => $request->get_param('start-year')
    ];

    $results = $model::getPastHackathons($query);

    $foundPosts = $results['foundPosts'];
    $out = [
      'totalPageCt' => ceil($foundPosts / $this->hackathons->resultsPerPage),
      'results' => $results['results']
    ];

    // if orderby is startDate, aggregate results by year
    if ( $query['orderby'] == 'startDate' || !$query['orderby'] ){
      $resultsByYear = [];
      foreach($out['results'] as $result){
        $year = date('Y', strtotime($result['hackathonStartDate']));
        if ( !isset($resultsByYear[$year]) ){
          $resultsByYear[$year] = [];
        }
        $resultsByYear[$year][] = $result;
      }
      $results = [];
      foreach($resultsByYear as $year => $hackathons){
        $results[] = [
          'year' => $year,
          'results' => $hackathons
        ];
      }
      usort($results, function($a, $b){
        return $b['year'] - $a['year'];
      });

      $out['results'] = $results;

    }

    return rest_ensure_response($out);
  }

}
