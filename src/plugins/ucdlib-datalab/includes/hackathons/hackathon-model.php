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

}
