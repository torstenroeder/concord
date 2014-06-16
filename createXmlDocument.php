<?php

function mysql_wiki_fetch_normdaten ($wiki_id) {
	$qs = 'SELECT * FROM normdaten WHERE id='.$wiki_id;
	$result = mysql_query($qs);
	if (mysql_num_rows($result) > 0) {
		return mysql_fetch_array($result);
	}
	else return NULL;
}

header('Content-type: text/xml; charset=utf-8');
$xml = new XMLWriter();
$xml->openURI('php://output');
$xml->setIndent(FALSE);
$xml->startDocument('1.0','UTF-8');
// go
$xml->startElement('concordance');
	$xml->writeAttribute('version','1.2');
	$xml->startElement('request');
		$xml->writeAttribute('minPersons',MIN_PERSONS);
		$xml->writeAttribute('maxPersons',MAX_PERSONS);
		$xml->writeAttribute('minScore',MIN_SCORE);
		$xml->writeElement('name',$get['name']);
		$xml->writeElement('otherNames',$get['otherNames']);
		$xml->startElement('dateOfBirth');
			if ($get['yearOfBirth']) $xml->writeAttribute('year',$get['yearOfBirth']);
			if ($get['monthOfBirth']) $xml->writeAttribute('month',$get['monthOfBirth']);
			if ($get['dayOfBirth']) $xml->writeAttribute('day',$get['dayOfBirth']);
			$xml->text($get['dateOfBirth']);
		$xml->endElement();
		$xml->writeElement('placeOfBirth',$get['placeOfBirth']);
		$xml->startElement('dateOfDeath');
			if ($get['yearOfDeath']) $xml->writeAttribute('year',$get['yearOfDeath']);
			if ($get['monthOfDeath']) $xml->writeAttribute('month',$get['monthOfDeath']);
			if ($get['dayOfDeath']) $xml->writeAttribute('day',$get['dayOfDeath']);
			$xml->text($get['dateOfDeath']);
		$xml->endElement();
		$xml->writeElement('placeOfDeath',$get['placeOfDeath']);
		$xml->writeElement('description',$get['description']);
	$xml->endElement();
	reset($scores);
	$xml->startElement('results');
		$xml->writeAttribute('count',count($scores));
		$xml->startElement('queries');
			$xml->writeAttribute('highestScore',reset($scores));
			$xml->writeAttribute('highestPossibleScore',$maxPossibleScore);
			$xml->writeAttribute('superScore',$superScore);
			$xml->writeAttribute('requestAffidability',$maxAffidability.'%');
			foreach ($queries as $key => $query) {
				$xml->startElement('context');
				$xml->writeAttribute('name',$key);
				$xml->writeAttribute('score',$contexts[$key]['score']);
				$xml->text($contexts[$key]['description']);
				$xml->endElement();
			}
		$xml->endElement();
		$counter = 0;
		while (list($key,$value) = each($scores)) {
			$counter++;
			$relativeValue = floor(100 * $value / $maxPossibleScore);
			if ($counter <= MIN_PERSONS || ($counter <= MAX_PERSONS && $value >= MIN_SCORE)) {
				$xml->startElement('match');
					$xml->writeAttribute('absoluteScore',$value);
					$xml->writeAttribute('relativeScore',$relativeValue.'%');
					$xml->writeAttribute('matchAffidability',floor($relativeValue * $maxAffidability / 100).'%');
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

?>
