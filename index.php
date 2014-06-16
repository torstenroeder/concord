<?php
require_once '../setup/ini.php';
require_once '../../lib/mysql.lib.php';
require_once '../../lib/http.lib.php';

require_once '../config/mysql_wikipedia.inc.php';

define ('MAX_PERSONS', 15);
define ('MIN_PERSONS', 5);
define ('MIN_SCORE', 40);
define ('LIMIT_MATCHES', 1000);

openDB (MYSQL_WIKIPEDIA_HOST,MYSQL_WIKIPEDIA_USER,MYSQL_WIKIPEDIA_PASS,MYSQL_WIKIPEDIA_NAME);

/* db test
$q = mysql_query('SELECT * FROM pd LIMIT 0,1');
$res = mysql_fetch_object($q);
print_r($res);
exit;
*/

// -----------------------------------------------------------------------------

$url = parse_url($_SERVER['REQUEST_URI']);

require_once 'parameters.php';

if (isset($url['query'])) {
	require_once 'parseParameters.php';
	require_once 'createQueries.php';
	require_once 'launchQueries.php';
	require_once 'createResponse.php';
}
else {
	header('Content-type: text/html; charset=utf-8');
	require_once '../templates/header.html';
	require_once 'doc.html';
	require_once '../templates/footer.html';
}

?>
