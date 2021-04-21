<?php

/* * * error reporting on ** */
error_reporting(E_ALL);
date_default_timezone_set('Asia/Tel_Aviv');
ini_set("display_errors", 1);
ob_start();
session_start();
define("APPLICATION_ENV", "development");
/* * * define the site path ** */
// $site_path = realpath(dirname(__FILE__));
$site_path = (realpath(dirname(__FILE__))) ? realpath(dirname(__FILE__)) : dirname(__FILE__);
define('__SITE_PATH', $site_path);

/* * * include the init.php file ** */
include 'includes/init.php';

/* * * load the router ** */
$registry->router = new router($registry);

/* * * set the controller path ** */
$registry->router->setPath(__SITE_PATH . '/controller');

/* * * load up the template ** */
$registry->template = new template($registry);

//--------
setup::setHeaders($registry);
//--------

/* * * load the controller ** */
$registry->router->loader();
?>
