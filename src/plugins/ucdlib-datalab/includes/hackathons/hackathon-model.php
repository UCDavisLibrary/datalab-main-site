<?php

require_once( get_template_directory() . "/includes/classes/post.php");

class UcdlibDatalabHackathonsModel extends UcdThemePost {

  // override children method to only return hackathon pages
  // otherwise we get images in our nav
  protected $children;
  public function children($post_type = 'any'){
    if ( ! empty( $this->children ) ) {
      return $this->children;
    }
    $mainClass = new UcdlibDatalabHackathons(null, false);
    $this->children = parent::children($mainClass->slugs['hackathon']);
    return $this->children;
  }

  // returns top-level hackathon page
  protected $landingPage;
  public function landingPage(){
    if ( ! empty( $this->landingPage ) ) {
      return $this->landingPage;
    }
    $ancestors = $this->ancestors();
    if (count( $ancestors )) {
      $this->landingPage = end($ancestors);
    } else {
      $this->landingPage = $this;
    }
    return $this->landingPage;
  }

  protected $isDeepPage;
  public function isDeepPage(){
    return count( $this->ancestors) >=2 ;
  }

  public function landingPageUrl(){
    return $this->landingPage()->link();
  }

  public function landingPageId(){
    return $this->landingPage()->id;
  }

  public function landingPageTitle(){
    return $this->landingPage()->meta('hackathonLandingPageTitle');
  }

  protected $isLandingPage;
  public function isLandingPage() {
    if ( ! empty( $this->isLandingPage ) ) {
      return $this->isLandingPage;
    }
    $landingPage = $this->landingPage();
    $this->isLandingPage = $landingPage->id == $this->id ? true : false;
    return $this->isLandingPage;
  }

  protected $hackathonIsHierarchical;
  public function hackathonIsHierarchical(){
    if ( ! empty( $this->hackathonIsHierarchical ) ) {
      return $this->hackathonIsHierarchical;
    }
    $landingPage = $this->landingPage();
    $this->hackathonIsHierarchical = count( $landingPage->children() ) ? true : false;
    return $this->hackathonIsHierarchical;
  }

  protected $pageTitle;
  public function pageTitle(){
    if ( ! empty( $this->pageTitle ) ) {
      return $this->pageTitle;
    }

    if ( !$this->isLandingPage() ){
      $this->pageTitle = $this->title();
      return $this->pageTitle;
    }
    $customLandingTitle = $this->meta('hackathonLandingPageTitle');
    if ( ! empty( $customLandingTitle ) ) {
      $this->pageTitle = $customLandingTitle;
      return $this->pageTitle;
    }
    $this->pageTitle = 'Challenge Overview';
    return $this->pageTitle;
  }

  protected $hackathonTitle;
  public function hackathonTitle(){
    if ( ! empty( $this->hackathonTitle ) ) {
      return $this->hackathonTitle;
    }
    $ancestor = $this->landingPage();
    $this->hackathonTitle = $this->landingPage()->title();
    return $this->hackathonTitle;
  }

  protected $hackathonExcerpt;
  public function hackathonExcerpt(){
    if ( ! empty( $this->hackathonExcerpt ) ) {
      return $this->hackathonExcerpt;
    }
    $this->hackathonExcerpt = $this->landingPage()->meta('hackathonExcerpt');
    return $this->hackathonExcerpt;
  }

  protected $hackathonStartDate;
  public function hackathonStartDate(){
    if ( ! empty( $this->hackathonStartDate ) ) {
      return $this->hackathonStartDate;
    }
    $this->hackathonStartDate = $this->landingPage()->meta('hackathonStartDate');
    return $this->hackathonStartDate;
  }

  protected $hackathonEndDate;
  public function hackathonEndDate(){
    if ( ! empty( $this->hackathonEndDate ) ) {
      return $this->hackathonEndDate;
    }
    $this->hackathonEndDate = $this->landingPage()->meta('hackathonEndDate');
    return $this->hackathonEndDate;
  }

  protected $hackathonHostedByExternal;
  public function hackathonHostedByExternal(){
    if ( ! empty( $this->hackathonHostedByExternal ) ) {
      return $this->hackathonHostedByExternal;
    }
    $this->hackathonHostedByExternal = $this->landingPage()->meta('hackathonHostedByExternal');
    return $this->hackathonHostedByExternal;
  }

  protected $hackathonContactEmail;
  public function hackathonContactEmail(){
    if ( ! empty( $this->hackathonContactEmail ) ) {
      return $this->hackathonContactEmail;
    }
    $this->hackathonContactEmail = $this->landingPage()->meta('hackathonContactEmail');
    return $this->hackathonContactEmail;
  }

  protected $hackathonContactUrl;
  public function hackathonContactUrl(){
    if ( ! empty( $this->hackathonContactUrl ) ) {
      return $this->hackathonContactUrl;
    }
    $this->hackathonContactUrl = $this->landingPage()->meta('hackathonContactUrl');
    return $this->hackathonContactUrl;
  }

  protected $hackathonTypes;
  public function hackathonTypes($brief=false){
    if ( ! empty( $this->hackathonTypes ) ) {
      $hackathonTypes = $this->hackathonTypes;
    }
    $hackathonTypes = $this->landingPage()->terms(['taxonomy' => 'hackathon-type']);
    if ($brief){
      $out = [];
      foreach ($hackathonTypes as $term) {
        $out[] = [
          'id' => $term->term_id,
          'name' => $term->name,
          'slug' => $term->slug
        ];
      }
      return $out;
    }
    $this->hackathonTypes = $hackathonTypes;
    return $this->hackathonTypes;
  }

  public static function getAllTypes($brief=false){
    $mainClass = new UcdlibDatalabHackathons(null, false);
    $terms = Timber::get_terms([
      'taxonomy' => $mainClass->slugs['taxonomies']['type'],
      'hide_empty' => true
    ]);
    if ($brief){
      $out = [];
      foreach ($terms as $term) {
        $out[] = [
          'id' => $term->term_id,
          'name' => $term->name,
          'slug' => $term->slug
        ];
      }
      return $out;
    }
    return $terms;
  }

  /**
   * Gets unique list of years for the hackathon post type using hackathonStartDate meta value
   */
  public static function getAllStartYears(){
    global $wpdb;
    $query = "SELECT DISTINCT YEAR(meta_value) as year FROM $wpdb->postmeta WHERE meta_key = 'hackathonStartDate' ORDER BY year DESC";
    $results = $wpdb->get_results($query);
    $years = [];
    foreach ($results as $result) {
      $years[] = $result->year;
    }
    rsort($years);
    return $years;
  }

  public function hackathonDateRange(){
    $out = '';
    if ( $this->hackathonStartDate() || $this->hackathonEndDate() ){
      $dateFormat = get_option( 'date_format' );
      $startTimestamp = strtotime($this->hackathonStartDate());
      $out = wp_date($dateFormat, $startTimestamp, new DateTimeZone("UTC"));
      if ( $this->hackathonEndDate() && $this->hackathonEndDate() != $this->hackathonStartDate() ){
        $endTimestamp = strtotime($this->hackathonEndDate());
        $out .= ' - ' . wp_date($dateFormat, $endTimestamp, new DateTimeZone("UTC"));
      }
    }
    return $out;
  }

  // return basic metadata for a hackathon
  public function hackathonMeta(){
    return [
      'hackathonLandingPageTitle' => html_entity_decode($this->landingPageTitle()),
      'hackathonLandingPageUrl' => $this->landingPageUrl(),
      'hackathonLandingPageId' => $this->landingPageId(),
      'hackathonTitle' => html_entity_decode($this->hackathonTitle()),
      'hackathonStartDate' => $this->hackathonStartDate(),
      'hackathonEndDate' => $this->hackathonEndDate(),
      'hackathonExcerpt' => html_entity_decode($this->hackathonExcerpt()),
      'hackathonHostedByExternal' => $this->hackathonHostedByExternal(),
      'hackathonContactEmail' => $this->hackathonContactEmail(),
      'hackathonContactUrl' => $this->hackathonContactUrl(),
      'hackathonTypes' => $this->hackathonTypes(true),
      'showGrandchildrenInNav' => $this->showGrandchildrenInNav(),
      'hackathonTeaserImage' => $this->teaser_image() ? $this->teaser_image()->src() : null,
      'hackathonTeaserImageId' => $this->teaser_image() ? $this->teaser_image()->id : 0,
      'hackathonCardImage' => $this->card_image() ? $this->card_image()->src() : null,
      'hackathonCardImageId' => $this->card_image() ? $this->card_image()->id : 0
    ];
  }

  protected $showGrandchildrenInNav;
  public function showGrandchildrenInNav(){
    if ( ! empty( $this->showGrandchildrenInNav ) ) {
      return $this->showGrandchildrenInNav;
    }
    $this->showGrandchildrenInNav = $this->landingPage()->meta('showGrandchildrenInNav');
    return $this->showGrandchildrenInNav;
  }

  protected $siblings;
  public function siblings(){
    if ( ! empty( $this->siblings ) ) {
      return $this->siblings;
    }
    $this->siblings = [];
    if ( $this->parent() ){
      $siblings = [];
      foreach ($this->parent()->children() as $s) {
        $siblings[] = $s;
      }
      $this->siblings = $siblings;
    }
    return $this->siblings;
  }

  protected $nextPage;
  public function nextPage(){
    if ( ! empty( $this->nextPage ) ) {
      return $this->nextPage;
    }

    $this->nextPage = null;
    $children = $this->children();
    $parent = $this->parent();

    if ( ($this->showGrandchildrenInNav() || $this->isLandingPage()) && count($children) ){
      foreach ($children as $child) {
        $this->nextPage = $child;
        break;
      }
    } elseif ( count($this->siblings()) ) {
      $is_last = true;
      $found_self = false;
      foreach ($this->siblings() as $sibling) {
        if ( $found_self ) {
          $this->nextPage = $sibling;
          $is_last = false;
          break;
        }
        if ( $sibling->id == $this->id ) $found_self = true;
      }
      if ( $is_last && $parent && count($parent->siblings())) {
        $found_parent = false;
        foreach ($parent->siblings() as $sibling) {
          if ( $found_parent ) {
            $this->nextPage = $sibling;
            break;
          }
          if ( $sibling->id == $parent->id ) $found_parent = true;
        }
      }
    }
    return $this->nextPage;
  }

  protected $prevPage;
  public function prevPage(){
    if ( ! empty( $this->prevPage ) ) {
      return $this->prevPage;
    }
    $this->prevPage = null;
    $found_self = false;
    foreach (array_reverse($this->siblings()) as $sibling) {
      if ( $found_self ) {
        $this->prevPage = $sibling;
        break;
      }
      if ( $sibling->id == $this->id ) $found_self = true;
    }
    if ( $this->showGrandchildrenInNav() && $this->prevPage && count($this->prevPage->children()) ){
      $parentChildren = (array)$this->prevPage->children();
      $this->prevPage = end($parentChildren);
    }
    else if ( !$this->prevPage && $this->parent() ) {
      $this->prevPage = $this->parent();
    }

    return $this->prevPage;
  }

  // retrieves all current and future hackathons based on current date
  public static function getCurrentHackathons(){
    $mainClass = new UcdlibDatalabHackathons(null, false);
    $today = date('Y-m-d');
    $q = [
      'post_type' => $mainClass->slugs['hackathon'],
      'posts_per_page' => -1,
      'post_parent' => 0,
      'orderby' => 'meta_value',
      'order' => 'ASC',
      'meta_query' => [
        [
          'key' => $mainClass->slugs['meta']['endDate'],
          'value' => $today,
          'compare' => '>=',
          'type' => 'DATE'
        ]
      ],
      'meta_key' => $mainClass->slugs['meta']['startDate'],
      'meta_type' => 'DATE'
    ];

    return Timber::get_posts($q);

  }

  public static function getPastHackathons($kwargs){
    $mainClass = new UcdlibDatalabHackathons(null, false);
    $q = [
      'post_type' => $mainClass->slugs['hackathon'],
      'posts_per_page' => $mainClass->resultsPerPage,
      'post_parent' => 0,
      'paged' => 1,
      'orderby' => 'meta_value',
      'order' => 'DESC',
      'meta_query' => [
        [
          'key' => $mainClass->slugs['meta']['endDate'],
          'value' => date('Y-m-d'),
          'compare' => '<',
          'type' => 'DATE'
        ]
      ],
      'tax_query' => [],
      'meta_key' => $mainClass->slugs['meta']['startDate'],
      'meta_type' => 'DATE'
    ];

    if (isset($kwargs['paged'])){
      $q['paged'] = $kwargs['paged'];
    }

    if ( isset($kwargs['orderby']) && $kwargs['orderby'] == 'title' ){
      $q['orderby'] = 'title';
      $q['order'] = 'ASC';
    }

    if ( !empty($kwargs['type']) ){
      $q['tax_query'][] = [
        'taxonomy' => $mainClass->slugs['taxonomies']['type'],
        'field' => 'slug',
        'terms' => $kwargs['type']
      ];
    }

    if (!empty($kwargs['start-year'])){
      $q['meta_query'][] = [
        'key' => $mainClass->slugs['meta']['startDate'],
        'value' => $kwargs['start-year'],
        'compare' => 'LIKE'
      ];
    }

    $results = [];
    $posts = Timber::get_posts($q);
    $foundPosts = $posts->found_posts;
    foreach ($posts as $post) {
      $results[] = $post->hackathonMeta();
    }

    return [
      'foundPosts' => $foundPosts,
      'results' => $results
    ];
  }



}
