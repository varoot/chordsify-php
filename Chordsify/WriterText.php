<?php
namespace Chordsify;

class WriterText extends Writer
{
    public $options = [
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
        if ($this->options['sections'])
        {
            $output = '['.$section->type.($section->number > 0 ? ' '.$section->number : '')."]\n";
        }

        $output .= implode("\n\n", $paragraphs);
        return $output;
    }

    protected static function findCollapse($lines)
    {
        $maxRepeat = $foundLines = $foundTimes = 0;

        for ($r = (int) (count($lines)/2); $r > 0; $r--) {
            // Testing for $r-line repeat, e.g. 2-line repeat (AABAAB), 2-line repeat (ABABC), etc.
            // Find how many times it repeats first $r lines.
            for ($times = 1; $times < (int) (count($lines) / $r); $times++) {
                // Check all the lines to make sure it's repeated
                $repeat = true;
                for ($i = 0; $i < $r; $i++) {
                    if ($lines[$i] != $lines[$i+($r*$times)]) {
                        $repeat = false;
                        break;
                    }
                }

                // Not all lines are repeated
                if ( ! $repeat)
                    break;
            }

            if ($times > 1 and $r*$times >= $maxRepeat) {
                $foundLines = $r;
                $foundTimes = $times;
                $maxRepeat = $r*$times;
            }
        }

        return array($foundLines, $foundTimes);
    }

    public function paragraph(Paragraph $paragraph, array $lines)
    {
        $lines = array_filter($lines);

        if (count($lines) == 0 or ! $this->options['collapse'])
            return implode("\n", $lines);

        list($repeat, $times) = self::findCollapse($lines);
        $saves = $repeat * ($times-1);

        if ($saves >= $this->options['collapse']) {
            array_splice($lines, $repeat, $saves);
            $lines[$repeat-1] .= " (×$times)";
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
        return $chordRoot->root->text($this->isFlatScale);
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
