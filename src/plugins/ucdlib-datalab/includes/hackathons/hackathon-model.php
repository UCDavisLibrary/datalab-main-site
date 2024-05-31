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
      'hackathonTypes' => $this->hackathonTypes()
    ];
  }

}
