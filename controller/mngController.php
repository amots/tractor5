<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of mngController
 *
 * @author amots
 */
class mngController extends baseController
{

    private $user;
    private $rt;
    private $permission;
    private $renderer;
    private $errors = [];
    private $messages = [];
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
        $this->user = new User();
        $this->rt = explode('/', $_REQUEST['rt']);
        $this->permission = $this->user->read_permission();
        //        Debug::dump($registry,'registry in ' . __METHOD__ . ' line ' . __LINE__);
        $this->mng = new mng();
        $this->renderer = new template_renderer(__SITE_PATH . '/includes/mng/mngNav.html');
        //        $this->renderer->viewData = $this->setMenuPermissions();
        $this->registry->template->mngNavBar = $this->mng->renderMngMenu(); //$this->renderer->render();
    }

    public function index()
    {
        $this->checkAuthorization();
        $this->registry->template->pageTitle = Lang::trans('mng.mng');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => NULL],
        ]);
        $this->renderTemplateAnnouncements();
        $this->registry->template->content = NULL; //'<pre>' . print_r($_SESSION, true) . '</pre>';
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function content()
    {
        $this->checkAuthorization(User::permission_content);
        $this->registry->template->pageTitle = Lang::trans('mng.mng');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
            ['literal' => Lang::trans('mng.content'), 'link' => NULL],
        ]);
        $this->renderer->viewFile = __SITE_PATH . '/includes/mng/contentNav.html';
        $this->registry->template->contentMenu = $this->renderer->render();
        //        Debug::dump($this->rt, 'rt in ' . __METHOD__ . ' line ' . __LINE__);
        $formatedList = "TODO " . util::getCaller();
        if (isset($this->rt[2])) {
            switch ($this->rt[2]) {
                case 'articleList':
                    $essay = new essay();
                    $this->registry->template->breadCrumbs = $essay->renderMngBreadcrumbs();
                    $list = new list_items($essay->getEssaysList());
                    $formatedList = $list->getArticlesPage();
                    break;
                case 'currentHighlightList':
                    $highlight = new highlights();
                    $this->registry->template->breadCrumbs = $highlight->renderMngBreadcrumbs('current');
                    $list = new list_items($highlight->fetchHighlights('CURRENT'));
                    $formatedList = $list->getHighlightsPage();
                    break;
                case 'highlightList':
                    $highlight = new highlights();
                    $this->registry->template->breadCrumbs = $highlight->renderMngBreadcrumbs();
                    $list = new list_items($highlight->fetchHighlights());
                    $formatedList = $list->getHighlightsPage();
                    break;
                case 'currentAnnouncementsList':
                    $announcements = new announcement();
                    $this->registry->template->breadCrumbs = $announcements->renderMngBreadcrumbs('current');
                    $list = new list_items($announcements->getCurrentAnnouncements());
                    $formatedList = $list->getAnnouncementsPage();
                    break;
                case 'announcementsList':
                    $announcements = new announcement();
                    $this->registry->template->breadCrumbs = $announcements->renderMngBreadcrumbs();
                    $list = new list_items($announcements->getAllAnnouncements());
                    $formatedList = $list->getAnnouncementsPage();
                    break;
                case 'briefsList':
                    $briefs = new briefs();
                    $formatedList = $briefs->renderBriefsList4Edit();
                    break;
                default:
                    $this->registry->template->content = '<pre>' . print_r(
                        $_SESSION,
                        true
                    ) . '</pre>';
                    break;
            }
            $this->registry->template->content = $formatedList;
        } else {
            $this->registry->template->content = NULL;
        }
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/content');
        $this->registry->template->show('/envelope/bottom');
    }

    private function checkAuthorization($level = 0)
    {
        if ($this->user->read_permission() and (($level == 0) or ($this->user->read_permission() &
            $level))) return;
        $_SESSION['errors'][] = 'User must be authorized to access';
        header('Location: /login');
        exit();
    }

    private function setMenuPermissions()
    {
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

    public function editArticle()
    {
        $this->checkAuthorization(User::permission_administrator);
        if (isset($_POST['action']) and $_POST['action'] == 'storeArticle')
            $this->articleUpdate();
        $indexAt = 2;
        if (isset($this->rt[$indexAt]) and is_numeric($this->rt[$indexAt])) {
            $article_id_literal = $article_id = strval($this->rt[$indexAt]);
        } else {
            $article_id = NULL;
            $article_id_literal = Lang::trans('mng.newArticle');
        }
        $this->registry->template->pageTitle = join(
            ' ',
            [Lang::trans('mng.articles'), $article_id_literal]
        );
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
            ['literal' => Lang::trans('mng.content'), 'link' => '/mng/content'],
            ['literal' => Lang::trans('mng.articles'), 'link' => '/mng/content/articleList'],
            ['literal' => $article_id_literal, 'link' => NULL],
        ]);
        $essay = new essay();
        $this->registry->template->content = $essay->renderArticleEditContent($article_id);
        $this->errors[] = $essay->get_errors();
        $this->renderTemplateAnnouncements();
        // $renderer = new template_renderer(__SITE_PATH . "/includes/mng/tinymce.html");
        // $this->registry->template->headerStuff = $renderer->render();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function editHighlight()
    {
        $this->checkAuthorization(User::permission_administrator);
        $indexAt = 2;
        if (isset($_POST['action']) and $_POST['action'] == 'storeHighlight') {
            $this->highLightUpdate();
            //            exit();
        }
        if (isset($this->rt[$indexAt]) and is_numeric($this->rt[$indexAt])) {
            $highlights_id = strval($this->rt[$indexAt]);
            $highlights_id_literal = join(
                ' ',
                [Lang::trans('mng.edit'), $highlights_id]
            );
        } else {
            $highlights_id = NULL;
            $highlights_id_literal = Lang::trans('mng.newHighlight');
        }
        $highlight = new highlights();
        $pageTitle = $this->registry->template->pageTitle = $highlights_id_literal;
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
            ['literal' => Lang::trans('mng.content'), 'link' => '/mng/content'],
            ['literal' => Lang::trans('mng.highlights'), 'link' => '/mng/content/highlightList'],
            ['literal' => $pageTitle, 'link' => NULL],
        ]);

        $this->renderTemplateAnnouncements();
        $renderer = new template_renderer(__SITE_PATH . "/includes/mng/tinymce.html");
        $this->registry->template->headerStuff = $renderer->render();
        $this->registry->template->content = $highlight->renderHighlightEditContent($highlights_id);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function editAnnouncemet()
    {
        $this->checkAuthorization(User::permission_administrator);
        if (isset($_POST['action']) and $_POST['action'] == 'storeAnnouncement') {
            $this->announcementUpdate();
            //            exit();
        }
        $indexAt = 2;
        if (isset($this->rt[$indexAt]) and is_numeric($this->rt[$indexAt])) {
            $news_id = strval($this->rt[$indexAt]);
            $news_id_literal = join(' ', [Lang::trans('mng.edit'), $news_id]);
        } else {
            $news_id = NULL;
            $news_id_literal = Lang::trans('mng.newAnnouncement');
        }
        $announcement = new announcement();
        $pageTitle = $this->registry->template->pageTitle = $news_id_literal;
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
            ['literal' => Lang::trans('mng.content'), 'link' => '/mng/content'],
            ['literal' => Lang::trans('mng.announcements'), 'link' => '/mng/content/announcementsList'],
            ['literal' => $pageTitle, 'link' => NULL],
        ]);
        $this->renderTemplateAnnouncements();
        $renderer = new template_renderer(__SITE_PATH . "/includes/mng/tinymce.html");
        $this->registry->template->headerStuff = $renderer->render();
        $this->registry->template->content = $announcement->renderEditAnnouncementContent($news_id);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function articleUpdate()
    {
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $this->errors[] = 'Failed to validate token';
            return;
        }
        unset($_SESSION['csrf_token']);
        $form = new form('articles');
        $result = $form->storePostedData();
        if (util::is_array_empty($result)) {
            //            $this->messages = "record {$form->last_id} save alright";
            $_SESSION['messages'] = "record {$form->last_id} save alright";
        } else {
            //            $this->errors[] = $result;
            $_SESSION['errors'] = $result;
        }
        header('location: /mng/editArticle/' . $form->last_id);
    }

    private function highLightUpdate()
    {
        //        Debug::dump($_POST,'post in ' . __METHOD__ . ' line ' . __LINE__);
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $this->errors[] = 'Failed to validate token';
            return;
        }
        unset($_SESSION['csrf_token']);
        $form = new form('highlights');
        $result = $form->storePostedData();
        if (util::is_array_empty($result)) {
            //            $this->messages[] = "record {$form->last_id} save alright";
            $_SESSION['messages'] = "record {$form->last_id} save alright";
        } else {
            //            $this->errors[] = $result;
            $_SESSION['errors'] = $result;
        }
        //        $this->renderTemplateAnnouncements();
        header('location: /mng/editHighlight/' . $form->last_id);
    }

    private function announcementUpdate()
    {
        //        Debug::dump($_POST,'post in ' . __METHOD__ . ' line ' . __LINE__);
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $_SESSION['errors'] = 'Failed to validate token';
            return;
        }
        unset($_SESSION['csrf_token']);
        $form = new form('news');
        $result = $form->storePostedData();
        if (util::is_array_empty($result)) {
            $_SESSION['messages'] = "record {$form->last_id} save alright";
        } else {
            $_SESSION['errors'] = $result;
        }
        header('location: /mng/editAnnouncemet/' . $form->last_id);
    }

    public function editBrief()
    {
        $this->checkAuthorization(User::permission_administrator);
        $indexAt = 2;
        if (isset($this->rt[$indexAt]) and is_numeric($this->rt[$indexAt])) {
            $briefs_id = strval($this->rt[$indexAt]);
            $brief_id_literal = join(' ', [Lang::trans('mng.edit'), $briefs_id]);
        } else {
            $briefs_id = null;
            $brief_id_literal = Lang::trans('mng.newBrief');
        }
        $brief = new briefs();
        $pageTitle = $this->registry->template->pageTitle = $brief_id_literal;
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
            ['literal' => Lang::trans('mng.content'), 'link' => '/mng/content'],
            ['literal' => Lang::trans('mng.briefs'), 'link' => '/mng/content/briefsList'],
            ['literal' => $pageTitle, 'link' => NULL],
        ]);
        $this->renderTemplateAnnouncements();
        $this->registry->template->pageTitle = $pageTitle;
        $renderer = new template_renderer(__SITE_PATH . "/includes/mng/tinymce.html");
        $this->registry->template->headerStuff = $renderer->render();
        $this->registry->template->content = $brief->renderEditBrief($briefs_id);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }
    public function user()
    {
        $this->checkAuthorization(User::permission_administrator);
        if (isset($_POST['action']) and $_POST['action'] == 'updatePermission') {
            $this->userUpdate();
        }
        $indexAt = 2;
        $baseCrumb = [
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
        ];
        if (isset($this->rt[$indexAt]) and !util::IsNullOrEmptyString($this->rt[$indexAt])) {
            $userName = filter_var($this->rt[$indexAt], FILTER_SANITIZE_STRING);
            $content = $this->user->renderEditUserPermission($userName);
            $baseCrumb[] = ['literal' => lang::trans('mng.user'), 'link' => '/mng/user'];
            $baseCrumb[] = ['literal' => $userName, 'link' => NULL];
        } else {
            $content = $this->user->renderUserSelectionList();
            $userName = NULL;
            $baseCrumb[] = ['literal' => lang::trans('mng.user'), 'link' => NULL];
        }
        $pageTitle = $this->registry->template->pageTitle = join(' ', [Lang::trans('mng.user'), $userName]);
        $this->registry->template->pageTitle = $pageTitle;

        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs($baseCrumb);
        $this->registry->template->content = $content;
        $this->renderTemplateAnnouncements();
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    private function userUpdate()
    {
        Debug::dump($_POST, 'post in ' . __METHOD__ . ' line ' . __LINE__);
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $_SESSION['errors'] = 'Failed to validate token';
            return;
        }
        unset($_SESSION['csrf_token']);
        $form = new form('members');
        $result = $form->storePostedData();
        if (util::is_array_empty($result)) {
            $_SESSION['messages'] = "record {$form->last_id} save alright";
        } else {
            $_SESSION['errors'] = $result;
        }
        header('location: /mng/user/');
    }

    private function renderTemplateAnnouncements()
    {
        $this->registry->template->errors = util::renderErrors($this->errors);
        $this->registry->template->messages = util::renderMessages($this->messages);
    }

    public function hash()
    {
        echo <<<EOF
            <style>
                .copy-to-clipboard input {
                cursor: pointer;
                width:75%;
                    border: none;
                    background: transparent;
            font-family: courier;
                }
                .copied {
                    position: absolute;
                    background: #1266ae;
                    color: #fff;
                    font-weight: bold;
                    z-index: 99;
                    width: 100%;
                    top: 0;
                    text-align: center;
                    padding: 15px;
                    display: none;
                    font-size: 18px;
                }
            </style>
            <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
            <script>
                $(document).ready(function () {
                    $('.copy-to-clipboard input').click(function() {
                        $(this).focus();
                        $(this).select();
                        document.execCommand('copy');
                        $(".copied").text("Copied to clipboard").show().fadeOut(3000);
                    });
                });
            </script>
            EOF;
        if (!isset($_GET['password']) or util::IsNullOrEmptyString($_GET['password'])) {
            echo 'usage: /mng/hash/?password=';
        } else {
            $password = $_GET['password'];
            echo "<h3>Hashed password for <span style=\"color:red;\">{$password}</span></h3>";
            for ($i = 0; $i < 5; $i++) {
                $hashedpassword = password_hash($password, PASSWORD_BCRYPT);
                $activation = md5(uniqid(rand(), true));

                echo '<div class="copy-to-clipboard"><input readonly type="text" value="' . $hashedpassword . '" /></div>';
            }
            echo "<div class='copied'></div>";
        }
    }
}
