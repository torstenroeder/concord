<?php

$get = array();

$get['name'] = getUrlParameter ('n',NULL);
$get['nameParts'] = explode(' ',$get['name']);

$get['otherNames'] = getUrlParameter ('on',NULL);
$get['otherNameParts'] = explode(' ',$get['otherNames']);

$get['description'] = getUrlParameter ('d',NULL);
$get['descriptionParts'] = explode(' ',$get['description']);

$get['placeOfBirth'] = getUrlParameter ('pb',NULL);

$get['placeOfDeath'] = getUrlParameter ('pd',NULL);

$get['dateOfBirth'] = getUrlParameter ('db',NULL);
if ($get['dateOfBirth']){
	$get['dateOfBirth'] = str_replace('-','',$get['dateOfBirth']);
	switch (strlen($get['dateOfBirth'])) {
		case 4:
			$get['yearOfBirth'] = substr ($get['dateOfBirth'],0,4);
			$get['monthOfBirth'] = NULL;
			$get['dayOfBirth'] = NULL;
			break;
		case 6:
			$get['yearOfBirth'] = substr ($get['dateOfBirth'],0,4);
			$get['monthOfBirth'] = substr ($get['dateOfBirth'],4,2);
			$get['dayOfBirth'] = NULL;
			break;
		case 8:
			$get['yearOfBirth'] = substr ($get['dateOfBirth'],0,4);
			$get['monthOfBirth'] = substr ($get['dateOfBirth'],4,2);
			$get['dayOfBirth'] = substr ($get['dateOfBirth'],6,2);
			break;
		default:
			// invalid
			$get['dateOfBirth'] = NULL;
			$get['yearOfBirth'] = NULL;
			$get['monthOfBirth'] = NULL;
			$get['dayOfBirth'] = NULL;
			break;
	}
}
else {
	$get['yearOfBirth'] = NULL;
	$get['monthOfBirth'] = NULL;
	$get['dayOfBirth'] = NULL;
}

$get['dateOfDeath'] = getUrlParameter ('dd',NULL);
if ($get['dateOfDeath']){
	$get['dateOfDeath'] = str_replace('-','',$get['dateOfDeath']);
	switch (strlen($get['dateOfDeath'])) {
		case 4:
			$get['yearOfDeath'] = substr ($get['dateOfDeath'],0,4);
			$get['monthOfDeath'] = NULL;
			$get['dayOfDeath'] = NULL;
			break;
		case 6:
			$get['yearOfDeath'] = substr ($get['dateOfDeath'],0,4);
			$get['monthOfDeath'] = substr ($get['dateOfDeath'],4,2);
			$get['dayOfDeath'] = NULL;
			break;
		case 8:
			$get['yearOfDeath'] = substr ($get['dateOfDeath'],0,4);
			$get['monthOfDeath'] = substr ($get['dateOfDeath'],4,2);
			$get['dayOfDeath'] = substr ($get['dateOfDeath'],6,2);
			break;
		default:
			// invalid
			$get['dateOfDeath'] = NULL;
			$get['yearOfDeath'] = NULL;
			$get['monthOfDeath'] = NULL;
			$get['dayOfDeath'] = NULL;
			break;
	}
}
else {
	$get['yearOfDeath'] = NULL;
	$get['monthOfDeath'] = NULL;
	$get['dayOfDeath'] = NULL;
}

?>
