<?php

Class error404Controller Extends baseController {

    public function index() {
        $this->registry->template->pageTitle = Lang::trans('general.404');
        $this->registry->template->show('envelope/head');
        $this->registry->template->show('error404');
        $this->registry->template->show('envelope/bottom');
    }

}
?>

<?php

/*
  Class error404Controller Extends baseController {

  public function __construct($registry) {
  parent::__construct($registry);
  $this->registry->template->headerStuff = NULL;
  $this->registry->template->footerStuff = NULL;
  $this->registry->template->title = 'Error on page';
  $this->registry->template->bodyParam = 'class="rtl"';
  $this->registry->template->pageTitle = "404";
  $this->registry->template->analytics = NULL;
  }

  public function index() {
  $this->registry->template->show('envelope/head');
  $this->registry->template->show('error404');
  $this->registry->template->show('envelope/bottom');
  }

  }
 */
?>