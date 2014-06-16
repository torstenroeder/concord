<?php

// productive environment
//ini_set('display_errors', 0);
//error_reporting(0);

// development environment
ini_set('display_errors', 1);
error_reporting(E_ALL);

// general settings
define ('PDR_CONCORD_URL',		'http://'.$_SERVER['SERVER_NAME'].'/concord/');
define ('PDR_CONCORD_LOCAL',	'/srv/www/htdocs/concord/');

define ('DB_HOST',	'localhost');
define ('DB_USER',	'www');
define ('DB_PASS',	'pdrrdp');
define ('DB_NAME',	'db_wikipedia');

// language settings
setlocale (LC_ALL,'de_DE@euro','de_DE','de','ge');

?>
