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
        $privatePath = "/home/tractororg/etc/private";
        $privatePath =__SITE_PATH . "/private";
}

define('__PRIVATE_PATH', $privatePath);
/* * * a new registry object ** */
$registry = new registry;
$registry->lang = new Lang($registry);

require_once __SITE_PATH . '/application/loadenv.php';
