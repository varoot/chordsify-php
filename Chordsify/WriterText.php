<?php
namespace Chordsify;

class WriterText extends Writer
{
    protected $options = [
        'sections'  => true,
        'chords'    => true,
        'collapse'  => 0,     // 0 = No collapse, 1 = Always collapse, 2+ = Collapse if saves n lines
        'formatted' => true,  // make curly quotes
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function song(Song $song, array $sections)
    {
        return implode("\n\n", $sections);
    }

    public function section(Section $section, array $paragraphs)
    {
        $output = '';
        if ($this->options['sections'] and ! empty($section->type))
        {
            $output = '['.$section->type.($section->number > 0 ? ' '.$section->number : '')."]\n";
        }

        $output .= implode("\n\n", $paragraphs);
        return $output;
    }

    public function paragraph(Paragraph $paragraph, array $lines)
    {
        $lines = array_filter($lines);

        if (count($lines) == 0 or ! $this->options['collapse'])
            return implode("\n", $lines);

        $collapse = $paragraph->collapse();

        if ($collapse->saves >= $this->options['collapse']) {
            array_splice($lines, $collapse->lines, $collapse->saves);
            $lines[$collapse->lines-1] .= " (×{$collapse->times})";
        }

        return implode("\n", $lines);
    }

    public function line(Line $line, array $words)
    {
        return trim(implode($words));
    }

    public function word(Word $word, array $chunks)
    {
        $output = implode($chunks);

        if ($this->options['chords'])
            return $output;

        // Remove the spaces that were only there to separate the chords
        return ltrim($output);
    }

    public function chunk(Chunk $chunk, $chord, $lyrics)
    {
        $output = '';
        if ($chord) {
            $output = '['.$chord.']';
        }
        return $output.$lyrics;
    }

    public function initChord(Chord $chord)
    {
        if ( ! $this->options['chords'])
            return false;
    }

    public function chord(Chord $chord, array $chordElements)
    {
        return implode($chordElements);
    }

    public function chordRoot(ChordRoot $chordRoot)
    {
        return $chordRoot->text();
    }

    public function chordText(ChordText $chordText)
    {
        return $chordText->content;
    }

    public function lyrics(Lyrics $lyrics)
    {
        if ($this->options['formatted'])
            return $lyrics->formattedContent();

        return $lyrics->content;
    }
}
