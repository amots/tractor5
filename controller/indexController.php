<?php

Class indexController Extends baseController {

    public function __construct($registry) {
        parent::__construct($registry);
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $renderer->viewFile = __SITE_PATH . '/includes/' . Lang::getLocale() . '/moto.html';
        $this->registry->template->moto = $renderer->render();
        $collage = new collage();
        $this->registry->template->collage = $collage->getCollage(1, 4);
        $highlights = new highlights();
        $this->registry->template->highlights = $highlights->renderHighlights();
        $news = new announcement();
        $this->registry->template->news = $news->renderValidNews();
        $renderer->viewFile = __SITE_PATH . '/includes/' . Lang::getLocale() . '/concludignStatement.html';
        $this->registry->template->concludingStatement = $renderer->render();
    }

    public function index() {
        $this->registry->template->pageTitle = Lang::trans('nav.homePage');
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('index');
        $this->registry->template->show('/envelope/bottom');
    }

}

?>
