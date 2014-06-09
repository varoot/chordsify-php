<?php
function __autoload($class_name) {
    include $class_name.'.php';
}

$song = new Chordsify\Song(<<<EOD
	[verse 1]
	[A] Promise maker, promise keeper
	[F#m]You finish wh[D]at You begin [A]
	[A] Our provision through the desert
	[F#m]You see it throu[D]gh 'til the end [A]
	[F#m]You see it throu[D]gh 'til the end [A]

	[chorus]
	[A] The Lord our God [F#m]is ever [D] faithful [A]
	Never changing [F#m] through the [D]ages [A]
	From this darkness [F#m] You will le[D]ad us [A]
	And forever we will [F#m]say
	You're the [D]Lord our G[A]od

	[verse 2]
	In the silence, in the waiting
	Still we can know You are good
	All Your plans are for Your glory
	Yes, we can know You are good
	Yes, we can know You are good

	[bridge]
	[A]We won't move without You
	[F#m]We won't mo[D]ve without You [A]
	You're the light of all and [F#m]all that we nee[D]d
EOD
, array('original_key'=>'A'));

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
	<div class="chordsify-raw"><?= $song->text(array('collapse'=>true, 'chords'=>false, 'sections'=>false)) ?></div>
	<h1>HTML</h1>
	<?= $song->html() ?>
</body>
</html>