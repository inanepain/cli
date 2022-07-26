<?php

use Inane\Cli\TextTable\DefinitionRule;

require_once 'common.php';
/*
 * Please notice that the data has to be an 0-based array,
 * not an associative one if you intend to work with custom
 * column widths.
 *
 * Table builds column definition dynamically.
 * 1: tables uses definition that fits all cells
 * 2: cells grow to fit text (not pretty)
 * 3: text is truncated to fit into cell, indicated by >
 * 4: tables grows column to fit text up to definition then starts truncating
 *
 */
$headers = ['First Name', 'Last Name', 'City', 'State'];
$data = [
	['Maryam',   'Elliott',    'Elizabeth City',   'SD'],
	['Jerry',    'Washington', 'Bessemer',         'ME'],
	['Allegra',  'Hopkins',    'Altoona',          'ME'],
	['Audrey',   'Oneil',      'Dalton',           'SK'],
	['Ruth',     'Mcpherson',  'San Francisco',    'ID'],
	['Odessa',   'Tate',       'Chattanooga',      'FL'],
	['Violet',   'Nielsen',    'Valdosta',         'AB'],
	['Summer',   'Rollins',    'Revere',           'SK'],
	['Mufutau',  'Bowers',     'Scottsbluff',      'WI'],
	['Grace',    'Rosario',    'Garden Grove',     'KY'],
	['Amanda',   'Berry',      'La Habra',         'AZ'],
	['Cassady',  'York',       'Fulton',           'BC'],
	['Heather',  'Terrell',    'Statesboro',       'SC'],
	['Dominic',  'Jimenez',    'West Valley City', 'ME'],
	['Rhonda',   'Potter',     'Racine',           'BC'],
	['Nathan',   'Velazquez',  'Cedarburg',        'BC'],
	['Richard',  'Fletcher',   'Corpus Christi',   'BC'],
	['Cheyenne', 'Rios',       'Broken Arrow',     'VA'],
	['Velma',    'Clemons',    'Helena',           'IL'],
	['Samuel',   'Berry',      'Lawrenceville',    'NU'],
	['Marcia',   'Swanson',    'Fontana',          'QC'],
	['Zachary',  'Silva',      'Port Washington',  'MB'],
	['Hilary',   'Chambers',   'Suffolk',          'HI'],
	['Idola',    'Carroll',    'West Sacramento',  'QC'],
	['Kirestin', 'Stephens',   'Fitchburg',        'AB'],
];

$table = new \Inane\Cli\TextTable();

// 1
// $table->setColumnDefinition([10, 10, 20, 10]);
// 2
// $table->setColumnDefinition([10, 10, 10, 5]);
// 3
// $table->setDefinitionRule(DefinitionRule::Truncate);
// 4
// $table->setDefinitionRule(DefinitionRule::Max);

$table->addHeader($headers);
$table->addRows($data);
echo $table->render();
