<?php

/**
 * Description of loginController
 *
 * @author amots
 */
class loginController extends baseController
{

    private $user;

    function __construct($registry)
    {
        //        Debug::dump($_SESSION, 'session in ' . __METHOD__ . ' line ' . __LINE__);
        parent::__construct($registry);
        $this->registry->template->message = isset($_SESSION['messages']) ?
            util::renderMessages($_SESSION['messages']) : NULL;
        $this->registry->template->error = isset($_SESSION['errors']) ?
            util::renderErrors($_SESSION['errors']) : NULL;
        $this->user = new User();
        unset($_SESSION['messages']);
        unset($_SESSION['errors']);
        $this->rt = explode('/', $_REQUEST['rt']);
        $this->registry->template->breadCrumbs = breadCrumbs::genBreadCrumbs([
            ['literal' => Lang::trans('nav.homePage'), 'link' => '/'],
            ['literal' => Lang::trans('mng.login'), 'link' => NULL],
        ]);
    }

    public function index()
    {
        $this->registry->template->pageTitle = Lang::trans('mng.login');
        //        Debug::dump($_POST,'post in ' . __METHOD__ . ' line ' . __LINE__);
        if (isset($_POST['submitLogin'])) {
            $this->dologin();
        }

        $this->user = new User();
        /*
          if (isset($_REQUEST['ref']) and ! util::IsNullOrEmptyString($_REQUEST['ref'])) {
          $this->registry->template->caller = $_REQUEST['ref'];
          } else {
          $this->registry->template->caller = NULL;
          }
         */
        $this->renderLogin();
    }

    public function dologin()
    {
        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        if (!isset($_POST['token']) or !util::validatePostToken(
            'token',
            'csrf_token'
        )) {
            $_SESSION['errors'][] = 'invalid csrf';
            header('location: /mng/login');
            exit();
        }
        unset($_SESSION['csrf_token']);
        if ($this->user->login($username, $password)) {
            header('Location: /mng');
            exit;
        } else {
            $this->registry->template->error = util::renderErrors(['Failed to login']);
            $this->renderLogin();
            exit();
        }
    }

    public function logout()
    {
        $this->user->logout();
        header('location: /');
    }

    private function renderLogin()
    {
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        $this->registry->template->csrf_token = $token;
        $this->registry->template->show('/envelope/head');
        $this->registry->template->show('/mng/login');
        $this->registry->template->show('/envelope/bottom');
    }
}
