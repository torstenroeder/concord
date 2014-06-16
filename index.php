<?php
require_once '../setup/ini.php';

function getUrlParameter ($parameter_name, $default_value = NULL) {
	// getUrlParameter ( parameter_name (, default_value) )
	return ((isset($_GET[$parameter_name]) && $_GET[$parameter_name]!='')?utf8_encode($_GET[$parameter_name]):$default_value);
}

function wiki_search (&$scores, $parameters, $value) {
	$parameters = str_replace(' ','+',$parameters);
	$pd_url = 'http://toolserver.org/~apper/pd/index.php?'.$parameters;
	$pd_html = file_get_contents($pd_url);
	$pd_matches = array();
	preg_match_all ('/\<a\ href\=\"\/\~apper\/pd\/person\/([^"]+)\"\>Mehr\ Informationen\<\/a\>/', $pd_html, $pd_matches, PREG_OFFSET_CAPTURE);
	while (list($count, $pd_match) = each($pd_matches[1])) {
		if (!isset($scores[$pd_match[0]]))
			$scores[$pd_match[0]] = 0;
		$scores[$pd_match[0]] += $value;
	}
}

function fetch_pnd ($wiki_name) {
	$url = 'http://toolserver.org/~apper/pd/person/'.urlencode($wiki_name);
	$html = file_get_contents($url);
	$matches = array();
	preg_match ('/PND\:\ \<a\ href\=\"(http\:\/\/d\-nb\.info\/gnd\/[0-9X]+)\"\>([0-9X]+)\<\/a\>/', $html, $matches, PREG_OFFSET_CAPTURE);
	if (count($matches) > 0) {
		return $matches[2][0];
	}
	else return NULL;
}

// -----------------------------------------------------------------------------

$url = parse_url($_SERVER['REQUEST_URI']);

if (isset($url['query'])) {
	$name = getUrlParameter ('n',NULL);
	$othernames = getUrlParameter ('on',NULL);
	$gebdat = getUrlParameter ('dob',NULL);
	$gebort = getUrlParameter ('pob',NULL);
	$stbdat = getUrlParameter ('dod',NULL);
	$stbort = getUrlParameter ('pod',NULL);
	$description = getUrlParameter ('desc',NULL);
	
	// Parameter für Wikipedia-Personensuche zusammenstellen
	
	// (1) voller Name
	if ($name && $othernames) {
		$parameters_1 = 'name='.$name.' '.$othernames;
	}
	
	// (2) Nachname und Lebensspanne
	if ($name && ($gebdat || $stbdat)) {
		$parameters_2 = 'name='.$name;
		if ($gebdat) $parameters_2 .= '&geb_jahr1='.substr ($gebdat,0,4);
		if ($stbdat) $parameters_2 .= '&st_jahr1='.substr ($stbdat,0,4);
	}
	
	// (3) exakte Geburtsdaten
	if ($gebdat && $gebort) {
		$parameters_3 = 'geb_ort='.$gebort;
		switch (strlen($gebdat)) {
			case 4:
				$parameters_3 .= '&geb_jahr1='.substr ($gebdat,0,4);
				break;
			case 7:
				$parameters_3 .= '&geb_jahr1='.substr ($gebdat,0,4);
				$parameters_3 .= '&geb_monat1='.substr ($gebdat,5,2);
				break;
			case 10:
				$parameters_3 .= '&geb_jahr1='.substr ($gebdat,0,4);
				$parameters_3 .= '&geb_monat1='.substr ($gebdat,5,2);
				$parameters_3 .= '&geb_tag1='.substr ($gebdat,8,2);
				break;
		}
	}
	
	// (4) exakte Sterbedaten
	if ($stbdat && $stbort) {
		$parameters_4 = 'st_ort='.$stbort;
		switch (strlen($stbdat)) {
			case 4:
				$parameters_4 .= '&st_jahr1='.substr ($stbdat,0,4);
				break;
			case 7:
				$parameters_4 .= '&st_jahr1='.substr ($stbdat,0,4);
				$parameters_4 .= '&st_monat1='.substr ($stbdat,5,2);
				break;
			case 10:
				$parameters_4 .= '&st_jahr1='.substr ($stbdat,0,4);
				$parameters_4 .= '&st_monat1='.substr ($stbdat,5,2);
				$parameters_4 .= '&st_tag1='.substr ($stbdat,8,2);
				break;
		}
	}
	
	// (5) Nachname und Beschreibung
	if ($name && $description) {
		$parameters_5 = 'name='.$name;
		$parameters_5 .= '&desc='.$description;
	}
	
	$scores = array();
	if (isset($parameters_1)) wiki_search ($scores, $parameters_1, 40);
	if (isset($parameters_2)) wiki_search ($scores, $parameters_2, 30);
	if (isset($parameters_3)) wiki_search ($scores, $parameters_3, 15);
	if (isset($parameters_4)) wiki_search ($scores, $parameters_4, 15);
	if (isset($parameters_5)) wiki_search ($scores, $parameters_5, 20);
	
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
		$xml->writeAttribute('version','1.0');
		$xml->startElement('request');
			$xml->writeElement('name',$name);
			$xml->writeElement('othernames',$othernames);
			$xml->writeElement('gebdat',$gebdat);
			$xml->writeElement('gebort',$gebort);
			$xml->writeElement('stbdat',$stbdat);
			$xml->writeElement('stbort',$stbort);
			$xml->writeElement('description',$description);
		$xml->endElement();
		reset($scores);
		$xml->startElement('results');
			$xml->writeAttribute('count',count($scores));
			$xml->startElement('queries');
				if (isset($parameters_1)) $xml->writeElement('query',$parameters_1);
				if (isset($parameters_2)) $xml->writeElement('query',$parameters_2);
				if (isset($parameters_3)) $xml->writeElement('query',$parameters_3);
				if (isset($parameters_4)) $xml->writeElement('query',$parameters_4);
				if (isset($parameters_5)) $xml->writeElement('query',$parameters_5);
			$xml->endElement();
			$counter = 0;
			while (list($key,$value) = each($scores)) {
				$counter++;
				if ($counter <= 5 || ($counter <=10 && $value >= 40)) {
					$xml->startElement('result');
						$xml->writeAttribute('score',$value);
						$xml->startElement('id');
							$xml->writeAttribute('provider','Wikipedia Personensuche');
							$xml->writeAttribute('url','http://toolserver.org/~apper/pd/person/'.$key);
							$xml->text($key);
						$xml->endElement();
						if ($pnd = fetch_pnd($key)) {
							$xml->startElement('id');
								$xml->writeAttribute('provider','PND');
								$xml->writeAttribute('url','http://d-nb.info/gnd/'.$pnd);
								$xml->text($pnd);
							$xml->endElement();
						}
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
