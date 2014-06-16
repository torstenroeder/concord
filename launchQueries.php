<?php

function mysql_wiki_search (&$scores, &$persons, $parameters, $value) {
	$qs = 'SELECT * FROM pd WHERE ';
	$qs .= '('.implode(') AND (',$parameters).')';
	$qs .= ' LIMIT 1000';
	// echo $qs; exit;
	if ($results = mysql_query($qs)) {
		while ($result = mysql_fetch_object($results)) {
			if (!isset($scores[$result->id])) {
				$scores[$result->id] = 0;
				$persons[$result->id] = $result;
			}
			$scores[$result->id] += $value;
		}
	}
	else {
		echo $qs; exit;
	}
}

// get results and calculate scores

$maxPossibleScore = 0;

$scores = array();
$persons = array();

foreach ($queries as $key => $query) {
	mysql_wiki_search ($scores, $persons, $query, $contexts[$key]['score']);
	$maxPossibleScore += $contexts[$key]['score'];
}
$maxAffidability = floor(100 * $maxPossibleScore / $superScore);

arsort($scores);
// scores enthält jetzt eine sortierte Liste mit möglichen Personen-IDs

?>
