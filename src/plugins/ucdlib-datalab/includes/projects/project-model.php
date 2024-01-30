<?php

require_once( get_template_directory() . "/includes/classes/post.php");

class UcdlibDatalabProjectsProjectModel extends UcdThemePost {

  protected $themes;
  public function themes($brief=false){
    if ( isset($this->themes) ){
      if ($brief){
        return array_map([$this, 'getBriefTaxItem'], $this->themes);
      }
      return $this->themes;
    }
    $projects = new UcdlibDatalabProjects(null, false);
    $this->themes = $this->terms(['taxonomy' => $projects->slugs['taxonomies']['theme']]);
    if ($brief){
      return array_map([$this, 'getBriefTaxItem'], $this->themes);
    }
    return $this->themes;
  }

  protected $approaches;
  public function approaches($brief=false){
    if ( isset($this->approaches) ){
      if ($brief){
        return array_map([$this, 'getBriefTaxItem'], $this->approaches);
      }
      return $this->approaches;
    }
    $projects = new UcdlibDatalabProjects(null, false);
    $this->approaches = $this->terms(['taxonomy' => $projects->slugs['taxonomies']['approach']]);
    if ($brief){
      return array_map([$this, 'getBriefTaxItem'], $this->approaches);
    }
    return $this->approaches;
  }

  protected $partners;
  public function partners($brief=false){
    if ( isset($this->partners) ){
      if ($brief){
        return array_map([$this, 'getBriefTaxItem'], $this->partners);
      }
      return $this->partners;
    }
    $projects = new UcdlibDatalabProjects(null, false);
    $this->partners = $this->terms(['taxonomy' => $projects->slugs['taxonomies']['partner']]);
    if ($brief){
      return array_map([$this, 'getBriefTaxItem'], $this->partners);
    }
    return $this->partners;
  }

  private function getBriefTaxItem($item){
    return [
      'id' => $item->term_id,
      'name' => $item->name,
      'slug' => $item->slug
    ];
  }

  protected $projectStatusObject;
  public function projectStatusObject(){
    if ( isset($this->projectStatus) ){
      return $this->projectStatus;
    }
    $projects = new UcdlibDatalabProjects(null, false);
    $value = $this->meta($projects->slugs['meta']['status']);
    if ( empty($value ) ) $value = 'active';
    $this->projectStatusObject = [
      'value' => $value,
      'label' => ucfirst($value)
    ];

    if ( $value == 'complete' ){
      $endDate = $this->meta($projects->slugs['meta']['endDate']);
      if ( !empty($endDate) && strlen($endDate) >= 4 ){
        $this->projectStatusObject['endYear'] = substr($endDate, 0, 4);
      }
    }
    return $this->projectStatusObject;
  }

  public static function queryProjects($kwargs=[]){
    $projects = new UcdlibDatalabProjects(null, false);
    $q = [
      'post_type' => $projects->slugs['project'],
      'posts_per_page' => $projects->resultsPerPage,
      'paged' => 1,
      'orderby' => 'meta_value',
      'order' => 'DESC',
      'meta_query' => [],
      'tax_query' => [],
      'meta_key' => $projects->slugs['meta']['startDate'],
      'meta_type' => 'DATE'
    ];

    if (isset($kwargs['paged'])){
      $q['paged'] = $kwargs['paged'];
    }

    if ( isset($kwargs['orderby']) && $kwargs['orderby'] == 'title' ){
      $q['orderby'] = 'title';
      $q['order'] = 'ASC';
    }

    if ( !empty($kwargs['theme']) ){
      $q['tax_query'][] = [
        'taxonomy' => $projects->slugs['taxonomies']['theme'],
        'field' => 'slug',
        'terms' => $kwargs['theme']
      ];
    }

    if ( !empty($kwargs['approach']) ){
      $q['tax_query'][] = [
        'taxonomy' => $projects->slugs['taxonomies']['approach'],
        'field' => 'slug',
        'terms' => $kwargs['approach']
      ];
    }

    if ( !empty($kwargs['status']) ){
      $q['meta_query'][] = [
        'key' => $projects->slugs['meta']['status'],
        'value' => $kwargs['status'],
        'compare' => '='
      ];
      if ( $kwargs['status'] == 'active' ){
        $q['meta_query']['relation'] = 'OR';
        $q['meta_query'][] = [
          'key' => $projects->slugs['meta']['status'],
          'compare' => 'NOT EXISTS'
        ];
      }
    }

    $results = [];
    $posts = Timber::get_posts($q);
    foreach($posts as $post){
      $p = [];
      $p['id'] = $post->ID;
      $p['title'] = $post->title();
      $p['permalink'] = $post->link();
      //$p['excerpt'] = strval($post->excerpt(['words' => 500]));
      $p['excerpt'] = $post->post_excerpt ? $post->post_excerpt : '';

      $p['showLink'] = $post->meta($projects->slugs['meta']['showLink']) ? true : false;
      $p['status'] = $post->projectStatusObject();

      $p['themes'] = $post->themes(true);
      $p['approaches'] = $post->approaches(true);
      $p['partners'] = $post->partners(true);

      $results[] = $p;
    }

    return [
      'foundPosts' => $posts->found_posts,
      'results' => $results
    ];



  }
}
