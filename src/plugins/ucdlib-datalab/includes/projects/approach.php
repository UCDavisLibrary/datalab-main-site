<?php

// approach taxonomy for projects
class UcdlibDatalabProjectsProjectApproach {
  public $projects;

  public function __construct( $projects ){
    $this->projects = $projects;
    add_action('init', [$this, 'register']);
    add_action(
      'edited_' . $this->projects->slugs['taxonomies']['approach'],
      [$this, 'clearProjectsLandingPageCache']
    );
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
      $this->projects->slugs['taxonomies']['approach'],
      $this->projects->slugs['project'],
      $args
    );

  }

  public function getLabels(){
    return [
      'name' => 'Approaches',
      'singular_name' => 'Project Approach',
      'search_items' => 'Search Project Approaches',
      'all_items' => 'All Project Approaches',
      'edit_item' => 'Edit Project Approach',
      'update_item' => 'Update Project Approach',
      'add_new_item' => 'Add New Project Approach',
      'new_item_name' => 'New Project Approach Name',
      'menu_name' => 'Approaches',
    ];
  }

  // the term filters for this taxonomy are rendered server side on the projects landing page
  public function clearProjectsLandingPageCache(){
    if ( !$this->projects->plugin->utils->isPluginActive('wp-hummingbird') ) return;
    do_action( 'wphb_clear_page_cache', $this->projects->projectsPostId );
  }

}
