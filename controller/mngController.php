<?php

/**
 * Description of mngController
 *
 * @author amots
 */
class mngController extends baseController
{

    private $user;
    private $rt;
    private $renderer;
    private $errors = [];
    private $messages = [];
    private $mng;
    private $permission;

    public function __construct($registry)
    {
        parent::__construct($registry);
        if (isset($_SESSION['messages'])) {
            $this->messages = $_SESSION['messages'];
        }
        $this->errors = isset($_SESSION['errors']) ?
            $_SESSION['errors'] : NULL;
        unset($_SESSION['messages']);
        unset($_SESSION['errors']);
        $this->user = new User();
        $this->rt = explode('/', $_REQUEST['rt']);
        $this->permission = User::permission();
        $this->mng = new mng();
        $this->renderer = new template_renderer(__SITE_PATH . '/includes/mng/mngNav.html');
        $this->registry->template->mngNavBar = $this->mng->renderMngMenu();
    }

    public function index()
    {
        User::checkAuthorization();
        $this->registry->template->pageTitle = Lang::trans('mng.mng');
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => NULL],
        ]);
        util::renderAnnouncements($this->registry, $this->messages);
        $this->registry->template->content = NULL; //'<pre>' . print_r($_SESSION, true) . '</pre>';
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function content()
    {
        User::checkAuthorization(User::permission_content);
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
                case 'peopleList':
                    $people = new people();
                    $this->registry->template->headerStuff = <<<EOF
                        <script src="/resources/DataTables-2/datatables.js"></script>
                        <script>
                            $(document).ready(function ()
                            {
                                new DataTable('#list2Sort',{
                                language : {
                                        'url' : '/resources/DataTables-2/plug-ins/he.json',
                                        },
                                paging: true,
                                pageLength: 100,
                                });
                            }
                            );
                        </script>
                        EOF;
                    $formatedList = $people->renderPeopleList4Edit();
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
        util::renderAnnouncements($this->registry, $this->messages);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/content');
        $this->registry->template->show('/envelope/bottom');
    }

    public function editArticle()
    {
        User::checkAuthorization(User::permission_administrator);
        if (isset($_POST['action']) and $_POST['action'] == 'storeArticle') {
            $this->articleUpdate();
        }
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
        util::renderAnnouncements($this->registry, $this->messages);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function editHighlight()
    {
        User::checkAuthorization(User::permission_administrator);
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

        // $this->renderTemplateAnnouncements();
        $renderer = new template_renderer(__SITE_PATH . "/includes/mng/tinymce.html");
        $this->registry->template->headerStuff = $renderer->render();
        $this->registry->template->content = $highlight->renderHighlightEditContent($highlights_id);
        util::renderAnnouncements($this->registry, $this->messages);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function editAnnouncemet()
    {
        User::checkAuthorization(User::permission_administrator);
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
        // $this->renderTemplateAnnouncements();
        $renderer = new template_renderer(__SITE_PATH . "/includes/mng/tinymce.html");
        $this->registry->template->headerStuff = $renderer->render();
        $this->registry->template->content = $announcement->renderEditAnnouncementContent($news_id);
        util::renderAnnouncements($this->registry, $this->messages);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function articleUpdate()
    {

        // Debug::dump($this->errors, 'errors in ' . util::getCaller());
        // Debug::dump($_POST, 'post in ' . util::getCaller());
        $returnId = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);
        // Debug::dump($this->errors, 'errors in ' . util::getCaller());
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $this->messages[] = [2, 'Failed to validate token'];
        } else {
            $form = new form('articles');
            $_SESSION['messages'][] = [0, $form->storePostedData()];
        }
        unset($_SESSION['csrf_token']);
        // Debug::dump($this->errors, 'errors in ' . util::getCaller());

        if (util::is_array_empty($this->errors)) {
            // Debug::dump("record {$form->last_id} save alright", 'in ' . util::getCaller());
            $_SESSION['messages'][] = [0, "record {$form->last_id} save alright"];
            // $returnId = $form->last_id;
        } else {
            // Debug::dump("Errors found", 'in ' . util::getCaller());
            $_SESSION['messages'][] = [2, $this->errors];
        }
        // Debug::dump($returnId, 'return id in ' . util::getCaller());
        // util::var_dump_pre($result, util::getCaller());
        // exit;
        header('location: /mng/editArticle/' . $returnId);
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
            $_SESSION['messages'][] = $this->messages[] = [0, "record {$form->last_id} save alright"];
        } else {
            //            $this->errors[] = $result;
            $_SESSION['messages'][] = [2, $result];
        }
        //        $this->renderTemplateAnnouncements();
        header('location: /mng/editHighlight/' . $form->last_id);
    }

    private function announcementUpdate()
    {
        //        Debug::dump($_POST,'post in ' . __METHOD__ . ' line ' . __LINE__);
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $_SESSION['messages'] = [2, 'Failed to validate token'];
            return;
        }
        unset($_SESSION['csrf_token']);
        $form = new form('news');
        $result = $form->storePostedData();
        if (util::is_array_empty($result)) {
            $message =$_SESSION['messages'][] = [0, "record {$form->last_id} save alright"];
        } else {
            $message = $_SESSION['messages'][] = [2, $result];
        }
        // util::var_dump_pre($result, util::getCaller());
        // util::var_dump_pre($message, util::getCaller());
        // exit();
        header('location: /mng/editAnnouncemet/' . $form->last_id);
    }

    public function editBrief()
    {
        User::checkAuthorization(User::permission_administrator);
        $indexAt = 2;
        if (
            isset($_POST['action'])
            and ($_POST['action'] == 'storeBrief')
        ) {
            if (!util::validatePostToken('csrf_token', 'csrf_token')) {
                $this->errors[] = 'Verification Error';
            } else {
                $form = new form('briefs');
                $results = $form->storePostedData();
                if (util::is_array_empty($results)) {
                    $this->messages[] = [0, "Record {$form->last_id} Saved OK"];
                } else {
                    $this->messages[]= $this->errors[] = [2, $results];
                }
            }
            unset($_SESSION['csrf_token']);
        }
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
        // $this->renderTemplateAnnouncements();
        $this->registry->template->pageTitle = $pageTitle;
        $renderer = new template_renderer(__SITE_PATH . "/includes/mng/tinymce.html");
        $this->registry->template->headerStuff = $renderer->render();
        $this->registry->template->content = $brief->renderEditBrief($briefs_id);
        util::renderAnnouncements($this->registry, $this->messages);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
        // Debug::dump($this->errors, 'errors at ' . util::getCaller());
        // Debug::dump($_POST, 'post at ' . util::getCaller());
    }

    public function quality()
    {
        User::checkAuthorization(User::permission_inventory);
        // util::var_dump_pre($_GET, util::getCaller());
        // $rt = explode('/',$_GET['rt']);
        // util::var_dump_pre($rt, util::getCaller());

        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
            ['literal' => Lang::trans('mng.quality'), 'link' => NULL],
        ]);
        $this->registry->template->pageTitle = Lang::trans('mng.quality');
        $renderer = new template_renderer(
            __SITE_PATH . '/includes/tableSortSetUp.html',
            [
                'tableID' => 'list2Sort',
                'options' => "sortList:[[0,0]],headers: {'.noSort': {sorter: false}}",
            ]
        );
        $this->registry->template->headerStuff = $renderer->render();
        $qualityModel = new quality();
        $this->registry->template->qualityMenu = $qualityModel->renderQualityMenu();
        $this->registry->template->content = $qualityModel->renderQualityPage();
        $this->messages = array_merge($this->messages, $qualityModel->messages);
        util::renderAnnouncements($this->registry, $this->messages);

        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/quality');
        $this->registry->template->show('/envelope/bottom');
    }
    public function user()
    {
        User::checkAuthorization(User::permission_administrator);
        if (isset($_POST['action']) and $_POST['action'] == 'updatePermission') {
            $this->userUpdate();
        }
        $indexAt = 2;
        $baseCrumb = [
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
        ];
        if (isset($this->rt[$indexAt]) and !util::IsNullOrEmptyString($this->rt[$indexAt])) {
            $userName = filter_var($this->rt[$indexAt], FILTER_VALIDATE_INT);
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
        // $this->renderTemplateAnnouncements();
        util::renderAnnouncements($this->registry, $this->messages);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }

    public function editPerson()
    {
        if (
            isset($_POST['action'])
            and ($_POST['action'] == 'storePerson')
        ) {
            if (!util::validatePostToken('csrf_token', 'csrf_token')) {
                $this->messages[] = [2, 'Verification Error'];
            } else {
                $form = new form('people');
                $results = $form->storePostedData();
                // util::var_dump_pre($results,'Store results '.util::getCaller());
                if (util::is_array_empty($results)) {
                    $this->messages[] = [0, "Record {$form->last_id} Saved OK"];
                } else {
                    $this->messages[] = [2, $results];
                }
            }
            unset($_SESSION['csrf_token']);
            $_SESSION['messages'] = $this->messages;
            header('location: /mng/editPerson/' . $form->last_id);
        }
        $indexAt = 2;
        if (isset($this->rt[$indexAt]) and is_numeric($this->rt[$indexAt])) {
            $people_id = strval($this->rt[$indexAt]);
            $people_id_literal = join(' ', [Lang::trans('mng.edit'), $people_id]);
        } else {
            $people_id = null;
            $people_id_literal = Lang::trans('mng.newPerson');
        }
        $people = new people();
        $pageTitle = $this->registry->template->pageTitle = $people_id_literal;
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.mng'), 'link' => '/mng'],
            ['literal' => Lang::trans('mng.content'), 'link' => '/mng/content'],
            ['literal' => Lang::trans('people.people'), 'link' => '/mng/content/peopleList'],
            ['literal' => $pageTitle, 'link' => NULL],
        ]);
        $renderer = new template_renderer(__SITE_PATH . "/includes/mng/editPerson.html");
        $this->registry->template->content = $people->renderEditPerson($people_id);
        util::renderAnnouncements($this->registry, $this->messages);
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/mng');
        $this->registry->template->show('/envelope/bottom');
    }
    private function userUpdate()
    {
        if (!util::validatePostToken('csrf_token', 'csrf_token')) {
            $_SESSION['messages'] = [2, 'Failed to validate token ' . util::getCaller()];
            header('location: /mng/user/');
            exit();
        }
        unset($_SESSION['csrf_token']);
        $form = new form('members');
        $convertedData = [];
        foreach ($_POST as $key => $value) {
            $cValue = ($key == 'password') ? password_hash($value, PASSWORD_BCRYPT) : $_POST[$key];
            $convertedData[$key] = $cValue;
        }

        $result = $form->storeData($convertedData);
        if (util::is_array_empty($result)) {
            $_SESSION['messages'] = [0, "record {$form->last_id} save alright"];
        } else {
            $_SESSION['messages'] = [2, $result];
        }
        header('location: /mng/user/');
        exit();
    }

    /*   private function renderTemplateAnnouncements()
    {
        $this->registry->template->errors = util::renderErrors($this->errors);
        $this->registry->template->messages = util::renderMessages($this->messages);
    } */

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
