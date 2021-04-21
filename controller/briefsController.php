<?php

/**
 * Description of briefsController
 *
 * @author amots
 */
class briefsController extends baseController {

    private $briefs;

    function __construct($registry) {
        parent::__construct($registry);
        $this->briefs = new briefs();
    }

    public function index() {
        $this->registry->template->pageTitle = Lang::trans('nav.briefs');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.briefs'), 'link' => NULL],
        ]);
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->content = $this->briefs->renderBriefsList();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('page');
        $this->registry->template->show('/envelope/bottom');
    }

    public function show() {
        $rt = explode('/', $_GET['rt']);
        $indexAt = 2;
        if (!isset($rt[$indexAt]) or ! is_numeric($rt[$indexAt])) {
            header('location: /briefs');
        }
        $briefs_id = $rt[$indexAt];
        $data = $this->briefs->getBrief($briefs_id);
//        Debug::dump($data, "data " . util::getCaller());

        $this->registry->template->pageTitle = Lang::trans('nav.briefs');
        $this->registry->template->content = $this->briefs->renderBrief($data);
        $title = $this->briefs->brief['title_'.Lang::getLocale()];
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.briefs'), 'link' => '/briefs'],
                    ['literal' => "{$title}", 'link' => NULL],
        ]);
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('page');
        $this->registry->template->show('/envelope/bottom');
    }

}
