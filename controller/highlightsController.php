<?php

/**
 * @author amots
 * @copyright 2021-05-03 Amots
 */
class highlightsController extends baseController
{
    private $highLights;
    private $rt;
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->highLights = new highlights;
        $this->rt = explode('/', $_GET['rt']);
    }
    public function index()
    {
        $indexAt = 1;
        $this->registry->template->pageTitle = Lang::trans('nav.highlights');
        // Debug::dump($this->rt,'rt at ' . util::getCaller());
        if (isset($this->rt[$indexAt]) and is_numeric($this->rt[$indexAt])) {
            $highlights_id = $this->rt[$indexAt];
            $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                ['literal' => Lang::trans('nav.highlights'), 'link' => '/highlights'],
                ['literal' => $highlights_id, 'link' => null],
            ]);
            $this->registry->template->content = $this->highLights->renderSingleHighlight($highlights_id);
        } else {
            $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                ['literal' => Lang::trans('nav.highlights'), 'link' => NULL],
            ]);
            $this->registry->template->content = $this->highLights->renderHighlightsList();
        }

        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();

        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('page');
        $this->registry->template->show('/envelope/bottom');
    }
}
