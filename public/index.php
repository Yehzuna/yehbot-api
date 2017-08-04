<?php

require_once '../vendor/autoload.php';

// Local server
if ($_SERVER['SERVER_NAME'] == 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    if (isset($_SERVER['PATH_INFO'])) {
        $_GET['_url'] = $_SERVER['PATH_INFO'];
    }
}

$bootstrap = new YezBot\Bootstrap();
$bootstrap->run();
