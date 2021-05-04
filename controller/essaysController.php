<?php

/**
 * Description of contactController
 *
 * @author amots
 */
class essaysController Extends baseController {

    public function __construct($registry) {
        parent::__construct($registry);
    }

    public function index() {
        $essays = new essay();
        $this->registry->template->pageTitle = Lang::trans('nav.articles');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.articles'), 'link' => NULL],
        ]);
        $this->registry->template->content = $essays->essayList();
        $renderer = new template_renderer();
        $renderer->viewFile = __SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html';
        $this->registry->template->info = $renderer->render();

        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('page');
        $this->registry->template->show('/envelope/bottom');
    }

    public function article() {
        $essay = new essay();
        $rt = explode("/", $_REQUEST['rt']);
        $renderer = new template_renderer();
        $renderer->viewFile = __SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html';
        $this->registry->template->info = $renderer->render();
        if (isset($rt['2']) and is_numeric($rt['2'])) {
            $id = intval($rt['2']);
            $title = $essay->getArticleTitle($id);
            $this->registry->template->pageTitle = $title;
            $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                        ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                        ['literal' => Lang::trans('nav.articles'), 'link' => '/essays'],
                        ['literal' =>$title , 'link' => NULL],
            ]);
            $this->registry->template->content = $essay->renderArticle($id);
            $this->registry->template->show('/envelope/head');
            $this->registry->template->show('page');
            $this->registry->template->show('/envelope/bottom');
        } else {
            $this->registry->template->show('/envelope/head');
            $this->registry->template->show('error404');
            $this->registry->template->show('/envelope/bottom');
        }
    }

}
