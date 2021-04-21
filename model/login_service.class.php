<?php

/**
 * Description of login
 *
 * @author amots
 * @created 2018-05-21
 */
class login_service {

    const LOGINSUBMIT = 'loginSubmit';
    const REGISTERSUBMIT = 'resisterSubmit';

    private $pdo;
    private $user;
    private $errors = [];
    private $messages = [];
    var $returnData = [];

    public function __construct($type = NULL) {
        $this->pdo = db::getInstance();
        $this->user = new User($this->pdo);
        switch ($type) {
            case 'register' :
                $this->handleRegister();
                break;
            case 'login' :
            default:
                $this->handleLogin();
                break;
        }
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getMessages() {
        return $this->messages;
    }

    private function handleLogin() {
        $this->returnData['username'] = NULL; // TODO: cannot call registry from here
//        Debug::dump($_POST,'post data in ' . __METHOD__ . ' line ' .__LINE__);
        if (isset($_POST[login_service::LOGINSUBMIT])) {
            if (empty($_POST['username']))
                    $this->errors[] = "Please fill out all fields";
            if (empty($_POST['password']))
                    $this->errors[] = "Please fill out all fields";
            $username = (!empty($_POST['username'])) ? $_POST['username'] : '';
            if ($this->user->isValidUsername($username)) {
                if (!isset($_POST['password'])) {
                    $this->errors[] = 'A password must be entered';
                }
                $password = $_POST['password'];
                if ($this->user->login($username, $password)) {
                    $_SESSION['username'] = $username;
                    $_SESSION['messages'] = $username . ' has logged in successfuly';
                    header('Location: /' . $_POST['caller']);
                    exit;
                } else {
                    $this->errors[] = 'Wrong username or password or your account has not been activated.';
                }
            } else {
                $this->errors[] = 'Usernames are required to be Alphanumeric, and between 3-16 characters long';
            }
        }
        if (sizeof($this->errors) == 0) {
            $this->returnData['username'] = htmlspecialchars($_POST['username'],
                    ENT_QUOTES); // TODO: cannot call registry from here
        }
    }

}
