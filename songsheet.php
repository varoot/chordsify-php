<?php
include 'vendor/autoload.php';

function loadSong($title, $key)
{
    return new Chordsify\Song(
        file_get_contents('chords/'.$title.'.txt'),
        array(
            'title' => $title,
            'originalKey' => $key
        )
    );
}

$sheet = new Chordsify\SongSheet(['chords'=>true, 'autonumber'=>true]);

$sheet->debug = FALSE;

/*
$sheet->add(loadSong('How Great Is Our God', 'A'));
$sheet->add(loadSong('Once Again', 'G'));
$sheet->add(loadSong('Come to the Water', 'G'));
$sheet->add(loadSong('Give Thanks', 'G'));
$sheet->add(loadSong('The Wonderful Cross', 'D'));
*/

$sheet->add(loadSong('10000 Reasons', 'G'));
$sheet->add(loadSong('I Could Sing of Your Love Forever', 'E'));

$sheet->add(loadSong('All Who Are Thirsty', 'E'));
$sheet->add(loadSong('The Stand', 'E'));

$sheet->add(loadSong('Jesus at the Center', 'E'));
$sheet->add(loadSong('All My Fountain', 'D'));
$sheet->add(loadSong('Heart of Worship', 'D'));
$sheet->add(loadSong('Nothing but the Blood', 'E'));
$sheet->add(loadSong('Your Presence is Heaven', 'A'));
$sheet->add(loadSong('My Redeemer Lives', 'F'));
$sheet->add(loadSong('Trust and Obey', 'F'));

if ($sheet->debug)
{
    $sheet->pdfOutput('F');
}
else
{
    $sheet->pdfOutput();
}
