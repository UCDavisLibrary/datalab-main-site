<?php

// partner taxonomy for projects
class UcdlibDatalabProjectsProjectPartner {
  public $projects;

  public function __construct( $projects ){
    $this->projects = $projects;
    add_action('init', [$this, 'register']);
  }

  public function register(){

    $args = [
      'labels' => $this->getLabels(),
      'public' => false,
      'publicly_queryable' => false,
      'hierarchical' => false,
      'show_ui' => true,
      'show_in_menu' => true,
      'show_in_nav_menus' => false,
      'show_in_rest' => true,
      'show_admin_column' => true,
    ];

    register_taxonomy(
      $this->projects->slugs['taxonomies']['partner'],
      $this->projects->slugs['project'],
      $args
    );

  }

  public function getLabels(){
    return [
      'name' => 'Partners',
      'singular_name' => 'Project Partner',
      'search_items' => 'Search Project Partners',
      'all_items' => 'All Project Partners',
      'edit_item' => 'Edit Project Partner',
      'update_item' => 'Update Project Partner',
      'add_new_item' => 'Add New Project Partner',
      'new_item_name' => 'New Project Partner Name',
      'menu_name' => 'Partners',
    ];
  }

}
