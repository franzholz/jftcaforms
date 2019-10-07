<?php

########################################################################
# Extension Manager/Repository config file for ext "jftcaforms".
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Additional TCA Forms',
	'description' => 'Provides additional TCA Forms for the backend. At this moment these forms are supported: Slider',
	'category' => 'be',
	'shy' => 1,
	'version' => '0.3.0',
	'module' => '',
	'state' => 'experimental',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Franz Holzinger, Juergen Furrer',
	'author_email' => 'franz@ttproducts.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
			'php' => '5.5.0-7.3.0',
			'typo3' => '7.6.0-8.7.99'
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

