<?php

declare(strict_types=1);
/* * * include the controller class ** */
include __SITE_PATH . '/application/' . 'controller_base.class.php';

/* * * include the registry class ** */
include __SITE_PATH . '/application/' . 'registry.class.php';

/* * * include the router class ** */
include __SITE_PATH . '/application/' . 'router.class.php';

/* * * include the template class ** */
include __SITE_PATH . '/application/' . 'template.class.php';

/* * * auto load model classes ** */
/* spl_autoload_register('autoloader');
function autoloader($class_name) {
    $filename = strtolower($class_name) . '.class.php';
    $file = __SITE_PATH . '/model/' . $filename;
    if (file_exists($file) == false) {
        return false;
    }
    include($file);
} */
spl_autoload_register(
    function ($class) {
        $prefix = 'App\\';
        $baseDir = __SITE_PATH . '/';
        // PSR-4 mapping for App\
        if (strncmp($prefix, $class, strlen($prefix)) === 0) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require $file;
                return true;
            }
        }

        // Legacy fallback
        $legacyFile = __SITE_PATH . '/model/' . $class . '.class.php';

        if (file_exists($legacyFile)) {
            require $legacyFile;
            return true;
        }

        return false;
    }
);
switch ($_SERVER['HTTP_HOST']) {
    case 'tractor.localhost' :
    case 'tractor.loc' :
    case 'tractor5.loc' :
        $privatePath = __SITE_PATH . '/private';
        break;
    default:
//        $privatePath = "/hsphere/local/home/amots-linux/private";
        $privatePath = "/home/tractororg/etc/private";
        $privatePath =__SITE_PATH . "/private";
}

define('__PRIVATE_PATH', $privatePath);
/* * * a new registry object ** */
$registry = new registry;
$registry->lang = new Lang($registry);

/* * * create the database registry object ** */
// $registry->db = db::getInstance();
?>
