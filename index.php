<?php
require_once '../setup/ini.php';
require_once '../../lib/mysql.lib.php';
require_once '../../lib/http.lib.php';

require_once '../config/mysql_wikipedia.inc.php';

define ('MAX_PERSONS', 10);
define ('MIN_PERSONS', 5);
define ('MIN_SCORE', 40);

openDB (MYSQL_WIKIPEDIA_HOST,MYSQL_WIKIPEDIA_USER,MYSQL_WIKIPEDIA_PASS,MYSQL_WIKIPEDIA_NAME);

/* db test
$q = mysql_query('SELECT * FROM pd LIMIT 0,1');
$res = mysql_fetch_object($q);
print_r($res);
exit;
*/

function mysql_wiki_search (&$scores, &$persons, $parameters, $value) {
	$qs = 'SELECT * FROM pd WHERE ';
	$qs .= '('.implode(') AND (',$parameters).')';
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

function mysql_wiki_fetch_normdaten ($wiki_id) {
	$qs = 'SELECT * FROM normdaten WHERE id='.$wiki_id;
	$result = mysql_query($qs);
	if (mysql_num_rows($result) > 0) {
		return mysql_fetch_array($result);
	}
	else return NULL;
}

// -----------------------------------------------------------------------------

$url = parse_url($_SERVER['REQUEST_URI']);

if (isset($url['query'])) {
	$name = getUrlParameter ('n',NULL);
	$otherNames = getUrlParameter ('on',NULL);
	$dateOfBirth = getUrlParameter ('db',NULL);
	$placeOfBirth = getUrlParameter ('pb',NULL);
	$dateOfDeath = getUrlParameter ('dd',NULL);
	$placeOfDeath = getUrlParameter ('pd',NULL);
	$description = getUrlParameter ('d',NULL);
	
	// Parameter für Wikipedia-Personensuche zusammenstellen
	
	$queries = array();
	
	if ($name) $nameParts = explode(' ',$name);
	if ($otherNames) $otherNameParts = explode(' ',$otherNames);
	
	// Namensteile
	if ($name && (count($nameParts) > 1)) {
		$queries['nameParts'] = array();
		foreach($nameParts as $part) {
			$queries['nameParts'][] = "MATCH (name) AGAINST ('$part')";
		}
	}
	
	// voller Name
	if ($name && $otherNames) {
		$queries['allNames'] = array();
		foreach($nameParts as $part) {
			$queries['allNames'][] = "MATCH (name) AGAINST ('$part')";
		}
		foreach($otherNameParts as $part) {
			$queries['allNames'][] = "MATCH (title,name,altname) AGAINST ('$part')";
		}
	}
	
	// Nachname und Lebensspanne
	if ($name && ($dateOfBirth || $dateOfDeath)) {
		$queries['nameAndLife'] = array();
		foreach($nameParts as $part) {
			$queries['allNames'][] = "MATCH (name) AGAINST ('$part')";
		}
		if ($dateOfBirth) $queries['nameAndLife'][] = 'b_year='.substr ($dateOfBirth,0,4);
		if ($dateOfDeath) $queries['nameAndLife'][] = 'd_year='.substr ($dateOfDeath,0,4);
	}
	
	// Nachname und Beschreibung
	if ($name && $description) {
		$queries['nameAndDesc'] = array();
		foreach($nameParts as $part) {
			$queries['allNames'][] = "MATCH (name) AGAINST ('$part')";
		}
		$queries['nameAndDesc'][] = "MATCH (description) AGAINST ('$description')";
	}
	
	// exakte Geburtsdaten
	if ($dateOfBirth && $placeOfBirth) {
		$queries['birth'] = array();
		$queries['birth'][] = "MATCH (b_place) AGAINST ('$placeOfBirth')";
		switch (strlen($dateOfBirth)) {
			case 4:
				$queries['birth'][] = 'b_year='.substr ($dateOfBirth,0,4);
				break;
			case 7:
				$queries['birth'][] = 'b_year='.substr ($dateOfBirth,0,4);
				$queries['birth'][] = 'b_month='.substr ($dateOfBirth,5,2);
				break;
			case 10:
				$queries['birth'][] = 'b_year='.substr ($dateOfBirth,0,4);
				$queries['birth'][] = 'b_month='.substr ($dateOfBirth,5,2);
				$queries['birth'][] = 'b_day='.substr ($dateOfBirth,8,2);
				break;
		}
	}
	
	// exakte Sterbedaten
	if ($dateOfDeath && $placeOfDeath) {
		$queries['death'] = array();
		$queries['death'][] = "MATCH (d_place) AGAINST ('$placeOfDeath')";
		switch (strlen($dateOfDeath)) {
			case 4:
				$queries['death'][] = 'd_year='.substr ($dateOfDeath,0,4);
				break;
			case 7:
				$queries['death'][] = 'd_year='.substr ($dateOfDeath,0,4);
				$queries['death'][] = 'd_month='.substr ($dateOfDeath,5,2);
				break;
			case 10:
				$queries['death'][] = 'd_year='.substr ($dateOfDeath,0,4);
				$queries['death'][] = 'd_month='.substr ($dateOfDeath,5,2);
				$queries['death'][] = 'd_day='.substr ($dateOfDeath,8,2);
				break;
		}
	}
	
	// get results and calculate scores
	
	$queryScores = array(
		'allNames' => 30,
		'nameParts' => 15,
		'nameAndLife' => 30,
		'nameAndDesc' => 20,
		'birth' => 15,
		'death' => 15
	);
	$maxPossibleScore = 0;
	$superScore = array_sum($queryScores);
	
	$scores = array();
	$persons = array();
	
	foreach ($queries as $key => $query) {
		mysql_wiki_search ($scores, $persons, $query, $queryScores[$key]);
		$maxPossibleScore += $queryScores[$key];
	}
	$maxAffidability = floor(100 * $maxPossibleScore / $superScore);
	
	arsort($scores);
	// scores enthält jetzt eine sortierte Liste mit möglichen Namen
	
	// create document
	header('Content-type: text/xml; charset=utf-8');
	$xml = new XMLWriter();
	$xml->openURI('php://output');
	$xml->setIndent(FALSE);
	$xml->startDocument('1.0','UTF-8');
	// go
	$xml->startElement('concordance');
		$xml->writeAttribute('version','1.1');
		$xml->startElement('request');
			$xml->writeAttribute('minPersons',MIN_PERSONS);
			$xml->writeAttribute('maxPersons',MAX_PERSONS);
			$xml->writeAttribute('minScore',MIN_SCORE);
			$xml->writeElement('name',$name);
			$xml->writeElement('otherNames',$otherNames);
			$xml->writeElement('dateOfBirth',$dateOfBirth);
			$xml->writeElement('placeOfBirth',$placeOfBirth);
			$xml->writeElement('dateOfDeath',$dateOfDeath);
			$xml->writeElement('placeOfDeath',$placeOfDeath);
			$xml->writeElement('description',$description);
		$xml->endElement();
		reset($scores);
		$xml->startElement('results');
			$xml->writeAttribute('count',count($scores));
			$xml->writeAttribute('highestScore',reset($scores));
			$xml->startElement('queries');
				$xml->writeAttribute('highestPossibleScore',$maxPossibleScore);
				$xml->writeAttribute('superScore',$superScore);
				$xml->writeAttribute('requestAffidability',$maxAffidability.'%');
				foreach ($queries as $key => $query) {
					$xml->startElement('context');
					$xml->writeAttribute('name',$key);
					$xml->writeAttribute('score',$queryScores[$key]);
					//$xml->text($query);
					$xml->endElement();
				}
			$xml->endElement();
			$counter = 0;
			while (list($key,$value) = each($scores)) {
				$counter++;
				$relativeValue = floor(100 * $value / $maxPossibleScore);
				if ($counter <= MIN_PERSONS || ($counter <= MAX_PERSONS && $value >= MIN_SCORE)) {
					$xml->startElement('result');
						$xml->writeAttribute('absoluteScore',$value);
						$xml->writeAttribute('relativeScore',$relativeValue.'%');
						$xml->writeAttribute('affidability',floor($relativeValue * $maxAffidability / 100).'%');
						// person
						$xml->startElement('person');
							// wikipedia person data
							$xml->writeElement('name',$persons[$key]->name);
							$xml->writeElement('otherNames',$persons[$key]->altname);
							$xml->writeElement('dateOfBirth',$persons[$key]->born);
							$xml->writeElement('placeOfBirth',$persons[$key]->b_place);
							$xml->writeElement('dateOfDeath',$persons[$key]->died);
							$xml->writeElement('placeOfDeath',$persons[$key]->d_place);
							$xml->writeElement('description',$persons[$key]->description);
							// wikipedia reference
							$xml->startElement('reference');
								$xml->writeAttribute('provider','Wikipedia');
								$xml->writeAttribute('url','http://de.wikipedia.org/wiki/'.$persons[$key]->title);
								$xml->text($persons[$key]->title);
							$xml->endElement(); // reference
						$xml->endElement(); // person
						// id section
						$xml->startElement('identifiers');
							$xml->startElement('personId');
								$xml->writeAttribute('provider','PeEnDe');
								$xml->writeAttribute('url','http://toolserver.org/~apper/pd/person/peende/'.$key);
								$xml->text($key);
							$xml->endElement();
							if ($normdaten = mysql_wiki_fetch_normdaten($key)) {
								if ($normdaten['pnd']) {
									$xml->startElement('personId');
										$xml->writeAttribute('provider','PND');
										$xml->writeAttribute('url','http://d-nb.info/gnd/'.$normdaten['pnd']);
										$xml->text($normdaten['pnd']);
									$xml->endElement();
								}
								if ($normdaten['lccn']) {
									$xml->startElement('personId');
										$xml->writeAttribute('provider','LCCN');
										$xml->writeAttribute('url','http://lccn.loc.gov/'.preg_replace('#(.*)\/(.*)\/(.*)#','${1}${2}0${3}',$normdaten['lccn']));
										$xml->text($normdaten['lccn']);
									$xml->endElement();
								}
								if ($normdaten['viaf']) {
									$xml->startElement('personId');
										$xml->writeAttribute('provider','VIAF');
										$xml->writeAttribute('url','http://viaf.org/viaf/'.$normdaten['viaf']);
										$xml->text($normdaten['viaf']);
									$xml->endElement();
								}
							} // if
						$xml->endElement(); // identifiers
					$xml->endElement();
				}
			}
		$xml->endElement();
	$xml->endElement();
}
else {
	header('Content-type: text/html; charset=utf-8');
	require_once '../templates/header.html';
	require_once 'doc.html';
	require_once '../templates/footer.html';
}

?>
