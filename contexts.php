<?php

$contexts = array(
	// contexts with name
	'allNames' => array(
		'score' => 30,
		'parameters' => array (
			'name','otherNames'
		),
		'description' => 'Concordance of name and other names'
	),
	'nameParts' => array(
		'score' => 15,
		'parameters' => array (
			'nameParts'
		),
		'description' => 'Concordance of all name parts (if more than one)'
	),
	'nameAndLifespan' => array(
		'score' => 30,
		'parameters' => array (
			'name','yearOfBirth','yearOfDeath'
		),
		'description' => 'Concordance of name and year of birth and death'
	),
	'nameAndBirth' => array(
		'score' => 10,
		'parameters' => array (
			'name','placeOfBirth'
		),
		'description' => 'Concordance of name and place of birth'
	),
	'nameAndDeath' => array(
		'score' => 10,
		'parameters' => array (
			'name','placeOfDeath'
		),
		'description' => 'Concordance of name and place of death'
	),
	'nameAndDescription' => array(
		'score' => 20,
		'parameters' => array (
			'name','description'
		),
		'description' => 'Concordance of name and description'
	),
	'otherNameAndDescription' => array(
		'score' => 10,
		'parameters' => array (
			'otherNames','description'
		),
		'description' => 'Concordance of other names and description'
	),
	// contexts without name
	'birthDetails' => array(
		'score' => 12,
		'parameters' => array (
			'dateOfBirth','placeOfBirth'
		),
		'description' => 'Concordance of date and place of birth'
	),
	'deathDetails' => array(
		'score' => 12,
		'parameters' => array (
			'dateOfDeath','placeOfDeath'
		),
		'description' => 'Concordance of date and place of death'
	),
	'birthAndDescription' => array(
		'score' => 10,
		'parameters' => array (
			'dateOfBirth','description'
		),
		'description' => 'Concordance of description and date of birth'
	),
	'deathAndDescription' => array(
		'score' => 10,
		'parameters' => array (
			'dateOfDeath','description'
		),
		'description' => 'Concordance of description and date of death'
	)
);

?>
