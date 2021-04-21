<?php

/**
 * Description of serviceController
 *
 * @author amots
 * @date 2021-03-30
 */
class serviceController Extends baseController {

    private $rt;
    var $errors = [];
    var $messages = [];
    private $user;
    private $service;
    private $mng;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->messages[] = isset($_SESSION['messages']) ?
                $_SESSION['messages'] : NULL;
        $this->errors[] = isset($_SESSION['errors']) ?
                $_SESSION['errors'] : NULL;
        unset($_SESSION['messages']);
        unset($_SESSION['errors']);
        $this->user = new User();
        $this->mng = new mng();
        $this->rt = explode('/', $_REQUEST['rt']);
        $this->service = new service();
        $this->renderer = new template_renderer(__SITE_PATH . '/includes/mng/mngNav.html');
//        $this->renderer->viewData = $this->setMenuPermissions();
        $this->registry->template->mngNavBar = $this->mng->renderMngMenu();//$this->renderer->render();
    }

    public function index() {
        $this->checkAuthorization(User::permission_service);
        $this->registry->template->pageTitle = Lang::trans('service.service');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('service.service'), 'link' => NULL],
        ]);
        $this->registry->template->atWorkList = $this->service->renderatWorkListBoard();
        $this->registry->template->onHoldList = $this->service->renderOnHoldListBoard();
//        $this->registry->template->errors = util::renderErrors($this->errors);
//        $this->registry->template->messages = util::renderMessages($this->messages);
        $this->renderTemplateAnnouncements();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('service/dashboard');
        $this->registry->template->show('/envelope/bottom');
    }

    public function search() {
        $this->checkAuthorization(User::permission_service);
//        if (isset($_POST['submit'])){
//        Debug::dump($_POST,'post in ' . __METHOD__ . ' line ' . __LINE__);}
        $this->registry->template->pageTitle = Lang::trans('service.service');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('service.service'), 'link' => '/service'],
                    ['literal' => Lang::trans('service.search'), 'link' => NULL],
        ]);
        $this->registry->template->content = $this->service->renderServiceSearchPage();
        $this->errors[] = $this->service->errors;
        $this->messages[] = $this->service->messages;
        $this->renderTemplateAnnouncements();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('service/serviceSearch');
        $this->registry->template->show('/envelope/bottom');
    }

    public function editService() {
        $this->checkAuthorization();
        $form = new form('service');
        if (!isset($this->rt[2]) or util::IsNullOrEmptyString($this->rt[2])) {
            $this->registry->template->show('error404');
            exit();
        } else {
            $this->item_id = filter_var($this->rt[2], FILTER_SANITIZE_NUMBER_INT);
        }
        $itemData = $this->service->getItem($this->rt[2]);
        if (isset($this->rt[3]) and ! util::IsNullOrEmptyString($this->rt[3])) {
            $this->service_id = filter_var($this->rt[3],
                    FILTER_SANITIZE_NUMBER_INT);
            $record = $this->service->getRecord($this->service_id);
            if (!($record['item_id'] === $this->item_id)) {
                $this->registry->template->show('error404');
                exit();
            }
        } else {
            $this->service_id = NULL;
            $record = $form->genEmptyRecord(); //$this->service->genEmptyRecord();
            $record['item_id'] = $this->item_id;
            $record['service_id'] = NULL;
        }
        $this->registry->template->pageTitle = Lang::trans('service.editService');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
                    ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
                    ['literal' => Lang::trans('service.service'), 'link' => '/service'],
                    ['literal' => Lang::trans('service.editService'), 'link' => NULL],
        ]);
        $record['inventoryDecsription'] = inventory::renderInventoryDesc($this->item_id);
        $record['recordDescription'] = $this->service->renderItemDesc($itemData);
        $record['servicePersonOptions'] = $this->service->renderServicePersonOptions($record['service_people_id']);
        $record['on_hold_form'] = $this->service->renderServiceOnHoldForm($record['on_hold']);
        $list = $this->service->getListOfRecords($this->rt[2]);
//        Debug::dump($record, 'list in ' . __METHOD__ . ' line ' . __LINE__);
        $this->registry->template->content = $this->service->renderEditPage($record,
                $list);
        $this->messages[] = $this->service->messages;
        $this->errors[] = $this->service->errors;
        $this->renderTemplateAnnouncements();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('service/serviceSearch');
        $this->registry->template->show('/envelope/bottom');
    }

    public function update() {
        $this->checkAuthorization(User::permission_service);
//        Debug::dump($_POST, 'Post in ' . __METHOD__ . ' line ' . __LINE__);
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $_SESSION['errors'] = 'Failed to validate csrf token';
            header('location: /service');
        }
        unset($_SESSION['csrf_token']);
        $form = new form('service');
        $storeErrors = $form->storePostedData();
        $serviceId = $form->last_id;
        $item_id = filter_input(INPUT_POST, 'item_id');
//        Debug::dump($form->last_id, 'last id in ' . __METHOD__ . ' line ' . __LINE__);
        $newUrl = "/service/editService/{$item_id}/{$serviceId}";
//        Debug::dump($newUrl, 'new URL in ' . __METHOD__ . ' line ' . __LINE__);
//        header('location: /service/editService/{$item_id}/{$serviceId}');
        if (util::is_array_empty($storeErrors)) {
            $_SESSION['messages'][] = "Record {$serviceId} stored OK";
        } else {
            $_SESSION['errors'][] = 'Failed to store ' . $serviceId;
            $_SESSION['errors'][] = $storeErrors;
        }
        header('location: ' . $newUrl);
    }

    private function checkAuthorization($level = 0) {

        if ($this->user->read_permission() and ( ($level == 0) or ( $this->user->read_permission() &
                $level))) return;
        $_SESSION['errors'][] = 'User must be authorized to access';
        header('Location: /login');
        exit();
    }

    private function renderTemplateAnnouncements() {
        $this->registry->template->errors = util::renderErrors($this->errors);
        $this->registry->template->messages = util::renderMessages($this->messages);
    }

    private function setMenuPermissions() {
        $this->checkAuthorization();
        $hide = 'd-none';
        $table = [
            'ms_auto' => $this->registry->template->ms_auto,
            'hide1' => $_SESSION['permission'] & User::permission_content ? NULL
                : $hide,
            'hide2' => $_SESSION['permission'] & User::permission_inventory ? NULL
                : $hide,
            'hide4' => $_SESSION['permission'] & User::permission_service ? NULL
                : $hide,
            'hide8' => $_SESSION['permission'] & User::permission_ownership ? NULL
                : $hide,
            'hide16' => $_SESSION['permission'] & User::permission_administrator
                ? NULL : $hide,
        ];
        return $table;
    }

}
