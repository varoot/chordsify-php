<?php
function __autoload($class_name)
{
    include $class_name.'.php';
}

include 'vendor/autoload.php';

function load_song($title, $key)
{
    return new Chordsify\Song(
        file_get_contents('chords/'.$title.'.txt'),
        array(
            'title' => $title,
            'original_key' => $key
        )
    );
}

$sheet = new Chordsify\SongSheet();
//$sheet->debug = TRUE;

/*
$sheet->add(load_song('10000 Reasons', 'G'));
$sheet->add(load_song('How Great Is Our God', 'A'));
$sheet->add(load_song('I Could Sing of Your Love Forever', 'E'));
$sheet->add(load_song('Your Presence is Heaven', 'A'));
$sheet->add(load_song('Once Again', 'G'));
*/

/*
$sheet->add(load_song('All Who Are Thirsty', 'E'));
$sheet->add(load_song('Come to the Water', 'G'));
$sheet->add(load_song('Give Thanks', 'G'));
$sheet->add(load_song('The Wonderful Cross', 'D'));
$sheet->add(load_song('The Stand', 'E'));
*/

$sheet->add(load_song('Jesus at the Center', 'E'));
$sheet->add(load_song('All My Fountain', 'D'));
$sheet->add(load_song('Heart of Worship', 'D'));
$sheet->add(load_song('Nothing but the Blood', 'E'));
$sheet->add(load_song('Your Presence is Heaven', 'A'));

if ($sheet->debug)
{
    $sheet->pdfOutput('F');
}
else
{
    $sheet->pdfOutput();
}
