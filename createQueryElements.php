<?php

$queries = array();
$superScore = 0;

foreach ($contexts as $contextName => $context) {
	$superScore += $context['score'];
	// prüfen, ob alle Parameter vorhanden sind
	foreach ($context['parameters'] as $parameter) {
		if ((!isset($get[$parameter])) || (!$get[$parameter]))
			continue (2); // cancel and continue with next context
	}
	// Parameter für Wikipedia-Personensuche zusammenstellen
	$queries[$contextName] = array();
	foreach ($context['parameters'] as $parameter) {
		switch ($parameter) {
			case 'name':
				foreach($get['nameParts'] as $part) {
					$queries[$contextName][] = "MATCH (name) AGAINST ('$part')";
				}
				break;
			case 'nameParts':
				if (count($get['nameParts']) > 1) {
					foreach($get['nameParts'] as $part) {
						$queries[$contextName][] = "MATCH (name) AGAINST ('$part')";
					}
				}
				else {
					// invalid
					unset($queries[$contextName]);
				}
				break;
			case 'otherNames':
				foreach($get['otherNameParts'] as $part) {
					$queries[$contextName][] = "MATCH (title,name,altname) AGAINST ('$part')";
				}
				break;
			case 'otherNameParts':
				if (count($get['otherNameParts']) > 1) {
					foreach($get['otherNameParts'] as $part) {
						$queries[$contextName][] = "MATCH (title,name,altname) AGAINST ('$part')";
					}
				}
				else {
					// invalid
					unset($queries[$contextName]);
				}
				break;
			case 'description':
				foreach($get['descriptionParts'] as $part) {
					$queries[$contextName][] = "MATCH (description) AGAINST ('$part' WITH QUERY EXPANSION)";
				}
				break;
			case 'descriptionParts':
				if (count($get['descriptionParts']) > 1) {
					foreach($get['descriptionParts'] as $part) {
						$queries[$contextName][] = "MATCH (description) AGAINST ('$part' WITH QUERY EXPANSION)";
					}
				}
				else {
					// invalid
					unset($queries[$contextName]);
				}
				break;
			case 'placeOfBirth':
				$queries[$contextName][] = "MATCH (b_place) AGAINST ('{$get['placeOfBirth']}')";
				break;
			case 'placeOfDeath':
				$queries[$contextName][] = "MATCH (d_place) AGAINST ('{$get['placeOfDeath']}')";
				break;
			case 'dateOfBirth':
				if ($get['yearOfBirth']) $queries[$contextName][] = 'b_year='.$get['yearOfBirth'];
				if ($get['monthOfBirth']) $queries[$contextName][] = 'b_month='.$get['monthOfBirth'];
				if ($get['dayOfBirth']) $queries[$contextName][] = 'b_day='.$get['dayOfBirth'];
				break;
			case 'dateOfDeath':
				if ($get['yearOfDeath']) $queries[$contextName][] = 'b_year='.$get['yearOfDeath'];
				if ($get['monthOfDeath']) $queries[$contextName][] = 'b_month='.$get['monthOfDeath'];
				if ($get['dayOfDeath']) $queries[$contextName][] = 'b_day='.$get['dayOfDeath'];
				break;
			case 'yearOfBirth':
				if ($get['dateOfBirth']) $queries[$contextName][] = 'b_year='.substr ($get['dateOfBirth'],0,4);
				break;
			case 'yearOfDeath':
				if ($get['dateOfDeath']) $queries[$contextName][] = 'd_year='.substr ($get['dateOfDeath'],0,4);
				break;
		} // switch ($parameter)
	} // foreach ($context['parameters'])
} // foreach ($contexts)

?>
