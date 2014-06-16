<?php

$parameters = array(
	// names
	'n'		=> array( 'name' => 'name',					'type' => 'string',		'score' => 60, 'description' => 'principal name' ),
	'on'	=> array( 'name' => 'otherNames',			'type' => 'string',		'score' => 30, 'description' => 'other names' ),
	'd'		=> array( 'name' => 'description',			'type' => 'string',		'score' => 30, 'description' => 'description (keywords as occupation, ethnicity, title or rank)' ),
//	'g'		=> array( 'name' => 'gender',				'type' => 'char',		'score' => 60, 'description' => 'gender (m or f)' ),

	'db'	=> array( 'name' => 'dateOfBirth',			'type' => 'date',		'score' => 30, 'description' => 'date of birth (ISO 8601: YYYY-MM-DD, YYYY-MM, YYYY, dashes are optional)' ),
	'pb'	=> array( 'name' => 'placeOfBirth',			'type' => 'string',		'score' => 12, 'description' => 'place of birth' ),

	'dd'	=> array( 'name' => 'dateOfDeath',			'type' => 'date',		'score' => 48, 'description' => 'date of death (ISO 8601: YYYY-MM-DD, YYYY-MM, YYYY, dashes are optional)' ),
	'pd'	=> array( 'name' => 'placeOfDeath',			'type' => 'string',		'score' => 24, 'description' => 'place of death' )

//	'ya'	=> array( 'name' => 'yearOfActivity',		'type' => 'date',		'score' => 24, 'description' => 'year of activity (YYYY) = a year between birth and death' )
//	'ca'	=> array( 'name' => 'countryOfActivity',	'type' => 'country',	'score' => 12, 'description' => 'country of activity (ISO 3-letter-code, comma separated)' )
);

foreach ($parameters as $parameterKey => $parameter) {
	$parameters[$parameterKey]['value'] = NULL;
	$parameters[$parameterKey]['tokens'] = NULL;
	$parameters[$parameterKey]['queries'] = NULL;
	$parameters[$parameterKey]['matches'] = NULL;
}

?>
