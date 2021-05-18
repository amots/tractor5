<?php

/**
 *
 * @author amots
 * @since 2021-04-04
 */
class ownershipController Extends baseController {

    private $user;
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
        $this->rt = explode('/', $_REQUEST['rt']);
        $this->permission = User::permission();//$this->user->read_permission();
        $this->mng = new mng();
        $this->renderer = new template_renderer(__SITE_PATH . '/includes/mng/mngNav.html');
        $this->registry->template->mngNavBar = $this->mng->renderMngMenu();
    }

    public function index() {
        User::checkAuthorization(User::permission_ownership);
        if (isset($_POST['action']) and $_POST['action'] = 'updateOwnership') {
            $this->ownershipUpdate($_POST['caller']);
        }
        $this->registry->template->pageTitle = Lang::trans('mng.ownership');
        $baseBread = [
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
        ];
        $ownObj = new ownership();
        $itemIndexAt = 1;
        $recordIndexAt = 2;
        if (isset($this->rt[$itemIndexAt]) and $item_id = intval(filter_var($this->rt[$itemIndexAt],
                        FILTER_SANITIZE_NUMBER_INT))) {
            /* valid 1st param */
            if (isset($this->rt[$recordIndexAt]) and ! util::IsNullOrEmptyString($this->rt[$recordIndexAt])) {
                if ($ownership_id = intval(filter_var($this->rt[$recordIndexAt],
                                FILTER_SANITIZE_NUMBER_INT))) {
                    /* valid 2nd param */
                    array_push($baseBread,
                            ['literal' => Lang::trans('mng.ownership'), 'link' => '/ownership'],
                            ['literal' => Lang::trans('mng.editRecord'), 'link' => NULL]);
                    $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs($baseBread);
                    $this->registry->template->content = $ownObj->renderItemOwnEditPage($item_id,
                            $ownership_id);
                } else {
                    if (strtoupper($this->rt[$recordIndexAt]) == "NEW") {
                        /* new record */
                        array_push($baseBread,
                                ['literal' => Lang::trans('mng.ownership'), 'link' => "/ownership"],
                                ['literal' => Lang::trans('mng.byItem'), 'link' => "/ownership/{$item_id}"],
                                ['literal' => Lang::trans('mng.newRecord'), 'link' => NULL]);
                        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs($baseBread);
                        $this->registry->template->content = $ownObj->renderItemOwnEditPage($item_id);
                    //    $this->registry->template->content ="Render new record for {$this->rt[$itemIndexAt]}";
                    }
                }
            } else {
                /* only one param */
                array_push($baseBread,
                        ['literal' => Lang::trans('mng.ownership'), 'link' => '/mng/ownership'],
                        ['literal' => Lang::trans('mng.byItem'), 'link' => NULL]);
                $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs($baseBread);
                $this->registry->template->content = $ownObj->renderOwnRecordsList($item_id);
            }
        } else { /* no params */
            if (isset($this->rt[$itemIndexAt]) and strtoupper($this->rt[$itemIndexAt]) ==
                    "ALL") {
                $this->messages[] = 'List distinct by Item';
                array_push($baseBread,
                        ['literal' => Lang::trans('mng.ownershipAll'), 'link' => NULL]);
                $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs($baseBread);
                $this->registry->template->content = $ownObj->listAllOwnershipItems();
            } else { /* no params show search */
                $ownership_id = NULL;
                array_push($baseBread,
                        ['literal' => Lang::trans('mng.ownership'), 'link' => NULL]);
                $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs($baseBread);
                $this->registry->template->content = $ownObj->renderOwnSearchPage();
            }
        }
        $this->renderTemplateAnnouncements();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    private function ownershipUpdate($caller) {
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $this->errors[] = 'Failed to validate token';
            return;
        }
        unset($_SESSION['csrf_token']);
    //    Debug::dump($_POST, 'post in ' . __METHOD__ . ' line ' . __LINE__);
        $form = new form('ownership');
        $result = $form->storePostedData();
        if (util::is_array_empty($result)) {
            $_SESSION['messages'] = "record {$form->last_id} save alright";
        } else {
            $_SESSION['errors'] = $result;
        }
        header("location: {$caller}/{$form->last_id}");
    }

    private function renderTemplateAnnouncements() {
        $this->registry->template->errors = util::renderErrors($this->errors);
        $this->registry->template->messages = util::renderMessages($this->messages);
    }

}
