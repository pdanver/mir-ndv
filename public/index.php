<?php
define('TRUEADMIN', true);
include_once '../core/Route.php';
spl_autoload_register(array('Route', 'autoLoad'));
Route::start();
?>