<?php
function __autoload($class_name)
{
	include $class_name.'.php';
}

include 'vendor/autoload.php';

function load_song($title, $key)
{
	return new Chordsify\Song(
		file_get_contents($title.'.txt'),
		array(
			'title' => $title,
			'original_key' => $key
		)
	);
}

$sheet = new Chordsify\SongSheet();

$sheet->add(load_song('10000 Reasons', 'G'));
$sheet->add(load_song('How Great Is Our God', 'A'));
$sheet->add(load_song('I Could Sing of Your Love Forever', 'E'));
$sheet->add(load_song('Your Presence is Heaven', 'A'));
$sheet->add(load_song('Once Again', 'G'));

$sheet->pdf_output('F');