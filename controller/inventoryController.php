<?php

/**
 * Description of inventoryController
 *
 * @author amots
 * @data 2021-04-02
 */
class inventoryController extends baseController
{

    private $rt;
    var $errors = [];
    var $messages = [];
    private $user;
    private $inventory;
    private $mng;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->messages[] = isset($_SESSION['messages']) ?
            $_SESSION['messages'] : NULL;
        $this->errors[] = isset($_SESSION['errors']) ?
            $_SESSION['errors'] : NULL;
        unset($_SESSION['messages']);
        unset($_SESSION['errors']);

        $this->inventory = new inventory();
        $this->user = new User();
        $this->rt = explode('/', $_REQUEST['rt']);
        $this->permission = User::permission(); //$this->user->read_permission();
        $this->mng = new mng();
        $this->renderer = new template_renderer(__SITE_PATH . '/includes/mng/mngNav.html');
        $this->registry->template->mngNavBar = $this->mng->renderMngMenu();
    }

    public function index()
    {
        User::checkAuthorization(User::permission_inventory);
        $this->registry->template->pageTitle = Lang::trans('mng.inventory');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.inventory'), 'link' => NULL],
        ]);
        $itemScript = <<<EOF
            <script>
            $(document).ready(function () {
                $('#item_id').bind("enterKey", function (e) {
                    console.log($('#item_id').val());
                    window.location.href='/inventory/editItem/' + $('#item_id').val();
                });
                $('#item_id').keyup(function (e) {
                    if (e.keyCode == 13)
                    {
                        $(this).trigger("enterKey");
                    }
                });
            });
            </script>
            EOF;
        $renderer = new template_renderer(
            __SITE_PATH . '/includes/tableSortSetUp.html',
            [
                'tableID' => 'list2Sort',
                'options' => "sortList:[[0,0]],headers: {'.noSort': {sorter: false}}",
            ]
        );

        $this->registry->template->headerStuff = $itemScript . $renderer->render();

        $this->registry->template->content = $this->renderIndexContent();
        $this->renderTemplateAnnouncements();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function search()
    {
        User::checkAuthorization(User::permission_inventory);
        $this->registry->template->pageTitle = Lang::trans('mng.search');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.inventory'), 'link' => "/inventory"],
            ['literal' => Lang::trans('service.search'), 'link' => NULL],
        ]);
        /* $renderer = new template_renderer(__SITE_PATH . '/includes/tableSortSetUp.html',
                [
            'tableID' => 'list2Sort',
            'options' => "sortList:[[0,0]],headers: {'.noSort': {sorter: false}}",
        ]);
        $this->registry->template->headerStuff = $renderer->render(); */

        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/inventoryNav.html');
        $inventoryNav = $renderer->render();
        $inventoryNav = <<<EOF
            <div class="text-center py-2">{$inventoryNav}</div>
            EOF;

        $searchClass = new search('/inventory/editItem/', '/collection/item/');
        $searchPage = $searchClass->renderSearchPage();
        // $this->registry->template->content = $searchPage . $this->inventory->renderInventorySearchPage();
        $this->registry->template->content = $inventoryNav . $searchPage;
        $this->renderTemplateAnnouncements();
        $this->registry->template->show('/envelope/head');
        // $this->registry->template->show('/service/serviceSearch');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function editItem()
    {
        User::checkAuthorization(User::permission_items);
        if (isset($_POST['action']) and $_POST['action'] == 'storeItem')
            $this->itemUpdate();
        $indexAt = 2;
        if (isset($this->rt[$indexAt]) and is_numeric($this->rt[$indexAt])) {
            $item_id = strval($this->rt[$indexAt]);
            $item_id_literal = $item_id;
        } else {
            $item_id = NULL;
            $item_id_literal = Lang::trans('mng.newItem');
        }
        $this->registry->template->pageTitle = Lang::trans('mng.editItem');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.inventory'), 'link' => '/inventory'],
            ['literal' => Lang::trans('mng.editItem'), 'link' => NULL],
        ]);
        $collection = new collection();
        //    $content = '<div class="text-center">TODO: in ' . __METHOD__ . ' line ' . __LINE__ . "</div>";
        $content = $collection->renderEditItem($item_id);
        $this->registry->template->content = $content;
        $this->renderTemplateAnnouncements();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    private function itemUpdate()
    {
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $this->errors[] = 'Failed to validate token';
            return;
        }
        unset($_SESSION['csrf_token']);
        //    Debug::dump($_POST, 'post in ' . __METHOD__ . ' line ' . __LINE__);
        $form = new form('items');
        $result = $form->storePostedData();
        if (util::is_array_empty($result)) {
            $_SESSION['messages'] = "record {$form->last_id} save alright";
        } else {
            $_SESSION['errors'] = $result;
        }
        header('location: /inventory/editItem/' . $form->last_id);
    }

    public function editPics()
    {
        User::checkAuthorization(User::permission_items);
        $indexAt = 2;
        if (isset($this->rt[$indexAt]) and is_numeric($this->rt[$indexAt])) {
            $item_id = strval($this->rt[$indexAt]);
        }
        $this->registry->template->pageTitle = Lang::trans('mng.editPics');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.inventory'), 'link' => '/inventory'],
            ['literal' => Lang::trans('mng.editPics'), 'link' => NULL],
        ]);
        $collection = new collection();
        $this->registry->template->content = $collection->renderEditItemPics($item_id);
        //    '<div class="text-center">TODO: in ' . __METHOD__ . ' line ' . __LINE__ . "</div>";
        $this->renderTemplateAnnouncements();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');;
    }

    public function updateItemPic()
    {
        //    Debug::dump($_POST, 'post in ' . __METHOD__ . ' line ' . __LINE__);
        $item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
        if (!util::validatePostToken('token', 'csrf_token')) {
            $this->errors[] = 'Failed to validate token';
            return;
        }
        unset($_SESSION['csrf_token']);
        $form = new form('pictures');
        $result = $form->storePostedData();
        if (util::is_array_empty($result)) {
            $_SESSION['messages'] = "picture record {$foןrm->last_id} for item {$item_id} wassaved alright";
        } else {
            $_SESSION['errors'] = $result;
        }
        header('location: /inventory/editPics/' . $item_id);
    }

    public function uploadItemPic()
    {
        $item_id = filter_input(INPUT_POST, 'item_id');
        $itemPics = new item_pic($this->registry, $item_id);
        if (util::validatePostToken('token', 'csrf_token')) {
            $response = upload::handleUpload(__SITE_PATH . '/assets/media/pics/items');
            if ($response['success']) {
                $_SESSION['messages'][] = $response['response'];
                $itemPics->registerItemPic($item_id, $response['fileName']);
                $_SESSION['errors'][] = $itemPics->errors;
            } else {
                $_SESSION['errors'][] = 'Failed to upload file';
                $_SESSION['errors'][] = $response['response'];
            }
        } else {
            $_SESSION['errors'][] = 'Token Failed in ' . __METHOD__ . ' line ' . __LINE__;
        }
        unset($_SESSION['csrf_token']);
        header('location: /inventory/editPics/' . $item_id);
    }


    private function renderTemplateAnnouncements()
    {
        $this->registry->template->errors = util::renderErrors($this->errors);
        $this->registry->template->messages = util::renderMessages($this->messages);
    }

    private function renderIndexContent()
    {
        if (isset($this->rt[1])) {
            return $this->inventory->renderList(strtoupper($this->rt[1]));
        } else return $this->inventory->renderList('ALL');
    }
}
