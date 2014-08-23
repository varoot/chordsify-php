<?php
include 'vendor/autoload.php';

$song = new Chordsify\Song(file_get_contents('chords/Your Presence is Heaven.txt'), array('originalKey'=>'auto'));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Chordsify Demo</title>
    <link href="//fonts.googleapis.com/css?family=PT+Sans:400,700" rel="stylesheet" type="text/css">
    <link href="//fonts.googleapis.com/css?family=PT+Serif:400,700" rel="stylesheet" type="text/css">
    <link rel="stylesheet/less" type="text/css" href="chordsify.less">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/less.js/1.6.1/less.min.js"></script>
</head>
<body>
    <h1>Raw</h1>
    <div class="chordsify-raw"><?= $song->text() ?></div>
    <h1>Lyrics Only</h1>
    <div class="chordsify-raw"><?= $song->text(['collapse'=>true, 'chords'=>false, 'sections'=>false]) ?></div>
    <h1>HTML</h1>
    <?= $song->html() ?>
</body>
</html>
