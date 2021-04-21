<?php

/**
 * Description of collectionController
 *
 * @author amots
 * @date 2021-03-10
 */
class collectionController Extends baseController {

    private $collection;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->collection = new collection();
    }

    public function index() {
        $this->registry->template->pageTitle = Lang::trans('nav.theCollection');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.theCollection'), 'link' => NULL],
        ]);
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->content = $this->collection->renderCollectionLandingPage();
        $this->registry->template->companies = $this->collection->renderCompaniesList();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('collection');
        $this->registry->template->show('/envelope/bottom');
    }

    public function tractors() {
        $this->registry->template->footerStuff = <<<EOF
<script src="/resources/js/masonry.min.js"></script>
EOF;
        $this->registry->template->pageTitle = Lang::trans('nav.theTractors');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.theCollection'), 'link' => '/collection'],
                    ['literal' => Lang::trans('nav.theTractors'), 'link' => NULL],
        ]);
        $this->registry->template->companies = $this->collection->renderCompaniesList();
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->content = $this->collection->renderCollectionGroupPage(1);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('collection');
        $this->registry->template->show('/envelope/bottom');
    }

    public function vehicles() {
        $this->registry->template->footerStuff = <<<EOF
<script src="/resources/js/masonry.min.js"></script>
EOF;
        $this->registry->template->pageTitle = Lang::trans('nav.theVehicles');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.theCollection'), 'link' => '/collection'],
                    ['literal' => Lang::trans('nav.theVehicles'), 'link' => NULL],
        ]);
        $this->registry->template->companies = NUll; //$this->collection->renderCompaniesList();
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->content = $this->collection->renderCollectionGroupPage(2);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('collection');
        $this->registry->template->show('/envelope/bottom');
    }

    public function tools() {
        $this->registry->template->footerStuff = <<<EOF
<script src="/resources/js/masonry.min.js"></script>
EOF;
        $this->registry->template->pageTitle = Lang::trans('nav.theTools');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.theCollection'), 'link' => '/collection'],
                    ['literal' => Lang::trans('nav.theTools'), 'link' => NULL],
        ]);
        $this->registry->template->companies = NUll; //$this->collection->renderCompaniesList();
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->content = $this->collection->renderCollectionGroupPage(3);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('collection');
        $this->registry->template->show('/envelope/bottom');
    }

    public function agron() {
        $this->registry->template->footerStuff = <<<EOF
<script src="/resources/js/masonry.min.js"></script>
EOF;
        $this->registry->template->pageTitle = Lang::trans('nav.theCollection');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.theCollection'), 'link' => '/collection'],
                    ['literal' => Lang::trans('nav.agron'), 'link' => NULL],
        ]);
        $this->registry->template->companies = NUll; //$this->collection->renderCompaniesList();
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->content = $this->collection->renderCollectionGroupPage(4);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('collection');
        $this->registry->template->show('/envelope/bottom');
    }

    public function item() {
        $rt = explode('/', $_GET['rt']);
//        Debug::dump($rt,'rt in ' . __METHOD__ . ' line ' . __LINE__);
        if (!isset($rt[2]) or ! is_numeric($rt[2])) {
            header('location: /collection');
        }
        $requestID = intval($rt['2']);
        $item = $this->collection->getItem($requestID);
//        Debug::dump($item,'item in ' . __METHOD__ . ' line ' . __LINE__);
        $title = join(' ',
                [$item['company' . ucfirst(Lang::getLocale())],
            $item['model' . ucfirst(Lang::getLocale())]]);
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('nav.theCollection'), 'link' => '/collection'],
                    ['literal' => $this->collection->navLiteralFromGroup($item['mGroup']),
                        'link' => $this->collection->navLinkFromGroup($item['mGroup'])],
                    ['literal' => $title, 'link' => NULL],
        ]);
//        Debug::dump($title,'title in ' . __METHOD__ . ' line ' . __LINE__);
        $this->registry->template->cLang = ucfirst(Lang::getLocale());
        $this->registry->template->pageTitle = $title;
        $this->registry->template->content = $this->collection->renderItemPage($item);
        $this->registry->template->pictures = itemPic::renderItemPicturesPage($item['pics']);
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('item');
        $this->registry->template->show('/envelope/bottom');
    }

}
