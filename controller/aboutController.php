<?php

/**
 *
 * @author amots
 */
class aboutController Extends baseController {

    public function __construct($registry) {
        parent::__construct($registry);
    }

    public function index() {
        $this->registry->template->pageTitle = Lang::trans('nav.venue');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.venue'), 'link' => NULL],
        ]);
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/about.html');
        $this->registry->template->content = $renderer->render();
        $renderer->viewFile = __SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html';
        $this->registry->template->info = $renderer->render();

        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('page');
        $this->registry->template->show('/envelope/bottom');
    }

}
