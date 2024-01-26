<?php

// theme taxonomy for projects
class UcdlibDatalabProjectsProjectTheme {
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

      // not actually hierarchical, but want the editor interface and meta_box_cb doesnt work
      // https://github.com/WordPress/gutenberg/issues/13816#issuecomment-532885577
      'hierarchical' => true,

      'show_ui' => true,
      'show_in_menu' => true,
      'show_in_nav_menus' => false,
      'show_in_rest' => true,
      'show_admin_column' => true,
    ];

    register_taxonomy(
      $this->projects->slugs['taxonomies']['theme'],
      $this->projects->slugs['project'],
      $args
    );

  }

  public function getLabels(){
    return [
      'name' => 'Themes',
      'singular_name' => 'Project Theme',
      'search_items' => 'Search Project Themes',
      'all_items' => 'All Project Themes',
      'edit_item' => 'Edit Project Theme',
      'update_item' => 'Update Project Theme',
      'add_new_item' => 'Add New Project Theme',
      'new_item_name' => 'New Project Theme Name',
      'menu_name' => 'Themes'
    ];
  }

}
