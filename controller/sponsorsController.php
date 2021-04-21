<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of contactController
 *
 * @author amots
 */
class sponsorsController Extends baseController {

    public function __construct($registry) {
        parent::__construct($registry);
    }

    public function index() {
        $people=new people();
        $this->registry->template->pageTitle = Lang::trans('nav.theCntributors');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.theCntributors'), 'link' => NULL],
        ]);
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/sponsors.html');
        $renderer->viewData = ['volunteers'=>$people->renderAllVolunteers()];
        $this->registry->template->content = $renderer->render();
        $renderer->viewFile = __SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html';
        $this->registry->template->info = $renderer->render();

        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('page');
        $this->registry->template->show('/envelope/bottom');
    }

}
