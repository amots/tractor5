<?php

/* * * include the controller class ** */
include __SITE_PATH . '/application/' . 'controller_base.class.php';

/* * * include the registry class ** */
include __SITE_PATH . '/application/' . 'registry.class.php';

/* * * include the router class ** */
include __SITE_PATH . '/application/' . 'router.class.php';

/* * * include the template class ** */
include __SITE_PATH . '/application/' . 'template.class.php';

/* * * auto load model classes ** */
spl_autoload_register('autoloader');
function autoloader($class_name) {
    $filename = strtolower($class_name) . '.class.php';
    $file = __SITE_PATH . '/model/' . $filename;
    if (file_exists($file) == false) {
        return false;
    }
    include($file);
}
switch ($_SERVER['HTTP_HOST']) {
    case 'tractor.localhost' :
    case 'tractor.loc' :
    case 'tractor5.loc' :
        $privatePath = __SITE_PATH . '/private';
        break;
    default:
//        $privatePath = "/hsphere/local/home/amots-linux/private";
        $privatePath = "/home/tractororg/etc/private";
}

define('__PRIVATE_PATH', $privatePath);
/* * * a new registry object ** */
$registry = new registry;
$registry->lang = new Lang($registry);

/* * * create the database registry object ** */
// $registry->db = db::getInstance();
?>
