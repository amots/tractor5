<?php

/**
 * Description of documentationController
 *
 * @author amots
 * @since 2021-11-12
 */

class documentationController extends baseController
{
    private $doc;
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->doc = new documentation();
    }
    public function index()
    {
        $this->registry->template->pageTitle = Lang::trans('nav.documentation');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('nav.documentation'), 'link' => NULL],
        ]);

        $renderer = new template_renderer();
        $renderer->viewFile = __SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html';
        $this->registry->template->info = $renderer->render();

        $this->registry->template->footerStuff = <<<EOF
                <script src="/resources/js/masonry.min.js"></script>
                EOF;
        $this->registry->template->content = $this->doc->renderDocPage();

        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('page');
        $this->registry->template->show('/envelope/bottom');
    }
    public function video()
    {
        $rt = explode("/", $_REQUEST['rt']);
        $id_in = 2;
        // Debug::dump($rt, 'rt in ' . util::getCaller());
        if (isset($rt[$id_in]) and is_numeric($rt[$id_in])) {
            $this->renderVideoPage($rt[$id_in]);
        } else {
            header('location: /documentation');
        }
    }
    private function renderVideoPage($doc_id)
    {
        $record = $this->doc->getRecord($doc_id);
        // Debug::dump($record, 'record in ' . util::getCaller());
        $title = $record['title_'.lang::getLocale()];
        $this->registry->template->pageTitle = $title;
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('nav.documentation'), 'link' => '/documentation'],
            ['literal' => $title, 'link' => NULL],
        ]);
        $renderer = new template_renderer();
        $renderer->viewFile = __SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html';
        $this->registry->template->info = $renderer->render();
        $this->registry->template->content = <<<EOT
            <div class="embed-responsive embed-responsive-16by9">
            {$record['link']}
            </div>
            EOT;
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('page');
        $this->registry->template->show('/envelope/bottom');
    }
}
