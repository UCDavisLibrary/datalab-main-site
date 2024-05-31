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
      'callback' => array($this, 'epcb_page'),
      'permission_callback' => function (){return true;}
    ) );
  }

  public function epcb_page($request){
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
}
