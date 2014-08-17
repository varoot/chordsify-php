<?php
namespace Chordsify;

class WriterHTML extends Writer
{
    public $options = [
        'sections'  => true,
        'chords'    => true,
        'formatted' => true,  // make curly quotes
    ];

    // Config for HTML
    public static $classes = array(
        'chord'       => 'chordsify-chord',
        'chordAnchor' => 'chordsify-chord-anchor',
        'chordRoot'   => 'chordsify-chord-inner',
        'gap'         => 'chordsify-gap',
        'gapDash'     => 'chordsify-gap-dash',
        'line'        => 'chordsify-line',
        'lyrics'      => 'chordsify-lyrics',
        'noChords'    => 'chordsify-no-chords',
        'paragraph'   => 'chordsify-paragraph',
        'section'     => 'chordsify-section',
        'song'        => 'chordsify',
        'word'        => 'chordsify-word',
    );

    public static $data_attr = array(
        'sectionType'  => 'data-section-type',
        'sectionNum'   => 'data-section-num',
        'chord'        => 'data-chord',
        'chordRel'     => 'data-chord-rel',
        'originalKey'  => 'data-original-key',
        'transposeKey' => 'data-transpose-to',
    );

    public static $elements = array(
        'chord'       => 'sup',
        'chordAnchor' => 'span',
        'chordRoot'   => 'span',
        'line'        => 'div',
        'lyrics'      => 'span',
        'paragraph'   => 'div',
        'section'     => 'div',
        'song'        => 'div',
        'word'        => 'span',
    );

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function song(Song $song, array $sections)
    {
        return self::element('song', $sections);
    }

    public function section(Section $section, array $paragraphs)
    {
        return self::element(
            'section',
            $paragraphs,
            [
                'sectionType' => $section->type,
                'sectionNum'  => $section->number > 0 ? $section->number : '',
            ]
        );
    }

    public function html_after(array $options = [])
    {
        return Config::tagClose('section');
    }

    public function paragraph(Paragraph $paragraph, array $lines)
    {
        return self::element(
            'paragraph',
            $lines,
            [],
            $paragraph->chord_exists ? [] : ['class' => self::$classes['noChords']]
        );
    }

    public function line(Line $line, array $words)
    {
        return self::element('line', $words);
    }

    public function word(Word $word, array $chunks)
    {
        return self::element('word', $chunks);
    }

    public function chunk(Chunk $chunk, $chord, $lyrics)
    {
        if ( ! $this->options['chords'] or ! $chord)
            return $lyrics;

        return $chord.$lyrics;
    }

    public function chord(Chord $chord, array $chordElements)
    {
        $data = [];

        if ($chord->main_root) {
            $data['chord'] = Key::value($chord->main_root->root);
        }

        return self::element(
            'chordAnchor',
            self::element('chord', $chordElements, $data)
        );
    }

    public function chordRoot(ChordRoot $chordRoot)
    {
        return self::element(
            'chordRoot',
            $chordRoot->root,
            [ 'chordRel' => $chordRoot->relative_root ]
        );
    }

    public function chordText(ChordText $chordText)
    {
        return htmlspecialchars($chordText->content);
    }

    public function lyrics(Lyrics $lyrics)
    {
        if ($lyrics->content === '')
            return ' '; // A space is needed for chords to be on top

        // Styling text
        if ($this->options['formatted'])
        {
            $content = $lyrics->formatted_content();
        }
        else
        {
            $content = $lyrics->content;
        }

        return self::element('lyrics', $content);
    }

    protected static function tag($name, $content, array $attr = [])
    {
        $attrs = '';
        if ( ! empty($attr)) {
            foreach ($attr as $key => $value) {
                // Skip empty values
                if ($value === '' or $value === null)
                    continue;

                $attrs .= " $key=\"$value\"";
            }
        }

        if (is_array($content)) {
            $content = implode($content);
        }

        return "<$name$attrs>$content</$name>";
    }

    protected static function element($unit, $content, array $data_attr = [], array $attr = [])
    {
        if (array_key_exists($unit, self::$classes)) {
            if (! empty($attr['class'])) {
                $attr['class'] .= ' '.self::$classes[$unit];
            } else {
                $attr['class'] = self::$classes[$unit];
            }
        }

        foreach ($data_attr as $key => $value) {
            $attr[self::$data_attr[$key]] = $value;
        }

        return self::tag(self::$elements[$unit], $content, $attr);
    }
}
