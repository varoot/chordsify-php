<?php
namespace Chordsify;

interface Writer
{
    public function song(Song $song, array $sections);
    public function section(Section $section, array $paragraphs);
    public function paragraph(Paragraph $paragraph, array $lines);
    public function line(Line $line, array $words);
    public function word(Word $word, array $chunks);
    public function chunk(Chunk $chunk, $chord, $lyrics);
    public function chord(Chord $chord, array $chordElements);
    public function chordRoot(ChordRoot $chordRoot);
    public function chordText(ChordText $chordText);
    public function lyrics(Lyrics $lyrics);
}
