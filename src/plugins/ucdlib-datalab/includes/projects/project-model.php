<?php

require_once( get_template_directory() . "/includes/classes/post.php");

class UcdlibDatalabProjectsProjectModel extends UcdThemePost {

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

    $results = [];
    $posts = Timber::get_posts($q);
    foreach($posts as $post){
      $p = [];
      $p['id'] = $post->ID;
      $p['title'] = $post->title();
      $p['permalink'] = $post->link();
      //$p['excerpt'] = strval($post->excerpt(['words' => 500]));
      $p['excerpt'] = $post->post_excerpt ? $post->post_excerpt : '';

      foreach($projects->slugs['meta'] as $k => $v){
        $p[$v] = $post->meta($v);
      }

      $results[] = $p;
    }

    return [
      'foundPosts' => $posts->found_posts,
      'results' => $results
    ];



  }
}
