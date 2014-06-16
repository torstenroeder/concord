<?php

$queries = array();
$perfectScore = 0;

foreach ($parameters as $parameterKey => $parameter) {
	$current = &$parameters[$parameterKey];
	$perfectScore += $parameter['score'];
	// Parameter für Wikipedia-Personensuche zusammenstellen
	if ($parameter['value']){
		$queries[$parameterKey] = array();
		switch ($parameter['name']) {
			case 'name':
				foreach($parameter['tokens'] as $token) {
					$current['queries'][] = "MATCH (name) AGAINST ('$token')";
					// Hauptname nur im normierten Namensansatz suchen
				}
				break;
			case 'otherNames':
				foreach($parameter['tokens'] as $token) {
					$current['queries'][] = "MATCH (title,name,altname) AGAINST ('$token')";
					// zusätzliche Namen auch in Nebenfeldern suchen
				}
				break;
			case 'description':
				foreach($parameter['tokens'] as $token) {
					// bei Bedarf die unscharfe Suche wieder einkommentieren
					//$current['queries'][] = "MATCH (description) AGAINST ('$token' WITH QUERY EXPANSION)";
					$current['queries'][] = "MATCH (description) AGAINST ('$token')";
				}
				break;
			case 'dateOfBirth':
				if ($parameter['tokens']['y']) $current['queries'][] = 'b_year='.$parameter['tokens']['y'];
				if ($parameter['tokens']['m']) $current['queries'][] = 'b_month='.$parameter['tokens']['m'];
				if ($parameter['tokens']['d']) $current['queries'][] = 'b_day='.$parameter['tokens']['d'];
				break;
			case 'placeOfBirth':
				foreach($parameter['tokens'] as $token) {
					$current['queries'][] = "MATCH (b_place) AGAINST ('$token')";
				}
				break;
			case 'dateOfDeath':
				if ($parameter['tokens']['y']) $current['queries'][] = 'd_year='.$parameter['tokens']['y'];
				if ($parameter['tokens']['m']) $current['queries'][] = 'd_month='.$parameter['tokens']['m'];
				if ($parameter['tokens']['d']) $current['queries'][] = 'd_day='.$parameter['tokens']['d'];
				break;
			case 'placeOfDeath':
				foreach($parameter['tokens'] as $token) {
					$current['queries'][] = "MATCH (d_place) AGAINST ('$token')";
				}
				break;
			case 'gender':
				if ($parameter['value'] == 'm') {
					$current['queries'][] = "sex=1";
				}
				elseif ($parameter['value'] == 'f') {
					$current['queries'][] = "sex=2";
				}
				break;
			case 'yearOfActivity':
				if ($parameter['tokens']['y']) $current['queries'][] = $parameter['tokens']['y'].' BETWEEN b_year AND d_year';
				break;
			case 'countryOfActivity':
				foreach($parameter['tokens'] as $token) {
					$current['queries'][] = "country=$token";
				}
				break;
		} // switch
	} // if
} // foreach

//print_r($parameters); exit;
?>
