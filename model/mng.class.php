<?php

/**
 * Description of mng
 *
 * @author amots
 * @date 2021-04-02
 */
class mng {
    private $user;
    public function __construct() {
        $this->user = new User();
    }
    public function renderMngMenu() {
        $renderer = new template_renderer(__SITE_PATH . '/includes/mng/mngNav.html');
        $renderer->viewData = $this->setMenuPermissions();
        return $renderer->render();
    }
    
    private function setMenuPermissions() {
        $this->checkAuthorization();
        $hide = 'd-none';
        $table = [
            'ms_auto' => Lang::getLocale() == 'he' ? 'ms-auto-left'
                    : 'ms-auto',
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
    private function checkAuthorization($level = 0) {

        if ($this->user->read_permission() and ( ($level == 0) or ( $this->user->read_permission() &
                $level))) return;
        $_SESSION['errors'][] = 'User must be authorized to access';
        header('Location: /login');
        exit();
    }
}
