<?php

/**
 * Customizations to the third-party "Hummingbird" performance/caching plugin
 */
class UcdlibDatalabHummingbird {

  public $plugin;

  public function __construct( $plugin ){
    $this->plugin = $plugin;

    $this->init();
  }

  public function init(){
    if ( !$this->plugin->utils->isPluginActive('wp-hummingbird') ) return;

    add_action( 'save_post', [$this, 'clearQueryBlocksCacheOnPostUpdate'], 10, 3 );
    add_action( 'save_post_wp_block', [$this, 'clearPageCacheOnPatternUpdate'], 10, 3 );
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
   * Runs every time a post is created/updated.
   * Clears the cache of other pages that could potentially reference updated post in a query block
   * AND clears cache of pages that use patterns containing those query blocks.
   */
  public function clearQueryBlocksCacheOnPostUpdate( $post_id, $post, $update ){
    if ( wp_is_post_revision( $post_id ) ) return;
    if ( empty($post) || empty($post->post_type) ) return;

    $postIdsToClear = [];

    foreach ( self::$queryBlocks as $queryBlock ) {
      $postTypes = $queryBlock['queriedPostType'];

      $matchesQueriedType =
        $postTypes === 'any' ||
        ( is_array($postTypes) && in_array($post->post_type, $postTypes, true) ) ||
        $post->post_type === $postTypes;

      if ( !$matchesQueriedType ) continue;

      // 1) Pages/posts that directly contain the block
      $postsWithBlock = $this->getPostIdsWithBlock( $queryBlock['id'], $queryBlock['hostPostType'] );
      $postIdsToClear = array_merge( $postIdsToClear, $postsWithBlock );

      // 2) Find patterns that contain the block and clear page caches that use those patterns
      $patternIds = $this->getPatternIdsWithBlock( $queryBlock['id'] );
      foreach ( $patternIds as $patternId ) {
        $postIdsToClear = array_merge( $postIdsToClear, $this->getPostIdsUsingPattern( $patternId ) );
      }
    }

    $this->clearHummingbirdCacheForPostIds( $postIdsToClear );
  }

  /**
   * Runs when a pattern (wp_block) is created/updated.
   * Clears cache for pages that use that pattern.
   */
  public function clearPageCacheOnPatternUpdate( $post_id, $post, $update ){
    if ( wp_is_post_revision( $post_id ) ) return;
    if ( empty($post) || $post->post_type !== 'wp_block' ) return;

    $postIdsToClear = $this->getPostIdsUsingPattern( $post_id );
    $this->clearHummingbirdCacheForPostIds( $postIdsToClear );
  }

  /**
   * Clear Hummingbird page cache for a list of post IDs.
   */
  protected function clearHummingbirdCacheForPostIds( $postIds ){
    if ( empty($postIds) ) return;
    $postIds = array_values( array_unique( array_map( 'intval', (array)$postIds ) ) );
    foreach ( $postIds as $id ) {
      do_action( 'wphb_clear_page_cache', $id );
    }
  }

  /**
   * Returns list of post ids (any public post types, or a specified type)
   * that contain a given block comment, e.g. <!-- wp:ucd-theme/query
   */
  public function getPostIdsWithBlock( $block, $post_type = 'any' ){
    $needle = '<!-- wp:' . $block;
    return $this->getPostIdsWhereContentLike( $needle, $post_type );
  }

  /**
   * Returns list of pattern IDs (wp_block) that contain a given block comment.
   */
  public function getPatternIdsWithBlock( $block ){
    $needle = '<!-- wp:' . $block;
    return $this->getPostIdsWhereContentLike( $needle, 'wp_block' );
  }

  /**
   * Returns list of published post IDs that use a given pattern.
   */
  public function getPostIdsUsingPattern( $pattern_id, $post_type = 'any' ){

    $needles = [
      '<!-- wp:block {"ref":' . $pattern_id
    ];

    $ids = [];
    foreach ( $needles as $needle ) {
      $ids = array_merge( $ids, $this->getPostIdsWhereContentLike( $needle, $post_type ) );
    }

    return array_values( array_unique( array_map( 'intval', $ids ) ) );
  }

  /**
   * Returns list of published post IDs (any public post types, or a specified type)
   * where post_content contains the given needle string.
   */
  protected function getPostIdsWhereContentLike( $needle, $post_type = 'any' ){
    global $wpdb;

    $like = '%' . $wpdb->esc_like( $needle ) . '%';

    $whereType = '';
    $params = [ $like ];

    if ( $post_type !== 'any' ) {
      if ( is_array($post_type) ) {
        $placeholders = implode( ',', array_fill( 0, count($post_type), '%s' ) );
        $whereType = " AND post_type IN ($placeholders) ";
        $params = array_merge( $params, array_values($post_type) );
      } else {
        $whereType = " AND post_type = %s ";
        $params[] = $post_type;
      }
    }

    // Only published content should be page-cached.
    $sql = "
      SELECT ID
      FROM {$wpdb->posts}
      WHERE post_status = 'publish'
        {$whereType}
        AND post_content LIKE %s
    ";

    $typeParams = [];
    $likeParam  = [ $like ];

    if ( $post_type !== 'any' ) {
      $typeParams = is_array($post_type) ? array_values($post_type) : [ $post_type ];
    }

    $prepared = $wpdb->prepare( $sql, array_merge( $typeParams, $likeParam ) );
    return $wpdb->get_col( $prepared );
  }

}
