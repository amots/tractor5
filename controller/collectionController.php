<?php

use function PHPSTORM_META\type;

/**
 * Description of collectionController
 *
 * @author amots
 * @since 2021-03-10
 */
class collectionController extends baseController
{

    private $collection;
    private $rt;
    private $mGroup;
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->collection = new collection();
        $this->rt = explode('/', $_GET['rt']);
        $this->mGroup = $this->collection->collectionGroupsIndexed();
    }

    public function index()
    {
        if (isset($this->rt[1])) {
            $currentGroup = ctype_digit($this->rt[1]) ? intval($this->rt[1]) : null;
            if (!in_array($currentGroup, array_keys($this->mGroup)))
                $currentGroup = null;
        } else {
            $currentGroup = null;
        }
        $this->registry->template->breadCrumbs = $this->collection->renderCollectionCrumbs($currentGroup);
        $this->registry->template->pageTitle = $this->collection->renderPageTitle($currentGroup);
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        if ($currentGroup) {
            $this->registry->template->footerStuff = <<<EOF
                <script src="/resources/js/masonry.min.js"></script>
                EOF;
            $this->registry->template->content = $this->collection->renderCollectionGroupPage($currentGroup);
        } else {
            $this->registry->template->content = $this->collection->renderCollectionLandingPage();
        }
        $this->registry->template->companies = $this->collection->renderCompaniesList($currentGroup);
        // if (isset($_GET['searchString']))
        $this->registry->template->searchStr = isset($_GET['searchString']) ? trim(filter_input(INPUT_GET, 'searchString')) : null;
        // else 
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('collection');
        $this->registry->template->show('/envelope/bottom');
    }

    public function item($requestID = null)
    {
        if (!isset($this->rt[2]) or !is_numeric($this->rt[2])) {
            header('location: /collection');
        }
        $requestID = intval($this->rt['2']);
        $item = $this->collection->getItem($requestID);
        $this->check4view($item);
        $title = join(
            ' ',
            [
                $item['caption_' . Lang::getLocale()],
                $item['company' . ucfirst(Lang::getLocale())],
                $item['model' . ucfirst(Lang::getLocale())]
            ]
        );
        $this->registry->template->headerStuff = <<<EOF
        <script type="text/javascript" src="/resources/js/ezoom.js"></script>
            <script type="text/javascript">
                $(document).ready(function () {
                    $(".ezoom").each(function() {
                        ezoom.onInit($(this), 
                            {
                                hideControlBtn: false,
                                onClose: function (result) {
                                    console.log(result);
                            },
                                onRotate: function (result) {
                                    console.log(result);
                            },
                        });
                    });
                });
            </script>
            <style>
                .ezoom {
                    cursor: pointer;
            }
            </style>
        EOF;
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('nav.theCollection'), 'link' => '/collection'],
            [
                'literal' => $this->collection->navLiteralFromGroup($item['mGroup']),
                'link' => $this->collection->navLinkFromGroup($item['mGroup'])
            ],
            ['literal' => $title, 'link' => NULL],
        ]);
        $this->registry->template->cLang = ucfirst(Lang::getLocale());
        $this->registry->template->pageTitle = $title;
        $this->registry->template->content = $this->collection->renderItemPage($item);
        $this->registry->template->pictures = item_pic::renderItemPicturesPage($item['pics'], $title);
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('item');
        $this->registry->template->show('/envelope/bottom');
    }

    public function search()
    {
        $list = $this->collection->searchResults();
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->companies = $this->collection->renderCompaniesList();
        $this->registry->template->pageTitle = Lang::trans('nav.search');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('nav.theCollection'), 'link' => '/collection'],
            ['literal' => Lang::trans('nav.search'), 'link' => NULL],
        ]);
        $listClass = new list_items($list);
        $this->registry->template->searchStr = trim(filter_input(INPUT_GET, 'searchString'));
        $this->registry->template->content = $listClass->getSearchResultsPage();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('collection');
        $this->registry->template->show('/envelope/bottom');
    }
    public function compList()
    {
        $currentCompany = filter_input(INPUT_GET, 'comp');
        $collection_group_id = filter_input(INPUT_GET, 't', FILTER_VALIDATE_INT);

        if ($collection_group_id) {
            $currentGroup = $this->mGroup[$collection_group_id];
            $groupLiteral = $currentGroup['group_' . lang::getLocale()];
            $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                ['literal' => Lang::trans('nav.theCollection'), 'link' => '/collection'],
                ['literal' => $groupLiteral, 'link' => '/collection/' . $collection_group_id],
                ['literal' => $currentCompany, 'link' => NULL],
            ]);
        } else {
            $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                ['literal' => Lang::trans('nav.theCollection'), 'link' => '/collection'],
                ['literal' => Lang::trans('nav.companies'), 'link' => NULL],
            ]);
        }
        $list = $this->collection->getItemsByCompany($currentCompany, $collection_group_id);
        $this->registry->template->pageTitle = Lang::trans('nav.companies');
        $renderer = new template_renderer(__SITE_PATH . '/includes/' . Lang::getLocale() . '/info.html');
        $this->registry->template->info = $renderer->render();
        $this->registry->template->searchStr = !isset($_GET['searchString']) or util::IsNullOrEmptyString($_GET['searchString']) ? null : trim(filter_input(INPUT_GET, 'searchString'));
        $this->registry->template->companies = $this->collection->renderCompaniesList($collection_group_id);
        $listClass = new list_items($list);
        $this->registry->template->content = $listClass->getCurrentCompPage();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('collection');
        $this->registry->template->show('/envelope/bottom');
    }

    private function check4view(array $item)
    {
        $level = 19;
        $verified = false;
        if ($item['archive']) {
            $permission = User::permission();
            if (isset($_REQUEST['forceview'])) {
                if ($permission and (($level == 0) or ($permission & $level))) {
                    $verified = true;
                }
            }
        } else {
            $verified = true;
        }
        if (!$verified) {
            header('location: /error404');
        }
    }
    public function showItem()
    {
        /** 
         * for backward competabilty
         */
        $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
        header('location: /collection/item/' . $id);
    }
}
