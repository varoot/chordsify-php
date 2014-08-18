<?php
namespace Chordsify;

abstract class Writer
{
    protected $isFlatScale = false;

    abstract public function song(Song $song, array $sections);
    abstract public function section(Section $section, array $paragraphs);
    abstract public function paragraph(Paragraph $paragraph, array $lines);
    abstract public function line(Line $line, array $words);
    abstract public function word(Word $word, array $chunks);
    abstract public function chunk(Chunk $chunk, $chord, $lyrics);
    abstract public function chord(Chord $chord, array $chordElements);
    abstract public function chordRoot(ChordRoot $chordRoot);
    abstract public function chordText(ChordText $chordText);
    abstract public function lyrics(Lyrics $lyrics);

    // Init functions

    // If an init function returns false
    // That element and its child will not be processed

    public function initSong(Song $song) {
        if ($song->originalKey())
        {
            $this->isFlatScale = $song->originalKey()->isFlatScale();
        }
    }

    public function initSection(Section $section) {}
    public function initParagraph(Paragraph $paragraph) {}
    public function initLine(Line $line) {}
    public function initWord(Word $word) {}
    public function initChunk(Chunk $chunk) {}
    public function initChord(Chord $chord) {}
    public function initChordRoot(ChordRoot $chordRoot) {}
    public function initChordText(ChordText $chordText) {}
    public function initLyrics(Lyrics $lyrics) {}
}
