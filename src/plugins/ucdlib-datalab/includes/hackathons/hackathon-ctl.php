<?php

require_once( __DIR__ . '/hackathon-model.php' );

class UcdlibDatalabHackathonsCtl {

  public $hackathons;

  public function __construct( $hackathons ){
    $this->hackathons = $hackathons;

    add_filter( 'timber/post/classmap', [$this, 'registerModel'] );
    add_action( 'init', [$this, 'register'] );
    add_action( 'init', [$this, 'registerPostMeta'] );
    add_filter( 'ucd-theme/post/breadcrumbs/custom_parent', [$this, 'setCustomParentPage'], 10, 2 );
    add_filter( 'ucd-theme/post/breadcrumbs/text', [$this, 'setCustomBreadcrumbText'], 10, 2 );
    add_filter( 'manage_' . $this->hackathons->slugs['hackathon'] . '_posts_columns', [$this, 'addAdminColumns'] );
    add_action( 'manage_' . $this->hackathons->slugs['hackathon'] . '_posts_custom_column', [$this, 'addAdminColumnContent'], 10, 2 );
    add_action( 'widgets_init', [$this, 'registerSidebar'] );
    add_filter( 'ucd-theme/templates/single', [$this, 'setTemplate'], 10, 2 );
    add_filter( 'ucd-theme/context/single', [$this, 'setContext'] );
  }


  public function register(){
    $args = [
      'labels' => $this->getLabels(),
      'description' => 'Datalab data challenges and hackathons',
      'public' => true,
      'show_in_rest' => true,
      'hierarchical' => true,
      'menu_position' => 20,
      'menu_icon' => 'dashicons-editor-code',
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
      'taxonomies' => array_values($this->hackathons->slugs['taxonomies'])
    ];

    register_post_type($this->hackathons->slugs['hackathon'], $args);
  }


  public function getLabels(){
    return [
      'name' => 'Data Challenges and Hackathons',
      'singular_name' => 'Data Challenge',
      'add_new' => 'Add New Data Challenge or Hackathon',
      'add_new_item' => 'Add New Data Challenge or Hackathon',
      'edit_item' => 'Edit Data Challenge or Hackathon',
      'new_item' => 'New Data Challenge or Hackathon',
      'all_items' => 'All Data Challenges and Hackathons',
      'view_item' => 'View Data Challenge or Hackathon',
      'search_items' => 'Search Data Challenges and Hackathons',
      'not_found' => 'No data challenges or hackathons found',
      'not_found_in_trash' => 'No data challenges or hackathons found in Trash',
      'parent_item_colon' => '',
      'menu_name' => 'Data Challenges and Hackathons'
    ];
  }

  // register custom post meta
  public function registerPostMeta(){
    $slug = $this->hackathons->slugs['hackathon'];
    $metaSlugs = $this->hackathons->slugs['meta'];

    register_post_meta(
      $slug,
      $metaSlugs['landingPageTitle'],
      [
        'show_in_rest' => true,
        'single' => true,
        'default' => 'Challenge Overview',
        'type' => 'string',
      ]
    );

    register_post_meta(
      $slug,
      $metaSlugs['excerpt'],
      [
        'show_in_rest' => true,
        'single' => true,
        'default' => '',
        'type' => 'string',
      ]
    );

    register_post_meta(
      $slug,
      $metaSlugs['showGrandchildrenInNav'],
      [
        'show_in_rest' => true,
        'single' => true,
        'default' => false,
        'type' => 'boolean',
      ]
    );

    register_post_meta(
      $slug,
      $metaSlugs['hostedByExternal'],
      [
        'show_in_rest' => true,
        'single' => true,
        'default' => false,
        'type' => 'boolean',
      ]
    );

    register_post_meta(
      $slug,
      $metaSlugs['contactEmail'],
      [
        'show_in_rest' => true,
        'single' => true,
        'default' => '',
        'type' => 'string',
      ]
    );

    register_post_meta(
      $slug,
      $metaSlugs['contactUrl'],
      [
        'show_in_rest' => true,
        'single' => true,
        'default' => '',
        'type' => 'string',
      ]
    );

    register_post_meta(
      $slug,
      $metaSlugs['startDate'],
      [
        'show_in_rest' => true,
        'single' => true
      ]
    );

    register_post_meta(
      $slug,
      $metaSlugs['endDate'],
      [
        'show_in_rest' => true,
        'single' => true
      ]
    );
  }

  // tell Timber about our post model
  public function registerModel($classmap){
    $classmap[$this->hackathons->slugs['hackathon']] = UcdlibDatalabHackathonsModel::class;
    return $classmap;
  }

  public function setCustomParentPage($value, $post){
    if ( $post->post_type == $this->hackathons->slugs['hackathon'] )
      return $this->hackathons->hackathonsMenuId;
    return $value;
  }

  public function setCustomBreadcrumbText($value, $post){
    if ( $post->post_type != $this->hackathons->slugs['hackathon'] ){
      return $value;
    }
    return $post->hackathonTitle();
  }

  // register sidebar widget area for single hackathon
  public function registerSidebar(){
    register_sidebar([
      'id'            => 'single-' . $this->hackathons->slugs['hackathon'],
      'name'          => "Single Data Challenge or Hackathon",
      'description'   => "Sidebar widgets for a single data challenge or hackathon.",
      'before_widget' => '',
      'after_widget' => ''
    ]);
  }

  public function setTemplate( $templates, $context ){
    if ( $context['post']->post_type == $this->hackathons->slugs['hackathon'] ){
      $template = '@' . $this->hackathons->plugin->timber->nameSpace . '/pages/single-' . $this->hackathons->slugs['hackathon'] . '.twig';
      array_unshift($templates, $template);
    }
    return $templates;
  }

  public function setContext($context){
    if ( $context['post']->post_type != $this->hackathons->slugs['hackathon'] ) return $context;
    $p = $context['post'];

    $context['title'] = $p->hackathonTitle();

    $context['sidebar'] = trim(Timber::get_widgets( 'single-' . $this->hackathons->slugs['hackathon'] ));
    return $context;
  }

  public function addAdminColumns($columns){
    $columns['startDate'] = 'Start Date';
    $columns['endDate'] = 'End Date';
    return $columns;
  }

  public function addAdminColumnContent($column, $post_id){
    if ( $column == 'startDate' ){
      echo get_post_meta($post_id, $this->hackathons->slugs['meta']['startDate'], true);
    }
    if ( $column == 'endDate' ){
      echo get_post_meta($post_id, $this->hackathons->slugs['meta']['endDate'], true);
    }
  }
}
