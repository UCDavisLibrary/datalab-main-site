<?php

require_once( __DIR__ . '/project-model.php' );

// controller for the project post type
class UcdlibDatalabProjectsProjectCtl {
  public $projects;

  public function __construct( $projects ){
    $this->projects = $projects;

    add_filter( 'timber/post/classmap', [$this, 'registerModel'] );
    add_action( 'init', [$this, 'register'] );
    add_action( 'init', [$this, 'registerPostMeta'] );
    add_filter( 'manage_' . $this->projects->slugs['project'] . '_posts_columns', [$this, 'addStatusAdminColumn'] );
    add_action( 'manage_' . $this->projects->slugs['project'] . '_posts_custom_column', [$this, 'addStatusAdminColumnContent'], 10, 2 );
  }

  public function register(){
    $args = [
      'labels' => $this->getLabels(),
      'description' => 'Datalab projects',
      'public' => true,
      'show_in_rest' => true,
      'menu_position' => 20,
      'menu_icon' => 'dashicons-portfolio',
      'rewrite' => [
        'with_front' => false,
      ],
      'supports' => array(
        'title',
        'editor',
        'thumbnail',
        'excerpt',
        'revisions',
        'page-attributes',
        'custom-fields'
      ),
      'taxonomies' => array_values($this->projects->slugs['taxonomies'])
    ];

    register_post_type($this->projects->slugs['project'], $args);
  }

  public function getLabels(){
    return [
      'name' => 'Projects',
      'singular_name' => 'Project',
      'add_new' => 'Add New Project',
      'add_new_item' => 'Add New Project',
      'edit_item' => 'Edit Project',
      'new_item' => 'New Project',
      'all_items' => 'All Projects',
      'view_item' => 'View Project',
      'search_items' => 'Search Projects',
      'not_found' => 'No projects found',
      'not_found_in_trash' => 'No projects found in Trash',
      'parent_item_colon' => '',
      'menu_name' => 'Projects'
    ];
  }

  // register custom post meta
  public function registerPostMeta(){

    register_post_meta(
      $this->projects->slugs['project'],
      $this->projects->slugs['meta']['status'],
      [
        'show_in_rest' => true,
        'single' => true,
        'default' => 'active',
        'type' => 'string',
      ]
    );
    register_post_meta(
      $this->projects->slugs['project'],
      $this->projects->slugs['meta']['startDate'],
      [
        'show_in_rest' => true,
        'single' => true
      ]
    );
    register_post_meta(
      $this->projects->slugs['project'],
      $this->projects->slugs['meta']['endDate'],
      [
        'show_in_rest' => true,
        'single' => true
      ]
    );
    register_post_meta(
      $this->projects->slugs['project'],
      $this->projects->slugs['meta']['showLink'],
      [
        'show_in_rest' => true,
        'single' => true,
        'default' => false,
        'type' => 'boolean',
      ]
    );
  }

  // tell Timber about our post model
  public function registerModel($classmap){
    $classmap[$this->projects->slugs['project']] = UcdlibDatalabProjectsProjectModel::class;
    return $classmap;
  }

  public function addStatusAdminColumn($columns){
    $columns['projectStatus'] = 'Status';
    return $columns;
  }

  public function addStatusAdminColumnContent($column, $post_id){
    if ($column == 'projectStatus'){
      $status = get_post_meta($post_id, 'projectStatus', true);
      $status = ucfirst($status);
      echo $status;
    }
  }

}
