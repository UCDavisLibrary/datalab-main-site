<?php

require_once( get_template_directory() . "/includes/classes/post.php");

class UcdlibDatalabHackathonsModel extends UcdThemePost {

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
  public function hackathonTypes(){
    if ( ! empty( $this->hackathonTypes ) ) {
      return $this->hackathonTypes;
    }
    $this->hackathonTypes = $this->landingPage()->terms(['taxonomy' => 'hackathon-type']);
    return $this->hackathonTypes;
  }

  // return basic metadata for a hackathon
  public function hackathonMeta(){
    return [
      'hackathonLandingPageTitle' => $this->landingPageTitle(),
      'hackathonLandingPageUrl' => $this->landingPageUrl(),
      'hackathonLandingPageId' => $this->landingPageId(),
      'hackathonTitle' => $this->hackathonTitle(),
      'hackathonStartDate' => $this->hackathonStartDate(),
      'hackathonEndDate' => $this->hackathonEndDate(),
      'hackathonExcerpt' => $this->hackathonExcerpt(),
      'hackathonHostedByExternal' => $this->hackathonHostedByExternal(),
      'hackathonContactEmail' => $this->hackathonContactEmail(),
      'hackathonContactUrl' => $this->hackathonContactUrl(),
      'hackathonTypes' => $this->hackathonTypes(),
      'showGrandchildrenInNav' => $this->showGrandchildrenInNav()
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

}
