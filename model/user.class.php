<?php

//include('password.php');

class User extends Password
{

    public $permission;

    const permission_guest = 0;
    const permission_inventory = 2;
    const permission_service = 4;
    const permission_items = 8;
    const permission_content = 16;
    const permission_ownership = 32;
    const permission_administrator = 1;

    function __construct()
    {
        parent::__construct();
        $this->permission = [
            'guest' => self::permission_guest,
            'inventory' => self::permission_inventory,
            'service' => self::permission_service,
            'items' => self::permission_items,
            'content' => self::permission_content,
            'ownership' => self::permission_ownership,
            'administrator' => self::permission_administrator,
        ];
    }

    private function get_user_hash($username)
    {
        $db = db::getInstance();
        try {
            $stmt = $db->prepare('SELECT password, username, memberID, permission FROM members WHERE username = :username AND active="Yes" ');
            $stmt->execute(array('username' => $username));

            return $stmt->fetch();
        } catch (PDOException $e) {
            Debug::dump(
                $e->getMessage(),
                'issues in ' . __METHOD__ . ' line ' . __LINE__
            );
        }
    }

    public function isValidUsername($username)
    {
        if (strlen($username) < 3) return false;
        if (strlen($username) > 17) return false;
        if (!ctype_alnum($username)) return false;
        return true;
    }

    public function login($username, $password)
    {
        //        if (!$this->isValidUsername($username)) return false;
        //        if (strlen($password) < 3) return false;

        $row = $this->get_user_hash($username);
        if ($this->password_verify($password, $row['password']) == 1) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $row['username'];
            $_SESSION['memberID'] = $row['memberID'];
            $_SESSION['permission'] = $row['permission'];
            return true;
        } else {
            $_SESSION['errors'][] = 'failed to verify password';
            return FALSE;
        };
    }

    public function logout()
    {
        session_destroy();
    }

    static function permission()
    {
        if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true) {
            return intval($_SESSION['permission']);
        } else {
            return 0;
            $_SESSION['errors'][] = 'User must be authorized to access';
            header('Location: /login');
            exit();
        }
    }
    static function checkAuthorization($level = 0)
    {
        $permission = self::permission();
        if ($permission and (($level == 0) or ($permission & $level))) return;
        $_SESSION['errors'][] = 'User must be authorized to access';
        header('Location: /login');
        exit();
    }

    public function renderUserSelectionList()
    {
        $list = $this->getAllUsers();
        $data = [];
        foreach ($list as $key => $value) {
            $data[] = <<<EOF
                <div class="ltr">
                <a href="/mng/user/{$value['username']}">{$value['username']}</a>
                </div>                    
                EOF;
        }
        $content = join('', $data);
        return $content;
    }

    public function renderEditUserPermission($username)
    {
        $user = $this->getUserData($username);
        $data = [];
        foreach ($this->permission as $key => $value) {
            if ($value == 0) continue;
            $and = $user['permission'] & $this->permission[$key];
            $checked = $and > 0 ? 'checked' : NULL;
            $data[] = <<<EOF
                <div>
                <input type="checkbox" id="{$key}" name="{$key}" data-p = "{$this->permission[$key]}"
                class="permission_opt"
                {$checked}>
                <label for="{$key}">{$key} {$this->permission[$key]} {$and}</label>
                </div>
                EOF;
        }
        $content = join('', $data);
        $token = util::RandomToken();
        $_SESSION['csrf_token'] = $token;
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/userEditPermission.html');
        $renderer->viewData = [
            'content' => $content,
            'memberID' => $user['memberID'],
            'csrf_token' => $token,
            'userPermission' => $user['permission'],
            'activeCheck' => (strtoupper($user['active']) == 'YES') ? 'checked' : NULL,
            'active' => $user['active'],
            'email' => $user['email'],
            'username' => $username,
        ];
        return $renderer->render();
    }

    private function getAllUsers()
    {
        $pdo = db::getInstance();
        $sql = "SELECT * FROM `members`";
        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute();
            $data = $stmt->fetchAll();
        } catch (Exception $ex) {
            $data = NULL;
            Debug::dump(
                $ex->getMessage(),
                'issues in ' . __METHOD__ . ' line ' . __LINE__
            );
        }
        return $data;
    }

    private function getUserData($username)
    {
        $db = db::getInstance();
        try {
            $stmt = $db->prepare('SELECT * FROM members WHERE username = :username AND UPPER(active)="YES" ');
            $stmt->execute(array('username' => $username));

            return $stmt->fetch();
        } catch (PDOException $e) {
            Debug::dump(
                $e->getMessage(),
                'issues in ' . __METHOD__ . ' line ' . __LINE__
            );
        }
    }
}
