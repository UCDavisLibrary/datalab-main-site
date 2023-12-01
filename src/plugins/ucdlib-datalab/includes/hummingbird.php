<?php

/**
 * Customizations to the third-party "Hummingbird" performance/caching plugin
 */
class UcdlibDatalabHummingbird {

  public function __construct( $plugin ){
    $this->plugin = $plugin;

    $this->init();
  }

  public function init(){
    if ( !$this->plugin->utils->isPluginActive('wp-hummingbird') ) return;

    add_action( 'save_post', [$this, 'clearQueryBlocksCacheOnPostUpdate'], 10,3 );
  }


  // list of custom query blocks used by theme/plugin
  public static $queryBlocks = [
    [
      'id' => 'ucd-theme/recent-posts',
      'hostPostType' => 'any',
      'queriedPostType' => ['post']
    ],
    [
      'id' => 'ucd-theme/query',
      'hostPostType' => 'any',
      'queriedPostType' => 'any'
    ]
  ];

  /**
   * Runs everytime a post is created/updated.
   * Clears the cache of other pages that could potentially reference updated post in a query block
   */
  public function clearQueryBlocksCacheOnPostUpdate( $post_id, $post, $update ){
    if ( wp_is_post_revision( $post_id ) ) {
      return;
    }

    $postIds = [];
    foreach (self::$queryBlocks as $queryBlock) {
      $postTypes = $queryBlock['queriedPostType'];
      if (
        $postTypes == 'any' ||
        ( is_array($postTypes) && in_array($post->post_type, $postTypes) ) ||
        $post->post_type == $postTypes
      ) {
        $postsWithBlock = $this->getPageIdsWithBlocks($queryBlock['id'], $queryBlock['hostPostType']);
        $postIds = array_merge($postIds, $postsWithBlock);
      }
    }

    foreach ( array_unique($postIds) as $postId ) {
      do_action( 'wphb_clear_page_cache', $postId );
    }
  }


  /**
   * Returns list of post ids that use a type of block
   */
  public function getPageIdsWithBlocks($block, $post_type='any'){
    $query = [
      's' => '<!-- wp:' . $block,
      'sentence'  => 1,
      'post_type' => $post_type,
      'nopaging' => true,
      'fields' => 'ids'
    ];
    return Timber::get_posts($query)->to_array();
  }

}
