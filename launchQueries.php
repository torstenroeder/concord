<?php

// abfangen: wenn es nur secondary queries gibt!

function classify_search (&$personScores, &$persons, $parameterKey) {
	$current = &$GLOBALS['parameters'][$parameterKey];
	$qs = 'SELECT id FROM pd WHERE ';
	$qs .= '('.implode(') AND (',$current['queries']).')';
	$qs .= ' LIMIT '.LIMIT_MATCHES;
	if ($results = mysql_query($qs)) {
		if (mysql_num_rows($results) == 0) {
			// diese Abfrage ausschließen, da sie keine Ergebnisse bringt
			$current['classification'] = 'null';
			$current['matches'] = mysql_num_rows($results);
		}
		elseif (mysql_num_rows($results) == LIMIT_MATCHES) {
			// diese Abfrage zunächst ausschließen, da sie zu viele Ergebnisse bringt
			// später aber zur genaueren Überprüfung der Personen heranziehen
			$current['classification'] = 'secondary';
		}
		else {
			// angemessene Anzahl an Ergebnissen erzielt
			// diese Abfrage trägt zur Grundmenge der Personen bei
			$current['classification'] = 'primary';
			$current['matches'] = mysql_num_rows($results);
		}
	}
	else {
		echo $qs; die ('this query seems to contain an error. maybe you can help?');
	}
}

function primary_search (&$personScores, &$persons, $parameterKey) {
	$current = &$GLOBALS['parameters'][$parameterKey];
	//$partialScore = $current['score'] / count($current['queries']);
	$qs = 'SELECT * FROM pd WHERE ';
	$qs .= '('.implode(') AND (',$current['queries']).')';
	$qs .= ' LIMIT '.LIMIT_MATCHES;
	if ($results = mysql_query($qs)) {
		if (mysql_num_rows($results) > 0 && mysql_num_rows($results) < LIMIT_MATCHES) {
			while ($result = mysql_fetch_object($results)) {
				if (!isset($personScores[$result->id])) {
					$personScores[$result->id] = 0; // Person in der Trefferliste anlegen
					$persons[$result->id] = $result; // Datensatz der Person festhalten
				}
				$personScores[$result->id] += $current['score']; // Punktzahl in der Trefferliste hinzuzählen
			} // while
		}
		else {
			// zu viele Treffer
			// das sollte eigentlich nicht passieren!
			die ('a query produced either none or too many matches. this should not have happened.');
		}
	}
	else {
		echo $qs; die ('this query seems to contain an error. maybe you can help?');
	}
}

function secondary_search (&$personScores, &$persons, $parameterKey, $primaryQuery) {
	$current = &$GLOBALS['parameters'][$parameterKey];
	if ($primaryQuery) {
		$qs = ' SELECT id';
	}
	else {
		$qs = ' SELECT *';
	}
	// ggf. die passende Untertabelle einbinden
	switch ($parameterKey) {
		case 'ca':
			$qs .= ' FROM pd LEFT JOIN country USING (id) WHERE ';
			break;
		case 'g':
			$qs .= ' FROM pd LEFT JOIN sex USING (id) WHERE ';
			break;
		default:
			$qs .= ' FROM pd WHERE ';
			break;
	}
	$qs .= '('.implode(') AND (',$current['queries']).')';
	if ($primaryQuery) {
		$qs .= ' AND '.$primaryQuery;
	}
	else {
		$qs .= ' LIMIT '.LIMIT_MATCHES;
	}
	// check that secondary parameter for each person in the primary results
	if ($results = mysql_query($qs)) {
		while ($result = mysql_fetch_object($results)) {
			if (!isset($personScores[$result->id])) {
				$personScores[$result->id] = 0; // Person in der Trefferliste anlegen
				$persons[$result->id] = $result; // Datensatz der Person festhalten
			}
			$personScores[$result->id] += $current['score']; // Punktzahl in der Trefferliste hinzuzählen
		}
	}
	else {
		echo $qs; die ('this query seems to contain an error. maybe you can help?');
	} // if
	$current['matches'] = mysql_num_rows($results);
	//exit;
}

// get results and calculate scores ---------------------------------

$persons = array();
$personScores = array();
$optimalScore = 0;

// prep search
foreach ($parameters as $parameterKey => $parameter) {
	if (count($parameter['queries']) > 0) {
		$optimalScore += $parameter['score'];
		if (!$parameter['classification']) {
			classify_search ($personScores, $persons, $parameterKey);
		}
	}
}

// primary results
$primaryParameters = array();
foreach ($parameters as $parameterKey => $parameter) {
	if (count($parameter['queries']) > 0) {
		if ($parameter['classification'] == 'primary') {
			primary_search ($personScores, $persons, $parameterKey);
			$primaryParameters[] = $parameter;
		}
	}
}

// secondary results
$primaryQuery = NULL;
if (count($primaryParameters) > 0) {
	$primaryQuery = array();
	foreach ($primaryParameters as $primaryParameter) {
		$primaryQuery[] = '('.implode(') AND (',$primaryParameter['queries']).')';
	}
	$primaryQuery = '('.implode(' OR ',$primaryQuery).')';
}
foreach ($parameters as $parameterKey => $parameter) {
	if (count($parameter['queries']) > 0) {
		if ($parameter['classification'] == 'secondary') {
			secondary_search ($personScores, $persons, $parameterKey, $primaryQuery);
		}
	}
}

$requestQuality = floor(100 * $optimalScore / $perfectScore);
arsort($personScores);
// $personScores enthält jetzt eine sortierte Liste mit möglichen Personen-IDs

?>
