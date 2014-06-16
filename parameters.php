<?php

$parameters = array(
	// names
	'n'		=> array( 'name' => 'name',					'type' => 'string',		'table' => 'pd',	'score' => 60,	'classification' => NULL,			'description' => 'principal name (surname, common forenames, an official name)' ),
	'on'	=> array( 'name' => 'otherNames',			'type' => 'string',		'table' => 'pd',	'score' => 30,	'classification' => NULL,			'description' => 'other names (forenames, less official names, uncommon names)' ),
	'd'		=> array( 'name' => 'description',			'type' => 'string',		'table' => 'pd',	'score' => 30,	'classification' => NULL,			'description' => 'description (keywords as occupation, ethnicity, title or rank)' ),

	'db'	=> array( 'name' => 'dateOfBirth',			'type' => 'date',		'table' => 'pd',	'score' => 30,	'classification' => NULL,			'description' => 'date of birth¹' ),
	'pb'	=> array( 'name' => 'placeOfBirth',			'type' => 'string',		'table' => 'pd',	'score' => 12,	'classification' => NULL,			'description' => 'place of birth' ),

	'dd'	=> array( 'name' => 'dateOfDeath',			'type' => 'date',		'table' => 'pd',	'score' => 48,	'classification' => NULL,			'description' => 'date of death¹' ),
	'pd'	=> array( 'name' => 'placeOfDeath',			'type' => 'string',		'table' => 'pd',	'score' => 24,	'classification' => NULL,			'description' => 'place of death' ),

	'g'		=> array( 'name' => 'gender',				'type' => 'char',		'table' => 'sex',	'score' => 60,	'classification' => 'secondary',	'description' => 'gender (m or f)' ),
	'ya'	=> array( 'name' => 'yearOfActivity',		'type' => 'date',		'table' => 'pd',	'score' => 24,	'classification' => 'secondary',	'description' => 'year of activity (YYYY) = a year between birth and death' ),
	'ca'	=> array( 'name' => 'countryOfActivity',	'type' => 'country',	'table' => 'pd',	'score' => 12,	'classification' => 'secondary',	'description' => 'country of activity²' )
);

foreach ($parameters as $parameterKey => $parameter) {
	$parameters[$parameterKey]['value'] = NULL;
	$parameters[$parameterKey]['tokens'] = NULL;
	$parameters[$parameterKey]['queries'] = NULL;
	$parameters[$parameterKey]['matches'] = NULL;
}

?>
