<?php

// partner taxonomy for projects
class UcdlibDatalabHackathonsType {
  public $hackathons;

  public function __construct( $hackathons ){
    $this->hackathons = $hackathons;
    add_action('init', [$this, 'register']);
    add_filter( 'rest_prepare_taxonomy', [ $this, 'hide_metabox'], 10, 3);
  }

  public function register(){

    $args = [
      'labels' => $this->getLabels(),
      'public' => false,
      'publicly_queryable' => false,
      'hierarchical' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'show_in_nav_menus' => false,
      'show_in_rest' => true,
      'show_admin_column' => true,
      'meta_box_cb' => false
    ];

    register_taxonomy(
      $this->hackathons->slugs['taxonomies']['type'],
      $this->hackathons->slugs['hackathon'],
      $args
    );

  }

  public function getLabels(){
    return [
      'name' => 'Types',
      'singular_name' => 'Type',
      'search_items' => 'Search Type',
      'all_items' => 'All Types',
      'edit_item' => 'Edit Type',
      'update_item' => 'Update Type',
      'add_new_item' => 'Add New Type',
      'new_item_name' => 'New Type Name',
      'menu_name' => 'Types'
    ];
  }

  public function hide_metabox( $response, $taxonomy, $request ){
    $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

    if( $context === 'edit'  && $taxonomy->name === $this->hackathons->slugs['taxonomies']['type']){
      $data_response = $response->get_data();
      $data_response['visibility']['show_ui'] = false;
      $response->set_data( $data_response );
    }

    return $response;

  }

}
