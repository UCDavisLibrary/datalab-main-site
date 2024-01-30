<?php
require_once( __DIR__ . '/block-transformations.php' );
require_once( get_template_directory() . '/includes/classes/block-renderer.php' );

// Set up server-side rendering for custom blocks
class UcdlibDatalabBlocks extends UCDThemeBlockRenderer {

  public $plugin;

  public function __construct($plugin){
    parent::__construct();
    $this->plugin = $plugin;

    add_action('block_categories_all', array($this, 'addCategories'), 10,2);
    add_action( 'init', array( $this, 'register_blocks'));

  }
  public static $transformationClass = 'UcdlibDatalabBlockTransformations';

  public static $registry = [
    'ucdlib-datalab/jobs-board' => [
      'twig' => '@ucdlib-datalab/blocks/jobs-board.twig'
    ],
    'ucdlib-datalab/project-partners' => [
      'twig' => '@ucdlib-datalab/blocks/project-partners.twig',
      'transform' => ['getCurrentPost']
    ]
  ];

  /**
   * Custom block categories
   */
  public function addCategories($block_categories, $editor_context){
    $customCategories = array(
      [
        'slug'  => $this->plugin->config->slug,
        'title' => 'Datalab',
        'icon'  => null,
      ]
    );

    return array_merge($block_categories, $customCategories);
  }


}
