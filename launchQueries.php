<?php

function mysql_wiki_search (&$personScores, &$persons, $parameterKey) {
	$current = &$GLOBALS['parameters'][$parameterKey];
	//$partialScore = $current['score'] / count($current['queries']);
	$qs = 'SELECT * FROM pd WHERE ';
	$qs .= '('.implode(') AND (',$current['queries']).')';
	$qs .= ' LIMIT '.LIMIT_MATCHES;
	if ($results = mysql_query($qs)) {
		$current['matches'] = mysql_num_rows($results);
		if (mysql_num_rows($results) < LIMIT_MATCHES) {
			while ($result = mysql_fetch_object($results)) {
				if (!isset($personScores[$result->id])) {
					$personScores[$result->id] = 0; // Person anlegen
					$persons[$result->id] = $result; // Datensatz der Person festhalten
				}
				$personScores[$result->id] += $current['score']; // Punktzahl zur Person hinzufügen
			}
		}
		else {
			// too many matches - this query should work only supportively
		}
	}
	else {
		echo $qs; exit;
	}
}

// get results and calculate scores

$persons = array();
$personScores = array();

$optimalScore = 0;

//print_r($queries); exit;

foreach ($parameters as $parameterKey => $parameter) {
	if (count($parameter['queries']) > 0) {
		$optimalScore += $parameter['score'];
		mysql_wiki_search ($personScores, $persons, $parameterKey);
	}
}
$requestQuality = floor(100 * $optimalScore / $perfectScore);
arsort($personScores);
// $personScores enthält jetzt eine sortierte Liste mit möglichen Personen-IDs
//print_r ($personScores); exit;

?>
