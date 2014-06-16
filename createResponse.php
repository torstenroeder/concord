<?php

// TODO geschlecht und länder noch einbinden

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
	$xml->writeAttribute('version','1.4');
	$xml->startElement('request');
		$xml->writeAttribute('minPersons',MIN_PERSONS);
		$xml->writeAttribute('maxPersons',MAX_PERSONS);
		$xml->writeAttribute('minScore',MIN_SCORE);
		foreach ($parameters as $parameterKey => $parameter) {
			switch ($parameter['type']) {
				case 'string':
					$xml->startElement($parameter['name']);
						if (count($parameter['tokens']) > 0) $xml->writeAttribute('tokens',count($parameter['tokens']));
						$xml->text($parameter['value']);
					$xml->endElement();
				break;
				case 'date':
					$xml->startElement($parameter['name']);
						if ($parameter['tokens']['c']) $xml->writeAttribute('century',$parameter['tokens']['c']);
						if ($parameter['tokens']['y']) $xml->writeAttribute('year',$parameter['tokens']['y']);
						if ($parameter['tokens']['m']) $xml->writeAttribute('month',$parameter['tokens']['m']);
						if ($parameter['tokens']['d']) $xml->writeAttribute('day',$parameter['tokens']['d']);
						$xml->text($parameter['value']);
					$xml->endElement();
				break;
				case 'country':
					$xml->startElement($parameter['name']);
						if (count($parameter['tokens']) > 0) $xml->writeAttribute('tokens',count($parameter['tokens']));
						$xml->text($parameter['value']);
					$xml->endElement();
				break;
				case 'char':
					$xml->startElement($parameter['name']);
						$xml->text($parameter['value']);
					$xml->endElement();
				break;
			}
			
		}
	$xml->endElement();
		$xml->startElement('queries');
		$xml->writeAttribute('criticalMass',LIMIT_MATCHES);
		$xml->writeAttribute('highestScore',reset($personScores));
		$xml->writeAttribute('optimalScore',$optimalScore);
		$xml->writeAttribute('perfectScore',$perfectScore);
		$xml->writeAttribute('requestQuality',$requestQuality.'%');
		foreach ($parameters as $parameterKey => $parameter) {
			if (count($parameter['queries']) > 0) {
				$xml->startElement('query');
					$xml->writeAttribute('parameter',$parameter['name']);
					$xml->writeAttribute('score',$parameter['score']);
					$xml->writeAttribute('classification',$parameter['classification']);
					$xml->writeAttribute('matches',$parameter['matches']);
					/*
					foreach ($parameter['queries'] as $queryKey => $query) {
						$xml->startElement('queryPart');
						$xml->text($query);
						$xml->endElement();
					}
					*/
				$xml->endElement();
			}
		}
	$xml->endElement();
	
	reset($personScores);
	$xml->startElement('results');
		$xml->writeAttribute('count',count($personScores));
		$counter = 0;
		while (list($key,$value) = each($personScores)) {
			$counter++;
			$relativeValue = floor(100 * $value / $optimalScore);
			if ($counter <= MIN_PERSONS || ($counter <= MAX_PERSONS && $value >= MIN_SCORE)) {
				$xml->startElement('match');
					$xml->writeAttribute('score',$value);
					$xml->writeAttribute('optimal',$relativeValue.'%');
					$xml->writeAttribute('perfect',floor($relativeValue * $requestQuality / 100).'%');
					// person
					$xml->startElement('person');
						// wikipedia person data
						$xml->writeElement('name',$persons[$key]->name);
						$xml->writeElement('otherNames',$persons[$key]->altname);
						$xml->startElement('dateOfBirth');
							$when = '';
							$when .= sprintf('%04d',$persons[$key]->b_year);
							$when .= '-'.sprintf('%02d',$persons[$key]->b_month);
							$when .= '-'.sprintf('%02d',$persons[$key]->b_day);
							$xml->writeAttribute('when',$when);
							$xml->text($persons[$key]->born);
						$xml->endElement();
						$xml->writeElement('placeOfBirth',$persons[$key]->b_place);
						$xml->startElement('dateOfDeath');
							$when = '';
							$when .= sprintf('%04d',$persons[$key]->d_year);
							$when .= '-'.sprintf('%02d',$persons[$key]->d_month);
							$when .= '-'.sprintf('%02d',$persons[$key]->d_day);
							$xml->writeAttribute('when',$when);
							$xml->text($persons[$key]->died);
						$xml->endElement();
						$xml->writeElement('placeOfDeath',$persons[$key]->d_place);
						$xml->writeElement('description',$persons[$key]->description);
						// TODO hier noch GENDER und COUNTRY einbinden!
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
							if ($normdaten['gnd']) {
								$xml->startElement('personId');
									$xml->writeAttribute('provider','GND');
									$xml->writeAttribute('url','http://d-nb.info/gnd/'.$normdaten['gnd']);
									$xml->text($normdaten['gnd']);
								$xml->endElement();
							}
							elseif ($normdaten['pnd']) {
								// wenn keine GND vorhanden ist, gilt: GND = PND
								$xml->startElement('personId');
									$xml->writeAttribute('provider','GND');
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
			} // if
		} // while
	$xml->endElement(); // results
$xml->endElement();

?>
